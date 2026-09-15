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

                    addToCart(product);

                    searchInput.value = '';
                    searchResults.innerHTML = `
                            <div class="rounded-xl border border-dashed border-slate-200 p-8 text-center text-sm text-slate-400">
                                Producto agregado al ticket.
                            </div>
                        `;

                    searchInput.focus();
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

        function addToCart(product) {

            const existing = state.cart.find(item =>
                item.stock_item_id === product.stock_item_id &&
                item.product_unit_id === product.product_unit_id
            );

            if (existing) {

                const nextQuantity =
                    Number(existing.quantity) + 1;

                if (
                    !product.allow_decimal &&
                    nextQuantity % 1 !== 0
                ) {
                    return;
                }

                existing.quantity = nextQuantity;

            } else {

                state.cart.push({
                    stock_item_id: product.stock_item_id,
                    product_unit_id: product.product_unit_id,
                    name: product.name,
                    sku: product.sku,
                    unit: product.unit,
                    price: Number(product.price),
                    quantity: 1,
                    allow_decimal: product.allow_decimal,
                    stock: Number(product.stock),
                });
            }

            renderCart();
        }

        function renderCart() {

            if (!state.cart.length) {

                cartElement.innerHTML = `
                        <div
                            id="empty-cart"
                            class="grid min-h-[280px] place-items-center text-center text-sm text-slate-400">
                            <div>
                                <div class="text-3xl font-black">+</div>
                                <p class="mt-2">
                                    Agrega productos al ticket.
                                </p>
                            </div>
                        </div>
                    `;

                checkoutButton.disabled = true;
                updateTotals();
                return;
            }

            cartElement.innerHTML = state.cart.map((item, index) => `
    <div class="rounded-2xl border border-slate-200 bg-white p-4">

        <div class="flex items-start justify-between gap-3">

            <div class="min-w-0">

                <p class="truncate text-base font-black text-slate-900">
                    ${escapeHtml(item.name)}
                </p>

                <p class="mt-1 text-sm text-slate-500">
                    ${money(item.price)} · ${escapeHtml(item.unit || '')}
                </p>

            </div>

            <button
                type="button"
                data-index="${index}"
                class="remove-line min-h-10 shrink-0 rounded-lg px-2 text-sm font-bold text-rose-600 transition hover:bg-rose-50 hover:text-rose-700">
                Quitar
            </button>

        </div>


        <div class="mt-4 flex items-center justify-between gap-3">

            <div class="flex min-h-11 items-center overflow-hidden rounded-xl border border-slate-300 bg-white">

                <button
                    type="button"
                    data-index="${index}"
                    class="quantity-minus grid h-11 w-11 shrink-0 place-items-center text-xl font-black text-slate-600 transition hover:bg-slate-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-emerald-600"
                    aria-label="Disminuir cantidad">
                    −
                </button>

                <input
                    data-index="${index}"
                    value="${item.quantity}"
                    type="number"
                    min="0.001"
                    step="${item.allow_decimal ? '0.001' : '1'}"
                    aria-label="Cantidad de ${escapeHtml(item.name)}"
                    class="quantity-input h-11 w-20 border-x border-slate-300 bg-white px-2 text-center text-base font-black text-slate-900 outline-none focus:bg-emerald-50">

                <button
                    type="button"
                    data-index="${index}"
                    class="quantity-plus grid h-11 w-11 shrink-0 place-items-center text-xl font-black text-slate-600 transition hover:bg-slate-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-emerald-600"
                    aria-label="Aumentar cantidad">
                    +
                </button>

            </div>


            <strong class="text-lg font-black text-slate-900">
                ${money(item.price * item.quantity)}
            </strong>

        </div>

    </div>
`).join('');

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

            document.querySelectorAll('.quantity-minus').forEach(button => {

                button.addEventListener('click', () => {

                    const index = Number(button.dataset.index);
                    const item = state.cart[index];

                    item.quantity =
                        Math.max(
                            item.allow_decimal ? 0.001 : 1,
                            Number(item.quantity) - 1
                        );

                    renderCart();
                });
            });

            document.querySelectorAll('.quantity-plus').forEach(button => {

                button.addEventListener('click', () => {

                    const index = Number(button.dataset.index);
                    const item = state.cart[index];

                    item.quantity =
                        Number(item.quantity) + 1;

                    renderCart();
                });
            });

            document.querySelectorAll('.quantity-input').forEach(input => {

                input.addEventListener('change', () => {

                    const index = Number(input.dataset.index);
                    const item = state.cart[index];

                    let quantity = Number(input.value);

                    if (!quantity || quantity <= 0) {
                        quantity = item.allow_decimal ? 0.001 : 1;
                    }

                    if (!item.allow_decimal) {
                        quantity = Math.round(quantity);
                    }

                    item.quantity = quantity;

                    renderCart();
                });
            });
        }

        function updateTotals() {

            const subtotal = state.cart.reduce(
                (sum, item) =>
                sum + (item.price * item.quantity),
                0
            );

            $('subtotal').textContent = money(subtotal);
            $('tax').textContent = money(0);
            $('total').textContent = money(subtotal);
        }

        function openPaymentModal() {

            if (!state.cart.length) return;

            const total = state.cart.reduce(
                (sum, item) =>
                sum + (item.price * item.quantity),
                0
            );

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
            const total = state.cart.reduce(
                (sum, item) =>
                sum + (item.price * item.quantity),
                0
            );

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

            const total = state.cart.reduce(
                (sum, item) =>
                sum + (item.price * item.quantity),
                0
            );

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


        renderCart();
    </script>

    @endif

</x-layouts.app>
