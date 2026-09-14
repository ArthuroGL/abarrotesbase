<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseLine;
use App\Models\PurchaseReceipt;
use App\Models\PurchaseReceiptLine;
use App\Models\Supplier;
use App\Models\StockItem;
use App\Models\ProductUnit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    private function getOrganizationId(): string
    {
        return auth()->user()->organization_id
            ?? session('current_organization_id')
            ?? DB::table('organizations')->value('id')
            ?? throw new \Exception('No hay una organización activa en el sistema.');
    }

    private function getBranchId(): string
    {
        return session('current_branch_id')
            ?? auth()->user()->branch_id
            ?? DB::table('branches')->where('organization_id', $this->getOrganizationId())->value('id')
            ?? throw new \Exception('No hay una sucursal registrada en el sistema.');
    }

    public function index(Request $request): View
    {
        $search = $request->input('search');
        $status = $request->input('status');

        $purchases = Purchase::with(['supplier', 'creator'])
            ->where('organization_id', $this->getOrganizationId())
            ->where('branch_id', $this->getBranchId())
            ->when($search, function ($q) use ($search) {
                $q->where('purchase_number', 'ilike', "%{$search}%")
                    ->orWhere('supplier_reference', 'ilike', "%{$search}%")
                    ->orWhereHas('supplier', fn($s) => $s->where('business_name', 'ilike', "%{$search}%"));
            })
            ->when($status, fn($q) => $q->where('status', $status))
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('modules.purchases.index', compact('purchases', 'search', 'status'));
    }

    public function create(): View
    {
        $orgId = $this->getOrganizationId();

        $suppliers = Supplier::query()
            ->where('organization_id', $orgId)
            ->where('is_active', true)
            ->orderBy('business_name')
            ->get();

        $stockItems = StockItem::query()
            ->with('product')
            ->where('organization_id', $orgId)
            ->where('is_active', true)
            ->get();

        /*
     * Unidades configuradas específicamente para cada artículo.
     *
     * No debemos cargar todas las ProductUnit de la organización,
     * porque cada ProductUnit pertenece a un stock_item.
     */
        $productUnitsByStockItem = ProductUnit::query()
            ->with('unit')
            ->where('organization_id', $orgId)
            ->where('is_active', true)
            ->where('is_purchase_unit', true)
            ->whereIn('stock_item_id', $stockItems->pluck('id'))
            ->get()
            ->groupBy('stock_item_id');

        return view('modules.purchases.create', compact(
            'suppliers',
            'stockItems',
            'productUnitsByStockItem'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'supplier_id' => 'required|uuid|exists:suppliers,id',
            'supplier_reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.stock_item_id' => 'required|uuid|exists:stock_items,id',
            'items.*.product_unit_id' => 'required|uuid|exists:product_units,id',
            'items.*.quantity' => 'required|numeric|gt:0',
            'items.*.unit_cost' => 'required|numeric|gte:0',
            'items.*.discount' => 'nullable|numeric|gte:0',
            'items.*.tax' => 'nullable|numeric|gte:0',
        ]);

        $purchase = DB::transaction(function () use ($validated) {
            $orgId = $this->getOrganizationId();
            $branchId = $this->getBranchId();

            $subtotal = 0;
            $discountTotal = 0;
            $taxTotal = 0;

            $purchaseNumber = 'OC-' . strtoupper(Str::random(8));

            $purchase = Purchase::create([
                'organization_id' => $orgId,
                'branch_id' => $branchId,
                'supplier_id' => $validated['supplier_id'],
                'purchase_number' => $purchaseNumber,
                'status' => 'approved',
                'supplier_reference' => $validated['supplier_reference'] ?? null,
                'currency_code' => 'MXN',
                'subtotal' => 0,
                'discount_total' => 0,
                'tax_total' => 0,
                'total' => 0,
                'ordered_at' => now(),
                'approved_at' => now(),
                'created_by' => auth()->id(),
                'approved_by' => auth()->id(),
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $index => $item) {
                $qty = (float) $item['quantity'];
                $cost = (float) $item['unit_cost'];
                $disc = (float) ($item['discount'] ?? 0);
                $tax = (float) ($item['tax'] ?? 0);

                $lineSubtotal = ($qty * $cost) - $disc;
                $lineTotal = $lineSubtotal + $tax;

                $subtotal += ($qty * $cost);
                $discountTotal += $disc;
                $taxTotal += $tax;

                $stockItem = StockItem::with('product')->find($item['stock_item_id']);

                PurchaseLine::create([
                    'purchase_id' => $purchase->id,
                    'line_number' => $index + 1,
                    'stock_item_id' => $item['stock_item_id'],
                    'product_unit_id' => $item['product_unit_id'],
                    'description' => $stockItem->product?->name ?? 'Artículo',
                    'ordered_quantity' => $qty,
                    'unit_cost' => $cost,
                    'discount_amount' => $disc,
                    'tax_amount' => $tax,
                    'line_total' => $lineTotal,
                ]);
            }

            $purchase->update([
                'subtotal' => $subtotal,
                'discount_total' => $discountTotal,
                'tax_total' => $taxTotal,
                'total' => ($subtotal - $discountTotal) + $taxTotal,
            ]);

            return $purchase;
        });

        return redirect()->route('purchases.index')->with('success', "Orden de compra {$purchase->purchase_number} registrada correctamente.");
    }

    public function show(Purchase $purchase): View
    {
        $purchase->load(['supplier', 'lines.stockItem.product', 'lines.productUnit', 'creator', 'receipts']);

        return view('modules.purchases.show', compact('purchase'));
    }

    public function receive(Purchase $purchase): RedirectResponse
    {
        if ($purchase->status === 'received') {
            return back()->with('error', 'Esta compra ya fue recibida anteriormente.');
        }

        if ($purchase->status === 'cancelled') {
            return back()->with('error', 'No se puede recibir una compra que ha sido rechazada/cancelada.');
        }

        DB::transaction(function () use ($purchase) {
            $orgId = $purchase->organization_id;
            $branchId = $purchase->branch_id;
            $userId = auth()->id();

            $receipt = PurchaseReceipt::create([
                'organization_id' => $orgId,
                'branch_id' => $branchId,
                'supplier_id' => $purchase->supplier_id,
                'purchase_id' => $purchase->id,
                'receipt_number' => 'REC-' . strtoupper(Str::random(8)),
                'status' => 'confirmed',
                'received_at' => now(),
                'received_by' => $userId,
                'notes' => 'Recepción directa de O.C. ' . $purchase->purchase_number,
            ]);

            foreach ($purchase->lines as $line) {
                $unit = ProductUnit::find($line->product_unit_id);
                $conversionFactor = (float) ($unit?->conversion_factor ?? 1.0);

                $baseQuantity = (float) $line->ordered_quantity * $conversionFactor;
                $unitCostBase = (float) $line->unit_cost / ($conversionFactor > 0 ? $conversionFactor : 1);

                PurchaseReceiptLine::create([
                    'purchase_receipt_id' => $receipt->id,
                    'purchase_line_id' => $line->id,
                    'stock_item_id' => $line->stock_item_id,
                    'product_unit_id' => $line->product_unit_id,
                    'received_quantity' => $line->ordered_quantity,
                    'conversion_factor' => $conversionFactor,
                    'unit_cost' => $line->unit_cost,
                    'line_total' => $line->line_total,
                ]);

                // 1. Registro del movimiento en Kardex
                DB::table('inventory_movements')->insert([
                    'id' => (string) Str::uuid(),
                    'organization_id' => $orgId,
                    'branch_id' => $branchId,
                    'stock_item_id' => $line->stock_item_id,
                    'movement_type' => 'purchase_receipt',
                    'quantity_delta' => $baseQuantity,
                    'unit_cost' => $unitCostBase,
                    'total_cost' => $line->line_total,
                    'source_type' => 'purchase_receipts',
                    'source_id' => $receipt->id,
                    'reason_code' => 'PURCHASE_RECEIPT',
                    'notes' => 'Entrada por compra O.C. ' . $purchase->purchase_number,
                    'created_by' => $userId,
                    'occurred_at' => now(),
                ]);

                // 2. Balance de inventario (llave primaria compuesta: org_id, branch_id, stock_item_id)
                $balance = DB::table('inventory_balances')
                    ->where('organization_id', $orgId)
                    ->where('branch_id', $branchId)
                    ->where('stock_item_id', $line->stock_item_id)
                    ->first();

                if ($balance) {
                    $currentOnHand = (float) $balance->on_hand_quantity;
                    $currentCost = (float) $balance->weighted_average_cost;
                    $newOnHand = $currentOnHand + $baseQuantity;

                    $newWeightedCost = $newOnHand > 0
                        ? (($currentOnHand * $currentCost) + ($baseQuantity * $unitCostBase)) / $newOnHand
                        : $unitCostBase;

                    DB::table('inventory_balances')
                        ->where('organization_id', $orgId)
                        ->where('branch_id', $branchId)
                        ->where('stock_item_id', $line->stock_item_id)
                        ->update([
                            'on_hand_quantity' => $newOnHand,
                            'weighted_average_cost' => $newWeightedCost,
                            'version' => DB::raw('version + 1'),
                            'updated_at' => now(),
                        ]);
                } else {
                    // FIX: Sin columna 'id' ya que usa PK compuesta
                    DB::table('inventory_balances')->insert([
                        'organization_id' => $orgId,
                        'branch_id' => $branchId,
                        'stock_item_id' => $line->stock_item_id,
                        'on_hand_quantity' => $baseQuantity,
                        'reserved_quantity' => 0,
                        'weighted_average_cost' => $unitCostBase,
                        'version' => 1,
                        'updated_at' => now(),
                    ]);
                }
            }

            $purchase->update([
                'status' => 'received',
            ]);
        });

        return back()->with('success', '¡Mercancía recibida e ingresada al inventario exitosamente!');
    }

    public function cancel(Request $request, Purchase $purchase): RedirectResponse
    {
        if ($purchase->status === 'received') {
            return back()->with('error', 'No se puede rechazar/cancelar una compra que ya fue recibida en almacén.');
        }

        if ($purchase->status === 'cancelled') {
            return back()->with('error', 'Esta orden de compra ya fue cancelada previamente.');
        }

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $purchase->update([
            'status' => 'cancelled',
            'notes' => trim(($purchase->notes ?? '') . "\n\n[CANCELADA/RECHAZADA]: " . ($validated['reason'] ?? 'Sin motivo especificado')),
        ]);

        return back()->with('success', "La orden de compra {$purchase->purchase_number} ha sido rechazada/cancelada.");
    }
}
