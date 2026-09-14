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
                class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 text-sm font-black text-white shadow-sm transition hover:bg-emerald-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600"
            >
                + Nueva venta
            </a>

        </x-slot:actions>
    </x-layout.page-header>


    {{-- =========================================================
         MÉTRICAS
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

        {{-- VENTAS --}}
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


                <div class="mt-6 grid grid-cols-3 border-t border-slate-100 pt-5">

                    <div>
                        <p class="text-xs font-semibold text-slate-500">
                            Ventas
                        </p>

                        <p class="mt-1 text-base font-black text-slate-950">
                            ${{ number_format($salesToday, 2) }}
                        </p>
                    </div>

                    <div class="border-l border-slate-100 pl-4">

                        <p class="text-xs font-semibold text-slate-500">
                            Tickets
                        </p>

                        <p class="mt-1 text-base font-black text-slate-950">
                            {{ $ticketsToday }}
                        </p>

                    </div>

                    <div class="border-l border-slate-100 pl-4">

                        <p class="text-xs font-semibold text-slate-500">
                            Ticket promedio
                        </p>

                        <p class="mt-1 text-base font-black text-slate-950">
                            ${{ number_format($averageTicket, 2) }}
                        </p>

                    </div>

                </div>

            </div>

        </x-ui.card>


        {{-- PENDIENTES --}}
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
                            {{ $outOfStock }} productos agotados.
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

                                {{ $cashSummary['label'] }} ·
                                ${{ number_format($cashSummary['theoretical_cash'], 2) }}

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
                            Órdenes aprobadas pendientes de recepción.
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
                            Gastos pendientes
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
         INVENTARIO + CAJA
    ========================================================== --}}

    <div class="mt-6 grid gap-6 lg:grid-cols-2">


        {{-- INVENTARIO --}}
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


        {{-- CAJA --}}
        <x-ui.card
            class="overflow-hidden"
            padding="p-0"
        >

            <div class="border-b border-slate-100 px-5 py-5">

                <p class="text-xs font-black uppercase tracking-[0.1em] text-violet-700">
                    Caja
                </p>

                <h2 class="mt-1 text-lg font-black text-slate-950">
                    Estado de la sesión
                </h2>

            </div>


            @if ($activeCashSession)

                <div class="p-5">

                    <div class="flex items-center justify-between">

                        <div>

                            <p class="text-xs font-bold uppercase tracking-[0.08em] text-slate-400">
                                Estado
                            </p>

                            <span class="mt-2 inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-black text-emerald-700">

                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>

                                {{ $cashSummary['label'] }}

                            </span>

                        </div>

                        <a
                            href="{{ route('cash.index') }}"
                            class="text-xs font-black text-emerald-700 hover:text-emerald-800"
                        >
                            Ver caja →
                        </a>

                    </div>


                    <div class="mt-6 grid grid-cols-2 gap-3">

                        <div class="rounded-xl bg-slate-50 p-4">

                            <p class="text-xs font-semibold text-slate-500">
                                Entradas
                            </p>

                            <p class="mt-2 text-lg font-black tabular-nums text-emerald-700">
                                ${{ number_format($cashSummary['cash_in'], 2) }}
                            </p>

                        </div>

                        <div class="rounded-xl bg-slate-50 p-4">

                            <p class="text-xs font-semibold text-slate-500">
                                Salidas
                            </p>

                            <p class="mt-2 text-lg font-black tabular-nums text-rose-700">
                                ${{ number_format($cashSummary['cash_out'], 2) }}
                            </p>

                        </div>

                    </div>


                    <div class="mt-3 rounded-2xl border border-violet-100 bg-violet-50 p-5">

                        <p class="text-xs font-bold text-violet-700">
                            Efectivo teórico
                        </p>

                        <p class="mt-1 text-3xl font-black tabular-nums text-slate-950">
                            ${{ number_format($cashSummary['theoretical_cash'], 2) }}
                        </p>

                        @if ($cashSummary['opened_at'])

                            <p class="mt-2 text-xs font-medium text-violet-700/70">
                                Apertura:
                                {{ \Carbon\Carbon::parse($cashSummary['opened_at'])->format('d/m/Y H:i') }}
                            </p>

                        @endif

                    </div>

                </div>

            @else

                <div class="grid min-h-56 place-items-center px-5 text-center">

                    <div>

                        <div class="mx-auto grid h-12 w-12 place-items-center rounded-2xl bg-violet-50 text-lg font-black text-violet-700">
                            $
                        </div>

                        <p class="mt-4 text-sm font-black text-slate-800">
                            Caja cerrada
                        </p>

                        <p class="mt-1 text-xs leading-5 text-slate-500">
                            No existe una sesión activa para esta sucursal.
                        </p>

                        <a
                            href="{{ route('cash.index') }}"
                            class="mt-4 inline-flex min-h-10 items-center justify-center rounded-xl bg-slate-900 px-4 text-xs font-black text-white transition hover:bg-slate-800"
                        >
                            Abrir caja
                        </a>

                    </div>

                </div>

            @endif

        </x-ui.card>

    </div>


    {{-- =========================================================
         COMPRAS + GASTOS
    ========================================================== --}}

    <div class="mt-6 grid gap-6 lg:grid-cols-2">


        {{-- COMPRAS --}}
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


        {{-- GASTOS --}}
        <x-ui.card
            class="overflow-hidden"
            padding="p-0"
        >

            <div class="border-b border-slate-100 px-5 py-5">

                <p class="text-xs font-black uppercase tracking-[0.1em] text-rose-700">
                    Gastos
                </p>

                <h2 class="mt-1 text-lg font-black text-slate-950">
                    Control del periodo
                </h2>

            </div>


            <div class="p-5">

                <div class="grid gap-3 sm:grid-cols-2">

                    <div class="rounded-2xl bg-slate-50 p-5">

                        <p class="text-xs font-semibold text-slate-500">
                            Gastos del mes
                        </p>

                        <p class="mt-2 text-2xl font-black tabular-nums text-slate-950">
                            ${{ number_format($monthExpenses, 2) }}
                        </p>

                    </div>


                    <div class="rounded-2xl bg-rose-50 p-5">

                        <p class="text-xs font-semibold text-rose-700">
                            Pendientes
                        </p>

                        <p class="mt-2 text-2xl font-black tabular-nums text-rose-800">
                            {{ $pendingExpenses }}
                        </p>

                        <p class="mt-1 text-[11px] font-semibold text-rose-700/70">
                            Por revisar
                        </p>

                    </div>

                </div>


                <a
                    href="{{ route('expenses.index') }}"
                    class="mt-4 inline-flex min-h-11 w-full items-center justify-center rounded-xl border border-slate-200 bg-white text-sm font-black text-slate-700 transition hover:bg-slate-50"
                >
                    Administrar gastos
                </a>

            </div>

        </x-ui.card>

    </div>


    {{-- =========================================================
         ACTIVIDAD RECIENTE
    ========================================================== --}}

    <x-ui.card
        class="mt-6 overflow-hidden"
        padding="p-0"
    >

        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-5">

            <div>

                <p class="text-xs font-black uppercase tracking-[0.1em] text-slate-500">
                    Trazabilidad
                </p>

                <h2 class="mt-1 text-lg font-black text-slate-950">
                    Actividad reciente
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Últimas operaciones registradas en la sucursal.
                </p>

            </div>

        </div>


        @if ($recentActivity->isEmpty())

            <div class="grid min-h-40 place-items-center px-5 text-center">

                <p class="text-sm font-semibold text-slate-500">
                    Todavía no hay actividad registrada.
                </p>

            </div>

        @else

            <div class="divide-y divide-slate-100">

                @foreach ($recentActivity as $activity)

                    <a
                        href="{{ $activity['url'] }}"
                        class="flex items-center gap-4 px-5 py-4 transition hover:bg-slate-50"
                    >

                        <div class="grid h-9 w-9 shrink-0 place-items-center rounded-xl
                            @if ($activity['type'] === 'sale')
                                bg-emerald-50 text-emerald-700
                            @elseif ($activity['type'] === 'purchase')
                                bg-sky-50 text-sky-700
                            @else
                                bg-rose-50 text-rose-700
                            @endif
                        ">

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


    {{-- =========================================================
         ACCIONES RÁPIDAS
    ========================================================== --}}

    <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">

        <a
            href="{{ route('sales.index') }}"
            class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-emerald-300 hover:shadow-md"
        >

            <p class="text-sm font-black text-slate-900">
                Nueva venta
            </p>

            <p class="mt-1 text-xs leading-5 text-slate-500">
                Abrir punto de venta.
            </p>

            <p class="mt-4 text-xs font-black text-emerald-700 group-hover:text-emerald-800">
                Abrir POS →
            </p>

        </a>


        <a
            href="{{ route('purchases.create') }}"
            class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-sky-300 hover:shadow-md"
        >

            <p class="text-sm font-black text-slate-900">
                Nueva compra
            </p>

            <p class="mt-1 text-xs leading-5 text-slate-500">
                Registrar mercancía de proveedor.
            </p>

            <p class="mt-4 text-xs font-black text-sky-700 group-hover:text-sky-800">
                Registrar compra →
            </p>

        </a>


        <a
            href="{{ route('stock.index') }}"
            class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-amber-300 hover:shadow-md"
        >

            <p class="text-sm font-black text-slate-900">
                Ajustar inventario
            </p>

            <p class="mt-1 text-xs leading-5 text-slate-500">
                Registrar entradas, salidas o ajustes.
            </p>

            <p class="mt-4 text-xs font-black text-amber-700 group-hover:text-amber-800">
                Ver existencias →
            </p>

        </a>


        <a
            href="{{ route('expenses.create') }}"
            class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-rose-300 hover:shadow-md"
        >

            <p class="text-sm font-black text-slate-900">
                Registrar gasto
            </p>

            <p class="mt-1 text-xs leading-5 text-slate-500">
                Registrar un gasto operativo.
            </p>

            <p class="mt-4 text-xs font-black text-rose-700 group-hover:text-rose-800">
                Nuevo gasto →
            </p>

        </a>

    </div>

</x-layouts.app>
