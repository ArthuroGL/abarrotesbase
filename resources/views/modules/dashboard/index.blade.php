<x-layouts.app title="Dashboard | ABARROTESBASE">

    {{-- =========================================================
         HEADER
    ========================================================== --}}

    <x-layout.page-header
        eyebrow="Operación"
        title="Dashboard"
        description="Resumen operativo de la sucursal activa."
    >
        <x-slot:actions>
            <a
                href="{{ route('sales.index') }}"
                class="inline-flex min-h-12 items-center justify-center rounded-xl bg-emerald-600 px-5 text-sm font-black text-white shadow-sm transition hover:bg-emerald-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600"
            >
                + Nueva venta
            </a>
        </x-slot:actions>
    </x-layout.page-header>


    {{-- =========================================================
         MÉTRICAS PRINCIPALES
    ========================================================== --}}

    <div class="mt-7 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">

        @foreach ($metrics as $metric)

            <x-ui.card
                class="metric-card metric-card-{{ $metric['tone'] }}"
            >
                <p class="text-xs font-black uppercase tracking-[0.1em] text-slate-500">
                    {{ $metric['label'] }}
                </p>

                <p class="mt-3 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">
                    {{ $metric['value'] }}
                </p>

                <p class="mt-2 text-xs font-semibold text-slate-500">
                    {{ $metric['detail'] }}
                </p>
            </x-ui.card>

        @endforeach

    </div>


    {{-- =========================================================
         VENTAS + PENDIENTES
    ========================================================== --}}

    <div class="mt-6 grid gap-6 xl:grid-cols-3">

        {{-- =====================================================
             VENTAS DE HOY
        ====================================================== --}}

        <x-ui.card
            class="overflow-hidden xl:col-span-2"
            padding="p-0"
        >

            <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-5 sm:flex-row sm:items-center sm:justify-between">

                <div>
                    <p class="text-xs font-black uppercase tracking-[0.1em] text-emerald-700">
                        Rendimiento
                    </p>

                    <h2 class="mt-1 text-lg font-black text-slate-950">
                        Ventas de hoy
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Comportamiento de las ventas por hora.
                    </p>
                </div>

                <div class="text-left sm:text-right">
                    <p class="text-2xl font-black tabular-nums text-slate-950">
                        ${{ number_format($salesToday, 2) }}
                    </p>

                    <p class="text-xs font-semibold text-slate-500">
                        {{ $ticketsToday }} tickets
                    </p>
                </div>

            </div>


            {{-- GRÁFICA --}}

            <div class="px-5 pb-6 pt-8">

                <div class="flex h-64 items-end gap-1.5 sm:gap-2">

                    @foreach ($hourlySales as $hour)

                        @php
                            $height = $hour['total'] > 0
                                ? max(
                                    4,
                                    ($hour['total'] / $maxHourlySales) * 100
                                )
                                : 2;
                        @endphp

                        <div class="group flex min-w-0 flex-1 flex-col items-center justify-end">

                            <div class="relative flex h-52 w-full items-end">

                                <div
                                    class="mx-auto w-full max-w-8 rounded-t-lg bg-emerald-100 transition group-hover:bg-emerald-500"
                                    style="height: {{ $height }}%;"
                                    title="{{ $hour['label'] }}:00 · ${{ number_format($hour['total'], 2) }}"
                                >
                                </div>

                            </div>

                            <span class="mt-2 text-[9px] font-bold text-slate-400 sm:text-[10px]">
                                {{ $hour['label'] }}
                            </span>

                        </div>

                    @endforeach

                </div>

            </div>

        </x-ui.card>


        {{-- =====================================================
             PENDIENTES OPERATIVOS
        ====================================================== --}}

        <x-ui.card
            class="overflow-hidden"
            padding="p-0"
        >

            <div class="border-b border-slate-100 px-5 py-5">

                <p class="text-xs font-black uppercase tracking-[0.1em] text-amber-700">
                    Atención
                </p>

                <h2 class="mt-1 text-lg font-black text-slate-950">
                    Pendientes operativos
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Situaciones que requieren atención.
                </p>

            </div>


            <div class="divide-y divide-slate-100">

                {{-- STOCK --}}

                <a
                    href="{{ route('stock.index', ['status' => 'low']) }}"
                    class="flex items-start gap-3 px-5 py-4 transition hover:bg-slate-50"
                >

                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-amber-50 text-sm font-black text-amber-700">
                        {{ $lowStock }}
                    </span>

                    <span class="min-w-0">

                        <span class="block text-sm font-black text-slate-800">
                            Productos bajo mínimo
                        </span>

                        <span class="mt-0.5 block text-xs leading-5 text-slate-500">
                            {{ $outOfStock }} agotados.
                        </span>

                    </span>

                </a>


                {{-- CAJA --}}

                <a
                    href="{{ route('cash.index') }}"
                    class="flex items-start gap-3 px-5 py-4 transition hover:bg-slate-50"
                >

                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-violet-50 text-sm font-black text-violet-700">
                        $
                    </span>

                    <span class="min-w-0">

                        <span class="block text-sm font-black text-slate-800">
                            Estado de caja
                        </span>

                        <span class="mt-0.5 block text-xs leading-5 text-slate-500">

                            @if ($activeCashSession)

                                {{ $cashSummary['label'] }}
                                · ${{ number_format($cashSummary['theoretical_cash'], 2) }}

                            @else

                                No hay una sesión activa.

                            @endif

                        </span>

                    </span>

                </a>


                {{-- COMPRAS --}}

                <a
                    href="{{ route('purchases.index', ['status' => 'approved']) }}"
                    class="flex items-start gap-3 px-5 py-4 transition hover:bg-slate-50"
                >

                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-sky-50 text-sm font-black text-sky-700">
                        {{ $pendingPurchases }}
                    </span>

                    <span class="min-w-0">

                        <span class="block text-sm font-black text-slate-800">
                            Compras por recibir
                        </span>

                        <span class="mt-0.5 block text-xs leading-5 text-slate-500">
                            {{ $pendingPurchases }} pendientes de recepción.
                        </span>

                    </span>

                </a>


                {{-- GASTOS --}}

                <a
                    href="{{ route('expenses.index', ['status' => 'pending_approval']) }}"
                    class="flex items-start gap-3 px-5 py-4 transition hover:bg-slate-50"
                >

                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-rose-50 text-sm font-black text-rose-700">
                        {{ $pendingExpenses }}
                    </span>

                    <span class="min-w-0">

                        <span class="block text-sm font-black text-slate-800">
                            Gastos por aprobar
                        </span>

                        <span class="mt-0.5 block text-xs leading-5 text-slate-500">
                            Requieren revisión o autorización.
                        </span>

                    </span>

                </a>

            </div>

        </x-ui.card>

    </div>


    {{-- =========================================================
         INVENTARIO + COMPRAS
    ========================================================== --}}

    <div class="mt-6 grid gap-6 lg:grid-cols-2">


        {{-- =====================================================
             INVENTARIO
        ====================================================== --}}

        <x-ui.card
            class="overflow-hidden"
            padding="p-0"
        >

            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-5">

                <div>

                    <p class="text-xs font-black uppercase tracking-[0.1em] text-amber-700">
                        Inventario
                    </p>

                    <h2 class="mt-1 text-lg font-black text-slate-950">
                        Requiere atención
                    </h2>

                </div>

                <a
                    href="{{ route('stock.index') }}"
                    class="text-xs font-black text-emerald-700 hover:text-emerald-800"
                >
                    Ver existencias →
                </a>

            </div>


            @if ($inventoryAlerts->isEmpty())

                <div class="grid min-h-56 place-items-center px-5 text-center">

                    <div>

                        <div class="mx-auto grid h-12 w-12 place-items-center rounded-2xl bg-emerald-50 text-lg font-black text-emerald-700">
                            ✓
                        </div>

                        <p class="mt-4 text-sm font-black text-slate-800">
                            Inventario en orden
                        </p>

                        <p class="mt-1 text-xs leading-5 text-slate-500">
                            No hay productos agotados o bajo mínimo.
                        </p>

                    </div>

                </div>

            @else

                <div class="divide-y divide-slate-100">

                    @foreach ($inventoryAlerts as $item)

                        <div class="flex items-center justify-between gap-4 px-5 py-4">

                            <div class="min-w-0">

                                <p class="truncate text-sm font-black text-slate-900">
                                    {{ $item['name'] }}
                                </p>

                                <p class="mt-1 font-mono text-[11px] font-semibold text-slate-400">
                                    SKU: {{ $item['sku'] ?? 'N/A' }}
                                </p>

                            </div>


                            <div class="shrink-0 text-right">

                                @if ($item['status'] === 'out')

                                    <span class="inline-flex rounded-full border border-rose-200 bg-rose-50 px-2.5 py-1 text-[11px] font-black text-rose-700">
                                        Agotado
                                    </span>

                                @else

                                    <span class="inline-flex rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-[11px] font-black text-amber-700">
                                        {{ number_format($item['available'], 2) }}
                                        / mín. {{ number_format($item['minimum'], 2) }}
                                    </span>

                                @endif

                            </div>

                        </div>

                    @endforeach

                </div>

            @endif

        </x-ui.card>


        {{-- =====================================================
             COMPRAS RECIENTES
        ====================================================== --}}

        <x-ui.card
            class="overflow-hidden"
            padding="p-0"
        >

            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-5">

                <div>

                    <p class="text-xs font-black uppercase tracking-[0.1em] text-sky-700">
                        Abastecimiento
                    </p>

                    <h2 class="mt-1 text-lg font-black text-slate-950">
                        Compras recientes
                    </h2>

                </div>

                <a
                    href="{{ route('purchases.index') }}"
                    class="text-xs font-black text-emerald-700 hover:text-emerald-800"
                >
                    Ver compras →
                </a>

            </div>


            @if ($recentPurchases->isEmpty())

                <div class="grid min-h-48 place-items-center px-5 text-center">

                    <p class="text-sm font-semibold text-slate-500">
                        No hay compras registradas.
                    </p>

                </div>

            @else

                <div class="divide-y divide-slate-100">

                    @foreach ($recentPurchases as $purchase)

                        <a
                            href="{{ route('purchases.show', $purchase->id) }}"
                            class="flex items-center justify-between gap-4 px-5 py-4 transition hover:bg-slate-50"
                        >

                            <div class="min-w-0">

                                <p class="text-sm font-black text-slate-900">
                                    {{ $purchase->purchase_number }}
                                </p>

                                <p class="mt-1 truncate text-xs text-slate-500">
                                    {{ $purchase->supplier_name }}
                                </p>

                            </div>


                            <div class="shrink-0 text-right">

                                <p class="text-sm font-black text-slate-900">
                                    ${{ number_format((float) $purchase->total, 2) }}
                                </p>

                                <p class="mt-1 text-[10px] font-bold uppercase text-slate-400">
                                    {{ $purchase->status === 'received' ? 'Recibida' : 'Por recibir' }}
                                </p>

                            </div>

                        </a>

                    @endforeach

                </div>

            @endif

        </x-ui.card>

    </div>


    {{-- =========================================================
         GASTOS + ÚLTIMAS OPERACIONES
    ========================================================== --}}

    <div class="mt-6 grid gap-6 lg:grid-cols-2">


        {{-- =====================================================
             GASTOS DEL MES
        ====================================================== --}}

        <x-ui.card
            class="overflow-hidden"
            padding="p-0"
        >

            <div class="border-b border-slate-100 px-5 py-5">

                <p class="text-xs font-black uppercase tracking-[0.1em] text-rose-700">
                    Gastos
                </p>

                <h2 class="mt-1 text-lg font-black text-slate-950">
                    Resumen del mes
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Gastos aprobados y pagados de la sucursal.
                </p>

            </div>


            <div class="p-5">

                <div class="grid gap-3 sm:grid-cols-3">

                    <div class="rounded-2xl bg-slate-50 p-4">

                        <p class="text-xs font-semibold text-slate-500">
                            Total
                        </p>

                        <p class="mt-2 text-xl font-black tabular-nums text-slate-950">
                            ${{ number_format($monthExpenses, 2) }}
                        </p>

                    </div>


                    <div class="rounded-2xl bg-emerald-50 p-4">

                        <p class="text-xs font-semibold text-emerald-700">
                            Pagado
                        </p>

                        <p class="mt-2 text-xl font-black tabular-nums text-emerald-800">
                            ${{ number_format($monthExpensesPaid, 2) }}
                        </p>

                    </div>


                    <div class="rounded-2xl bg-amber-50 p-4">

                        <p class="text-xs font-semibold text-amber-700">
                            Pendiente
                        </p>

                        <p class="mt-2 text-xl font-black tabular-nums text-amber-800">
                            ${{ number_format($monthExpensesPending, 2) }}
                        </p>

                    </div>

                </div>


                <a
                    href="{{ route('expenses.index') }}"
                    class="mt-4 inline-flex min-h-11 w-full items-center justify-center rounded-xl border border-slate-200 bg-white text-sm font-black text-slate-700 transition hover:bg-slate-50"
                >
                    Administrar gastos →
                </a>

            </div>

        </x-ui.card>


        {{-- =====================================================
             ÚLTIMAS OPERACIONES
        ====================================================== --}}

        <x-ui.card
            class="overflow-hidden"
            padding="p-0"
        >

            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-5">

                <div>

                    <p class="text-xs font-black uppercase tracking-[0.1em] text-slate-500">
                        Actividad
                    </p>

                    <h2 class="mt-1 text-lg font-black text-slate-950">
                        Últimas operaciones
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Movimientos recientes de la sucursal.
                    </p>

                </div>

            </div>


            @if ($recentActivity->isEmpty())

                <div class="grid min-h-40 place-items-center px-5 text-center">

                    <p class="text-sm font-semibold text-slate-500">
                        Todavía no hay operaciones registradas.
                    </p>

                </div>

            @else

                <div class="divide-y divide-slate-100">

                    @foreach ($recentActivity as $activity)

                        <a
                            href="{{ $activity['url'] }}"
                            class="flex items-center gap-4 px-5 py-4 transition hover:bg-slate-50"
                        >

                            <div
                                class="grid h-9 w-9 shrink-0 place-items-center rounded-xl
                                @if ($activity['type'] === 'sale')
                                    bg-emerald-50 text-emerald-700
                                @elseif ($activity['type'] === 'purchase')
                                    bg-sky-50 text-sky-700
                                @else
                                    bg-rose-50 text-rose-700
                                @endif"
                            >

                                @if ($activity['type'] === 'sale')
                                    $
                                @elseif ($activity['type'] === 'purchase')
                                    +
                                @else
                                    −
                                @endif

                            </div>


                            <div class="min-w-0 flex-1">

                                <p class="text-sm font-black text-slate-900">

                                    {{ $activity['label'] }}

                                    <span class="font-mono text-xs font-semibold text-slate-400">
                                        {{ $activity['reference'] }}
                                    </span>

                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    {{ \Carbon\Carbon::parse($activity['created_at'])->format('d/m/Y H:i') }}
                                </p>

                            </div>


                            <p class="shrink-0 text-sm font-black tabular-nums text-slate-900">
                                ${{ number_format($activity['amount'], 2) }}
                            </p>

                        </a>

                    @endforeach

                </div>

            @endif

        </x-ui.card>

    </div>

</x-layouts.app>
