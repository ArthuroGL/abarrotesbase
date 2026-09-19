<x-layouts.app title="Historial de ventas | ABARROTESBASE">

    <x-layout.page-header
        eyebrow="Operación"
        title="Historial de ventas"
        description="Consulta ventas realizadas, revisa su detalle y administra cancelaciones.">
        <x-slot:actions>
            <a href="{{ route('sales.index') }}"
                 class="mt-4 min-h-14 w-full rounded-xl bg-emerald-600 px-5 py-4 text-lg font-black text-white shadow-sm transition hover:bg-emerald-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 disabled:cursor-not-allowed disabled:bg-slate-200 disabled:text-slate-400 disabled:shadow-none">
                Nueva venta
            </a>
        </x-slot:actions>
    </x-layout.page-header>

    {{-- Indicadores --}}
    <div class="mt-6 grid gap-4 sm:grid-cols-3">
        <x-ui.card class="metric-card metric-card-emerald p-5 sm:p-6">
            <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Ventas de hoy</p>
            <p class="mt-3 text-3xl font-black tracking-tight text-slate-950">{{ number_format($stats['today_count']) }}</p>
            <p class="mt-1 text-sm text-slate-500">Operaciones registradas</p>
        </x-ui.card>

        <x-ui.card class="metric-card metric-card-sky p-5 sm:p-6">
            <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Total vendido hoy</p>
            <p class="mt-3 text-3xl font-black tracking-tight text-emerald-700">
                $ {{ number_format((float) $stats['today_total'], 2) }} MXN
            </p>
            <p class="mt-1 text-sm text-slate-500">Ventas confirmadas</p>
        </x-ui.card>

        <x-ui.card class="metric-card metric-card-amber p-5 sm:p-6">
            <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Canceladas hoy</p>
            <p class="mt-3 text-3xl font-black tracking-tight text-rose-600">{{ number_format($stats['cancelled_today']) }}</p>
            <p class="mt-1 text-sm text-slate-500">Requieren revisión si aplica</p>
        </x-ui.card>
    </div>

    {{-- Filtros --}}
    <x-ui.card class="mt-6 p-5 sm:p-6">
        <div class="mb-5">
            <h2 class="text-lg font-black text-slate-950">Buscar ventas</h2>
            <p class="mt-1 text-sm text-slate-500">Usa uno o varios filtros para localizar una operación.</p>
        </div>

        <form method="GET" action="{{ route('sales.history') }}" class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_200px_190px_auto_auto] lg:items-end">
            <div>
                <label for="sales-search" class="mb-2 block text-sm font-bold text-slate-800">Folio o cliente</label>
                <input id="sales-search" type="text" name="search" value="{{ request('search') }}"
                    placeholder="Ej. V-20260911 o Juan Pérez" class="app-input">
            </div>

            <div>
                <label for="sales-status" class="mb-2 block text-sm font-bold text-slate-800">Estado</label>
                <select id="sales-status" name="status" class="app-input">
                    <option value="">Todos</option>
                    <option value="confirmed" @selected(request('status')==='confirmed' )>Confirmadas</option>
                    <option value="cancelled" @selected(request('status')==='cancelled' )>Canceladas</option>
                    <option value="returned" @selected(request('status')==='returned' )>Devueltas</option>
                </select>
            </div>

            <div>
                <label for="sales-date" class="mb-2 block text-sm font-bold text-slate-800">Fecha</label>
                <input id="sales-date" type="date" name="date" value="{{ request('date') }}" class="app-input">
            </div>

            <button type="submit"
                class="min-h-12 rounded-xl bg-slate-950 px-5 py-3 text-base font-black text-white transition hover:bg-slate-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-950">
                Buscar
            </button>

            @if(request()->hasAny(['search','status','date']))
            <a href="{{ route('sales.history') }}"
                class="inline-flex min-h-12 items-center justify-center rounded-xl border-2 border-slate-300 bg-white px-5 py-3 text-base font-bold text-slate-700 transition hover:bg-slate-50">
                Limpiar
            </a>
            @endif
        </form>
    </x-ui.card>

   {{-- =========================================================
     HISTORIAL DE VENTAS
========================================================= --}}
<x-ui.card class="mt-6 overflow-hidden p-0">

    {{-- ENCABEZADO --}}
    <div class="flex flex-col gap-3 border-b border-slate-200 bg-white px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">

        <div>
            <p class="text-xs font-black uppercase tracking-[0.14em] text-emerald-700">
                Ventas
            </p>

            <h2 class="mt-1 text-lg font-black text-slate-950">
                Ventas registradas
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Consulta las operaciones realizadas.
            </p>
        </div>

        <span class="w-fit rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-600">
            {{ $sales->total() }}
            {{ $sales->total() === 1 ? 'resultado' : 'resultados' }}
        </span>

    </div>


    {{-- =====================================================
         DESKTOP / TABLET
    ====================================================== --}}
    <div class="hidden min-w-0 px-3 py-3 lg:block sm:px-5">

        <x-ui.table
            caption="Historial de ventas"
            maxHeight="clamp(280px, calc(100vh - 560px), 520px)"
            class="table-fixed">

            <x-slot:head>

                <tr>

                    {{-- FOLIO --}}
                    <th
                        scope="col"
                        class="w-[190px] whitespace-nowrap border-b border-slate-200 bg-slate-50 px-4 py-3.5 text-left text-xs font-black uppercase tracking-wider text-slate-500 sm:px-5">
                        Folio
                    </th>

                    {{-- FECHA --}}
                    <th
                        scope="col"
                        class="w-[145px] whitespace-nowrap border-b border-slate-200 bg-slate-50 px-4 py-3.5 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                        Fecha
                    </th>

                    {{-- CLIENTE --}}
                    <th
                        scope="col"
                        class="border-b border-slate-200 bg-slate-50 px-4 py-3.5 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                        Cliente
                    </th>

                    {{-- ESTADO --}}
                    <th
                        scope="col"
                        class="w-[135px] whitespace-nowrap border-b border-slate-200 bg-slate-50 px-4 py-3.5 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                        Estado
                    </th>

                    {{-- TOTAL --}}
                    <th
                        scope="col"
                        class="w-[145px] whitespace-nowrap border-b border-slate-200 bg-slate-50 px-4 py-3.5 text-right text-xs font-black uppercase tracking-wider text-slate-500">
                        Total
                    </th>

                    {{-- ACCIONES --}}
                    <th
                        scope="col"
                        class="w-[205px] whitespace-nowrap border-b border-slate-200 bg-slate-50 px-4 py-3.5 text-right text-xs font-black uppercase tracking-wider text-slate-500">
                        Acción
                    </th>

                </tr>

            </x-slot:head>


            @forelse ($sales as $sale)

                @php
                    $status = match ($sale->status) {
                        'confirmed' => [
                            'Confirmada',
                            'bg-emerald-50 text-emerald-800 ring-1 ring-inset ring-emerald-200'
                        ],
                        'cancelled' => [
                            'Cancelada',
                            'bg-rose-50 text-rose-800 ring-1 ring-inset ring-rose-200'
                        ],
                        'returned' => [
                            'Devuelta',
                            'bg-amber-50 text-amber-800 ring-1 ring-inset ring-amber-200'
                        ],
                        'partially_returned' => [
                            'Devolución parcial',
                            'bg-amber-50 text-amber-800 ring-1 ring-inset ring-amber-200'
                        ],
                        default => [
                            ucfirst($sale->status),
                            'bg-slate-100 text-slate-700 ring-1 ring-inset ring-slate-200'
                        ],
                    };
                @endphp


                <tr class="group transition hover:bg-slate-50/70">

                    {{-- FOLIO --}}
                    <td class="px-5 py-4 align-middle">

                        <span class="block truncate text-sm font-black text-slate-950">
                            {{ $sale->sale_number }}
                        </span>

                    </td>


                    {{-- FECHA --}}
                    <td class="px-4 py-4 align-middle">

                        <div class="whitespace-nowrap">

                            <p class="text-sm font-bold text-slate-700">
                                {{ $sale->created_at->format('d/m/Y') }}
                            </p>

                            <p class="mt-0.5 text-xs font-medium text-slate-400">
                                {{ $sale->created_at->format('H:i') }}
                            </p>

                        </div>

                    </td>


                    {{-- CLIENTE --}}
                    <td class="min-w-0 px-4 py-4 align-middle">

                        <p class="truncate text-sm font-semibold text-slate-700">
                            {{ $sale->customer?->name ?? 'Público general' }}
                        </p>

                    </td>


                    {{-- ESTADO --}}
                    <td class="px-4 py-4 align-middle">

                        <span
                            class="inline-flex min-h-8 items-center rounded-full px-2.5 py-1 text-xs font-black {{ $status[1] }}">
                            {{ $status[0] }}
                        </span>

                    </td>


                    {{-- TOTAL --}}
                    <td class="px-4 py-4 text-right align-middle">

                        <p class="whitespace-nowrap text-sm font-black tabular-nums text-slate-950">
                            ${{ number_format((float) $sale->total, 2) }}
                        </p>

                        <p class="mt-0.5 text-xs font-medium text-slate-400">
                            MXN
                        </p>

                    </td>


                    {{-- ACCIONES --}}
                    <td class="px-4 py-4 text-right align-middle">

                        <div class="flex items-center justify-end gap-2">

                            <a
                                href="{{ route('sales.show', $sale) }}"
                                class="inline-flex min-h-10 items-center justify-center rounded-lg bg-slate-950 px-3 text-xs font-bold text-white transition hover:bg-slate-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-950">
                                Ver
                            </a>

                            <a
                                href="{{ route('sales.ticket', $sale) }}"
                                target="_blank"
                                class="inline-flex min-h-10 items-center justify-center rounded-lg border border-slate-300 bg-white px-3 text-xs font-bold text-slate-700 transition hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-500">
                                Ticket
                            </a>

                            @if ($sale->status === 'confirmed')

                                <button
                                    type="button"
                                    data-cancel-sale="{{ $sale->id }}"
                                    data-sale-number="{{ $sale->sale_number }}"
                                    class="inline-flex min-h-10 items-center justify-center rounded-lg border border-rose-200 bg-rose-50 px-3 text-xs font-bold text-rose-700 transition hover:border-rose-300 hover:bg-rose-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-rose-600">
                                    Cancelar
                                </button>

                            @endif

                        </div>

                    </td>

                </tr>

            @empty

                <tr>

                    <td colspan="6" class="px-6 py-16 text-center">

                        <div class="mx-auto max-w-md">

                            <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-slate-100 text-xl font-black text-slate-400">
                                ∅
                            </div>

                            <p class="mt-4 text-base font-black text-slate-900">
                                No hay ventas que coincidan con los filtros.
                            </p>

                            <p class="mt-2 text-sm leading-6 text-slate-500">
                                Prueba con otros criterios de búsqueda.
                            </p>

                        </div>

                    </td>

                </tr>

            @endforelse

        </x-ui.table>

    </div>


    {{-- =====================================================
         MOBILE
    ====================================================== --}}
    <div class="divide-y divide-slate-100 lg:hidden">

        @forelse ($sales as $sale)

            @php
                $status = match ($sale->status) {
                    'confirmed' => [
                        'Confirmada',
                        'bg-emerald-50 text-emerald-800 ring-1 ring-inset ring-emerald-200'
                    ],
                    'cancelled' => [
                        'Cancelada',
                        'bg-rose-50 text-rose-800 ring-1 ring-inset ring-rose-200'
                    ],
                    'returned' => [
                        'Devuelta',
                        'bg-amber-50 text-amber-800 ring-1 ring-inset ring-amber-200'
                    ],
                    'partially_returned' => [
                        'Devolución parcial',
                        'bg-amber-50 text-amber-800 ring-1 ring-inset ring-amber-200'
                    ],
                    default => [
                        ucfirst($sale->status),
                        'bg-slate-100 text-slate-700 ring-1 ring-inset ring-slate-200'
                    ],
                };
            @endphp


            <article class="p-5">

                {{-- FOLIO + ESTADO --}}
                <div class="flex items-start justify-between gap-4">

                    <div class="min-w-0">

                        <p class="text-xs font-black uppercase tracking-[0.1em] text-slate-400">
                            Folio
                        </p>

                        <h3 class="mt-1 truncate text-base font-black text-slate-950">
                            {{ $sale->sale_number }}
                        </h3>

                    </div>

                    <span
                        class="shrink-0 rounded-full px-2.5 py-1 text-xs font-black {{ $status[1] }}">
                        {{ $status[0] }}
                    </span>

                </div>


                {{-- INFORMACIÓN --}}
                <div class="mt-4 grid grid-cols-2 gap-4">

                    <div class="min-w-0">

                        <p class="text-xs font-semibold text-slate-400">
                            Cliente
                        </p>

                        <p class="mt-1 truncate text-sm font-bold text-slate-700">
                            {{ $sale->customer?->name ?? 'Público general' }}
                        </p>

                    </div>

                    <div>

                        <p class="text-xs font-semibold text-slate-400">
                            Fecha
                        </p>

                        <p class="mt-1 text-sm font-bold text-slate-700">
                            {{ $sale->created_at->format('d/m/Y') }}
                        </p>

                        <p class="text-xs font-medium text-slate-400">
                            {{ $sale->created_at->format('H:i') }}
                        </p>

                    </div>

                </div>


                {{-- TOTAL --}}
                <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">

                    <p class="text-xs font-black uppercase tracking-[0.1em] text-slate-500">
                        Total
                    </p>

                    <p class="mt-1 text-2xl font-black tracking-tight text-slate-950 tabular-nums">
                        ${{ number_format((float) $sale->total, 2) }}
                        <span class="text-sm font-bold text-slate-500">
                            MXN
                        </span>
                    </p>

                </div>


                {{-- ACCIONES --}}
                <div class="mt-4 grid gap-2 sm:grid-cols-2">

                    <a
                        href="{{ route('sales.show', $sale) }}"
                        class="inline-flex min-h-12 items-center justify-center rounded-xl bg-slate-950 px-4 text-sm font-black text-white transition hover:bg-slate-800">
                        Ver venta
                    </a>

                    <a
                        href="{{ route('sales.ticket', $sale) }}"
                        target="_blank"
                        class="inline-flex min-h-12 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                        Ver ticket
                    </a>

                    @if ($sale->status === 'confirmed')

                        <button
                            type="button"
                            data-cancel-sale="{{ $sale->id }}"
                            data-sale-number="{{ $sale->sale_number }}"
                            class="inline-flex min-h-12 items-center justify-center rounded-xl border border-rose-200 bg-rose-50 px-4 text-sm font-black text-rose-700 transition hover:bg-rose-100 sm:col-span-2">
                            Cancelar venta
                        </button>

                    @endif

                </div>

            </article>

        @empty

            <div class="px-5 py-16 text-center">

                <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-slate-100 text-xl font-black text-slate-400">
                    ∅
                </div>

                <p class="mt-4 text-base font-black text-slate-900">
                    No hay ventas que coincidan con los filtros.
                </p>

                <p class="mt-2 text-sm leading-6 text-slate-500">
                    Prueba con otros criterios de búsqueda.
                </p>

            </div>

        @endforelse

    </div>


    {{-- PAGINACIÓN --}}
    <x-ui.table-pagination
        :paginator="$sales"
        :per-page-options="[10, 20, 50, 100]" />

</x-ui.card>

    {{-- Modal de cancelación --}}
    <x-ui.modal id="cancel-sale-modal" size="sm" title="Cancelar venta" description="Esta acción revertirá la operación y sus movimientos." close-id="close-cancel-sale">
        <div class="px-5 py-5 sm:px-6">
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4">
                <p class="text-sm font-bold text-amber-900">Folio seleccionado</p>
                <p id="cancel-sale-number" class="mt-1 text-lg font-black text-amber-950"></p>
                <p class="mt-2 text-sm leading-6 text-amber-800">La cancelación realizará los movimientos correspondientes de inventario y caja.</p>
            </div>

            <form id="cancel-sale-form" method="POST" class="mt-6">
                @csrf
                <label for="cancel-reason" class="mb-2 block text-sm font-black text-slate-900">Motivo de cancelación</label>
                <textarea id="cancel-reason" name="reason" required maxlength="500" rows="5" class="app-input min-h-32 resize-none" placeholder="Explica brevemente por qué se cancela la venta..."></textarea>
            </form>
        </div>
        <x-slot:footer>
            <div class="grid gap-3 sm:grid-cols-2">
                <button type="button" id="cancel-sale-back" class="min-h-12 rounded-xl border-2 border-slate-300 bg-white px-5 py-3 text-base font-bold text-slate-700 hover:bg-slate-50">Regresar</button>
                <button type="submit" form="cancel-sale-form" class="min-h-12 rounded-xl bg-rose-600 px-5 py-3 text-base font-black text-white hover:bg-rose-700">Confirmar cancelación</button>
            </div>
        </x-slot:footer>
    </x-ui.modal>

    <script>
        const modal = document.getElementById('cancel-sale-modal');
        const form = document.getElementById('cancel-sale-form');
        const number = document.getElementById('cancel-sale-number');
        const closeButton = document.getElementById('cancel-sale-back');

        document.querySelectorAll('[data-cancel-sale]').forEach(button => {
            button.addEventListener('click', () => {
                number.textContent = button.dataset.saleNumber;
                form.action = `/sales/${button.dataset.cancelSale}/cancel`;
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                setTimeout(() => document.getElementById('cancel-reason')?.focus(), 50);
            });
        });

        const hideCancelModal = () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        };

        document.getElementById('close-cancel-sale')?.addEventListener('click', hideCancelModal);
        closeButton?.addEventListener('click', hideCancelModal);
        modal?.addEventListener('click', event => {
            if (event.target === modal) hideCancelModal();
        });
        document.addEventListener('keydown', event => {
            if (event.key === 'Escape') hideCancelModal();
        });
    </script>

</x-layouts.app>
