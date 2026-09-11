<?php

declare(strict_types=1);

namespace App\Modules\Operation\Presentation\Http\Controllers;

use App\Models\ProductPrice;
use App\Models\ProductUnit;
use App\Models\StockItem;
use App\Modules\Operation\Infrastructure\Persistence\Models\CashMovement;
use App\Modules\Operation\Infrastructure\Persistence\Models\CashSession;
use App\Modules\Operation\Infrastructure\Persistence\Models\PaymentMethod;
use App\Modules\Operation\Infrastructure\Persistence\Models\Sale;
use App\Modules\Operation\Infrastructure\Persistence\Models\SaleLine;
use App\Modules\Operation\Infrastructure\Persistence\Models\SalePayment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

use App\Modules\Payment\Application\Services\MercadoPagoService;
use App\Modules\Payment\Application\Services\PaymentPointService;
use App\Modules\Payment\Infrastructure\Persistence\Models\PaymentTransaction;

final class SaleController
{

    public function __construct(
        private readonly PaymentPointService $paymentPointService,
    ) {}


    public function index(Request $request): View
    {
        $session = $this->activeCashSession($request);

        $paymentMethods = $session
            ? PaymentMethod::query()
            ->where('organization_id', $session->organization_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            : collect();

        return view('modules.operation.sales.index', [
            'activeSession' => $session,
            'paymentMethods' => $paymentMethods,
        ]);
    }

    public function show(Sale $sale): View
    {
        $orgId = DB::table('organizations')->value('id');

        abort_unless(
            $sale->organization_id === $orgId,
            404
        );

        $sale->load([
            'customer',
            'lines',
            'payments.paymentMethod',
        ]);

        return view(
            'modules.operation.sales.show',
            compact('sale')
        );
    }

    public function ticket(Sale $sale): View
    {
        $orgId = DB::table('organizations')->value('id');

        abort_unless(
            $sale->organization_id === $orgId,
            404
        );

        $sale->load([
            'customer',
            'lines',
            'payments.paymentMethod',
        ]);

        return view(
            'modules.operation.sales.ticket',
            compact('sale')
        );
    }

    public function lookup(Request $request): JsonResponse
    {
        $session = $this->activeCashSession($request);

        if (!$session) {
            return response()->json([
                'message' => 'No tienes una sesión de caja abierta.',
            ], 422);
        }

        $q = trim((string) $request->query('q', ''));

        if ($q === '') {
            return response()->json([
                'items' => [],
            ]);
        }

        $items = StockItem::query()
            ->where('stock_items.organization_id', $session->organization_id)
            ->where('stock_items.is_active', true)
            ->where(function ($query) use ($q, $session) {

                $query->whereHas('product', function ($product) use ($q) {

                    $product
                        ->where('is_active', true)
                        ->where(function ($product) use ($q) {
                            $product
                                ->where('name', 'ilike', "%{$q}%")
                                ->orWhere('sku', 'ilike', "%{$q}%");
                        });
                })->orWhereHas('barcodes', function ($barcode) use ($q, $session) {

                    $barcode
                        ->where('organization_id', $session->organization_id)
                        ->where('barcode', $q)
                        ->where('is_active', true);
                });
            })
            ->with([
                'product',
                'productUnits' => fn($query) => $query
                    ->where('is_active', true)
                    ->where('is_sale_unit', true)
                    ->with('unit'),
                'barcodes' => fn($query) => $query
                    ->where('is_active', true),
                'inventoryBalance' => fn($query) => $query
                    ->where('branch_id', $session->branch_id),
            ])
            ->limit(10)
            ->get();

        $results = $items->map(function (StockItem $stockItem) use ($session) {
            $unit = $stockItem->productUnits
                ->sortByDesc('conversion_factor')
                ->first();

            if (!$unit) {
                return null;
            }

            $price = $this->resolvePrice(
                $session->organization_id,
                $stockItem->id,
                $unit->id,
                1
            );

            if (!$price) {
                return null;
            }

            $barcode = $stockItem->barcodes
                ->where('product_unit_id', $unit->id)
                ->first()
                ?? $stockItem->barcodes->first();

            return [
                'stock_item_id' => $stockItem->id,
                'product_unit_id' => $unit->id,
                'barcode' => $barcode?->barcode,
                'sku' => $stockItem->product?->sku,
                'name' => $stockItem->product?->name
                    ?? $stockItem->productVariant?->name,
                'unit' => $unit->unit?->name,
                'allow_decimal' => (bool) $unit->allow_decimal,
                'conversion_factor' => (float) $unit->conversion_factor,
                'price' => (float) $price->amount,
                'stock' => (float) ($stockItem->inventoryBalance?->on_hand_quantity ?? 0),
            ];
        })
            ->filter()
            ->values();

        return response()->json([
            'items' => $results,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.stock_item_id' => ['required', 'uuid'],
            'items.*.product_unit_id' => ['required', 'uuid'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'payment_method_id' => ['required', 'uuid'],
            'amount_received' => ['required', 'numeric', 'gt:0'],
            'reference' => ['nullable', 'string', 'max:120'],
        ]);

        $user = $request->user();

        $result = DB::transaction(function () use ($validated, $user) {
            $session = CashSession::query()
                ->where('responsible_user_id', $user->id)
                ->where('status', 'open')
                ->lockForUpdate()
                ->first();

            if (!$session) {
                throw ValidationException::withMessages([
                    'cash' => 'No tienes una sesión de caja abierta.',
                ]);
            }

            $paymentMethod = PaymentMethod::query()
                ->where('organization_id', $session->organization_id)
                ->where('id', $validated['payment_method_id'])
                ->where('is_active', true)
                ->first();

            if (!$paymentMethod) {
                throw ValidationException::withMessages([
                    'payment_method_id' => 'El método de pago no es válido.',
                ]);
            }

            if (
                $paymentMethod->requires_reference
                && empty($validated['reference'])
            ) {
                throw ValidationException::withMessages([
                    'reference' => 'Este método de pago requiere una referencia.',
                ]);
            }

            $subtotal = 0.0;
            $discountTotal = 0.0;
            $taxTotal = 0.0;
            $total = 0.0;

            $preparedLines = [];

            foreach ($validated['items'] as $index => $item) {
                $stockItem = StockItem::query()
                    ->where('organization_id', $session->organization_id)
                    ->where('id', $item['stock_item_id'])
                    ->where('is_active', true)
                    ->with('product')
                    ->lockForUpdate()
                    ->first();

                if (!$stockItem) {
                    throw ValidationException::withMessages([
                        "items.{$index}.stock_item_id" =>
                        'El producto ya no está disponible.',
                    ]);
                }

                $unit = ProductUnit::query()
                    ->where('organization_id', $session->organization_id)
                    ->where('stock_item_id', $stockItem->id)
                    ->where('id', $item['product_unit_id'])
                    ->where('is_active', true)
                    ->where('is_sale_unit', true)
                    ->with('unit')
                    ->first();

                if (!$unit) {
                    throw ValidationException::withMessages([
                        "items.{$index}.product_unit_id" =>
                        'La unidad de venta no es válida.',
                    ]);
                }

                $quantity = (float) $item['quantity'];

                if (!$unit->allow_decimal && $quantity != floor($quantity)) {
                    throw ValidationException::withMessages([
                        "items.{$index}.quantity" =>
                        'Este producto solo puede venderse en cantidades enteras.',
                    ]);
                }

                $price = $this->resolvePrice(
                    $session->organization_id,
                    $stockItem->id,
                    $unit->id,
                    $quantity
                );

                if (!$price) {
                    throw ValidationException::withMessages([
                        "items.{$index}.quantity" =>
                        'El producto no tiene un precio vigente.',
                    ]);
                }

                $balance = DB::table('inventory_balances')
                    ->where('organization_id', $session->organization_id)
                    ->where('branch_id', $session->branch_id)
                    ->where('stock_item_id', $stockItem->id)
                    ->lockForUpdate()
                    ->first();

                $inventoryQuantity = $quantity * (float) $unit->conversion_factor;

                $currentStock = (float) ($balance->on_hand_quantity ?? 0);

                if (
                    $stockItem->product?->track_inventory
                    && !$stockItem->product?->allow_negative_stock
                    && $currentStock < $inventoryQuantity
                ) {
                    throw ValidationException::withMessages([
                        "items.{$index}.quantity" =>
                        "Existencia insuficiente para {$stockItem->product->name}. Disponible: {$currentStock}.",
                    ]);
                }

                $gross = $quantity * (float) $price->amount;

                $taxRate = 0.0;
                $taxIncluded = false;

                if ($stockItem->product?->tax_rate_id) {
                    $tax = DB::table('tax_rates')
                        ->where('organization_id', $session->organization_id)
                        ->where('id', $stockItem->product->tax_rate_id)
                        ->where('is_active', true)
                        ->where(function ($query) {
                            $query->whereNull('starts_at')
                                ->orWhere('starts_at', '<=', now());
                        })
                        ->where(function ($query) {
                            $query->whereNull('ends_at')
                                ->orWhere('ends_at', '>=', now());
                        })
                        ->first();

                    if ($tax) {
                        $taxRate = (float) $tax->rate;
                        $taxIncluded = (bool) $tax->is_included;
                    }
                }

                if ($taxIncluded && $taxRate > 0) {
                    $lineTax = $gross - ($gross / (1 + $taxRate));
                    $lineSubtotal = $gross - $lineTax;
                    $lineTotal = $gross;
                } else {
                    $lineSubtotal = $gross;
                    $lineTax = $gross * $taxRate;
                    $lineTotal = $gross + $lineTax;
                }

                $unitCost = (float) ($balance->weighted_average_cost ?? 0);

                $subtotal += $lineSubtotal;
                $taxTotal += $lineTax;
                $total += $lineTotal;

                $preparedLines[] = [
                    'stockItem' => $stockItem,
                    'unit' => $unit,
                    'quantity' => $quantity,
                    'inventoryQuantity' => $inventoryQuantity,
                    'unitPrice' => (float) $price->amount,
                    'subtotal' => $lineSubtotal,
                    'tax' => $lineTax,
                    'total' => $lineTotal,
                    'unitCost' => $unitCost,
                ];
            }

            $amountReceived = (float) $validated['amount_received'];

            if (!$paymentMethod->affects_cash && $amountReceived != $total) {
                throw ValidationException::withMessages([
                    'amount_received' =>
                    'Los pagos que no afectan efectivo deben cubrir exactamente el total.',
                ]);
            }

            if ($amountReceived < $total) {
                throw ValidationException::withMessages([
                    'amount_received' =>
                    'El importe recibido es menor al total de la venta.',
                ]);
            }

            $change = $paymentMethod->affects_cash
                ? max(0, $amountReceived - $total)
                : 0;

            $sale = Sale::create([
                'organization_id' => $session->organization_id,
                'branch_id' => $session->branch_id,
                'register_id' => $session->register_id,
                'cash_session_id' => $session->id,
                'sale_number' => $this->generateSaleNumber(
                    $session->organization_id
                ),
                'status' => 'draft',
                'currency_code' => 'MXN',
                'subtotal' => $subtotal,
                'discount_total' => $discountTotal,
                'tax_total' => $taxTotal,
                'total' => $total,
                'change_total' => $change,
                'created_by' => $user->id,
            ]);

            foreach ($preparedLines as $index => $line) {
                $inventoryMovement = null;

                if ($line['stockItem']->product?->track_inventory) {
                    $inventoryMovement = \App\Models\InventoryMovement::create([
                        'organization_id' => $session->organization_id,
                        'branch_id' => $session->branch_id,
                        'stock_item_id' => $line['stockItem']->id,
                        'movement_type' => 'sale',
                        'quantity_delta' => -$line['inventoryQuantity'],
                        'unit_cost' => $line['unitCost'],
                        'total_cost' => abs(
                            $line['inventoryQuantity'] * $line['unitCost']
                        ),
                        'source_type' => 'SALE',
                        'source_id' => $sale->id,
                        'reason_code' => 'SALE',
                        'notes' => 'Salida por venta',
                        'created_by' => $user->id,
                        'occurred_at' => now(),
                    ]);

                    $balance = DB::table('inventory_balances')
                        ->where('organization_id', $session->organization_id)
                        ->where('branch_id', $session->branch_id)
                        ->where('stock_item_id', $line['stockItem']->id)
                        ->lockForUpdate()
                        ->first();

                    if ($balance) {
                        DB::table('inventory_balances')
                            ->where('organization_id', $session->organization_id)
                            ->where('branch_id', $session->branch_id)
                            ->where('stock_item_id', $line['stockItem']->id)
                            ->update([
                                'on_hand_quantity' =>
                                (float) $balance->on_hand_quantity
                                    - $line['inventoryQuantity'],
                                'version' => ((int) $balance->version) + 1,
                            ]);
                    }
                }

                SaleLine::create([
                    'sale_id' => $sale->id,
                    'line_number' => $index + 1,
                    'stock_item_id' => $line['stockItem']->id,
                    'product_unit_id' => $line['unit']->id,
                    'sku' => $line['stockItem']->product?->sku,
                    'description' => $line['stockItem']->product?->name
                        ?? $line['stockItem']->productVariant?->name
                        ?? 'Producto',
                    'quantity' => $line['quantity'],
                    'conversion_factor' => $line['unit']->conversion_factor,
                    'unit_price' => $line['unitPrice'],
                    'discount_amount' => 0,
                    'tax_amount' => $line['tax'],
                    'unit_cost' => $line['unitCost'],
                    'line_total' => $line['total'],
                    'inventory_movement_id' => $inventoryMovement?->id,
                ]);
            }

            SalePayment::create([
                'sale_id' => $sale->id,
                'line_number' => 1,
                'payment_method_id' => $paymentMethod->id,
                'amount_received' => $amountReceived,
                'amount_applied' => $total,
                'reference' => $validated['reference'] ?? null,
                'paid_at' => now(),
            ]);

            if ($paymentMethod->affects_cash) {
                CashMovement::create([
                    'organization_id' => $session->organization_id,
                    'branch_id' => $session->branch_id,
                    'cash_session_id' => $session->id,
                    'payment_method_id' => $paymentMethod->id,
                    'movement_type' => 'sale_payment',
                    'amount' => $amountReceived,
                    'source_type' => 'SALE',
                    'source_id' => $sale->id,
                    'created_by' => $user->id,
                    'occurred_at' => now(),
                ]);

                if ($change > 0) {
                    CashMovement::create([
                        'organization_id' => $session->organization_id,
                        'branch_id' => $session->branch_id,
                        'cash_session_id' => $session->id,
                        'payment_method_id' => $paymentMethod->id,
                        'movement_type' => 'sale_change',
                        'amount' => $change,
                        'source_type' => 'SALE',
                        'source_id' => $sale->id,
                        'created_by' => $user->id,
                        'occurred_at' => now(),
                    ]);
                }
            }

            $sale->update([
                'status' => 'confirmed',
                'confirmed_at' => now(),
                'confirmed_by' => $user->id,
            ]);

            return $sale;
        });

        return response()->json([
            'success' => true,
            'message' => 'Venta registrada correctamente.',
            'sale_id' => $result->id,
            'sale_number' => $result->sale_number,
            'total' => (float) $result->total,
            'change' => (float) $result->change_total,
        ]);
    }

    private function activeCashSession(Request $request): ?CashSession
    {
        return CashSession::query()
            ->with(['branch', 'register'])
            ->where('responsible_user_id', $request->user()->id)
            ->where('status', 'open')
            ->latest('opened_at')
            ->first();
    }

    private function resolvePrice(
        string $organizationId,
        string $stockItemId,
        string $productUnitId,
        float $quantity
    ): ?ProductPrice {
        $priceList = DB::table('price_lists')
            ->where('organization_id', $organizationId)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->orderByDesc('priority')
            ->first();

        if (!$priceList) {
            return null;
        }

        return ProductPrice::query()
            ->where('organization_id', $organizationId)
            ->where('price_list_id', $priceList->id)
            ->where('stock_item_id', $stockItemId)
            ->where('product_unit_id', $productUnitId)
            ->where('is_active', true)
            ->where('min_quantity', '<=', $quantity)
            ->where(function ($query) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->orderByDesc('min_quantity')
            ->first();
    }

    private function generateSaleNumber(string $organizationId): string
    {
        do {
            $number = 'V-' . now()->format('Ymd-His')
                . '-' . Str::upper(Str::random(5));
        } while (
            Sale::query()
            ->where('organization_id', $organizationId)
            ->where('sale_number', $number)
            ->exists()
        );

        return $number;
    }
    public function history(Request $request): View
    {
        $orgId = DB::table('organizations')->value('id');

        $query = Sale::query()
            ->where('sales.organization_id', $orgId)
            ->with([
                'customer',
                'payments.paymentMethod',
            ])
            ->orderByDesc('created_at');

        if ($request->filled('search')) {
            $search = trim($request->input('search'));

            $query->where(function ($q) use ($search) {
                $q->where('sale_number', 'ilike', "%{$search}%")
                    ->orWhereHas('customer', function ($customer) use ($search) {
                        $customer->where('name', 'ilike', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->input('date'));
        }

        $sales = $query
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'today_count' => Sale::query()
                ->where('organization_id', $orgId)
                ->whereDate('created_at', today())
                ->where('status', 'confirmed')
                ->count(),

            'today_total' => Sale::query()
                ->where('organization_id', $orgId)
                ->whereDate('created_at', today())
                ->where('status', 'confirmed')
                ->sum('total'),

            'cancelled_today' => Sale::query()
                ->where('organization_id', $orgId)
                ->whereDate('created_at', today())
                ->where('status', 'cancelled')
                ->count(),
        ];

        return view(
            'modules.operation.sales.history',
            compact('sales', 'stats')
        );
    }
    public function cancel(
        Request $request,
        Sale $sale
    ): JsonResponse {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $user = $request->user();

        $result = DB::transaction(function () use (
            $sale,
            $validated,
            $user
        ) {
            $orgId = DB::table('organizations')->value('id');

            if ($sale->organization_id !== $orgId) {
                abort(404);
            }

            /*
         * Bloqueamos la venta para evitar dos cancelaciones
         * simultáneas sobre el mismo folio.
         */
            $sale = Sale::query()
                ->where('id', $sale->id)
                ->where('organization_id', $orgId)
                ->lockForUpdate()
                ->first();

            if (!$sale) {
                abort(404);
            }

            if ($sale->status !== 'confirmed') {
                throw ValidationException::withMessages([
                    'sale' => 'La venta no puede cancelarse porque no está confirmada.',
                ]);
            }

            /*
         * Cargamos las líneas y pagos dentro de la misma
         * transacción.
         */
            $sale->load([
                'lines',
                'payments.paymentMethod',
            ]);

            /*
         * Si existe un pago que afecta efectivo, necesitamos
         * una caja abierta para registrar la devolución.
         */
            $cashPayments = $sale->payments
                ->filter(
                    fn($payment) =>
                    $payment->paymentMethod?->affects_cash
                );

            if ($cashPayments->isNotEmpty()) {
                $session = CashSession::query()
                    ->where('responsible_user_id', $user->id)
                    ->where('status', 'open')
                    ->where('branch_id', $sale->branch_id)
                    ->lockForUpdate()
                    ->first();

                if (!$session) {
                    throw ValidationException::withMessages([
                        'cash' =>
                        'Debes tener una sesión de caja abierta en la sucursal de la venta para realizar la devolución.',
                    ]);
                }
            } else {
                $session = null;
            }

            /*
         * 1. REVERTIR INVENTARIO
         */
            foreach ($sale->lines as $line) {

                if (!$line->inventory_movement_id) {
                    continue;
                }

                $originalMovement = \App\Models\InventoryMovement::query()
                    ->where('id', $line->inventory_movement_id)
                    ->where('organization_id', $sale->organization_id)
                    ->where('branch_id', $sale->branch_id)
                    ->where('stock_item_id', $line->stock_item_id)
                    ->lockForUpdate()
                    ->first();

                if (!$originalMovement) {
                    throw ValidationException::withMessages([
                        'inventory' =>
                        "No se encontró el movimiento de inventario de la línea {$line->line_number}.",
                    ]);
                }

                /*
             * La venta original tuvo quantity_delta negativo.
             * La cancelación crea el movimiento inverso positivo.
             */
                $reversalQuantity = abs(
                    (float) $originalMovement->quantity_delta
                );

                \App\Models\InventoryMovement::create([
                    'organization_id' => $sale->organization_id,
                    'branch_id' => $sale->branch_id,
                    'stock_item_id' => $line->stock_item_id,
                    'movement_type' => 'void_reversal',
                    'quantity_delta' => $reversalQuantity,
                    'unit_cost' => $originalMovement->unit_cost,
                    'total_cost' => $originalMovement->total_cost,
                    'source_type' => 'SALE_CANCELLATION',
                    'source_id' => $sale->id,
                    'reason_code' => 'SALE_CANCELLATION',
                    'notes' => 'Reversión de inventario por cancelación de venta',
                    'created_by' => $user->id,
                    'occurred_at' => now(),
                ]);

                /*
             * Actualizamos la proyección de inventario.
             */
                $balance = DB::table('inventory_balances')
                    ->where('organization_id', $sale->organization_id)
                    ->where('branch_id', $sale->branch_id)
                    ->where('stock_item_id', $line->stock_item_id)
                    ->lockForUpdate()
                    ->first();

                if ($balance) {
                    DB::table('inventory_balances')
                        ->where('organization_id', $sale->organization_id)
                        ->where('branch_id', $sale->branch_id)
                        ->where('stock_item_id', $line->stock_item_id)
                        ->update([
                            'on_hand_quantity' =>
                            (float) $balance->on_hand_quantity
                                + $reversalQuantity,

                            'version' => ((int) $balance->version) + 1,
                        ]);
                }
            }

            /*
         * 2. REVERTIR EFECTIVO
         *
         * Se devuelve únicamente lo aplicado a la venta,
         * no el importe originalmente recibido.
         *
         * Ejemplo:
         * Venta = $28
         * Recibido = $30
         * Cambio = $2
         * Devolución = $28
         */
            foreach ($cashPayments as $payment) {

                $refundAmount = (float) $payment->amount_applied;

                if ($refundAmount <= 0) {
                    continue;
                }

                CashMovement::create([
                    'organization_id' => $sale->organization_id,
                    'branch_id' => $sale->branch_id,
                    'cash_session_id' => $session->id,
                    'payment_method_id' => $payment->payment_method_id,
                    'movement_type' => 'return_payment',
                    'amount' => $refundAmount,
                    'source_type' => 'SALE_CANCELLATION',
                    'source_id' => $sale->id,
                    'reason_code' => 'SALE_CANCELLATION',
                    'notes' => 'Devolución por cancelación de venta',
                    'created_by' => $user->id,
                    'occurred_at' => now(),
                ]);
            }

            /*
         * 3. MARCAR VENTA COMO CANCELADA
         */
            $sale->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => $user->id,
                'cancellation_reason' => $validated['reason'],
            ]);

            return $sale->fresh();
        });

        return response()->json([
            'success' => true,
            'message' => 'La venta fue cancelada correctamente.',
            'sale_id' => $result->id,
            'sale_number' => $result->sale_number,
        ]);
    }
}
