<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\StockItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class StockController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $categoryId = $request->input('category_id');
        $stockStatus = $request->input('status');

        $orgId = DB::table('organizations')->value('id');
        $branchId = session('current_branch_id')
            ?? DB::table('branches')->where('organization_id', $orgId)->value('id');

        $query = StockItem::query()
            ->where('stock_items.organization_id', $orgId)
            ->where('stock_items.is_active', true)
            ->with([
                'product.category',
                'product.brand',
                'inventoryUnit',
                'barcodes' => fn($b) => $b->where('is_active', true),
                'inventoryBalance' => fn($q) => $q->where('branch_id', $branchId),
                'reorderLevel' => fn($q) => $q->where('branch_id', $branchId),
            ]);

        if ($search) {
            $query->whereHas('product', function ($p) use ($search) {
                $p->where('name', 'ilike', "%{$search}%")
                  ->orWhere('sku', 'ilike', "%{$search}%");
            })->orWhereHas('barcodes', function ($b) use ($search) {
                $b->where('barcode', 'ilike', "%{$search}%");
            });
        }

        if ($categoryId) {
            $query->whereHas('product', fn($p) => $p->where('category_id', $categoryId));
        }

        $items = $query->paginate(15)->withQueryString();
        $categories = Category::query()->where('is_active', true)->orderBy('name')->get();

        return view('modules.inventory.stock.index', compact('items', 'categories', 'search', 'categoryId', 'stockStatus'));
    }

    public function adjust(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'stock_item_id' => ['required', 'uuid', 'exists:stock_items,id'],
            'movement_type' => ['required', 'string', 'in:initial_load,adjustment_in,adjustment_out'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $orgId = DB::table('organizations')->value('id');
        $branchId = session('current_branch_id')
            ?? DB::table('branches')->where('organization_id', $orgId)->value('id');

        DB::transaction(function () use ($validated, $orgId, $branchId) {
            $qtyDelta = $validated['movement_type'] === 'adjustment_out'
                ? -abs((float) $validated['quantity'])
                : abs((float) $validated['quantity']);

            $cost = (float) ($validated['unit_cost'] ?? 0);

            // 1. Registrar Movimiento Kardex
            InventoryMovement::create([
                'organization_id' => $orgId,
                'branch_id' => $branchId,
                'stock_item_id' => $validated['stock_item_id'],
                'movement_type' => $validated['movement_type'],
                'quantity_delta' => $qtyDelta,
                'unit_cost' => $cost,
                'total_cost' => abs($qtyDelta * $cost),
                'source_type' => 'MANUAL_ADJUSTMENT',
                'source_id' => null,
                'reason_code' => $validated['movement_type'],
                'notes' => $validated['notes'] ?? 'Ajuste manual de existencias',
                'created_by' => auth()->id(),
                'occurred_at' => now(),
            ]);

            // 2. Actualizar o Crear Balance de Inventario sin referencias a timestamps
            $balance = DB::table('inventory_balances')
                ->where('organization_id', $orgId)
                ->where('branch_id', $branchId)
                ->where('stock_item_id', $validated['stock_item_id'])
                ->first();

            if ($balance) {
                $newOnHand = max(0, (float) $balance->on_hand_quantity + $qtyDelta);
                DB::table('inventory_balances')
                    ->where('organization_id', $orgId)
                    ->where('branch_id', $branchId)
                    ->where('stock_item_id', $validated['stock_item_id'])
                    ->update([
                        'on_hand_quantity' => $newOnHand,
                        'weighted_average_cost' => $cost > 0 ? $cost : $balance->weighted_average_cost,
                        'version' => ((int) $balance->version) + 1,
                    ]);
            } else {
                DB::table('inventory_balances')->insert([
                    'organization_id' => $orgId,
                    'branch_id' => $branchId,
                    'stock_item_id' => $validated['stock_item_id'],
                    'on_hand_quantity' => max(0, $qtyDelta),
                    'reserved_quantity' => 0,
                    'weighted_average_cost' => $cost,
                    'version' => 1,
                ]);
            }
        });

        return redirect()->route('stock.index')->with('status', 'Existencia actualizada correctamente.');
    }
}
