<x-layouts.app title="Ventas | ABARROTESBASE">

    <x-layout.page-header
        eyebrow="Operación"
        title="Ventas"
        description="Punto de venta y registro de operaciones.">

        <x-slot:actions>

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
        class="mt-6 grid gap-5 xl:grid-cols-[minmax(0,1fr)_420px]">

        {{-- BUSCADOR --}}
        <x-ui.card>

            <div class="border-b border-slate-200 pb-5">

                <label
                    for="product-search"
                    class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-500">
                    Buscar producto
                </label>

                <div class="relative">

                    <input
                        id="product-search"
                        type="search"
                        autocomplete="off"
                        autofocus
                        placeholder="Escanea código de barras o escribe producto..."
                        class="app-input pr-12 text-base">

                    <span class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-slate-400">
                        ↵
                    </span>

                </div>

                <p class="mt-2 text-xs text-slate-400">
                    El código de barras se busca primero en el catálogo interno.
                </p>

            </div>


            <div id="search-results" class="mt-5 space-y-2">

                <div class="rounded-xl border border-dashed border-slate-200 p-8 text-center text-sm text-slate-400">
                    Busca o escanea un producto para comenzar.
                </div>

            </div>

        </x-ui.card>


        {{-- TICKET --}}
        <x-ui.card class="flex flex-col">

            <div class="flex items-center justify-between border-b border-slate-200 pb-4">

                <div>
                    <h2 class="font-black text-slate-900">
                        Ticket actual
                    </h2>

                    <p class="mt-1 text-xs text-slate-500">
                        Venta nueva
                    </p>
                </div>

                <button
                    type="button"
                    id="clear-cart"
                    class="text-xs font-bold text-rose-600 hover:text-rose-700">
                    Vaciar
                </button>

            </div>


            <div
                id="cart"
                class="min-h-[280px] flex-1 space-y-3 py-4">

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

            </div>


            <div class="border-t border-slate-200 pt-4">

                <div class="flex justify-between text-sm text-slate-500">
                    <span>Subtotal</span>
                    <strong id="subtotal">$0.00</strong>
                </div>

                <div class="mt-2 flex justify-between text-sm text-slate-500">
                    <span>Impuestos</span>
                    <strong id="tax">$0.00</strong>
                </div>

                <div class="mt-3 flex justify-between border-t border-slate-200 pt-3">
                    <span class="text-base font-bold text-slate-900">
                        Total
                    </span>

                    <strong
                        id="total"
                        class="text-2xl font-black text-emerald-600">
                        $0.00
                    </strong>
                </div>


                <button
                    type="button"
                    id="checkout"
                    disabled
                    class="mt-5 w-full rounded-xl bg-emerald-600 px-5 py-3.5 text-sm font-black text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-40">
                    Cobrar
                </button>

            </div>

        </x-ui.card>

    </div>


    {{-- MODAL COBRO --}}
    <div
        id="payment-modal"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/50 px-4">

        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">

            <div class="flex items-start justify-between">

                <div>
                    <h2 class="text-lg font-black text-slate-900">
                        Cobrar venta
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Selecciona el método de pago.
                    </p>
                </div>

                <button
                    type="button"
                    id="close-payment"
                    class="rounded-lg p-2 text-slate-400 hover:bg-slate-100">
                    ✕
                </button>

            </div>


            <div class="mt-6 rounded-xl bg-slate-50 p-4">

                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                    Total a pagar
                </p>

                <p
                    id="payment-total"
                    class="mt-1 text-3xl font-black text-slate-900">
                    $0.00
                </p>

            </div>


            <div class="mt-5">

                <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-600">
                    Método de pago
                </label>

                <select
                    id="payment-method"
                    class="app-input">

                    @foreach ($paymentMethods as $method)
                    <option
                        value="{{ $method->id }}"
                        data-affects-cash="{{ $method->affects_cash ? '1' : '0' }}">
                        {{ $method->name }}
                    </option>
                    @endforeach

                </select>

            </div>


            <div class="mt-5">

                <label
                    for="amount-received"
                    class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-600">
                    Importe recibido
                </label>

                <input
                    id="amount-received"
                    type="number"
                    step="0.01"
                    min="0"
                    class="app-input text-xl font-bold"
                    value="0">

            </div>


            <div
                id="change-box"
                class="mt-4 hidden rounded-xl bg-emerald-50 p-4">

                <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">
                    Cambio
                </p>

                <p
                    id="change"
                    class="mt-1 text-2xl font-black text-emerald-700">
                    $0.00
                </p>

            </div>


            <div class="mt-6 flex gap-3">

                <button
                    type="button"
                    id="cancel-payment"
                    class="flex-1 rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50">
                    Cancelar
                </button>

                <button
                    type="button"
                    id="confirm-payment"
                    class="flex-1 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-black text-white hover:bg-emerald-700">
                    Confirmar venta
                </button>

            </div>

        </div>

    </div>


    <div
        id="pos-message"
        class="pointer-events-none fixed inset-x-0 bottom-5 z-[60] hidden justify-center px-4">

        <div
            id="pos-message-text"
            class="rounded-xl px-5 py-3 text-sm font-bold shadow-xl">
        </div>

    </div>
    {{-- MODAL VENTA COMPLETADA --}}
    <div
        id="sale-completed-modal"
        class="fixed inset-0 z-[70] hidden items-center justify-center bg-slate-950/50 px-4">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">

            <div class="text-center">

                <div class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-emerald-50 text-2xl font-black text-emerald-600">
                    ✓
                </div>

                <h2 class="mt-5 text-xl font-black text-slate-900">
                    Venta completada
                </h2>

                <p class="mt-2 text-sm text-slate-500">
                    La operación se registró correctamente.
                </p>

            </div>

            <div class="mt-6 rounded-xl bg-slate-50 p-4">

                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">
                        Folio
                    </span>

                    <strong
                        id="completed-sale-number"
                        class="text-sm font-black text-slate-900">
                    </strong>
                </div>

                <div class="mt-3 flex items-center justify-between">
                    <span class="text-sm text-slate-500">
                        Total
                    </span>

                    <strong
                        id="completed-sale-total"
                        class="text-lg font-black text-slate-900">
                        $0.00
                    </strong>
                </div>

                <div
                    id="completed-change-row"
                    class="mt-2 flex items-center justify-between">
                    <span class="text-sm text-slate-500">
                        Cambio
                    </span>

                    <strong
                        id="completed-sale-change"
                        class="text-lg font-black text-emerald-600">
                        $0.00
                    </strong>
                </div>

            </div>

            <div class="mt-6 grid gap-3">

                <button
                    type="button"
                    id="print-completed-sale"
                    class="w-full rounded-xl bg-slate-900 px-5 py-3.5 text-sm font-black text-white transition hover:bg-slate-800">
                    Imprimir ticket
                </button>

                <button
                    type="button"
                    id="new-sale"
                    class="w-full rounded-xl border border-slate-300 bg-white px-5 py-3.5 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                    Nueva venta
                </button>

            </div>

        </div>
    </div>


    <script>
        const state = {
            cart: [],
            searchTimer: null,
            submitting: false,
        };

        const $ = (id) => document.getElementById(id);

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

        function money(value) {
            return Number(value || 0).toLocaleString('es-MX', {
                style: 'currency',
                currency: 'MXN'
            });
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
                        class="product-result flex w-full items-center justify-between rounded-xl border border-slate-200 bg-white p-4 text-left transition hover:border-emerald-300 hover:bg-emerald-50">

                        <div class="min-w-0">
                            <p class="truncate font-bold text-slate-900">
                                ${escapeHtml(item.name || 'Producto')}
                            </p>

                            <p class="mt-1 text-xs text-slate-500">
                                ${escapeHtml(item.sku || item.barcode || 'Sin clave')}
                                · ${escapeHtml(item.unit || '')}
                            </p>
                        </div>

                        <div class="ml-4 shrink-0 text-right">
                            <p class="font-black text-emerald-600">
                                ${money(item.price)}
                            </p>

                            <p class="mt-1 text-xs text-slate-400">
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
                    <div class="rounded-xl border border-slate-200 p-3">

                        <div class="flex items-start justify-between gap-3">

                            <div class="min-w-0">
                                <p class="truncate font-bold text-slate-900">
                                    ${escapeHtml(item.name)}
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    ${money(item.price)} · ${escapeHtml(item.unit || '')}
                                </p>
                            </div>

                            <button
                                type="button"
                                data-index="${index}"
                                class="remove-line shrink-0 text-xs font-bold text-rose-600">
                                Quitar
                            </button>

                        </div>

                        <div class="mt-3 flex items-center justify-between">

                            <div class="flex items-center rounded-lg border border-slate-200">

                                <button
                                    type="button"
                                    data-index="${index}"
                                    class="quantity-minus px-3 py-2 font-bold text-slate-500 hover:bg-slate-50">
                                    −
                                </button>

                                <input
                                    data-index="${index}"
                                    value="${item.quantity}"
                                    type="number"
                                    min="0.001"
                                    step="${item.allow_decimal ? '0.001' : '1'}"
                                    class="quantity-input w-16 border-x border-slate-200 px-2 py-2 text-center text-sm font-bold outline-none">

                                <button
                                    type="button"
                                    data-index="${index}"
                                    class="quantity-plus px-3 py-2 font-bold text-slate-500 hover:bg-slate-50">
                                    +
                                </button>

                            </div>

                            <strong class="text-base font-black text-slate-900">
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

            updateChange();

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

            $('change').textContent = money(change);

            $('change-box').classList.toggle(
                'hidden',
                !affectsCash || change <= 0
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

            if (received < total) {
                showMessage(
                    'El importe recibido es menor al total.',
                    'error'
                );
                return;
            }

            state.submitting = true;

            try {

              const response = await fetch("{{ route('sales.store') }}", {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document
            .querySelector('meta[name="csrf-token"]')
            .content
    },
    body: JSON.stringify({
        items: state.cart.map(item => ({
            stock_item_id: item.stock_item_id,
            product_unit_id: item.product_unit_id,
            quantity: item.quantity
        })),
        payment_method_id: paymentMethod.value,
        amount_received: received
    })
});

                const data = await response.json();

                if (!response.ok) {

                    const firstError =
                        data.errors ?
                        Object.values(data.errors).flat()[0] :
                        data.message;

                    throw new Error(
                        firstError || 'No fue posible registrar la venta.'
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
            updateChange
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
