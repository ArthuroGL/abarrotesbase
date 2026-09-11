<x-layouts.app title="Detalle de venta | ABARROTESBASE">

    <x-layout.page-header
        eyebrow="Operación · Ventas"
        title="Detalle de venta"
        description="Consulta la información completa de la operación."
    >
        <x-slot:actions>

            <a
                href="{{ route('sales.history') }}"
                class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-50"
            >
                Regresar
            </a>

            <a
                href="{{ route('sales.ticket', $sale) }}"
                target="_blank"
                class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-slate-800"
            >
                Imprimir ticket
            </a>

            @if ($sale->status === 'confirmed')

                <button
                    type="button"
                    id="open-cancel-modal"
                    class="inline-flex items-center justify-center rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-rose-700"
                >
                    Cancelar venta
                </button>

            @endif

        </x-slot:actions>
    </x-layout.page-header>


    {{-- Encabezado de la venta --}}
    <div class="mt-6 grid gap-4 lg:grid-cols-4">

        <x-ui.card>

            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                Folio
            </p>

            <p class="mt-2 text-lg font-black text-slate-900">
                {{ $sale->sale_number }}
            </p>

        </x-ui.card>


        <x-ui.card>

            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                Fecha
            </p>

            <p class="mt-2 text-lg font-black text-slate-900">
                {{ $sale->created_at->format('d/m/Y H:i') }}
            </p>

        </x-ui.card>


        <x-ui.card>

            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                Cliente
            </p>

            <p class="mt-2 text-lg font-black text-slate-900">
                {{ $sale->customer?->name ?? 'Público general' }}
            </p>

        </x-ui.card>


        <x-ui.card>

            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                Estado
            </p>

            @php
                $status = match ($sale->status) {
                    'confirmed' => [
                        'Confirmada',
                        'bg-emerald-50 text-emerald-700'
                    ],
                    'cancelled' => [
                        'Cancelada',
                        'bg-rose-50 text-rose-700'
                    ],
                    'returned' => [
                        'Devuelta',
                        'bg-amber-50 text-amber-700'
                    ],
                    'partially_returned' => [
                        'Devolución parcial',
                        'bg-amber-50 text-amber-700'
                    ],
                    default => [
                        ucfirst($sale->status),
                        'bg-slate-100 text-slate-600'
                    ],
                };
            @endphp

            <div class="mt-2">
                <span class="inline-flex rounded-lg px-3 py-1.5 text-xs font-bold {{ $status[1] }}">
                    {{ $status[0] }}
                </span>
            </div>

        </x-ui.card>

    </div>


    {{-- Información general --}}
    <div class="mt-6 grid gap-6 lg:grid-cols-3">

        <x-ui.card class="lg:col-span-2">

            <div class="flex items-center justify-between border-b border-slate-100 pb-4">

                <div>
                    <h2 class="text-base font-black text-slate-900">
                        Productos vendidos
                    </h2>

                    <p class="mt-1 text-xs text-slate-500">
                        Detalle de los productos incluidos en esta venta.
                    </p>
                </div>

                <span class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-600">
                    {{ $sale->lines->count() }}
                    {{ $sale->lines->count() === 1 ? 'producto' : 'productos' }}
                </span>

            </div>


            <div class="mt-5 overflow-x-auto">

                <table class="min-w-full">

                    <thead>
                        <tr class="border-b border-slate-200">

                            <th class="px-3 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                Producto
                            </th>

                            <th class="px-3 py-3 text-center text-xs font-bold uppercase tracking-wider text-slate-500">
                                Cant.
                            </th>

                            <th class="px-3 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500">
                                Precio
                            </th>

                            <th class="px-3 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500">
                                Descuento
                            </th>

                            <th class="px-3 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500">
                                Total
                            </th>

                        </tr>
                    </thead>


                    <tbody class="divide-y divide-slate-100">

                        @forelse ($sale->lines as $line)

                            <tr>

                                <td class="px-3 py-4">

                                    <div class="font-bold text-slate-900">
                                        {{ $line->description }}
                                    </div>

                                    @if ($line->sku)
                                        <div class="mt-1 text-xs text-slate-400">
                                            SKU: {{ $line->sku }}
                                        </div>
                                    @endif

                                </td>


                                <td class="px-3 py-4 text-center text-sm font-semibold text-slate-700">
                                    {{ number_format((float) $line->quantity, 3) }}
                                </td>


                                <td class="px-3 py-4 text-right text-sm text-slate-700">
                                    ${{ number_format((float) $line->unit_price, 2) }}
                                </td>


                                <td class="px-3 py-4 text-right text-sm text-slate-500">
                                    ${{ number_format((float) $line->discount_amount, 2) }}
                                </td>


                                <td class="px-3 py-4 text-right font-black text-slate-900">
                                    ${{ number_format((float) $line->line_total, 2) }}
                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="5"
                                    class="px-3 py-10 text-center text-sm text-slate-400"
                                >
                                    Esta venta no tiene líneas registradas.
                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </x-ui.card>


        {{-- Resumen --}}
        <x-ui.card>

            <h2 class="text-base font-black text-slate-900">
                Resumen
            </h2>

            <div class="mt-5 space-y-4">

                <div class="flex items-center justify-between gap-4">
                    <span class="text-sm text-slate-500">
                        Subtotal
                    </span>

                    <span class="font-bold text-slate-900">
                        ${{ number_format((float) $sale->subtotal, 2) }}
                    </span>
                </div>


                <div class="flex items-center justify-between gap-4">
                    <span class="text-sm text-slate-500">
                        Descuento
                    </span>

                    <span class="font-bold text-slate-900">
                        ${{ number_format((float) $sale->discount_total, 2) }}
                    </span>
                </div>


                <div class="flex items-center justify-between gap-4">
                    <span class="text-sm text-slate-500">
                        Impuestos
                    </span>

                    <span class="font-bold text-slate-900">
                        ${{ number_format((float) $sale->tax_total, 2) }}
                    </span>
                </div>


                <div class="border-t border-slate-200 pt-4">

                    <div class="flex items-end justify-between gap-4">

                        <span class="text-sm font-bold text-slate-600">
                            Total
                        </span>

                        <span class="text-2xl font-black text-emerald-700">
                            ${{ number_format((float) $sale->total, 2) }}
                        </span>

                    </div>

                </div>

            </div>

        </x-ui.card>

    </div>


    {{-- Pagos --}}
    <x-ui.card class="mt-6">

        <div class="border-b border-slate-100 pb-4">

            <h2 class="text-base font-black text-slate-900">
                Pagos
            </h2>

            <p class="mt-1 text-xs text-slate-500">
                Medios de pago utilizados en la operación.
            </p>

        </div>


        <div class="mt-5 overflow-x-auto">

            <table class="min-w-full">

                <thead>

                    <tr class="border-b border-slate-200">

                        <th class="px-3 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                            Método
                        </th>

                        <th class="px-3 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                            Referencia
                        </th>

                        <th class="px-3 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500">
                            Recibido
                        </th>

                        <th class="px-3 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500">
                            Aplicado
                        </th>

                    </tr>

                </thead>


                <tbody class="divide-y divide-slate-100">

                    @forelse ($sale->payments as $payment)

                        <tr>

                            <td class="px-3 py-4">

                                <span class="font-bold text-slate-900">
                                    {{ $payment->paymentMethod?->name ?? 'Sin método' }}
                                </span>

                            </td>

                            <td class="px-3 py-4 text-sm text-slate-500">
                                {{ $payment->reference ?: '—' }}
                            </td>

                            <td class="px-3 py-4 text-right text-sm text-slate-700">
                                ${{ number_format((float) $payment->amount_received, 2) }}
                            </td>

                            <td class="px-3 py-4 text-right font-black text-slate-900">
                                ${{ number_format((float) $payment->amount_applied, 2) }}
                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="4"
                                class="px-3 py-10 text-center text-sm text-slate-400"
                            >
                                No hay pagos registrados.
                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </x-ui.card>


    {{-- Información adicional --}}
    @if ($sale->notes || $sale->cancelled_at)

        <x-ui.card class="mt-6">

            <h2 class="text-base font-black text-slate-900">
                Información adicional
            </h2>

            <div class="mt-4 space-y-3 text-sm">

                @if ($sale->notes)

                    <div>
                        <span class="font-bold text-slate-700">
                            Notas:
                        </span>

                        <span class="text-slate-500">
                            {{ $sale->notes }}
                        </span>
                    </div>

                @endif


                @if ($sale->cancelled_at)

                    <div>
                        <span class="font-bold text-slate-700">
                            Cancelada:
                        </span>

                        <span class="text-slate-500">
                            {{ $sale->cancelled_at->format('d/m/Y H:i') }}
                        </span>
                    </div>

                @endif

            </div>

        </x-ui.card>

    @endif


    {{-- Modal de cancelación --}}
    @if ($sale->status === 'confirmed')
        <x-ui.modal id="cancel-sale-modal" size="sm" title="Cancelar venta" description="Confirma que deseas revertir esta operación." close-id="close-cancel-modal">
            <div class="px-5 py-5 sm:px-6">
                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5">
                    <p class="text-xs font-bold uppercase tracking-[0.14em] text-amber-800">Venta seleccionada</p>
                    <p class="mt-1 text-xl font-black text-amber-950">{{ $sale->sale_number }}</p>
                    <p class="mt-3 text-sm leading-6 text-amber-900">Esta operación revertirá la venta y realizará los movimientos correspondientes de inventario y caja.</p>
                </div>
                <form id="cancel-sale-form" method="POST" action="{{ route('sales.cancel', $sale) }}" class="mt-6">
                    @csrf
                    <label for="reason" class="mb-2 block text-sm font-black text-slate-900">Motivo de cancelación</label>
                    <textarea id="reason" name="reason" required maxlength="500" rows="5" class="app-input min-h-32 resize-none" placeholder="Indica el motivo de la cancelación..."></textarea>
                </form>
            </div>
            <x-slot:footer>
                <div class="grid gap-3 sm:grid-cols-2"><button type="button" id="cancel-modal-back" class="min-h-12 rounded-xl border-2 border-slate-300 bg-white px-5 py-3 text-base font-bold text-slate-700 hover:bg-slate-50">Regresar</button><button type="submit" form="cancel-sale-form" class="min-h-12 rounded-xl bg-rose-600 px-5 py-3 text-base font-black text-white hover:bg-rose-700">Confirmar cancelación</button></div>
            </x-slot:footer>
        </x-ui.modal>

    @endif


    @if ($sale->status === 'confirmed')

        <script>
            const cancelModal = document.getElementById('cancel-sale-modal');

            const openCancelModal =
                document.getElementById('open-cancel-modal');

            const closeCancelModal =
                document.getElementById('close-cancel-modal');

            const cancelModalBack =
                document.getElementById('cancel-modal-back');


            function showCancelModal() {
                cancelModal.classList.remove('hidden');
                cancelModal.classList.add('flex');
            }


            function hideCancelModal() {
                cancelModal.classList.add('hidden');
                cancelModal.classList.remove('flex');
            }


            openCancelModal?.addEventListener(
                'click',
                showCancelModal
            );

            closeCancelModal?.addEventListener(
                'click',
                hideCancelModal
            );

            cancelModalBack?.addEventListener(
                'click',
                hideCancelModal
            );


            cancelModal?.addEventListener('click', event => {

                if (event.target === cancelModal) {
                    hideCancelModal();
                }

            });


            document.addEventListener('keydown', event => {

                if (event.key === 'Escape') {
                    hideCancelModal();
                }

            });

        </script>

    @endif

</x-layouts.app>
