<?php

declare(strict_types=1);

namespace App\Modules\Payment\Application\Services;

use App\Models\InventoryMovement;
use App\Models\StockItem;
use App\Modules\Operation\Infrastructure\Persistence\Models\Sale;
use App\Modules\Operation\Infrastructure\Persistence\Models\SaleLine;
use App\Modules\Operation\Infrastructure\Persistence\Models\SalePayment;
use App\Modules\Payment\Infrastructure\Persistence\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

final class PointPaymentFinalizer
{
    public function __construct(
        private readonly MercadoPagoService $mercadoPago,
    ) {}

    public function finalize(
        PaymentTransaction $transaction,
        ?string $confirmedBy = null,
    ): ?Sale {
        /*
         * La consulta a Mercado Pago debe hacerse FUERA
         * de la transacción de base de datos.
         */
        if (!$transaction->provider_order_id) {
            throw new RuntimeException(
                'La transacción no tiene provider_order_id.'
            );
        }

        $order = $this->mercadoPago->getOrder(
            $transaction->provider_order_id
        );

        $orderStatus = strtolower(
            (string) ($order['status'] ?? '')
        );

        $orderStatusDetail = strtolower(
            (string) ($order['status_detail'] ?? '')
        );

        /*
         * Estados finales negativos.
         */
        if ($orderStatus === 'failed') {
            $transaction->update([
                'status' => 'rejected',
                'status_detail' => $orderStatusDetail ?: 'failed',
                'response_payload' => $order,
            ]);

            return null;
        }

        if ($orderStatus === 'canceled') {
            $transaction->update([
                'status' => 'cancelled',
                'status_detail' => $orderStatusDetail ?: 'canceled',
                'response_payload' => $order,
            ]);

            return null;
        }

        if ($orderStatus === 'expired') {
            $transaction->update([
                'status' => 'expired',
                'status_detail' => $orderStatusDetail ?: 'expired',
                'response_payload' => $order,
            ]);

            return null;
        }

        if ($orderStatus === 'refunded') {
            $transaction->update([
                'status' => 'refunded',
                'status_detail' => $orderStatusDetail ?: 'refunded',
                'response_payload' => $order,
            ]);

            return null;
        }

        /*
         * Todavía no termina el pago.
         */
        if ($orderStatus !== 'processed') {
            $transaction->update([
                'status' => 'processing',
                'status_detail' => $orderStatusDetail ?: $orderStatus,
                'response_payload' => $order,
            ]);

            return null;
        }

        /*
         * Mercado Pago indica que la Order fue procesada.
         */
        $payments =
            data_get($order, 'transactions.payments', []);

        $payment = collect($payments)->first();

        if (!$payment) {
            throw new RuntimeException(
                'Mercado Pago indicó la orden como procesada, pero no devolvió el pago.'
            );
        }

        $paymentStatus = strtolower(
            (string) ($payment['status'] ?? '')
        );

        $paymentStatusDetail = strtolower(
            (string) ($payment['status_detail'] ?? '')
        );

        if (
            $paymentStatus !== 'processed'
            || $paymentStatusDetail !== 'accredited'
        ) {
            throw new RuntimeException(
                'El pago de Mercado Pago no tiene estado processed/accredited.'
            );
        }

        /*
         * Validamos importe.
         */
        $paidAmount = (float) (
            $payment['paid_amount']
            ?? $payment['amount']
            ?? 0
        );

        $expectedAmount = (float) $transaction->amount;

        if (abs($paidAmount - $expectedAmount) > 0.01) {
            throw new RuntimeException(
                sprintf(
                    'El importe pagado por Mercado Pago (%0.2f) no coincide con la transacción local (%0.2f).',
                    $paidAmount,
                    $expectedAmount
                )
            );
        }

        $providerPaymentId = $payment['id'] ?? null;

        if (!$providerPaymentId) {
            throw new RuntimeException(
                'Mercado Pago no devolvió el ID del pago.'
            );
        }

        /*
         * A partir de aquí hacemos todos los cambios locales
         * de forma atómica.
         */
        return DB::transaction(function () use (
            $transaction,
            $order,
            $payment,
            $providerPaymentId,
            $confirmedBy,
        ) {
            $lockedTransaction =
                PaymentTransaction::query()
                    ->where('id', $transaction->id)
                    ->lockForUpdate()
                    ->first();

            if (!$lockedTransaction) {
                throw new RuntimeException(
                    'No se encontró la transacción de pago.'
                );
            }

            /*
             * Idempotencia:
             * si ya terminó, no volvemos a descontar inventario
             * ni crear otro SalePayment.
             */
            if ($lockedTransaction->status === 'approved') {
                return Sale::query()->findOrFail(
                    $lockedTransaction->sale_id
                );
            }

            $sale = Sale::query()
                ->where('id', $lockedTransaction->sale_id)
                ->where(
                    'organization_id',
                    $lockedTransaction->organization_id
                )
                ->lockForUpdate()
                ->first();

            if (!$sale) {
                throw new RuntimeException(
                    'No se encontró la venta asociada al pago.'
                );
            }

            /*
             * Si ya fue confirmada, sincronizamos la transacción
             * y evitamos duplicados.
             */
            if ($sale->status === 'confirmed') {
                $lockedTransaction->update([
                    'provider_payment_id' => $providerPaymentId,
                    'status' => 'approved',
                    'status_detail' =>
                        $order['status_detail']
                        ?? $payment['status_detail']
                        ?? 'accredited',
                    'response_payload' => $order,
                    'paid_at' => now(),
                ]);

                return $sale;
            }

            if ($sale->status !== 'draft') {
                throw new RuntimeException(
                    "La venta {$sale->sale_number} no puede finalizarse porque su estado actual es {$sale->status}."
                );
            }

            $sale->load('lines');

            /*
             * 1. INVENTARIO
             */
            foreach ($sale->lines as $line) {
                if ($line->inventory_movement_id) {
                    continue;
                }

                $stockItem = StockItem::query()
                    ->where('organization_id', $sale->organization_id)
                    ->where('id', $line->stock_item_id)
                    ->where('is_active', true)
                    ->with('product')
                    ->lockForUpdate()
                    ->first();

                if (!$stockItem) {
                    throw ValidationException::withMessages([
                        'inventory' =>
                            "No se encontró el producto de la línea {$line->line_number}.",
                    ]);
                }

                /*
                 * Los productos sin control de inventario no
                 * generan movimiento.
                 */
                if (!$stockItem->product?->track_inventory) {
                    continue;
                }

                $inventoryQuantity =
                    (float) $line->quantity
                    * (float) $line->conversion_factor;

                $balance = DB::table('inventory_balances')
                    ->where(
                        'organization_id',
                        $sale->organization_id
                    )
                    ->where('branch_id', $sale->branch_id)
                    ->where(
                        'stock_item_id',
                        $line->stock_item_id
                    )
                    ->lockForUpdate()
                    ->first();

                $currentStock =
                    (float) ($balance->on_hand_quantity ?? 0);

                if (
                    !$stockItem->product?->allow_negative_stock
                    && $currentStock < $inventoryQuantity
                ) {
                    throw ValidationException::withMessages([
                        'inventory' =>
                            "Existencia insuficiente para {$line->description}. Disponible: {$currentStock}.",
                    ]);
                }

                $inventoryMovement =
                    InventoryMovement::create([
                        'organization_id' =>
                            $sale->organization_id,
                        'branch_id' =>
                            $sale->branch_id,
                        'stock_item_id' =>
                            $line->stock_item_id,
                        'movement_type' => 'sale',
                        'quantity_delta' =>
                            -$inventoryQuantity,
                        'unit_cost' =>
                            (float) $line->unit_cost,
                        'total_cost' => abs(
                            $inventoryQuantity
                            * (float) $line->unit_cost
                        ),
                        'source_type' => 'SALE',
                        'source_id' => $sale->id,
                        'reason_code' => 'SALE',
                        'notes' =>
                            'Salida por venta Mercado Pago Point',
                        'created_by' => $confirmedBy,
                        'occurred_at' => now(),
                    ]);

                if ($balance) {
                    DB::table('inventory_balances')
                        ->where(
                            'organization_id',
                            $sale->organization_id
                        )
                        ->where(
                            'branch_id',
                            $sale->branch_id
                        )
                        ->where(
                            'stock_item_id',
                            $line->stock_item_id
                        )
                        ->update([
                            'on_hand_quantity' =>
                                $currentStock
                                - $inventoryQuantity,
                            'version' =>
                                ((int) $balance->version) + 1,
                        ]);
                }

                $line->update([
                    'inventory_movement_id' =>
                        $inventoryMovement->id,
                ]);
            }

            /*
             * 2. REGISTRAR PAGO
             */
            SalePayment::query()->create([
                'sale_id' => $sale->id,
                'line_number' => 1,
                'payment_method_id' =>
                    $lockedTransaction->payment_method_id,
                'amount_received' =>
                    $lockedTransaction->amount,
                'amount_applied' =>
                    $lockedTransaction->amount,
                'reference' =>
                    $lockedTransaction->external_reference,
                'metadata' => [
                    'provider' => 'mercadopago',
                    'provider_order_id' =>
                        $lockedTransaction->provider_order_id,
                    'provider_payment_id' =>
                        $providerPaymentId,
                    'status' =>
                        $order['status'] ?? null,
                    'status_detail' =>
                        $order['status_detail'] ?? null,
                ],
                'paid_at' => now(),
            ]);

            /*
             * 3. CONFIRMAR VENTA
             */
            $sale->update([
                'status' => 'confirmed',
                'confirmed_at' => now(),
                'confirmed_by' => $confirmedBy,
            ]);

            /*
             * 4. CONFIRMAR TRANSACCIÓN
             */
            $lockedTransaction->update([
                'provider_payment_id' => $providerPaymentId,
                'status' => 'approved',
                'status_detail' =>
                    $order['status_detail']
                    ?? $payment['status_detail']
                    ?? 'accredited',
                'response_payload' => $order,
                'paid_at' => now(),
            ]);

            return $sale->fresh();
        });
    }
}
