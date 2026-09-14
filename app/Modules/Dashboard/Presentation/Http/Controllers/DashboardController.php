<?php

declare(strict_types=1);

namespace App\Modules\Dashboard\Presentation\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

final class DashboardController
{
    public function __invoke(): View
    {
        $orgId = auth()->user()->organization_id
            ?? DB::table('organizations')->value('id');

        if (!$orgId) {
            abort(500, 'No hay una organización configurada.');
        }

        $branchId = session('current_branch_id')
            ?? DB::table('branches')
            ->where('organization_id', $orgId)
            ->where('is_active', true)
            ->value('id');

        if (!$branchId) {
            abort(500, 'No hay una sucursal activa configurada.');
        }

        $today = now()->startOfDay();
        $tomorrow = now()->copy()->addDay()->startOfDay();

        /*
        |--------------------------------------------------------------------------
        | VENTAS DEL DÍA
        |--------------------------------------------------------------------------
        */

        $salesTodayQuery = DB::table('sales')
            ->where('organization_id', $orgId)
            ->where('branch_id', $branchId)
            ->where('status', 'confirmed')
            ->where('created_at', '>=', $today)
            ->where('created_at', '<', $tomorrow);

        $salesToday = (float) (clone $salesTodayQuery)->sum('total');

        $ticketsToday = (int) (clone $salesTodayQuery)->count();

        $averageTicket = $ticketsToday > 0
            ? $salesToday / $ticketsToday
            : 0;

        /*
        |--------------------------------------------------------------------------
        | VENTAS POR HORA
        |--------------------------------------------------------------------------
        */

        $salesByHour = DB::table('sales')
            ->selectRaw('EXTRACT(HOUR FROM created_at)::integer as hour')
            ->selectRaw('SUM(total) as total')
            ->where('organization_id', $orgId)
            ->where('branch_id', $branchId)
            ->where('status', 'confirmed')
            ->where('created_at', '>=', $today)
            ->where('created_at', '<', $tomorrow)
            ->groupByRaw('EXTRACT(HOUR FROM created_at)')
            ->orderByRaw('EXTRACT(HOUR FROM created_at)')
            ->get()
            ->keyBy('hour');

        $hourlySales = collect(range(0, 23))
            ->map(function (int $hour) use ($salesByHour) {
                $data = $salesByHour->get($hour);

                return [
                    'hour' => $hour,
                    'label' => str_pad((string) $hour, 2, '0', STR_PAD_LEFT),
                    'total' => (float) ($data->total ?? 0),
                ];
            });

        $maxHourlySales = max(
            1,
            (float) $hourlySales->max('total')
        );

        /*
        |--------------------------------------------------------------------------
        | CAJA
        |--------------------------------------------------------------------------
        */

        $activeCashSession = DB::table('cash_sessions')
            ->where('organization_id', $orgId)
            ->where('branch_id', $branchId)
            ->whereIn('status', ['open', 'counting'])
            ->orderByDesc('opened_at')
            ->first();

        $cashSummary = [
            'status' => 'closed',
            'label' => 'Cerrada',
            'opening_float' => 0,
            'cash_in' => 0,
            'cash_out' => 0,
            'theoretical_cash' => 0,
            'opened_at' => null,
        ];

        if ($activeCashSession) {
            $movements = DB::table('cash_movements')
                ->where('cash_session_id', $activeCashSession->id)
                ->get();

            $cashIn = 0;
            $cashOut = 0;

            foreach ($movements as $movement) {
                $amount = (float) $movement->amount;

                switch ($movement->movement_type) {
                    case 'opening_float':
                    case 'sale_payment':
                    case 'income':
                        $cashIn += $amount;
                        break;

                    case 'sale_change':
                    case 'return_payment':
                    case 'expense':
                    case 'withdrawal':
                    case 'deposit':
                        $cashOut += $amount;
                        break;
                }
            }

            $cashSummary = [
                'status' => $activeCashSession->status,
                'label' => $activeCashSession->status === 'counting'
                    ? 'En arqueo'
                    : 'Abierta',
                'opening_float' => (float) $activeCashSession->opening_float,
                'cash_in' => round($cashIn, 2),
                'cash_out' => round($cashOut, 2),
                'theoretical_cash' => round($cashIn - $cashOut, 2),
                'opened_at' => $activeCashSession->opened_at,
            ];
        }

        /*
|--------------------------------------------------------------------------
| INVENTARIO
|--------------------------------------------------------------------------
*/

        $inventoryBase = DB::table('stock_items')
            ->join(
                'products',
                'products.id',
                '=',
                'stock_items.product_id'
            )
            ->leftJoin('inventory_balances', function ($join) use ($orgId, $branchId) {
                $join->on(
                    'inventory_balances.stock_item_id',
                    '=',
                    'stock_items.id'
                )
                    ->where('inventory_balances.organization_id', $orgId)
                    ->where('inventory_balances.branch_id', $branchId);
            })
            ->leftJoin('stock_reorder_levels', function ($join) use ($orgId, $branchId) {
                $join->on(
                    'stock_reorder_levels.stock_item_id',
                    '=',
                    'stock_items.id'
                )
                    ->where('stock_reorder_levels.organization_id', $orgId)
                    ->where('stock_reorder_levels.branch_id', $branchId);
            })
            ->where('stock_items.organization_id', $orgId)
            ->where('stock_items.is_active', true);


        /*
|--------------------------------------------------------------------------
| TOTAL DE PRODUCTOS CONTROLADOS
|--------------------------------------------------------------------------
*/

        $inventoryTotal = (clone $inventoryBase)->count();


        /*
|--------------------------------------------------------------------------
| AGOTADOS
|--------------------------------------------------------------------------
*/

        $outOfStock = (clone $inventoryBase)
            ->whereRaw(
                'COALESCE(inventory_balances.available_quantity, 0) <= 0'
            )
            ->count();


        /*
|--------------------------------------------------------------------------
| STOCK BAJO
|--------------------------------------------------------------------------
*/

        $lowStock = (clone $inventoryBase)
            ->whereRaw(
                'COALESCE(stock_reorder_levels.minimum_quantity, 0) > 0'
            )
            ->whereRaw(
                'COALESCE(inventory_balances.available_quantity, 0) > 0'
            )
            ->whereRaw(
                'COALESCE(inventory_balances.available_quantity, 0) <= COALESCE(stock_reorder_levels.minimum_quantity, 0)'
            )
            ->count();


        /*
|--------------------------------------------------------------------------
| PRODUCTOS QUE REQUIEREN ATENCIÓN
|--------------------------------------------------------------------------
*/

        $inventoryAlerts = (clone $inventoryBase)
            ->select([
                'stock_items.id',
                'products.name',
                'products.sku',
                'inventory_balances.on_hand_quantity',
                'inventory_balances.reserved_quantity',
                'inventory_balances.available_quantity',
                'stock_reorder_levels.minimum_quantity',
            ])
            ->where(function ($query) {
                $query
                    ->whereRaw(
                        'COALESCE(inventory_balances.available_quantity, 0) <= 0'
                    )
                    ->orWhere(function ($query) {
                        $query
                            ->whereRaw(
                                'COALESCE(stock_reorder_levels.minimum_quantity, 0) > 0'
                            )
                            ->whereRaw(
                                'COALESCE(inventory_balances.available_quantity, 0) > 0'
                            )
                            ->whereRaw(
                                'COALESCE(inventory_balances.available_quantity, 0) <= COALESCE(stock_reorder_levels.minimum_quantity, 0)'
                            );
                    });
            })
            ->orderByRaw(
                'COALESCE(inventory_balances.available_quantity, 0) ASC'
            )
            ->limit(8)
            ->get()
            ->map(function ($item) {

                $onHand = (float) ($item->on_hand_quantity ?? 0);

                $reserved = (float) ($item->reserved_quantity ?? 0);

                $available = (float) (
                    $item->available_quantity
                    ?? ($onHand - $reserved)
                );

                $available = max(0, $available);

                $minimum = (float) (
                    $item->minimum_quantity ?? 0
                );

                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'sku' => $item->sku,
                    'available' => $available,
                    'minimum' => $minimum,
                    'status' => $available <= 0
                        ? 'out'
                        : 'low',
                ];
            });
        /*
        |--------------------------------------------------------------------------
        | COMPRAS
        |--------------------------------------------------------------------------
        */

        $pendingPurchases = DB::table('purchases')
            ->where('organization_id', $orgId)
            ->where('branch_id', $branchId)
            ->where('status', 'approved')
            ->count();

        $recentPurchases = DB::table('purchases')
            ->join(
                'suppliers',
                'suppliers.id',
                '=',
                'purchases.supplier_id'
            )
            ->where('purchases.organization_id', $orgId)
            ->where('purchases.branch_id', $branchId)
            ->select([
                'purchases.id',
                'purchases.purchase_number',
                'purchases.total',
                'purchases.status',
                'purchases.created_at',
                'suppliers.business_name as supplier_name',
            ])
            ->orderByDesc('purchases.created_at')
            ->limit(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | GASTOS
        |--------------------------------------------------------------------------
        */

        $pendingExpenses = DB::table('expenses')
            ->where('organization_id', $orgId)
            ->where('branch_id', $branchId)
            ->where('status', 'pending_approval')
            ->count();

        $monthStart = now()->startOfMonth();
        $monthEnd = now()->copy()->endOfMonth();

        $monthExpenses = (float) DB::table('expenses')
            ->where('organization_id', $orgId)
            ->where('branch_id', $branchId)
            ->whereBetween('expense_date', [
                $monthStart->toDateString(),
                $monthEnd->toDateString(),
            ])
            ->whereIn('status', ['approved', 'paid'])
            ->sum('amount');

        /*
        |--------------------------------------------------------------------------
        | ACTIVIDAD RECIENTE
        |--------------------------------------------------------------------------
        */

        $recentSales = DB::table('sales')
            ->where('organization_id', $orgId)
            ->where('branch_id', $branchId)
            ->where('status', 'confirmed')
            ->select([
                'id',
                'sale_number as reference',
                'total as amount',
                'created_at',
            ])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(fn($item) => [
                'type' => 'sale',
                'label' => 'Venta',
                'reference' => $item->reference,
                'amount' => (float) $item->amount,
                'created_at' => $item->created_at,
                'url' => route('sales.show', $item->id),
            ]);

        $recentPurchasesActivity = DB::table('purchases')
            ->where('organization_id', $orgId)
            ->where('branch_id', $branchId)
            ->select([
                'id',
                'purchase_number as reference',
                'total as amount',
                'created_at',
            ])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(fn($item) => [
                'type' => 'purchase',
                'label' => 'Compra',
                'reference' => $item->reference,
                'amount' => (float) $item->amount,
                'created_at' => $item->created_at,
                'url' => route('purchases.show', $item->id),
            ]);

        $recentExpenses = DB::table('expenses')
            ->where('organization_id', $orgId)
            ->where('branch_id', $branchId)
            ->select([
                'id',
                'expense_number as reference',
                'amount',
                'created_at',
            ])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(fn($item) => [
                'type' => 'expense',
                'label' => 'Gasto',
                'reference' => $item->reference,
                'amount' => (float) $item->amount,
                'created_at' => $item->created_at,
                'url' => route('expenses.show', $item->id),
            ]);

        $recentActivity = $recentSales
            ->concat($recentPurchasesActivity)
            ->concat($recentExpenses)
            ->sortByDesc('created_at')
            ->take(8)
            ->values();

        /*
        |--------------------------------------------------------------------------
        | MÉTRICAS PRINCIPALES
        |--------------------------------------------------------------------------
        */

        $metrics = [
            [
                'label' => 'Ventas de hoy',
                'value' => '$' . number_format($salesToday, 2),
                'detail' => $ticketsToday > 0
                    ? $ticketsToday . ' tickets registrados'
                    : 'Sin ventas registradas',
                'tone' => 'emerald',
            ],
            [
                'label' => 'Ticket promedio',
                'value' => '$' . number_format($averageTicket, 2),
                'detail' => $ticketsToday . ' tickets hoy',
                'tone' => 'sky',
            ],
            [
                'label' => 'Stock bajo',
                'value' => (string) $lowStock,
                'detail' => $outOfStock . ' agotados',
                'tone' => 'amber',
            ],
            [
                'label' => 'Caja',
                'value' => $cashSummary['status'] === 'closed'
                    ? 'Cerrada'
                    : '$' . number_format(
                        $cashSummary['theoretical_cash'],
                        2
                    ),
                'detail' => $cashSummary['status'] === 'closed'
                    ? 'Sin sesión activa'
                    : $cashSummary['label'],
                'tone' => 'violet',
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | RESPUESTA
        |--------------------------------------------------------------------------
        */

        return view('modules.dashboard.index', [
            'metrics' => $metrics,

            'salesToday' => $salesToday,
            'ticketsToday' => $ticketsToday,
            'averageTicket' => $averageTicket,

            'hourlySales' => $hourlySales,
            'maxHourlySales' => $maxHourlySales,

            'cashSummary' => $cashSummary,
            'activeCashSession' => $activeCashSession,

            'inventoryTotal' => $inventoryTotal,
            'lowStock' => $lowStock,
            'outOfStock' => $outOfStock,
            'inventoryAlerts' => $inventoryAlerts,

            'pendingPurchases' => $pendingPurchases,
            'recentPurchases' => $recentPurchases,

            'pendingExpenses' => $pendingExpenses,
            'monthExpenses' => $monthExpenses,

            'recentActivity' => $recentActivity,
        ]);
    }
}
