<x-layouts.app title="Procesar devolución">

    <div class="space-y-6">

        <x-layout.page-header
            eyebrow="Devoluciones"
            title="Procesar devolución"
            description="Selecciona los productos y cantidades que serán devueltos.">
            <x-slot:actions>
                <a
                    href="{{ route('sales.history') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                    Regresar a ventas
                </a>
            </x-slot:actions>
        </x-layout.page-header>


        {{-- Información de la venta --}}
        <x-ui.card>

            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">

                <div>
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-400">
                        Venta
                    </p>

                    <p class="mt-1 text-lg font-black text-slate-900">
                        {{ $sale->sale_number }}
                    </p>
                </div>

                <div>
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-400">
                        Fecha
                    </p>

                    <p class="mt-1 text-sm font-semibold text-slate-700">
                        {{ $sale->confirmed_at?->format('d/m/Y H:i') ?? $sale->created_at?->format('d/m/Y H:i') }}
                    </p>
                </div>

                <div>
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-400">
                        Cliente
                    </p>

                    <p class="mt-1 text-sm font-semibold text-slate-700">
                        {{ $sale->customer?->name ?? 'Mostrador' }}
                    </p>
                </div>

                <div>
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-400">
                        Total original
                    </p>

                    <p class="mt-1 text-lg font-black text-slate-900">
                        $ {{ number_format((float) $sale->total, 2) }} MXN
                    </p>
                </div>

            </div>

        </x-ui.card>


        {{-- Productos --}}
        <x-ui.card padding="p-0" class="overflow-hidden">

            <div class="border-b border-slate-200 px-5 py-5">

                <h2 class="text-lg font-black text-slate-900">
                    Productos de la venta
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Indica cuánto deseas devolver de cada producto.
                </p>

            </div>


            {{-- Desktop --}}
            <div class="hidden min-w-0 px-3 py-3 lg:block sm:px-5">

                <x-ui.table>

                    <x-slot:head>
                        <tr>
                            <th>Producto</th>
                            <th>Vendido</th>
                            <th>Devuelto</th>
                            <th>Disponible</th>
                            <th>Devolver</th>
                            <th>Condición</th>
                        </tr>
                    </x-slot:head>

                    <tbody>

                        @foreach ($sale->lines as $line)

                        <tr
                            data-line
                            data-line-id="{{ $line->id }}"
                            data-unit-price="{{ (float) $line->unit_price }}"
                            data-original-quantity="{{ (float) $line->quantity }}"
                            data-line-total="{{ (float) $line->line_total }}"
                            data-available="{{ (float) $line->available_return_quantity }}">

                            <td>
                                <div>
                                    <p class="font-bold text-slate-900">
                                        {{ $line->description }}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-400">
                                        SKU: {{ $line->sku }}
                                    </p>
                                </div>
                            </td>

                            <td>
                                {{ rtrim(rtrim(number_format((float) $line->quantity, 3), '0'), '.') }}
                            </td>

                            <td>
                                {{ rtrim(rtrim(number_format((float) $line->returned_quantity, 3), '0'), '.') }}
                            </td>

                            <td>
                                <span class="font-bold text-slate-900">
                                    {{ rtrim(rtrim(number_format((float) $line->available_return_quantity, 3), '0'), '.') }}
                                </span>
                            </td>

                            <td>

                                <input
                                    type="number"
                                    min="0"
                                    max="{{ $line->available_return_quantity }}"
                                    step="any"
                                    value="0"
                                    data-quantity
                                    class="app-input w-28 text-center"
                                    @disabled($line->available_return_quantity <= 0)>

                            </td>

                            <td>

                                <select
                                    data-condition
                                    class="app-input min-w-44"
                                    @disabled($line->available_return_quantity <= 0)>
                                        <option value="resellable">
                                            Producto en buen estado
                                        </option>

                                        <option value="damaged">
                                            Dañado
                                        </option>

                                        <option value="discarded">
                                            Desechado
                                        </option>
                                </select>

                            </td>

                        </tr>

                        @endforeach

                    </tbody>

                </x-ui.table>

            </div>


            {{-- Mobile --}}
            <div class="space-y-4 p-4 lg:hidden">

                @foreach ($sale->lines as $line)

                <article
                    data-line
                    data-line-id="{{ $line->id }}"
                    data-unit-price="{{ (float) $line->unit_price }}"
                    data-original-quantity="{{ (float) $line->quantity }}"
                    data-line-total="{{ (float) $line->line_total }}"
                    data-available="{{ (float) $line->available_return_quantity }}"
                    class="rounded-2xl border border-slate-200 p-4">

                    <div class="flex items-start justify-between gap-4">

                        <div class="min-w-0">

                            <p class="font-black text-slate-900">
                                {{ $line->description }}
                            </p>

                            <p class="mt-1 text-xs text-slate-400">
                                SKU: {{ $line->sku }}
                            </p>

                        </div>

                        @if ($line->available_return_quantity > 0)

                        <span class="shrink-0 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">
                            Disponible
                        </span>

                        @else

                        <span class="shrink-0 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-500">
                            Devuelto
                        </span>

                        @endif

                    </div>


                    <div class="mt-4 grid grid-cols-3 gap-3">

                        <div>
                            <p class="text-xs text-slate-400">
                                Vendido
                            </p>

                            <p class="mt-1 font-bold text-slate-800">
                                {{ rtrim(rtrim(number_format((float) $line->quantity, 3), '0'), '.') }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-slate-400">
                                Devuelto
                            </p>

                            <p class="mt-1 font-bold text-slate-800">
                                {{ rtrim(rtrim(number_format((float) $line->returned_quantity, 3), '0'), '.') }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-slate-400">
                                Disponible
                            </p>

                            <p class="mt-1 font-bold text-slate-800">
                                {{ rtrim(rtrim(number_format((float) $line->available_return_quantity, 3), '0'), '.') }}
                            </p>
                        </div>

                    </div>


                    <div class="mt-4 grid gap-3">

                        <div>

                            <label class="mb-1 block text-xs font-bold text-slate-600">
                                Cantidad a devolver
                            </label>

                            <input
                                type="number"
                                min="0"
                                max="{{ $line->available_return_quantity }}"
                                step="any"
                                value="0"
                                data-quantity
                                class="app-input w-full"
                                @disabled($line->available_return_quantity <= 0)>

                        </div>

                        <div>

                            <label class="mb-1 block text-xs font-bold text-slate-600">
                                Condición
                            </label>

                            <select
                                data-condition
                                class="app-input w-full"
                                @disabled($line->available_return_quantity <= 0)>
                                    <option value="resellable">
                                        Producto en buen estado
                                    </option>

                                    <option value="damaged">
                                        Dañado
                                    </option>

                                    <option value="discarded">
                                        Desechado
                                    </option>
                            </select>

                        </div>

                    </div>

                </article>

                @endforeach

            </div>

        </x-ui.card>


        {{-- Motivo y resumen --}}
        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_360px]">

            <x-ui.card>

                <h2 class="text-lg font-black text-slate-900">
                    Motivo de devolución
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Registra el motivo para conservar trazabilidad de la operación.
                </p>

                <div class="mt-5">

                    <label
                        for="reason-code"
                        class="mb-2 block text-sm font-bold text-slate-700">
                        Motivo
                    </label>

                    <select
                        id="reason-code"
                        class="app-input">

                        <option value="">
                            Selecciona un motivo
                        </option>

                        <option value="CUSTOMER_REQUEST">
                            Solicitud del cliente
                        </option>

                        <option value="DEFECTIVE">
                            Producto defectuoso
                        </option>

                        <option value="WRONG_PRODUCT">
                            Producto incorrecto
                        </option>

                        <option value="DAMAGED">
                            Producto dañado
                        </option>

                        <option value="OTHER">
                            Otro
                        </option>

                    </select>

                </div>

            </x-ui.card>


            <x-ui.card>

                <p class="text-xs font-bold uppercase tracking-wide text-slate-400">
                    Importe a devolver
                </p>

                <p
                    id="return-total"
                    class="mt-2 text-3xl font-black tracking-tight text-slate-900">
                    $ 0.00 MXN
                </p>

                <div class="mt-5 border-t border-slate-200 pt-5">

                    <x-ui.button
                        id="process-return"
                        type="button"
                        class="w-full">
                        Procesar devolución
                    </x-ui.button>

                </div>

            </x-ui.card>

        </div>


        <div
            id="return-message"
            class="hidden rounded-xl px-5 py-4 text-sm font-semibold"></div>

    </div>

    <x-ui.modal
        id="confirm-return-modal"
        title="Confirmar devolución"
        description="Revisa el importe antes de confirmar la devolución."
        size="md">
        <div class="space-y-5">

            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                <p class="text-sm font-bold text-amber-900">
                    Importe a devolver
                </p>

                <p
                    id="confirm-return-total"
                    class="mt-1 text-2xl font-black text-amber-950">
                    $0.00 MXN
                </p>
            </div>

            <div>
                <p class="text-sm font-bold text-slate-900">
                    ¿Confirmar devolución?
                </p>

                <p class="mt-1 text-sm text-slate-500">
                    Los productos seleccionados serán registrados como devueltos
                    y se actualizará el inventario según la condición indicada.
                </p>
            </div>

            <div class="flex justify-end gap-3">

                <button
                    type="button"
                    id="cancel-confirm-return"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                    Cancelar
                </button>

                <button
                    type="button"
                    id="confirm-return-button"
                    class="inline-flex items-center justify-center rounded-xl bg-rose-600 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-rose-700 disabled:cursor-not-allowed disabled:opacity-50">
                    Confirmar devolución
                </button>

            </div>

        </div>
    </x-ui.modal>

<script>
    document.addEventListener('DOMContentLoaded', () => {

        const lines = [
            ...document.querySelectorAll('[data-line]')
        ];

        const totalElement =
            document.getElementById('return-total');

        const processButton =
            document.getElementById('process-return');

        const confirmReturnTotal =
            document.getElementById('confirm-return-total');

        const confirmReturnButton =
            document.getElementById('confirm-return-button');

        const cancelConfirmReturn =
            document.getElementById('cancel-confirm-return');

        const reasonSelect =
            document.getElementById('reason-code');

        const message =
            document.getElementById('return-message');

        let pendingReturnPayload = null;


        /*
         * Abrir modal
         */
       function openConfirmReturnModal() {

    const modal =
        document.getElementById('confirm-return-modal');

    if (!modal) {
        console.error(
            'No se encontró el modal confirm-return-modal.'
        );

        return;
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');

    modal.setAttribute(
        'aria-hidden',
        'false'
    );

    document.body.classList.add(
        'overflow-hidden'
    );
}


function closeConfirmReturnModal() {

    const modal =
        document.getElementById('confirm-return-modal');

    if (!modal) {
        return;
    }

    modal.classList.add('hidden');
    modal.classList.remove('flex');

    modal.setAttribute(
        'aria-hidden',
        'true'
    );

    document.body.classList.remove(
        'overflow-hidden'
    );
}


        /*
         * Formato monetario
         */
        function money(value) {

            return `$ ${Number(value || 0).toLocaleString(
                'es-MX',
                {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }
            )} MXN`;
        }


        /*
         * Mensajes
         */
        function showMessage(
    text,
    type = 'error'
) {

    message.textContent = text;

    message.className =
        'rounded-xl px-5 py-4 text-sm font-semibold ' +
        (
            type === 'success'
                ? 'bg-emerald-50 text-emerald-700'
                : 'bg-rose-50 text-rose-700'
        );

    message.classList.remove('hidden');
}


        function clearMessage() {

            message.textContent = '';

            message.classList.add('hidden');
        }


        /*
         * Calcular total
         */
        function calculateTotal() {

            let total = 0;

            lines.forEach(line => {

                const quantityInput =
                    line.querySelector('[data-quantity]');

                const quantity =
                    Number(quantityInput?.value || 0);

                const originalQuantity =
                    Number(
                        line.dataset.originalQuantity || 0
                    );

                const lineTotal =
                    Number(
                        line.dataset.lineTotal || 0
                    );

                if (
                    quantity <= 0 ||
                    originalQuantity <= 0
                ) {
                    return;
                }

                const amount =
                    (lineTotal / originalQuantity)
                    * quantity;

                total += amount;

            });

            total =
                Math.round(
                    (total + Number.EPSILON) * 100
                ) / 100;

            totalElement.textContent =
                money(total);

            return total;
        }


        /*
         * Controlar cantidades
         */
        lines.forEach(line => {

            const input =
                line.querySelector('[data-quantity]');

            if (!input) {
                return;
            }

            input.addEventListener(
                'input',
                () => {

                    const available =
                        Number(
                            line.dataset.available || 0
                        );

                    let quantity =
                        Number(input.value || 0);

                    if (quantity < 0) {
                        quantity = 0;
                    }

                    if (quantity > available) {
                        quantity = available;
                    }

                    input.value =
                        quantity === 0
                            ? '0'
                            : quantity;

                    calculateTotal();
                }
            );
        });


        /*
         * BOTÓN PRINCIPAL
         *
         * Aquí NO se procesa la devolución.
         * Solo se prepara y abre el modal.
         */
        processButton.addEventListener(
            'click',
            () => {

                clearMessage();

                const reason =
                    reasonSelect.value;

                if (!reason) {

                    showMessage(
                        'Selecciona el motivo de la devolución.'
                    );

                    reasonSelect.focus();

                    return;
                }


                const selectedLines = [];


                lines.forEach(line => {

                    const input =
                        line.querySelector(
                            '[data-quantity]'
                        );

                    const condition =
                        line.querySelector(
                            '[data-condition]'
                        );

                    const quantity =
                        Number(
                            input?.value || 0
                        );

                    if (quantity <= 0) {
                        return;
                    }


                    selectedLines.push({
                        sale_line_id:
                            line.dataset.lineId,

                        quantity,

                        inventory_condition:
                            condition.value
                    });

                });


                if (!selectedLines.length) {

                    showMessage(
                        'Selecciona al menos un producto para devolver.'
                    );

                    return;
                }


                const total =
                    calculateTotal();


                if (total <= 0) {

                    showMessage(
                        'El importe de devolución debe ser mayor a cero.'
                    );

                    return;
                }


                /*
                 * Guardamos temporalmente
                 * la información de la devolución.
                 */
                pendingReturnPayload = {
                    lines: selectedLines,
                    reason_code: reason
                };


                /*
                 * Mostramos importe en el modal.
                 */
                confirmReturnTotal.textContent =
                    money(total);


                /*
                 * Abrimos modal.
                 */
                openConfirmReturnModal();

            }
        );


        /*
         * CONFIRMAR DEVOLUCIÓN
         *
         * Aquí sí se hace el POST.
         */
        confirmReturnButton.addEventListener(
            'click',
            async () => {

                if (!pendingReturnPayload) {
                    return;
                }


                confirmReturnButton.disabled = true;

                confirmReturnButton.textContent =
                    'Procesando...';


                processButton.disabled = true;

                processButton.textContent =
                    'Procesando...';


                try {

                    const response =
                        await fetch(
                            '{{ route('returns.store', $sale->id) }}',
                            {
                                method: 'POST',

                                headers: {
                                    'Content-Type':
                                        'application/json',

                                    'Accept':
                                        'application/json',

                                    'X-CSRF-TOKEN':
                                        '{{ csrf_token() }}'
                                },

                                body: JSON.stringify(
                                    pendingReturnPayload
                                )
                            }
                        );


                    const data =
                        await response.json();


                    if (!response.ok) {

                        if (
                            response.status === 422 &&
                            data.errors
                        ) {

                            const firstError =
                                Object.values(
                                    data.errors
                                ).flat()[0];

                            throw new Error(
                                firstError ||
                                data.message ||
                                'Los datos de la devolución no son válidos.'
                            );
                        }


                        throw new Error(
                            data.message ||
                            'No fue posible procesar la devolución.'
                        );
                    }


                    /*
                     * Cerrar modal
                     */
                    closeConfirmReturnModal();


                    /*
                     * Mostrar éxito
                     */
                    showMessage(
                        `Devolución ${data.return.return_number} procesada correctamente.`,
                        'success'
                    );


                    processButton.textContent =
                        'Devolución procesada';


                    pendingReturnPayload = null;


                    /*
                     * Regresar al historial.
                     */
                    setTimeout(() => {

                        window.location.href =
                            '{{ route('sales.history') }}';

                    }, 1500);

                } catch (error) {

                    closeConfirmReturnModal();


                    showMessage(
                        error.message ||
                        'No fue posible procesar la devolución.'
                    );


                    processButton.disabled = false;

                    processButton.textContent =
                        'Procesar devolución';

                } finally {

                    confirmReturnButton.disabled =
                        false;

                    confirmReturnButton.textContent =
                        'Confirmar devolución';
                }

            }
        );


        /*
         * CANCELAR MODAL
         */
        cancelConfirmReturn.addEventListener(
            'click',
            () => {

                pendingReturnPayload = null;

                closeConfirmReturnModal();

            }
        );


        /*
         * Cálculo inicial
         */
        calculateTotal();

    });
</script>

</x-layouts.app>
