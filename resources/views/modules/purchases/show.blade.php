<x-layouts.app title="Compra {{ $purchase->purchase_number }} | ABARROTESBASE">

    <div class="space-y-6">

        {{-- =========================================================
             HEADER
        ========================================================== --}}
        <x-layout.page-header
            eyebrow="Orden de compra"
            title="{{ $purchase->purchase_number }}"
            description="Detalle de la orden, proveedor, mercancía y estado de recepción.">

            <x-slot:actions>

                <a
                    href="{{ route('purchases.index') }}"
                    class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                    Volver a compras
                </a>

                @if ($purchase->status === 'approved')

                    <button
                        type="button"
                        onclick="openPurchaseModal('receivePurchaseModal')"
                        class="inline-flex min-h-11 items-center justify-center rounded-xl bg-emerald-600 px-4 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700">
                        Marcar como recibida
                    </button>

                    <button
                        type="button"
                        onclick="openPurchaseModal('cancelPurchaseModal')"
                        class="inline-flex min-h-11 items-center justify-center rounded-xl border border-rose-200 bg-rose-50 px-4 text-sm font-bold text-rose-700 transition hover:bg-rose-100">
                        Rechazar compra
                    </button>

                @endif

            </x-slot:actions>

        </x-layout.page-header>


        {{-- =========================================================
             ESTADO
        ========================================================== --}}
        <div class="flex flex-wrap items-center gap-3">

            @if ($purchase->status === 'received')

                <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-black text-emerald-700">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    Recibida
                </span>

            @elseif ($purchase->status === 'approved')

                <span class="inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-black text-amber-700">
                    <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                    Pendiente de recepción
                </span>

            @elseif ($purchase->status === 'cancelled')

                <span class="inline-flex items-center gap-2 rounded-full border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-black text-rose-700">
                    <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                    Cancelada
                </span>

            @else

                <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-100 px-3 py-1.5 text-xs font-black text-slate-600">
                    <span class="h-2 w-2 rounded-full bg-slate-400"></span>
                    Borrador
                </span>

            @endif

            <span class="text-sm text-slate-500">
                Registrada el {{ $purchase->created_at->format('d/m/Y H:i') }}
                @if ($purchase->creator)
                    por <strong class="text-slate-700">{{ $purchase->creator->name }}</strong>
                @endif
            </span>

        </div>


        {{-- =========================================================
             FLASH
        ========================================================== --}}
        @if (session('success'))

            <div
                class="flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4"
                role="status">

                <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-emerald-600 text-xs font-black text-white">
                    ✓
                </span>

                <p class="text-sm font-semibold text-emerald-800">
                    {{ session('success') }}
                </p>

            </div>

        @endif


        @if (session('error'))

            <div
                class="flex items-start gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4"
                role="alert">

                <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-rose-600 text-xs font-black text-white">
                    !
                </span>

                <p class="text-sm font-semibold text-rose-800">
                    {{ session('error') }}
                </p>

            </div>

        @endif


        {{-- =========================================================
             INFORMACIÓN PRINCIPAL
        ========================================================== --}}
        <div class="grid gap-4 lg:grid-cols-3">

            <x-ui.card padding="p-5" class="relative overflow-hidden">

                <div class="absolute inset-y-0 left-0 w-1 bg-sky-500"></div>

                <p class="text-xs font-black uppercase tracking-[0.12em] text-slate-400">
                    Proveedor
                </p>

                <p class="mt-2 text-lg font-black text-slate-950">
                    {{ $purchase->supplier?->business_name ?? 'Proveedor no disponible' }}
                </p>

                <p class="mt-1 text-sm text-slate-500">
                    RFC: {{ $purchase->supplier?->rfc ?? 'No registrado' }}
                </p>

            </x-ui.card>


            <x-ui.card padding="p-5" class="relative overflow-hidden">

                <div class="absolute inset-y-0 left-0 w-1 bg-violet-500"></div>

                <p class="text-xs font-black uppercase tracking-[0.12em] text-slate-400">
                    Referencia del proveedor
                </p>

                <p class="mt-2 text-lg font-black text-slate-950">
                    {{ $purchase->supplier_reference ?: 'Sin referencia' }}
                </p>

                <p class="mt-1 text-sm text-slate-500">
                    Moneda: {{ $purchase->currency_code }}
                </p>

            </x-ui.card>


            <x-ui.card padding="p-5" class="relative overflow-hidden">

                <div class="absolute inset-y-0 left-0 w-1 bg-emerald-500"></div>

                <p class="text-xs font-black uppercase tracking-[0.12em] text-slate-400">
                    Total de la orden
                </p>

                <p class="mt-2 text-3xl font-black tracking-tight text-slate-950">
                    ${{ number_format((float) $purchase->total, 2) }}
                </p>

                <p class="mt-1 text-sm font-medium text-slate-500">
                    {{ $purchase->currency_code }}
                </p>

            </x-ui.card>

        </div>


        {{-- =========================================================
             PARTIDAS
        ========================================================== --}}
        <x-ui.card padding="p-0" class="overflow-hidden">

            <div class="border-b border-slate-200 px-5 py-5 sm:px-6">

                <p class="text-xs font-black uppercase tracking-[0.12em] text-emerald-700">
                    Mercancía
                </p>

                <h2 class="mt-1 text-base font-black text-slate-950">
                    Artículos de la orden
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    {{ $purchase->lines->count() }}
                    {{ $purchase->lines->count() === 1 ? 'partida registrada' : 'partidas registradas' }}
                </p>

            </div>


            {{-- Desktop --}}
            <div class="hidden overflow-x-auto lg:block">

                <table class="min-w-[850px] w-full text-left">

                    <thead class="border-b border-slate-200 bg-slate-50">

                        <tr class="text-xs font-black uppercase tracking-[0.08em] text-slate-500">

                            <th class="px-6 py-4">
                                #
                            </th>

                            <th class="px-6 py-4">
                                Artículo
                            </th>

                            <th class="px-6 py-4 text-center">
                                Unidad
                            </th>

                            <th class="px-6 py-4 text-right">
                                Cantidad
                            </th>

                            <th class="px-6 py-4 text-right">
                                Costo unitario
                            </th>

                            <th class="px-6 py-4 text-right">
                                Total
                            </th>

                        </tr>

                    </thead>


                    <tbody class="divide-y divide-slate-100">

                        @foreach ($purchase->lines as $line)

                            <tr class="transition hover:bg-slate-50/70">

                                <td class="px-6 py-5 text-xs font-bold text-slate-400">
                                    {{ $line->line_number }}
                                </td>

                                <td class="px-6 py-5">

                                    <p class="text-sm font-black text-slate-950">
                                        {{ $line->description }}
                                    </p>

                                </td>

                                <td class="px-6 py-5 text-center">

                                    <span class="inline-flex rounded-full border border-slate-200 bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">
                                        {{ $line->productUnit?->unit?->name ?? 'PZA' }}
                                    </span>

                                </td>

                                <td class="px-6 py-5 text-right">

                                    <span class="text-sm font-black text-slate-800">
                                        {{ number_format((float) $line->ordered_quantity, 2) }}
                                    </span>

                                </td>

                                <td class="px-6 py-5 text-right">

                                    <span class="font-mono text-sm font-bold text-slate-700">
                                        ${{ number_format((float) $line->unit_cost, 2) }}
                                    </span>

                                </td>

                                <td class="px-6 py-5 text-right">

                                    <span class="font-mono text-sm font-black text-slate-950">
                                        ${{ number_format((float) $line->line_total, 2) }}
                                    </span>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>


            {{-- Mobile --}}
            <div class="divide-y divide-slate-100 lg:hidden">

                @foreach ($purchase->lines as $line)

                    <article class="p-5">

                        <div class="flex items-start justify-between gap-4">

                            <div class="min-w-0">

                                <p class="text-xs font-black uppercase tracking-[0.1em] text-slate-400">
                                    Partida {{ $line->line_number }}
                                </p>

                                <h3 class="mt-1 text-sm font-black text-slate-950">
                                    {{ $line->description }}
                                </h3>

                            </div>

                            <span class="shrink-0 rounded-full border border-slate-200 bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600">
                                {{ $line->productUnit?->unit?->name ?? 'PZA' }}
                            </span>

                        </div>


                        <div class="mt-4 grid grid-cols-3 gap-3">

                            <div class="rounded-xl bg-slate-50 p-3">

                                <p class="text-[11px] font-bold uppercase text-slate-400">
                                    Cantidad
                                </p>

                                <p class="mt-1 text-sm font-black text-slate-900">
                                    {{ number_format((float) $line->ordered_quantity, 2) }}
                                </p>

                            </div>


                            <div class="rounded-xl bg-slate-50 p-3">

                                <p class="text-[11px] font-bold uppercase text-slate-400">
                                    Costo
                                </p>

                                <p class="mt-1 text-sm font-black text-slate-900">
                                    ${{ number_format((float) $line->unit_cost, 2) }}
                                </p>

                            </div>


                            <div class="rounded-xl bg-emerald-50 p-3">

                                <p class="text-[11px] font-bold uppercase text-emerald-600">
                                    Total
                                </p>

                                <p class="mt-1 text-sm font-black text-emerald-800">
                                    ${{ number_format((float) $line->line_total, 2) }}
                                </p>

                            </div>

                        </div>

                    </article>

                @endforeach

            </div>


            {{-- Total --}}
            <div class="border-t border-slate-200 bg-slate-50 px-5 py-5 sm:px-6">

                <div class="flex items-center justify-between gap-4">

                    <span class="text-sm font-bold text-slate-600">
                        Total de la orden
                    </span>

                    <span class="text-2xl font-black text-slate-950">
                        ${{ number_format((float) $purchase->total, 2) }}
                        <span class="text-xs font-bold text-slate-400">
                            {{ $purchase->currency_code }}
                        </span>
                    </span>

                </div>

            </div>

        </x-ui.card>


        {{-- =========================================================
             NOTAS
        ========================================================== --}}
        @if ($purchase->notes)

            <x-ui.card padding="p-5 sm:p-6">

                <p class="text-xs font-black uppercase tracking-[0.12em] text-slate-400">
                    Observaciones
                </p>

                <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-700">
                    {{ $purchase->notes }}
                </p>

            </x-ui.card>

        @endif


        {{-- =========================================================
             MODAL RECEPCIÓN
        ========================================================== --}}
        <x-ui.modal
            id="receivePurchaseModal"
            size="sm"
            title="Confirmar recepción"
            description="La mercancía será incorporada al inventario de la sucursal."
            close-id="close-receive-purchase-modal">

            <form
                id="receive-purchase-form"
                method="POST"
                action="{{ route('purchases.receive', $purchase) }}">

                @csrf

                <div class="space-y-5 px-5 py-5 sm:px-6">

                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4">

                        <p class="text-xs font-black uppercase tracking-[0.1em] text-emerald-700">
                            Orden
                        </p>

                        <p class="mt-1 text-base font-black text-emerald-950">
                            {{ $purchase->purchase_number }}
                        </p>

                    </div>

                    <p class="text-sm leading-6 text-slate-600">
                        Estás a punto de marcar esta orden como recibida.
                        Las cantidades de sus partidas se registrarán como entradas de inventario y se actualizará el costo promedio ponderado.
                    </p>

                </div>

            </form>


            <x-slot:footer>

                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

                    <button
                        type="button"
                        id="cancel-receive-purchase"
                        onclick="closePurchaseModal('receivePurchaseModal')"
                        class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-5 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                        Cancelar
                    </button>

                    <x-ui.button
                        type="submit"
                        form="receive-purchase-form"
                        variant="primary"
                        size="lg">
                        Confirmar recepción
                    </x-ui.button>

                </div>

            </x-slot:footer>

        </x-ui.modal>


        {{-- =========================================================
             MODAL CANCELACIÓN
        ========================================================== --}}
        <x-ui.modal
            id="cancelPurchaseModal"
            size="md"
            title="Rechazar compra"
            description="Registra el motivo por el que esta orden no continuará."
            close-id="close-cancel-purchase-modal">

            <form
                id="cancel-purchase-form"
                method="POST"
                action="{{ route('purchases.cancel', $purchase) }}">

                @csrf

                <div class="space-y-5 px-5 py-5 sm:px-6">

                    <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4">

                        <p class="text-xs font-black uppercase tracking-[0.1em] text-rose-700">
                            Orden a cancelar
                        </p>

                        <p class="mt-1 text-base font-black text-rose-950">
                            {{ $purchase->purchase_number }}
                        </p>

                    </div>


                    <div>

                        <label
                            for="purchase-cancel-reason"
                            class="mb-2 block text-sm font-bold text-slate-700">
                            Motivo / observaciones
                        </label>

                        <textarea
                            id="purchase-cancel-reason"
                            name="reason"
                            rows="5"
                            maxlength="500"
                            class="app-input min-h-32 resize-none"
                            placeholder="Ej. Precios incorrectos, proveedor canceló el pedido..."
                            required></textarea>

                        <p class="mt-2 text-xs text-slate-400">
                            Máximo 500 caracteres.
                        </p>

                    </div>

                </div>

            </form>


            <x-slot:footer>

                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

                    <button
                        type="button"
                        id="cancel-cancel-purchase"
                        onclick="closePurchaseModal('cancelPurchaseModal')"
                        class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-5 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                        Volver
                    </button>

                    <x-ui.button
                        type="submit"
                        form="cancel-purchase-form"
                        variant="danger"
                        size="lg">
                        Confirmar rechazo
                    </x-ui.button>

                </div>

            </x-slot:footer>

        </x-ui.modal>

    </div>


    <script>

        function openPurchaseModal(modalId) {

            const modal = document.getElementById(modalId);

            if (!modal) {
                return;
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            modal.setAttribute('aria-hidden', 'false');

            document.body.classList.add('overflow-hidden');

        }


        function closePurchaseModal(modalId) {

            const modal = document.getElementById(modalId);

            if (!modal) {
                return;
            }

            modal.classList.add('hidden');
            modal.classList.remove('flex');
            modal.setAttribute('aria-hidden', 'true');

            document.body.classList.remove('overflow-hidden');

        }


        document.addEventListener('DOMContentLoaded', function () {

            const receiveModal =
                document.getElementById('receivePurchaseModal');

            const cancelModal =
                document.getElementById('cancelPurchaseModal');


            document
                .getElementById('close-receive-purchase-modal')
                ?.addEventListener(
                    'click',
                    () => closePurchaseModal('receivePurchaseModal')
                );


            document
                .getElementById('close-cancel-purchase-modal')
                ?.addEventListener(
                    'click',
                    () => closePurchaseModal('cancelPurchaseModal')
                );


            receiveModal?.addEventListener('click', function (event) {

                if (event.target === receiveModal) {
                    closePurchaseModal('receivePurchaseModal');
                }

            });


            cancelModal?.addEventListener('click', function (event) {

                if (event.target === cancelModal) {
                    closePurchaseModal('cancelPurchaseModal');
                }

            });


            document.addEventListener('keydown', function (event) {

                if (event.key !== 'Escape') {
                    return;
                }

                if (
                    receiveModal &&
                    !receiveModal.classList.contains('hidden')
                ) {
                    closePurchaseModal('receivePurchaseModal');
                    return;
                }

                if (
                    cancelModal &&
                    !cancelModal.classList.contains('hidden')
                ) {
                    closePurchaseModal('cancelPurchaseModal');
                }

            });

        });

    </script>

</x-layouts.app>
