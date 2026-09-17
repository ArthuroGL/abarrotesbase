<x-layouts.app title="Ventas | ABARROTESBASE">

    <x-layout.page-header
        eyebrow="Operación"
        title="Ventas"
        description="Punto de venta y registro de operaciones.">

        <x-slot:actions>

            <a
                href="{{ route('sales.history') }}"
                class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:border-slate-400 hover:bg-slate-50">
                Historial
            </a>

            @if ($activeSession)
            <span class="inline-flex items-center gap-2 rounded-xl bg-emerald-50 px-3 py-2 text-xs font-bold text-emerald-700">
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                Caja {{ $activeSession->register?->name }}
            </span>
            @else
            <span class="rounded-xl bg-rose-50 px-3 py-2 text-xs font-bold text-rose-700">
                Sin caja abierta
            </span>
            @endif

        </x-slot:actions>

    </x-layout.page-header>


    @if (!$activeSession)

    <x-ui.card class="mt-6">
        <div class="mx-auto max-w-xl py-12 text-center">

            <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-rose-50 text-xl font-black text-rose-600">
                !
            </div>

            <h2 class="mt-5 text-xl font-black text-slate-900">
                No puedes realizar ventas
            </h2>

            <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500">
                Debes tener una sesión de caja abierta para comenzar a vender.
            </p>

            <a
                href="{{ route('cash.index') }}"
                class="mt-6 inline-flex rounded-xl bg-emerald-600 px-5 py-3 text-sm font-bold text-white hover:bg-emerald-700">
                Ir a Caja
            </a>

        </div>
    </x-ui.card>

    @else

    <div
        id="pos-app"
        class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1fr)_440px]">

        {{-- =========================
         BUSCADOR / PRODUCTOS
    ========================== --}}
        <x-ui.card class="min-w-0">

            <div class="border-b border-slate-200 pb-6">

                <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <label
                            for="product-search"
                            class="block text-base font-black text-slate-900">
                            Buscar producto
                        </label>

                        <p class="mt-1 text-sm text-slate-500">
                            Escanea el código de barras o escribe el nombre del producto.
                        </p>
                    </div>

                    <span class="shrink-0 text-xs font-bold uppercase tracking-wider text-slate-400">
                        Catálogo interno
                    </span>
                </div>

                <div class="relative mt-4">
                    <input
                        id="product-search"
                        type="search"
                        autocomplete="off"
                        autofocus
                        placeholder="Código de barras, SKU o nombre..."
                        class="app-input min-h-14 pr-14 text-lg font-semibold">

                    <span
                        class="pointer-events-none absolute inset-y-0 right-4 grid place-items-center text-sm font-black text-slate-400"
                        aria-hidden="true">
                        ↵
                    </span>
                </div>

                <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs font-medium text-slate-500">
                    <span>
                        El código de barras se busca primero.
                    </span>

                    <span class="hidden h-1 w-1 rounded-full bg-slate-300 sm:block"></span>

                    <span>
                        Enter para buscar.
                    </span>
                </div>

            </div>


            {{-- RESULTADOS --}}
            <div
                id="search-results"
                class="mt-6 space-y-3">

                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-10 text-center">

                    <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-white text-2xl font-black text-slate-400 shadow-sm ring-1 ring-slate-200">
                        +
                    </div>

                    <p class="mt-4 text-base font-bold text-slate-600">
                        Busca o escanea un producto
                    </p>

                    <p class="mt-1 text-sm text-slate-400">
                        Los resultados aparecerán aquí.
                    </p>

                </div>

            </div>

        </x-ui.card>


        {{-- =========================
         TICKET
    ========================== --}}
        <x-ui.card class="flex min-h-0 flex-col xl:sticky xl:top-24 xl:max-h-[calc(100vh-7rem)]">

            {{-- CABECERA --}}
            <div class="flex items-start justify-between gap-4 border-b border-slate-200 pb-5">

                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.14em] text-emerald-700">
                        Venta actual
                    </p>

                    <h2 class="mt-1 text-xl font-black text-slate-900">
                        Ticket
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Productos agregados a esta venta.
                    </p>
                </div>

                <button
                    type="button"
                    id="clear-cart"
                    class="min-h-11 rounded-xl px-3 text-sm font-bold text-rose-600 transition hover:bg-rose-50 hover:text-rose-700">
                    Vaciar
                </button>

            </div>


            {{-- PRODUCTOS DEL TICKET --}}
            <div
                id="cart"
                class="min-h-[280px] flex-1 space-y-3 overflow-y-auto py-5">

                <div
                    id="empty-cart"
                    class="grid min-h-[280px] place-items-center rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 text-center">

                    <div>

                        <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-white text-2xl font-black text-slate-400 shadow-sm ring-1 ring-slate-200">
                            +
                        </div>

                        <p class="mt-4 text-base font-bold text-slate-600">
                            El ticket está vacío
                        </p>

                        <p class="mt-1 text-sm text-slate-400">
                            Agrega productos desde el buscador.
                        </p>

                    </div>

                </div>

            </div>


            {{-- TOTALES --}}
            <div class="border-t border-slate-200 pt-5">

                <div class="space-y-2 text-sm">

                    <div class="flex items-center justify-between text-slate-500">
                        <span>Subtotal</span>

                        <strong
                            id="subtotal"
                            class="font-bold text-slate-700">
                            0.00 MXN
                        </strong>
                    </div>

                    <div class="flex items-center justify-between text-slate-500">
                        <span>Impuestos</span>

                        <strong
                            id="tax"
                            class="font-bold text-slate-700">
                            0.00 MXN
                        </strong>
                    </div>

                </div>


                <div class="mt-4 rounded-2xl bg-slate-950 p-5">

                    <div class="flex items-end justify-between gap-4">

                        <div>
                            <p class="text-sm font-bold text-slate-400">
                                Total a cobrar
                            </p>

                            <p class="mt-1 text-xs text-slate-500">
                                Importe final de la venta
                            </p>
                        </div>

                        <strong
                            id="total"
                            class="text-right text-3xl font-black tracking-tight text-white sm:text-4xl">
                            0.00 MXN
                        </strong>

                    </div>

                </div>


                <button
                    type="button"
                    id="checkout"
                    disabled
                    class="mt-4 min-h-14 w-full rounded-xl bg-emerald-600 px-5 py-4 text-lg font-black text-white shadow-sm transition hover:bg-emerald-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 disabled:cursor-not-allowed disabled:bg-slate-200 disabled:text-slate-400 disabled:shadow-none">
                    Cobrar venta
                </button>

            </div>

        </x-ui.card>

    </div>

    {{-- =========================
     MODAL CANTIDAD A GRANEL
    ========================== --}}
    <x-ui.modal
        id="bulk-quantity-modal"
        size="sm"
        title="Cantidad del producto"
        description="Elige si quieres vender por importe o por peso."
        close-id="close-bulk-quantity">

        <div class="px-5 py-5 sm:px-6">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                <p id="bulk-product-name" class="text-lg font-black text-slate-900">Producto</p>
                <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm">
                    <span id="bulk-product-price" class="font-black text-emerald-700">$ 0.00 MXN / kg</span>
                    <span class="text-slate-300">•</span>
                    <span id="bulk-product-stock" class="font-semibold text-slate-500">Stock disponible: 0 kg</span>
                </div>
            </div>

            <div class="mt-5 grid grid-cols-2 rounded-xl bg-slate-100 p-1">
                <button type="button" id="bulk-mode-money"
                    class="min-h-12 rounded-lg bg-white px-4 py-3 text-sm font-black text-slate-900 shadow-sm ring-1 ring-slate-200 transition">
                    Dinero ($)
                </button>
                <button type="button" id="bulk-mode-weight"
                    class="min-h-12 rounded-lg bg-transparent px-4 py-3 text-sm font-black text-slate-500 transition hover:text-slate-900">
                    Peso (kg/g)
                </button>
            </div>

            <div id="bulk-money-panel" class="mt-5">
                <label for="bulk-money-input" class="mb-2 block text-sm font-black text-slate-900">Importe</label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-xl font-black text-slate-400">$</span>
                    <input id="bulk-money-input" type="number" min="0.01" step="0.01"
                        inputmode="decimal" value="10.00"
                        class="app-input min-h-16 pl-10 text-2xl font-black tabular-nums">
                </div>
                <div class="mt-3 grid grid-cols-4 gap-2">
                    <button type="button" data-bulk-money="5" class="bulk-money-shortcut min-h-11 rounded-xl border border-slate-200 bg-white px-2 text-sm font-black text-slate-700 transition hover:border-emerald-300 hover:bg-emerald-50">$5</button>
                    <button type="button" data-bulk-money="10" class="bulk-money-shortcut min-h-11 rounded-xl border border-slate-200 bg-white px-2 text-sm font-black text-slate-700 transition hover:border-emerald-300 hover:bg-emerald-50">$10</button>
                    <button type="button" data-bulk-money="20" class="bulk-money-shortcut min-h-11 rounded-xl border border-slate-200 bg-white px-2 text-sm font-black text-slate-700 transition hover:border-emerald-300 hover:bg-emerald-50">$20</button>
                    <button type="button" data-bulk-money="50" class="bulk-money-shortcut min-h-11 rounded-xl border border-slate-200 bg-white px-2 text-sm font-black text-slate-700 transition hover:border-emerald-300 hover:bg-emerald-50">$50</button>
                </div>
            </div>

            <div id="bulk-weight-panel" class="mt-5 hidden">
                <label for="bulk-weight-input" class="mb-2 block text-sm font-black text-slate-900">Peso</label>
                <div class="relative">
                    <input id="bulk-weight-input" type="number" min="0.001" step="0.001"
                        inputmode="decimal" value="0.500"
                        class="app-input min-h-16 pr-20 text-2xl font-black tabular-nums">
                    <span class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-sm font-black text-slate-400">kg</span>
                </div>
                <div class="mt-3 grid grid-cols-3 gap-2">
                    <button type="button" data-bulk-weight="0.25" class="bulk-weight-shortcut min-h-11 rounded-xl border border-slate-200 bg-white px-2 text-sm font-black text-slate-700 transition hover:border-emerald-300 hover:bg-emerald-50">1/4 kg</button>
                    <button type="button" data-bulk-weight="0.5" class="bulk-weight-shortcut min-h-11 rounded-xl border border-slate-200 bg-white px-2 text-sm font-black text-slate-700 transition hover:border-emerald-300 hover:bg-emerald-50">1/2 kg</button>
                    <button type="button" data-bulk-weight="1" class="bulk-weight-shortcut min-h-11 rounded-xl border border-slate-200 bg-white px-2 text-sm font-black text-slate-700 transition hover:border-emerald-300 hover:bg-emerald-50">1 kg</button>
                </div>
            </div>

            <div class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 p-5">
                <p class="text-xs font-black uppercase tracking-[0.14em] text-emerald-800">Conversión</p>
                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    <div>
                        <p class="text-xs font-semibold text-emerald-700">Peso</p>
                        <p id="bulk-converted-weight" class="mt-1 text-xl font-black text-emerald-900">0.500 kg</p>
                        <p id="bulk-converted-grams" class="mt-0.5 text-xs font-semibold text-emerald-700">500 gramos</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-emerald-700">Importe</p>
                        <p id="bulk-converted-money" class="mt-1 text-xl font-black text-emerald-900">$ 10.00 MXN</p>
                    </div>
                </div>
            </div>

            <div id="bulk-stock-error"
                class="mt-4 hidden rounded-2xl border-2 border-rose-200 bg-rose-50 p-4 text-sm font-semibold leading-6 text-rose-700">
            </div>
        </div>

        <x-slot:footer>
            <div class="grid gap-3 sm:grid-cols-2">
                <button type="button" id="cancel-bulk-quantity"
                    class="min-h-14 rounded-xl border-2 border-slate-300 bg-white px-5 py-3 text-base font-black text-slate-700 transition hover:border-slate-400 hover:bg-slate-100">
                    Cancelar
                </button>
                <button type="button" id="confirm-bulk-quantity"
                    class="min-h-14 rounded-xl bg-emerald-600 px-5 py-3 text-base font-black text-white shadow-sm transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:bg-slate-200 disabled:text-slate-400">
                    Agregar al ticket
                </button>
            </div>
        </x-slot:footer>
    </x-ui.modal>

    {{-- =========================
     MODAL DE COBRO
========================== --}}
    <x-ui.modal
        id="payment-modal"
        size="md"
        title="Cobrar venta"
        description="Selecciona el método de pago."
        close-id="close-payment">

        {{-- CONTENIDO --}}
        <div class="px-5 py-5 sm:px-6">

            {{-- TOTAL --}}
            <div class="rounded-2xl border border-slate-800 bg-slate-950 p-5 shadow-inner sm:p-6">

                <p class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">
                    Total a pagar
                </p>

                <p
                    id="payment-total"
                    class="mt-2 text-4xl font-black tracking-tight text-white sm:text-[2.75rem]">
                    0.00 MXN
                </p>

            </div>


            {{-- MÉTODO DE PAGO --}}
            <div class="mt-6">

                <label
                    for="payment-method"
                    class="mb-2 block text-sm font-black text-slate-900">
                    Método de pago
                </label>

                <select
                    id="payment-method"
                    class="app-input min-h-14 text-base font-bold">

                    @foreach ($paymentMethods as $method)

                    <option
                        value="{{ $method->id }}"
                        data-code="{{ $method->code }}"
                        data-affects-cash="{{ $method->affects_cash ? '1' : '0' }}"
                        data-requires-reference="{{ $method->requires_reference ? '1' : '0' }}">
                        {{ $method->name }}
                    </option>

                    @endforeach

                </select>

            </div>


            {{-- IMPORTE RECIBIDO --}}
            <div class="mt-6">

                <label
                    for="amount-received"
                    class="mb-2 block text-sm font-black text-slate-900">
                    Importe recibido
                </label>

                <div class="relative">

                    <span
                        class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-xl font-black text-slate-400"
                        aria-hidden="true">
                        $
                    </span>

                    <input
                        id="amount-received"
                        type="number"
                        step="0.01"
                        min="0"
                        value="0"
                        inputmode="decimal"
                        class="app-input min-h-16 pl-10 text-2xl font-black">

                </div>

                <p class="mt-2 text-xs leading-5 text-slate-500">
                    Captura el importe que entrega el cliente.
                </p>

            </div>


            {{-- REFERENCIA --}}
            <div
                id="reference-group"
                class="mt-6 hidden">

                <label
                    for="payment-reference"
                    class="mb-2 block text-sm font-black text-slate-900">
                    Referencia del pago
                </label>

                <input
                    id="payment-reference"
                    type="text"
                    maxlength="120"
                    autocomplete="off"
                    class="app-input min-h-14 text-base font-semibold"
                    placeholder="Ej. TRX-123456789">

                <p class="mt-2 text-xs leading-5 text-slate-500">
                    Captura la referencia de la transferencia.
                </p>

            </div>


            {{-- CAMBIO --}}
            {{-- CAMBIO --}}
            <div
                id="change-box"
                class="mt-6 hidden overflow-hidden rounded-2xl border-2 border-emerald-200 bg-emerald-50">

                <div class="flex items-center justify-between gap-4 p-5">

                    <div class="min-w-0">

                        <p
                            id="change-title"
                            class="text-sm font-black uppercase tracking-wide text-emerald-800">
                            Cambio
                        </p>

                        <p
                            id="change-description"
                            class="mt-1 text-xs leading-5 text-emerald-700">
                            Entregar al cliente.
                        </p>

                    </div>

                    <p
                        id="change"
                        class="shrink-0 text-2xl font-black text-emerald-700 sm:text-3xl">
                        0.00 MXN
                    </p>

                </div>

            </div>

            {{-- DISPONIBILIDAD DE CAJA --}}
            <div
                id="cash-availability-box"
                class="mt-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">

                <div class="flex items-center justify-between gap-4">

                    <span class="text-sm font-semibold text-slate-600">
                        Efectivo disponible en caja
                    </span>

                    <strong
                        id="available-cash"
                        class="text-base font-black text-slate-900">
                        0.00 MXN
                    </strong>

                </div>

            </div>

            {{-- ERROR DE CAMBIO --}}
            <div
                id="change-error-box"
                class="mt-3 hidden rounded-2xl border-2 border-rose-200 bg-rose-50 p-4">

                <div class="flex items-start gap-3">

                    <div
                        class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-rose-100 text-sm font-black text-rose-600">
                        !
                    </div>

                    <div class="min-w-0">

                        <p class="text-sm font-black text-rose-900">
                            No hay suficiente efectivo para entregar el cambio
                        </p>

                        <p
                            id="change-error-message"
                            class="mt-1 text-xs leading-5 text-rose-700">
                        </p>

                    </div>

                </div>

            </div>

        </div>


        {{-- ACCIONES --}}
        <x-slot:footer>

            <div class="grid gap-3 sm:grid-cols-2 sm:gap-4">

                <button
                    type="button"
                    id="cancel-payment"
                    class="min-h-14 rounded-xl border-2 border-slate-300 bg-white px-5 py-3 text-base font-black text-slate-700 transition hover:border-slate-400 hover:bg-slate-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-500">
                    Cancelar
                </button>

                <button
                    type="button"
                    id="confirm-payment"
                    class="min-h-14 rounded-xl bg-emerald-600 px-5 py-3 text-base font-black text-white shadow-sm transition hover:bg-emerald-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">
                    Confirmar venta
                </button>

            </div>

        </x-slot:footer>

    </x-ui.modal>


    <div
        id="pos-message"
        class="pointer-events-none fixed inset-x-0 bottom-5 z-[60] hidden justify-center px-4">

        <div
            id="pos-message-text"
            class="rounded-xl px-5 py-3 text-sm font-bold shadow-xl">
        </div>

    </div>
    {{-- =========================
     MODAL VENTA COMPLETADA
========================== --}}
    <x-ui.modal
        id="sale-completed-modal"
        size="sm"
        title="Venta completada"
        description="La operación se registró correctamente.">

        <div class="px-5 py-6 sm:px-6">

            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 text-center">

                <div class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-emerald-600 text-2xl font-black text-white shadow-sm">
                    ✓
                </div>

                <p class="mt-3 text-base font-black text-emerald-900">
                    Venta registrada correctamente
                </p>

            </div>

            <div class="mt-5 divide-y divide-slate-200 rounded-2xl border border-slate-200 bg-slate-50">

                <div class="flex items-center justify-between gap-4 p-4">
                    <span class="text-sm font-bold text-slate-500">
                        Folio
                    </span>

                    <strong
                        id="completed-sale-number"
                        class="text-right text-sm font-black text-slate-900">
                    </strong>
                </div>

                <div class="flex items-center justify-between gap-4 p-4">

                    <span class="text-base font-bold text-slate-600">
                        Total
                    </span>

                    <strong
                        id="completed-sale-total"
                        class="text-2xl font-black text-slate-950">
                        0.00 MXN
                    </strong>

                </div>

                <div
                    id="completed-change-row"
                    class="flex items-center justify-between gap-4 p-4">

                    <span class="text-base font-bold text-slate-600">
                        Cambio
                    </span>

                    <strong
                        id="completed-sale-change"
                        class="text-xl font-black text-emerald-600">
                        0.00 MXN
                    </strong>

                </div>

            </div>

        </div>

        <x-slot:footer>

            <div class="grid gap-3">

                <button
                    type="button"
                    id="print-completed-sale"
                    class="min-h-14 w-full rounded-xl bg-slate-950 px-5 py-3 text-base font-black text-white shadow-sm transition hover:bg-slate-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-900">
                    Imprimir ticket
                </button>

                <button
                    type="button"
                    id="new-sale"
                    class="min-h-14 w-full rounded-xl border-2 border-slate-300 bg-white px-5 py-3 text-base font-black text-slate-700 transition hover:border-slate-400 hover:bg-slate-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-500">
                    Nueva venta
                </button>

            </div>

        </x-slot:footer>

    </x-ui.modal>

    <script>
        const state = {
            cart: [],
            searchTimer: null,
            submitting: false,
        };

        const $ = (id) => document.getElementById(id);

        const availableCash = Number(@json($availableCash));
        let changeAvailable = true;

        const completedSaleModal = $('sale-completed-modal');
        const completedSaleNumber = $('completed-sale-number');
        const completedSaleTotal = $('completed-sale-total');
        const completedSaleChange = $('completed-sale-change');
        const completedChangeRow = $('completed-change-row');
        const printCompletedSale = $('print-completed-sale');
        const newSaleButton = $('new-sale');

        let completedSaleId = null;

        const searchInput = $('product-search');
        const searchResults = $('search-results');
        const cartElement = $('cart');
        const checkoutButton = $('checkout');

        const paymentModal = $('payment-modal');
        const paymentMethod = $('payment-method');
        const amountReceived = $('amount-received');

        const referenceGroup = $('reference-group');
        const paymentReference = $('payment-reference');

        function money(value) {
            return `$ ${Number(value || 0).toLocaleString('es-MX', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })} MXN`;
        }

        function updatePaymentFields() {
            const option =
                paymentMethod.options[paymentMethod.selectedIndex];

            const affectsCash =
                option?.dataset.affectsCash === '1';

            const requiresReference =
                option?.dataset.requiresReference === '1';

            referenceGroup.classList.toggle(
                'hidden',
                !requiresReference
            );

            paymentReference.required = requiresReference;

            if (!requiresReference) {
                paymentReference.value = '';
            }

            updateChange();
        }

        function showMessage(message, type = 'ok') {
            const box = $('pos-message');
            const text = $('pos-message-text');

            text.textContent = message;

            text.className =
                'rounded-xl px-5 py-3 text-sm font-bold shadow-xl ' +
                (type === 'error' ?
                    'bg-rose-600 text-white' :
                    'bg-slate-900 text-white');

            box.classList.remove('hidden');
            box.classList.add('flex');

            setTimeout(() => {
                box.classList.add('hidden');
                box.classList.remove('flex');
            }, 3500);
        }

        function openCompletedSaleModal(data) {

            completedSaleId = data.sale_id;

            completedSaleNumber.textContent =
                data.sale_number || 'Sin folio';

            completedSaleTotal.textContent =
                money(data.total);

            const change = Number(data.change || 0);

            completedSaleChange.textContent =
                money(change);

            completedChangeRow.classList.toggle(
                'hidden',
                change <= 0
            );

            completedSaleModal.classList.remove('hidden');
            completedSaleModal.classList.add('flex');
        }

        function closeCompletedSaleModal() {

            completedSaleModal.classList.add('hidden');
            completedSaleModal.classList.remove('flex');

            completedSaleId = null;
        }

        async function searchProducts(query) {

            if (!query.trim()) {
                searchResults.innerHTML = `
                        <div class="rounded-xl border border-dashed border-slate-200 p-8 text-center text-sm text-slate-400">
                            Busca o escanea un producto para comenzar.
                        </div>
                    `;
                return;
            }

            searchResults.innerHTML = `
                    <div class="rounded-xl bg-slate-50 p-5 text-center text-sm text-slate-500">
                        Buscando...
                    </div>
                `;

            try {

                const response = await fetch(
                    `{{ route('sales.lookup') }}?q=${encodeURIComponent(query)}`, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    }
                );
                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'No fue posible buscar.');
                }

                renderSearchResults(data.items || []);

            } catch (error) {

                searchResults.innerHTML = `
                        <div class="rounded-xl bg-rose-50 p-5 text-sm font-semibold text-rose-700">
                            ${error.message}
                        </div>
                    `;
            }
        }

        function renderSearchResults(items) {

            if (!items.length) {
                searchResults.innerHTML = `
                        <div class="rounded-xl border border-dashed border-slate-200 p-8 text-center text-sm text-slate-400">
                            No encontramos productos.
                        </div>
                    `;
                return;
            }

            searchResults.innerHTML = items.map(item => `
    <button
        type="button"
        data-product='${JSON.stringify(item).replace(/'/g, '&apos;')}'
        class="product-result group flex min-h-[76px] w-full items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-white p-4 text-left transition hover:border-emerald-300 hover:bg-emerald-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">

        <div class="min-w-0 flex-1">

            <p class="truncate text-base font-black text-slate-900 group-hover:text-emerald-800">
                ${escapeHtml(item.name || 'Producto')}
            </p>

            <div class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-slate-500">

                <span>
                    ${escapeHtml(item.sku || item.barcode || 'Sin clave')}
                </span>

                <span class="text-slate-300">•</span>

                <span>
                    ${escapeHtml(item.unit || '')}
                </span>

            </div>

        </div>


        <div class="shrink-0 text-right">

            <p class="text-lg font-black text-emerald-600">
                ${money(item.price)}
            </p>

            <p class="mt-1 text-xs font-semibold ${
                Number(item.stock) > 0
                    ? 'text-slate-400'
                    : 'text-rose-600'
            }">
                Stock: ${item.stock}
            </p>

        </div>

    </button>
`).join('');

            document.querySelectorAll('.product-result').forEach(button => {

                button.addEventListener('click', () => {

                    const product = JSON.parse(
                        button.dataset.product.replace(/&apos;/g, "'")
                    );

                    const addedDirectly = addToCart(product);

                    if (addedDirectly) {
                        searchInput.value = '';
                        searchResults.innerHTML = `
                            <div class="rounded-xl border border-dashed border-slate-200 p-8 text-center text-sm text-slate-400">
                                Producto agregado al ticket.
                            </div>
                        `;
                        searchInput.focus();
                    }
                });

            });
        }

        function escapeHtml(value) {

            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        let bulkProduct = null;
        let bulkMode = 'money';
        let bulkEditingIndex = null;

        const bulkQuantityModal = $('bulk-quantity-modal');
        const bulkProductName = $('bulk-product-name');
        const bulkProductPrice = $('bulk-product-price');
        const bulkProductStock = $('bulk-product-stock');
        const bulkMoneyPanel = $('bulk-money-panel');
        const bulkWeightPanel = $('bulk-weight-panel');
        const bulkMoneyInput = $('bulk-money-input');
        const bulkWeightInput = $('bulk-weight-input');
        const bulkConvertedWeight = $('bulk-converted-weight');
        const bulkConvertedGrams = $('bulk-converted-grams');
        const bulkConvertedMoney = $('bulk-converted-money');
        const bulkStockError = $('bulk-stock-error');
        const confirmBulkQuantity = $('confirm-bulk-quantity');

        function roundMoney(value) {
            return Math.round((Number(value) + Number.EPSILON) * 100) / 100;
        }

        function roundQuantity(value) {
            return Math.round((Number(value) + Number.EPSILON) * 1000000) / 1000000;
        }

        function cartItemTotal(item) {
            if (item.allow_decimal && Number.isFinite(Number(item.sale_amount))) {
                return Number(item.sale_amount);
            }

            return Number(item.price) * Number(item.quantity);
        }

        function cartTotal() {
            return state.cart.reduce(
                (sum, item) => sum + cartItemTotal(item),
                0
            );
        }

        function formatQuantity(quantity, allowDecimal = true) {
            const value = Number(quantity || 0);

            if (!allowDecimal) {
                return value.toLocaleString('es-MX', {
                    maximumFractionDigits: 0
                });
            }

            return value.toLocaleString('es-MX', {
                minimumFractionDigits: 3,
                maximumFractionDigits: 6
            });
        }

        function openBulkQuantityModal(product, editIndex = null) {
            bulkProduct = {
                ...product,
                price: Number(product.price),
                stock: Number(product.stock),
                conversion_factor: Number(product.conversion_factor || 1),
            };

            bulkEditingIndex = editIndex;

            const existing = editIndex !== null ?
                state.cart[editIndex] :
                null;

            bulkMode = existing?.sale_mode === 'weight' ?
                'weight' :
                'money';

            const initialQuantity = existing ?
                Number(existing.quantity) :
                0.5;

            const initialMoney = existing ?
                Number(existing.sale_amount ?? (existing.price * existing.quantity)) :
                roundMoney(bulkProduct.price * initialQuantity);

            bulkProductName.textContent = bulkProduct.name || 'Producto';
            bulkProductPrice.textContent = `${money(bulkProduct.price)} / kg`;
            bulkProductStock.textContent =
                `Stock disponible: ${formatQuantity(bulkProduct.stock)} kg`;

            bulkMoneyInput.value =
                Math.max(0.01, initialMoney).toFixed(2);

            bulkWeightInput.value =
                Math.max(0.001, initialQuantity).toFixed(6);

            setBulkMode(bulkMode);

            bulkQuantityModal.classList.remove('hidden');
            bulkQuantityModal.classList.add('flex');

            updateBulkConversion();

            setTimeout(() => {
                const input = bulkMode === 'money' ?
                    bulkMoneyInput :
                    bulkWeightInput;

                input.focus();
                input.select();
            }, 50);
        }

        function closeBulkQuantityModal() {
            bulkQuantityModal.classList.add('hidden');
            bulkQuantityModal.classList.remove('flex');

            bulkProduct = null;
            bulkEditingIndex = null;

            bulkStockError.classList.add('hidden');
            bulkStockError.textContent = '';
        }

        function setBulkMode(mode) {
            bulkMode = mode === 'weight' ? 'weight' : 'money';

            const moneyButton = $('bulk-mode-money');
            const weightButton = $('bulk-mode-weight');

            const activeClasses = [
                'bg-white', 'text-slate-900', 'shadow-sm',
                'ring-1', 'ring-slate-200'
            ];

            const inactiveClasses = [
                'bg-transparent', 'text-slate-500',
                'shadow-none', 'ring-0'
            ];

            const activeButton =
                bulkMode === 'money' ? moneyButton : weightButton;

            const inactiveButton =
                bulkMode === 'money' ? weightButton : moneyButton;

            activeButton.classList.remove(...inactiveClasses);
            activeButton.classList.add(...activeClasses);

            inactiveButton.classList.remove(...activeClasses);
            inactiveButton.classList.add(...inactiveClasses);

            bulkMoneyPanel.classList.toggle(
                'hidden',
                bulkMode !== 'money'
            );

            bulkWeightPanel.classList.toggle(
                'hidden',
                bulkMode !== 'weight'
            );

            updateBulkConversion();
        }

        function getBulkValues() {
            if (!bulkProduct) {
                return {
                    quantity: 0,
                    amount: 0,
                    inventoryQuantity: 0
                };
            }

            const price = Number(bulkProduct.price);

            if (!price || price <= 0) {
                return {
                    quantity: 0,
                    amount: 0,
                    inventoryQuantity: 0
                };
            }

            if (bulkMode === 'money') {
                const requestedAmount =
                    Number(bulkMoneyInput.value || 0);

                const quantity = roundQuantity(
                    requestedAmount / price
                );

                const amount = roundMoney(
                    quantity * price
                );

                return {
                    quantity,
                    amount,
                    inventoryQuantity: quantity * Number(bulkProduct.conversion_factor || 1)
                };
            }

            const quantity = roundQuantity(
                Number(bulkWeightInput.value || 0)
            );

            const amount = roundMoney(
                quantity * price
            );

            return {
                quantity,
                amount,
                inventoryQuantity: quantity * Number(bulkProduct.conversion_factor || 1)
            };
        }

        function updateBulkConversion() {
            if (!bulkProduct) {
                return;
            }

            const values = getBulkValues();

            bulkConvertedWeight.textContent =
                `${formatQuantity(values.quantity)} kg`;

            bulkConvertedGrams.textContent =
                `${(values.quantity * 1000).toLocaleString('es-MX', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 3
                })} gramos`;

            bulkConvertedMoney.textContent =
                money(values.amount);

            const insufficient =
                values.inventoryQuantity >
                Number(bulkProduct.stock) + 0.0000001;

            bulkStockError.classList.toggle(
                'hidden',
                !insufficient
            );

            if (insufficient) {
                bulkStockError.textContent =
                    `La cantidad seleccionada supera la existencia disponible. Disponible: ${formatQuantity(bulkProduct.stock)} kg.`;
            } else {
                bulkStockError.textContent = '';
            }

            confirmBulkQuantity.disabled = !values.quantity ||
                values.quantity <= 0 ||
                values.amount <= 0 ||
                insufficient;
        }

        function saveBulkQuantity() {
            if (!bulkProduct) {
                return;
            }

            const values = getBulkValues();

            if (
                !values.quantity ||
                values.quantity <= 0 ||
                values.amount <= 0
            ) {
                showMessage('Captura una cantidad válida.', 'error');
                return;
            }

            if (
                values.inventoryQuantity >
                Number(bulkProduct.stock) + 0.0000001
            ) {
                showMessage(
                    'La cantidad seleccionada supera la existencia disponible.',
                    'error'
                );
                return;
            }

            if (bulkEditingIndex !== null) {
                const item = state.cart[bulkEditingIndex];

                if (!item) {
                    closeBulkQuantityModal();
                    return;
                }

                item.quantity = values.quantity;
                item.sale_mode = bulkMode;
                item.sale_amount = values.amount;

                renderCart();
                closeBulkQuantityModal();
                searchInput.focus();
                return;
            }

            const existingIndex = state.cart.findIndex(item =>
                item.stock_item_id === bulkProduct.stock_item_id &&
                item.product_unit_id === bulkProduct.product_unit_id
            );

            if (existingIndex !== -1) {
                const existing = state.cart[existingIndex];

                existing.quantity = roundQuantity(
                    Number(existing.quantity) + values.quantity
                );

                existing.sale_mode = 'weight';
                existing.sale_amount = roundMoney(
                    existing.price * existing.quantity
                );
            } else {
                state.cart.push({
                    stock_item_id: bulkProduct.stock_item_id,
                    product_unit_id: bulkProduct.product_unit_id,
                    name: bulkProduct.name,
                    sku: bulkProduct.sku,
                    unit: bulkProduct.unit,
                    price: Number(bulkProduct.price),
                    quantity: values.quantity,
                    allow_decimal: true,
                    stock: Number(bulkProduct.stock),
                    conversion_factor: Number(bulkProduct.conversion_factor || 1),
                    sale_mode: bulkMode,
                    sale_amount: values.amount,
                });
            }

            renderCart();
            closeBulkQuantityModal();

            searchInput.value = '';
            searchResults.innerHTML = `
                <div class="rounded-xl border border-dashed border-slate-200 p-8 text-center text-sm text-slate-400">
                    Producto agregado al ticket.
                </div>
            `;

            searchInput.focus();
        }

        function addToCart(product) {
            if (product.allow_decimal) {
                openBulkQuantityModal(product);
                return false;
            }

            const existing = state.cart.find(item =>
                item.stock_item_id === product.stock_item_id &&
                item.product_unit_id === product.product_unit_id
            );

            if (existing) {
                const nextQuantity =
                    Number(existing.quantity) + 1;

                if (nextQuantity > Number(product.stock)) {
                    showMessage(
                        'La cantidad seleccionada supera la existencia disponible.',
                        'error'
                    );
                    return false;
                }

                existing.quantity = nextQuantity;
                existing.sale_amount = null;
            } else {
                state.cart.push({
                    stock_item_id: product.stock_item_id,
                    product_unit_id: product.product_unit_id,
                    name: product.name,
                    sku: product.sku,
                    unit: product.unit,
                    price: Number(product.price),
                    quantity: 1,
                    allow_decimal: false,
                    stock: Number(product.stock),
                    conversion_factor: Number(product.conversion_factor || 1),
                    sale_mode: 'unit',
                    sale_amount: null,
                });
            }

            renderCart();
            return true;
        }

        function renderCart() {
            if (!state.cart.length) {
                cartElement.innerHTML = `
                    <div id="empty-cart"
                        class="grid min-h-[280px] place-items-center text-center text-sm text-slate-400">
                        <div>
                            <div class="text-3xl font-black">+</div>
                            <p class="mt-2">Agrega productos al ticket.</p>
                        </div>
                    </div>
                `;

                checkoutButton.disabled = true;
                updateTotals();
                return;
            }

            cartElement.innerHTML = state.cart.map((item, index) => {
                const isBulk = Boolean(item.allow_decimal);
                const lineTotal = cartItemTotal(item);

                const quantityText = isBulk ?
                    `${formatQuantity(item.quantity)} kg` :
                    `${formatQuantity(item.quantity, false)} ${Number(item.quantity) === 1 ? 'pieza' : 'piezas'}`;

                const detailText = isBulk ?
                    `${money(item.price)} / kg · ${quantityText}` :
                    `${money(item.price)} · ${escapeHtml(item.unit || '')}`;

                return `
                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate text-base font-black text-slate-900">
                                    ${escapeHtml(item.name)}
                                </p>
                                <p class="mt-1 text-sm text-slate-500">
                                    ${detailText}
                                </p>
                                ${
                                    isBulk
                                        ? `<p class="mt-1 text-xs font-semibold text-slate-400">
                                            ${item.sale_mode === 'money'
                                                ? 'Venta por importe'
                                                : 'Venta por peso'}
                                           </p>`
                                        : ''
                                }
                            </div>

                            <button type="button"
                                data-index="${index}"
                                class="remove-line min-h-10 shrink-0 rounded-lg px-2 text-sm font-bold text-rose-600 transition hover:bg-rose-50 hover:text-rose-700">
                                Quitar
                            </button>
                        </div>

                        ${
                            isBulk
                                ? `
                                    <div class="mt-4 flex items-center justify-between gap-3">
                                        <button type="button"
                                            data-index="${index}"
                                            class="edit-bulk-line min-h-11 rounded-xl border border-slate-300 bg-white px-4 text-sm font-black text-slate-700 transition hover:border-emerald-300 hover:bg-emerald-50">
                                            Modificar cantidad
                                        </button>
                                        <strong class="text-lg font-black text-slate-900">
                                            ${money(lineTotal)}
                                        </strong>
                                    </div>
                                `
                                : `
                                    <div class="mt-4 flex items-center justify-between gap-3">
                                        <div class="flex min-h-11 items-center overflow-hidden rounded-xl border border-slate-300 bg-white">
                                            <button type="button"
                                                data-index="${index}"
                                                class="quantity-minus grid h-11 w-11 shrink-0 place-items-center text-xl font-black text-slate-600 transition hover:bg-slate-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-emerald-600"
                                                aria-label="Disminuir cantidad">
                                                −
                                            </button>

                                            <input data-index="${index}"
                                                value="${item.quantity}"
                                                type="number"
                                                min="1"
                                                step="1"
                                                aria-label="Cantidad de ${escapeHtml(item.name)}"
                                                class="quantity-input h-11 w-20 border-x border-slate-300 bg-white px-2 text-center text-base font-black text-slate-900 outline-none focus:bg-emerald-50">

                                            <button type="button"
                                                data-index="${index}"
                                                class="quantity-plus grid h-11 w-11 shrink-0 place-items-center text-xl font-black text-slate-600 transition hover:bg-slate-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-emerald-600"
                                                aria-label="Aumentar cantidad">
                                                +
                                            </button>
                                        </div>

                                        <strong class="text-lg font-black text-slate-900">
                                            ${money(lineTotal)}
                                        </strong>
                                    </div>
                                `
                        }
                    </div>
                `;
            }).join('');

            checkoutButton.disabled = false;

            bindCartEvents();
            updateTotals();
        }

        function bindCartEvents() {
            document.querySelectorAll('.remove-line').forEach(button => {
                button.addEventListener('click', () => {
                    state.cart.splice(
                        Number(button.dataset.index),
                        1
                    );
                    renderCart();
                });
            });

            document.querySelectorAll('.edit-bulk-line').forEach(button => {
                button.addEventListener('click', () => {
                    const index = Number(button.dataset.index);
                    const item = state.cart[index];

                    if (!item) {
                        return;
                    }

                    openBulkQuantityModal({
                        stock_item_id: item.stock_item_id,
                        product_unit_id: item.product_unit_id,
                        name: item.name,
                        sku: item.sku,
                        unit: item.unit,
                        price: item.price,
                        stock: item.stock,
                        conversion_factor: item.conversion_factor || 1,
                        allow_decimal: true,
                    }, index);
                });
            });

            document.querySelectorAll('.quantity-minus').forEach(button => {
                button.addEventListener('click', () => {
                    const index = Number(button.dataset.index);
                    const item = state.cart[index];

                    if (!item) {
                        return;
                    }

                    item.quantity = Math.max(
                        1,
                        Number(item.quantity) - 1
                    );

                    item.sale_amount = null;
                    renderCart();
                });
            });

            document.querySelectorAll('.quantity-plus').forEach(button => {
                button.addEventListener('click', () => {
                    const index = Number(button.dataset.index);
                    const item = state.cart[index];

                    if (!item) {
                        return;
                    }

                    const nextQuantity =
                        Number(item.quantity) + 1;

                    if (nextQuantity > Number(item.stock)) {
                        showMessage(
                            'La cantidad seleccionada supera la existencia disponible.',
                            'error'
                        );
                        return;
                    }

                    item.quantity = nextQuantity;
                    item.sale_amount = null;
                    renderCart();
                });
            });

            document.querySelectorAll('.quantity-input').forEach(input => {
                input.addEventListener('change', () => {
                    const index = Number(input.dataset.index);
                    const item = state.cart[index];

                    if (!item) {
                        return;
                    }

                    let quantity = Number(input.value);

                    if (!quantity || quantity <= 0) {
                        quantity = 1;
                    }

                    quantity = Math.round(quantity);

                    if (quantity > Number(item.stock)) {
                        showMessage(
                            'La cantidad seleccionada supera la existencia disponible.',
                            'error'
                        );
                        quantity = Number(item.stock);
                    }

                    item.quantity = quantity;
                    item.sale_amount = null;
                    renderCart();
                });
            });
        }

        function updateTotals() {
            const subtotal = cartTotal();

            $('subtotal').textContent = money(subtotal);
            $('tax').textContent = money(0);
            $('total').textContent = money(subtotal);
        }

        function openPaymentModal() {

            if (!state.cart.length) return;

            const total = cartTotal();

            $('payment-total').textContent = money(total);

            amountReceived.value = total.toFixed(2);

            paymentModal.classList.remove('hidden');
            paymentModal.classList.add('flex');

            /* updateChange(); */

            updatePaymentFields();

            amountReceived.focus();
            amountReceived.select();
        }

        function closePaymentModal() {

            paymentModal.classList.add('hidden');
            paymentModal.classList.remove('flex');
        }

        function updateChange() {
            const total = cartTotal();

            const received = Number(amountReceived.value || 0);

            const option =
                paymentMethod.options[paymentMethod.selectedIndex];

            const affectsCash =
                option?.dataset.affectsCash === '1';

            const change =
                affectsCash ?
                Math.max(0, received - total) :
                0;

            const hasChange =
                affectsCash && change > 0;

            changeAvailable = !hasChange || change <= availableCash;

            $('change').textContent = money(change);

            $('cash-availability-box').classList.toggle(
                'hidden',
                !affectsCash
            );

            $('available-cash').textContent =
                money(availableCash);

            $('change-box').classList.toggle(
                'hidden',
                !hasChange
            );

            $('change-error-box').classList.toggle(
                'hidden',
                !hasChange || changeAvailable
            );

            if (hasChange && !changeAvailable) {

                const missing =
                    change - availableCash;

                $('change-title').textContent =
                    'Cambio no disponible';

                $('change-description').textContent =
                    'La caja no tiene suficiente efectivo.';

                $('change').classList.remove(
                    'text-emerald-700'
                );

                $('change').classList.add(
                    'text-rose-700'
                );

                $('change-box').classList.remove(
                    'border-emerald-200',
                    'bg-emerald-50'
                );

                $('change-box').classList.add(
                    'border-rose-200',
                    'bg-rose-50'
                );

                $('change-error-message').textContent =
                    `Necesitas ${money(change)} de cambio, pero la caja dispone de ${money(availableCash)}. Faltan ${money(missing)}.`;

            } else {

                $('change-title').textContent =
                    'Cambio';

                $('change-description').textContent =
                    'Entregar al cliente.';

                $('change').classList.remove(
                    'text-rose-700'
                );

                $('change').classList.add(
                    'text-emerald-700'
                );

                $('change-box').classList.remove(
                    'border-rose-200',
                    'bg-rose-50'
                );

                $('change-box').classList.add(
                    'border-emerald-200',
                    'bg-emerald-50'
                );
            }

            $('confirm-payment').disabled = !changeAvailable;
        }


        async function waitForPointPayment(transactionId) {
            const maxAttempts = 60;
            const interval = 2000;

            for (let attempt = 0; attempt < maxAttempts; attempt++) {
                try {
                    const response = await fetch(
                        `/sales/point/${transactionId}/finalize`, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document
                                    .querySelector(
                                        'meta[name="csrf-token"]'
                                    )
                                    .content
                            }
                        }
                    );

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(
                            data.message ||
                            'No fue posible consultar el estado del pago.'
                        );
                    }

                    /*
                     * Pago confirmado.
                     */
                    if (data.status === 'approved') {
                        state.cart = [];

                        renderCart();

                        openCompletedSaleModal(data);

                        return;
                    }

                    /*
                     * Pago rechazado, cancelado o expirado.
                     */
                    if (
                        data.status === 'rejected' ||
                        data.status === 'cancelled' ||
                        data.status === 'expired' ||
                        data.status === 'refunded'
                    ) {
                        showMessage(
                            data.message ||
                            'El pago no pudo completarse.',
                            'error'
                        );

                        return;
                    }

                    /*
                     * Sigue esperando.
                     */
                    if (attempt % 3 === 0) {
                        showMessage(
                            'Esperando confirmación del pago en Mercado Pago...',
                            'ok'
                        );
                    }

                    await new Promise(resolve =>
                        setTimeout(resolve, interval)
                    );

                } catch (error) {
                    showMessage(
                        error.message,
                        'error'
                    );

                    return;
                }
            }

            showMessage(
                'No se recibió confirmación del pago. Revisa el estado de la operación antes de intentar cobrar nuevamente.',
                'error'
            );
        }

        async function confirmPayment() {
            if (state.submitting || !state.cart.length) {
                return;
            }

            const total = cartTotal();

            const received =
                Number(amountReceived.value || 0);

            const option =
                paymentMethod.options[paymentMethod.selectedIndex];

            const affectsCash =
                option?.dataset.affectsCash === '1';

            const change =
                affectsCash ?
                Math.max(0, received - total) :
                0;

            if (affectsCash && change > availableCash) {
                showMessage(
                    `No hay suficiente efectivo en caja para entregar ${money(change)} de cambio.`,
                    'error'
                );

                return;
            }

            const requiresReference =
                option?.dataset.requiresReference === '1';

            const reference =
                paymentReference.value.trim();

            /*
             * Validaciones normales de efectivo / transferencia.
             */
            if (affectsCash && received < total) {
                showMessage(
                    'El importe recibido es menor al total.',
                    'error'
                );
                return;
            }

            if (!affectsCash && received !== total) {
                showMessage(
                    'Para este método de pago, el importe debe ser exactamente igual al total.',
                    'error'
                );
                return;
            }

            if (requiresReference && !reference) {
                showMessage(
                    'Debes capturar la referencia del pago.',
                    'error'
                );

                paymentReference.focus();
                return;
            }

            state.submitting = true;

            try {
                const items = state.cart.map(item => ({
                    stock_item_id: item.stock_item_id,
                    product_unit_id: item.product_unit_id,
                    quantity: item.quantity,
                    sale_mode: item.sale_mode || 'unit',
                    sale_amount: item.sale_amount ?? null,
                }));

                /*
                 * MERCADO PAGO POINT
                 *
                 * Este flujo NO registra todavía la venta como confirmada.
                 * Primero crea la orden en Mercado Pago y la envía al Point.
                 */
                if (option?.dataset.code === 'MP_POINT') {
                    const response = await fetch(
                        "{{ route('sales.point.start') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document
                                    .querySelector(
                                        'meta[name="csrf-token"]'
                                    )
                                    .content
                            },
                            body: JSON.stringify({
                                items: items
                            })
                        }
                    );

                    const data = await response.json();

                    if (!response.ok) {
                        const firstError =
                            data.errors ?
                            Object.values(data.errors).flat()[0] :
                            data.message;

                        throw new Error(
                            firstError ||
                            'No fue posible iniciar el pago con Mercado Pago Point.'
                        );
                    }

                    /*
                     * IMPORTANTE:
                     * Aquí todavía NO mostramos "Venta completada".
                     *
                     * La venta está esperando la confirmación de Mercado Pago.
                     */
                    closePaymentModal();

                    showMessage(
                        'Pago enviado a Mercado Pago Point. Esperando confirmación...',
                        'ok'
                    );

                    await waitForPointPayment(data.transaction_id);
                    return;
                }

                /*
                 * FLUJO NORMAL
                 *
                 * Efectivo, transferencia, etc.
                 */
                const response = await fetch(
                    "{{ route('sales.store') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document
                                .querySelector(
                                    'meta[name="csrf-token"]'
                                )
                                .content
                        },
                        body: JSON.stringify({
                            items: items,
                            payment_method_id: paymentMethod.value,
                            amount_received: received,
                            reference: paymentReference.value.trim() || null
                        })
                    }
                );

                const data = await response.json();

                if (!response.ok) {
                    const firstError =
                        data.errors ?
                        Object.values(data.errors).flat()[0] :
                        data.message;

                    throw new Error(
                        firstError ||
                        'No fue posible registrar la venta.'
                    );
                }

                closePaymentModal();

                state.cart = [];

                renderCart();

                openCompletedSaleModal(data);

            } catch (error) {

                showMessage(
                    error.message,
                    'error'
                );

            } finally {

                state.submitting = false;
            }
        }


        searchInput.addEventListener('input', () => {

            clearTimeout(state.searchTimer);

            state.searchTimer = setTimeout(() => {
                searchProducts(searchInput.value);
            }, 250);
        });


        searchInput.addEventListener('keydown', event => {

            if (event.key === 'Enter') {

                event.preventDefault();

                clearTimeout(state.searchTimer);

                searchProducts(searchInput.value);
            }
        });


        checkoutButton.addEventListener(
            'click',
            openPaymentModal
        );


        $('close-payment').addEventListener(
            'click',
            closePaymentModal
        );


        $('cancel-payment').addEventListener(
            'click',
            closePaymentModal
        );


        paymentModal.addEventListener('click', event => {

            if (event.target === paymentModal) {
                closePaymentModal();
            }
        });


        amountReceived.addEventListener(
            'input',
            updateChange
        );


        paymentMethod.addEventListener(
            'change',
            updatePaymentFields
        );


        $('confirm-payment').addEventListener(
            'click',
            confirmPayment
        );


        $('clear-cart').addEventListener(
            'click',
            () => {

                if (!state.cart.length) return;

                state.cart = [];
                renderCart();
                searchInput.focus();
            }
        );

        printCompletedSale.addEventListener('click', () => {

            if (!completedSaleId) {
                return;
            }

            window.open(
                `/sales/${completedSaleId}/ticket`,
                '_blank'
            );

            closeCompletedSaleModal();

            searchInput.focus();
        });

        newSaleButton.addEventListener('click', () => {

            closeCompletedSaleModal();

            searchInput.focus();
        });

        completedSaleModal.addEventListener('click', event => {

            if (event.target === completedSaleModal) {
                closeCompletedSaleModal();
                searchInput.focus();
            }

        });


        $('close-bulk-quantity').addEventListener(
            'click',
            closeBulkQuantityModal
        );

        $('cancel-bulk-quantity').addEventListener(
            'click',
            closeBulkQuantityModal
        );

        bulkQuantityModal.addEventListener('click', event => {
            if (event.target === bulkQuantityModal) {
                closeBulkQuantityModal();
                searchInput.focus();
            }
        });

        $('bulk-mode-money').addEventListener(
            'click',
            () => setBulkMode('money')
        );

        $('bulk-mode-weight').addEventListener(
            'click',
            () => setBulkMode('weight')
        );

        bulkMoneyInput.addEventListener(
            'input',
            updateBulkConversion
        );

        bulkWeightInput.addEventListener(
            'input',
            updateBulkConversion
        );

        document.querySelectorAll('.bulk-money-shortcut').forEach(button => {
            button.addEventListener('click', () => {
                bulkMoneyInput.value =
                    Number(button.dataset.bulkMoney).toFixed(2);

                setBulkMode('money');
                bulkMoneyInput.focus();
                bulkMoneyInput.select();
            });
        });

        document.querySelectorAll('.bulk-weight-shortcut').forEach(button => {
            button.addEventListener('click', () => {
                bulkWeightInput.value =
                    Number(button.dataset.bulkWeight).toFixed(3);

                setBulkMode('weight');
                bulkWeightInput.focus();
                bulkWeightInput.select();
            });
        });

        confirmBulkQuantity.addEventListener(
            'click',
            saveBulkQuantity
        );

        bulkMoneyInput.addEventListener('keydown', event => {
            if (event.key === 'Enter') {
                event.preventDefault();
                saveBulkQuantity();
            }
        });

        bulkWeightInput.addEventListener('keydown', event => {
            if (event.key === 'Enter') {
                event.preventDefault();
                saveBulkQuantity();
            }
        });

        renderCart();
    </script>

    @endif

</x-layouts.app>
