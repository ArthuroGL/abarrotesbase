<x-layouts.app title="Historial de ventas | ABARROTESBASE">

    <x-layout.page-header
        eyebrow="Operación"
        title="Historial de ventas"
        description="Consulta ventas realizadas, revisa su detalle y administra cancelaciones."
    >
        <x-slot:actions>
            <a
                href="{{ route('sales.index') }}"
                class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-emerald-700"
            >
                Nueva venta
            </a>
        </x-slot:actions>
    </x-layout.page-header>

    <div class="mt-6 grid gap-4 sm:grid-cols-3">

        <x-ui.card>
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                Ventas de hoy
            </p>

            <p class="mt-2 text-2xl font-black text-slate-900">
                {{ number_format($stats['today_count']) }}
            </p>
        </x-ui.card>

        <x-ui.card>
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                Total vendido hoy
            </p>

            <p class="mt-2 text-2xl font-black text-emerald-700">
                ${{ number_format((float) $stats['today_total'], 2) }}
            </p>
        </x-ui.card>

        <x-ui.card>
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                Canceladas hoy
            </p>

            <p class="mt-2 text-2xl font-black text-rose-600">
                {{ number_format($stats['cancelled_today']) }}
            </p>
        </x-ui.card>

    </div>

    <x-ui.card class="mt-6">

        <form
            method="GET"
            action="{{ route('sales.history') }}"
            class="grid gap-3 md:grid-cols-[1fr_180px_180px_auto]"
        >

            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Buscar folio o cliente..."
                class="app-input"
            >

            <select name="status" class="app-input">
                <option value="">Todos los estados</option>
                <option value="confirmed" @selected(request('status') === 'confirmed')>
                    Confirmadas
                </option>
                <option value="cancelled" @selected(request('status') === 'cancelled')>
                    Canceladas
                </option>
                <option value="returned" @selected(request('status') === 'returned')>
                    Devueltas
                </option>
            </select>

            <input
                type="date"
                name="date"
                value="{{ request('date') }}"
                class="app-input"
            >

            <button
                type="submit"
                class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-bold text-white hover:bg-slate-800"
            >
                Filtrar
            </button>

        </form>

    </x-ui.card>

    <x-ui.card class="mt-6 overflow-hidden p-0">

        <div class="overflow-x-auto">

            <table class="min-w-full divide-y divide-slate-200">

                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                            Folio
                        </th>

                        <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                            Fecha
                        </th>

                        <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                            Cliente
                        </th>

                        <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                            Estado
                        </th>

                        <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500">
                            Total
                        </th>

                        <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500">
                            Acciones
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100 bg-white">

                    @forelse ($sales as $sale)

                        <tr class="hover:bg-slate-50">

                            <td class="px-5 py-4">
                                <span class="font-bold text-slate-900">
                                    {{ $sale->sale_number }}
                                </span>
                            </td>

                            <td class="px-5 py-4 text-sm text-slate-500">
                                {{ $sale->created_at->format('d/m/Y H:i') }}
                            </td>

                            <td class="px-5 py-4 text-sm text-slate-600">
                                {{ $sale->customer?->name ?? 'Público general' }}
                            </td>

                            <td class="px-5 py-4">

                                @php
                                    $status = match ($sale->status) {
                                        'confirmed' => ['Confirmada', 'bg-emerald-50 text-emerald-700'],
                                        'cancelled' => ['Cancelada', 'bg-rose-50 text-rose-700'],
                                        'returned' => ['Devuelta', 'bg-amber-50 text-amber-700'],
                                        'partially_returned' => ['Devolución parcial', 'bg-amber-50 text-amber-700'],
                                        default => [ucfirst($sale->status), 'bg-slate-100 text-slate-600'],
                                    };
                                @endphp

                                <span class="inline-flex rounded-lg px-2.5 py-1 text-xs font-bold {{ $status[1] }}">
                                    {{ $status[0] }}
                                </span>

                            </td>

                            <td class="px-5 py-4 text-right font-black text-slate-900">
                                ${{ number_format((float) $sale->total, 2) }}
                            </td>

                            <td class="px-5 py-4">

                                <div class="flex justify-end gap-2">

                                    <a
                                        href="{{ route('sales.show', $sale) }}"
                                        class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50"
                                    >
                                        Ver
                                    </a>

                                    <a
                                        href="{{ route('sales.ticket', $sale) }}"
                                        target="_blank"
                                        class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50"
                                    >
                                        Ticket
                                    </a>

                                    @if ($sale->status === 'confirmed')
                                        <button
                                            type="button"
                                            data-cancel-sale="{{ $sale->id }}"
                                            data-sale-number="{{ $sale->sale_number }}"
                                            class="rounded-lg bg-rose-50 px-3 py-2 text-xs font-bold text-rose-700 hover:bg-rose-100"
                                        >
                                            Cancelar
                                        </button>
                                    @endif

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td
                                colspan="6"
                                class="px-5 py-12 text-center text-sm text-slate-400"
                            >
                                No hay ventas que coincidan con los filtros.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        @if ($sales->hasPages())
            <div class="border-t border-slate-100 px-5 py-4">
                {{ $sales->links() }}
            </div>
        @endif

    </x-ui.card>

    {{-- Modal cancelación --}}
    <div
        id="cancel-sale-modal"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/50 px-4"
    >
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">

            <h2 class="text-lg font-black text-slate-900">
                Cancelar venta
            </h2>

            <p class="mt-2 text-sm text-slate-500">
                Venta:
                <strong id="cancel-sale-number"></strong>
            </p>

            <form id="cancel-sale-form" method="POST" class="mt-5">

                @csrf

                <label class="mb-2 block text-sm font-bold text-slate-700">
                    Motivo
                </label>

                <textarea
                    name="reason"
                    required
                    maxlength="500"
                    rows="4"
                    class="app-input resize-none"
                    placeholder="Indica por qué se cancela la venta..."
                ></textarea>

                <div class="mt-5 flex justify-end gap-3">

                    <button
                        type="button"
                        id="close-cancel-sale"
                        class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-bold text-slate-700"
                    >
                        Regresar
                    </button>

                    <button
                        type="submit"
                        class="rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-rose-700"
                    >
                        Confirmar cancelación
                    </button>

                </div>

            </form>

        </div>
    </div>

    <script>
        const modal = document.getElementById('cancel-sale-modal');
        const form = document.getElementById('cancel-sale-form');
        const number = document.getElementById('cancel-sale-number');

        document.querySelectorAll('[data-cancel-sale]').forEach(button => {
            button.addEventListener('click', () => {
                number.textContent = button.dataset.saleNumber;

                form.action = `/sales/${button.dataset.cancelSale}/cancel`;

                modal.classList.remove('hidden');
                modal.classList.add('flex');
            });
        });

        document.getElementById('close-cancel-sale')
            ?.addEventListener('click', () => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            });
    </script>

</x-layouts.app>
