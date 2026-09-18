<?php

declare(strict_types=1);

namespace App\Modules\Operation\Presentation\Http\Controllers;

use App\Modules\Identity\Infrastructure\Persistence\Models\Branch;
use App\Modules\Operation\Infrastructure\Persistence\Models\CashMovement;
use App\Modules\Operation\Infrastructure\Persistence\Models\CashSession;
use App\Modules\Operation\Infrastructure\Persistence\Models\Sale;
use App\Modules\Operation\Infrastructure\Persistence\Models\SaleReturn;
use App\Modules\Operation\Infrastructure\Persistence\Models\SaleReturnLine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class SaleReturnController
{
    public function index(Request $request)
{
    $saleNumber = trim((string) $request->query('sale'));

    if ($saleNumber === '') {
        return view('modules.operation.returns.index');
    }

    $user = $request->user();

    $sale = Sale::query()
        ->where('organization_id', $user->organization_id)
        ->where('sale_number', $saleNumber)
        ->first();

    if (!$sale) {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'No se encontró una venta con ese folio.',
            ], 404);
        }

        return back()
            ->withInput()
            ->with('error', 'No se encontró una venta con ese folio.');
    }

    return redirect()->route('returns.show', $sale);
}

    public function show(Request $request, string $sale)
    {
        $user = $request->user();

        $saleModel = Sale::query()
            ->where('organization_id', $user->organization_id)
            ->with([
                'customer',
                'lines.stockItem.product',
                'lines.productUnit',
                'payments.paymentMethod',
            ])
            ->where('id', $sale)
            ->first();

        if (!$saleModel) {
            abort(404, 'La venta no existe.');
        }

        if (!in_array($saleModel->status, [
            'confirmed',
            'partially_returned',
        ], true)) {
            abort(422, 'Esta venta no puede recibir devoluciones.');
        }

        $returnedQuantities = SaleReturnLine::query()
            ->whereHas('saleReturn', function ($query) use ($saleModel) {
                $query
                    ->where('original_sale_id', $saleModel->id)
                    ->where('status', 'confirmed');
            })
            ->select(
                'original_sale_line_id',
                DB::raw('SUM(quantity) as returned_quantity')
            )
            ->groupBy('original_sale_line_id')
            ->pluck('returned_quantity', 'original_sale_line_id');

        $saleModel->lines->each(function ($line) use ($returnedQuantities) {
            $returned = (float) ($returnedQuantities[$line->id] ?? 0);

            $line->returned_quantity = $returned;
            $line->available_return_quantity = max(
                0,
                (float) $line->quantity - $returned
            );
        });

        return view('modules.operation.returns.show', [
            'sale' => $saleModel,
        ]);
    }

    public function store(Request $request, string $sale): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.sale_line_id' => ['required', 'uuid'],
            'lines.*.quantity' => ['required', 'numeric', 'gt:0'],
            'lines.*.inventory_condition' => [
                'required',
                'in:resellable,damaged,discarded',
            ],
            'reason_code' => ['required', 'string', 'max:50'],
        ]);

        try {
            $result = DB::transaction(function () use (
                $validated,
                $sale,
                $user
            ) {
                $saleModel = Sale::query()
                    ->where('organization_id', $user->organization_id)
                    ->lockForUpdate()
                    ->find($sale);

                if (!$saleModel) {
                    throw ValidationException::withMessages([
                        'sale' => 'La venta no existe.',
                    ]);
                }

                if (!in_array($saleModel->status, [
                    'confirmed',
                    'partially_returned',
                ], true)) {
                    throw ValidationException::withMessages([
                        'sale' => 'Esta venta no puede recibir devoluciones.',
                    ]);
                }

                $saleModel->load([
                    'lines',
                    'payments.paymentMethod',
                ]);

                $saleLines = $saleModel->lines
                    ->keyBy('id');

                $returnNumber = $this->generateReturnNumber(
                    $user->organization_id
                );

                $return = SaleReturn::create([
                    'organization_id' => $user->organization_id,
                    'branch_id' => $saleModel->branch_id,
                    'original_sale_id' => $saleModel->id,
                    'cash_session_id' => null,
                    'return_number' => $returnNumber,
                    'status' => 'draft',
                    'total' => 0,
                    'reason_code' => $validated['reason_code'],
                    'created_by' => $user->id,
                ]);

                $totalRefund = 0;

                foreach ($validated['lines'] as $inputLine) {
                    $saleLine = $saleLines->get($inputLine['sale_line_id']);

                    if (!$saleLine) {
                        throw ValidationException::withMessages([
                            'lines' => 'Una de las líneas no pertenece a la venta.',
                        ]);
                    }

                    $quantity = (float) $inputLine['quantity'];
                    $originalQuantity = (float) $saleLine->quantity;

                    $alreadyReturned = (float) SaleReturnLine::query()
                        ->where('original_sale_line_id', $saleLine->id)
                        ->whereHas('saleReturn', function ($query) use ($saleModel) {
                            $query
                                ->where('original_sale_id', $saleModel->id)
                                ->where('status', 'confirmed');
                        })
                        ->sum('quantity');

                    $available = $originalQuantity - $alreadyReturned;

                    if ($quantity > $available) {
                        throw ValidationException::withMessages([
                            'lines' => sprintf(
                                'No puedes devolver %.3f unidades de "%s". Solo hay %.3f disponibles.',
                                $quantity,
                                $saleLine->description,
                                max(0, $available)
                            ),
                        ]);
                    }

                    $lineTotal = round(
                        ((float) $saleLine->line_total / $originalQuantity)
                        * $quantity,
                        2
                    );

                    SaleReturnLine::create([
                        'sale_return_id' => $return->id,
                        'original_sale_line_id' => $saleLine->id,
                        'stock_item_id' => $saleLine->stock_item_id,
                        'quantity' => $quantity,
                        'line_total' => $lineTotal,
                        'inventory_condition' => $inputLine['inventory_condition'],
                    ]);

                    $totalRefund += $lineTotal;

                    if ($inputLine['inventory_condition'] === 'resellable') {
                        $this->restoreInventory(
                            $saleModel,
                            $saleLine,
                            $quantity,
                            $return,
                            $user->id
                        );
                    }
                }

                $totalRefund = round($totalRefund, 2);

                if ($totalRefund <= 0) {
                    throw ValidationException::withMessages([
                        'lines' => 'El importe de devolución debe ser mayor a cero.',
                    ]);
                }

                /*
                 * Por ahora el reembolso se aplica al método
                 * de pago original cuando éste afecta caja.
                 */
                $cashPayment = $saleModel->payments
                    ->first(function ($payment) {
                        return $payment->paymentMethod
                            && $payment->paymentMethod->affects_cash;
                    });

                if ($cashPayment) {
                    $cashSession = CashSession::query()
                        ->where('organization_id', $user->organization_id)
                        ->where('branch_id', $saleModel->branch_id)
                        ->where('status', 'open')
                        ->lockForUpdate()
                        ->first();

                    if (!$cashSession) {
                        throw ValidationException::withMessages([
                            'cash' => 'Necesitas una caja abierta para devolver efectivo.',
                        ]);
                    }

                    $return->cash_session_id = $cashSession->id;
                    $return->save();

                    CashMovement::create([
                        'organization_id' => $user->organization_id,
                        'branch_id' => $saleModel->branch_id,
                        'cash_session_id' => $cashSession->id,
                        'payment_method_id' => $cashPayment->payment_method_id,
                        'movement_type' => 'return_payment',
                        'amount' => $totalRefund,
                        'source_type' => 'SALE_RETURN',
                        'source_id' => $return->id,
                        'reason_code' => 'SALE_RETURN',
                        'notes' => 'Reembolso por devolución ' . $return->return_number,
                        'created_by' => $user->id,
                        'occurred_at' => now(),
                    ]);
                }

                $return->update([
                    'status' => 'confirmed',
                    'total' => $totalRefund,
                    'confirmed_at' => now(),
                ]);

                $this->updateSaleStatus($saleModel);

                return $return->fresh([
                    'lines',
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'La devolución fue procesada correctamente.',
                'return' => [
                    'id' => $result->id,
                    'return_number' => $result->return_number,
                    'total' => $result->total,
                ],
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'No fue posible procesar la devolución.',
            ], 500);
        }
    }

    private function restoreInventory(
        Sale $sale,
        $saleLine,
        float $quantity,
        SaleReturn $return,
        string $userId
    ): void {
        $inventoryQuantity = $quantity
            * (float) $saleLine->conversion_factor;

        $balance = DB::table('inventory_balances')
            ->where('organization_id', $sale->organization_id)
            ->where('branch_id', $sale->branch_id)
            ->where('stock_item_id', $saleLine->stock_item_id)
            ->lockForUpdate()
            ->first();

        if (!$balance) {
            throw ValidationException::withMessages([
                'inventory' => 'No existe el saldo de inventario del producto devuelto.',
            ]);
        }

        DB::table('inventory_movements')->insert([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'organization_id' => $sale->organization_id,
            'branch_id' => $sale->branch_id,
            'stock_item_id' => $saleLine->stock_item_id,
            'movement_type' => 'sale_return',
            'quantity_delta' => $inventoryQuantity,
            'unit_cost' => $saleLine->unit_cost,
            'total_cost' => round(
                $inventoryQuantity * (float) $saleLine->unit_cost,
                2
            ),
            'source_type' => 'SALE_RETURN',
            'source_id' => $return->id,
            'reason_code' => 'SALE_RETURN',
            'notes' => 'Entrada por devolución ' . $return->return_number,
            'created_by' => $userId,
            'occurred_at' => now(),
        ]);

        DB::table('inventory_balances')
            ->where('organization_id', $sale->organization_id)
            ->where('branch_id', $sale->branch_id)
            ->where('stock_item_id', $saleLine->stock_item_id)
            ->update([
                'on_hand_quantity' => DB::raw(
                    'on_hand_quantity + ' . $inventoryQuantity
                ),
                'version' => DB::raw('version + 1'),
            ]);
    }

    private function updateSaleStatus(Sale $sale): void
    {
        $sale->load('lines');

        $returnedQuantities = SaleReturnLine::query()
            ->whereHas('saleReturn', function ($query) use ($sale) {
                $query
                    ->where('original_sale_id', $sale->id)
                    ->where('status', 'confirmed');
            })
            ->select(
                'original_sale_line_id',
                DB::raw('SUM(quantity) as returned_quantity')
            )
            ->groupBy('original_sale_line_id')
            ->pluck('returned_quantity', 'original_sale_line_id');

        $allReturned = true;
        $someReturned = false;

        foreach ($sale->lines as $line) {
            $returned = (float) ($returnedQuantities[$line->id] ?? 0);
            $original = (float) $line->quantity;

            if ($returned > 0) {
                $someReturned = true;
            }

            if ($returned < $original) {
                $allReturned = false;
            }
        }

        if ($allReturned) {
            $sale->update([
                'status' => 'returned',
            ]);

            return;
        }

        if ($someReturned) {
            $sale->update([
                'status' => 'partially_returned',
            ]);
        }
    }

    private function generateReturnNumber(string $organizationId): string
    {
        $prefix = 'D-' . now()->format('Ym') . '-';

        $last = SaleReturn::query()
            ->where('organization_id', $organizationId)
            ->where('return_number', 'like', $prefix . '%')
            ->orderByDesc('return_number')
            ->value('return_number');

        $sequence = 1;

        if ($last) {
            $sequence = ((int) substr($last, -6)) + 1;
        }

        return $prefix . str_pad(
            (string) $sequence,
            6,
            '0',
            STR_PAD_LEFT
        );
    }
}
