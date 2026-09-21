<x-layouts.app title="Nueva Compra | ABARROTESBASE">

    <div class="space-y-6">

        {{-- =========================================================
             HEADER
        ========================================================== --}}
        <x-layout.page-header
            eyebrow="Abastecimiento"
            title="Nueva compra"
            description="Registra una orden de compra y sus artículos antes de recibir la mercancía.">

            <x-slot:actions>
                <a
                    href="{{ route('purchases.index') }}"
                    class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">
                    Cancelar
                </a>
            </x-slot:actions>

        </x-layout.page-header>


        {{-- =========================================================
             ERRORES
        ========================================================== --}}
        @if ($errors->any())

        <div
            class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4"
            role="alert">

            <p class="text-sm font-black text-rose-900">
                No se pudo registrar la compra.
            </p>

            <ul class="mt-2 space-y-1 text-sm font-medium text-rose-700">

                @foreach ($errors->all() as $error)
                <li>• {{ $error }}</li>
                @endforeach

            </ul>

        </div>

        @endif


        <form
            method="POST"
            action="{{ route('purchases.store') }}"
            id="purchase-form"
            class="space-y-6">

            @csrf


            {{-- =====================================================
                 INFORMACIÓN DE LA ORDEN
            ====================================================== --}}
            <x-ui.card padding="p-5 sm:p-6">

                <div class="mb-6">

                    <p class="text-xs font-black uppercase tracking-[0.12em] text-emerald-700">
                        01 · Orden de compra
                    </p>

                    <h2 class="mt-1 text-base font-black text-slate-950">
                        Información del proveedor
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Define a quién se realizará la compra y agrega la referencia correspondiente.
                    </p>

                </div>


                <div class="grid gap-5 lg:grid-cols-12">

                    {{-- Proveedor --}}
                    <div class="lg:col-span-5">

                        <label
                            for="supplier_id"
                            class="mb-2 block text-sm font-bold text-slate-700">
                            Proveedor
                            <span class="text-rose-600">*</span>
                        </label>

                        <div class="relative z-50">
                            <x-ui.search
                                id="purchase-supplier-search"
                                name="supplier_search"
                                endpoint="{{ route('suppliers.search') }}"
                                placeholder="Buscar proveedor..."
                                autofocus />

                            <input
                                type="hidden"
                                id="supplier_id"
                                name="supplier_id"
                                value="{{ old('supplier_id') }}"
                                required>
                        </div>

                    </div>


                    {{-- Referencia --}}
                    <div class="lg:col-span-3">

                        <label
                            for="supplier_reference"
                            class="mb-2 block text-sm font-bold text-slate-700">
                            Folio / factura
                        </label>

                        <x-ui.input
                            id="supplier_reference"
                            name="supplier_reference"
                            type="text"
                            :value="old('supplier_reference')"
                            placeholder="Ej. FAC-99823"
                            maxlength="100"
                            autocomplete="off" />

                    </div>


                    {{-- Notas --}}
                    <div class="lg:col-span-4">

                        <label
                            for="notes"
                            class="mb-2 block text-sm font-bold text-slate-700">
                            Notas / observaciones
                        </label>

                        <x-ui.input
                            id="notes"
                            name="notes"
                            type="text"
                            :value="old('notes')"
                            placeholder="Ej. Entrega por la tarde"
                            autocomplete="off" />

                    </div>

                </div>

            </x-ui.card>


            {{-- =====================================================
                 PARTIDAS
            ====================================================== --}}
            <x-ui.card padding="p-0" class="relative z-30 overflow-visible">

                <div class="flex flex-col gap-4 border-b border-slate-200 bg-white px-5 py-5 sm:px-6 sm:flex-row sm:items-center sm:justify-between">

                    <div>

                        <p class="text-xs font-black uppercase tracking-[0.12em] text-sky-700">
                            02 · Mercancía
                        </p>

                        <h2 class="mt-1 text-base font-black text-slate-950">
                            Artículos de la compra
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Agrega cada producto, unidad, cantidad y costo de adquisición.
                        </p>

                    </div>


                    <button
                        type="button"
                        onclick="addPurchaseRow()"
                        class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 text-sm font-bold text-white transition hover:bg-slate-800">
                        <span class="text-lg leading-none">+</span>
                        Agregar artículo
                    </button>

                </div>


                <div class="bg-slate-50/60 p-5 sm:p-6">

                    <div
                        id="items-container"
                        class="space-y-4">
                    </div>

                    <div
                        id="empty-items-message"
                        class="hidden rounded-2xl border border-dashed border-slate-300 bg-white px-5 py-10 text-center">

                        <p class="text-sm font-black text-slate-800">
                            No hay artículos agregados
                        </p>

                        <p class="mt-1 text-sm text-slate-500">
                            Agrega al menos un artículo para registrar la compra.
                        </p>

                    </div>

                </div>


                {{-- TOTAL --}}
                <div class="border-t border-slate-200 bg-white px-5 py-5 sm:px-6">

                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                        <div>
                            <p class="text-sm font-bold text-slate-700">
                                Total estimado
                            </p>

                            <p class="mt-1 text-xs text-slate-400">
                                Calculado a partir de cantidades y costos unitarios.
                            </p>
                        </div>

                        <p
                            id="grand-total-display"
                            class="text-3xl font-black tracking-tight text-slate-950">
                            0.00
                            <span class="text-sm font-bold text-slate-400">
                                MXN
                            </span>
                        </p>

                    </div>

                </div>

            </x-ui.card>


            {{-- =====================================================
                 ACCIONES
            ====================================================== --}}
            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end">

                <a
                    href="{{ route('purchases.index') }}"
                    class="inline-flex min-h-12 items-center justify-center rounded-xl border border-slate-300 bg-white px-5 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                    Cancelar
                </a>

                <x-ui.button
                    type="submit"
                    variant="primary"
                    size="lg">
                    Guardar orden de compra
                </x-ui.button>

            </div>

        </form>

    </div>
    {{-- =============================================================
         TEMPLATE DE PARTIDA
    ============================================================== --}}
    <template id="purchase-row-template">

        <div class="item-row rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">

            <div class="mb-4 flex items-center justify-between gap-3">

                <div>

                    <p class="text-xs font-black uppercase tracking-[0.1em] text-slate-400">
                        Partida
                        <span class="row-number">1</span>
                    </p>

                    <p class="mt-0.5 text-sm font-black text-slate-900">
                        Detalle del artículo
                    </p>

                </div>

                <button
                    type="button"
                    onclick="removePurchaseRow(this)"
                    class="inline-flex min-h-10 items-center justify-center rounded-xl border border-rose-200 bg-rose-50 px-3 text-sm font-bold text-rose-700 transition hover:bg-rose-100">
                    Eliminar
                </button>

            </div>


            <div class="grid gap-4 lg:grid-cols-12">

                {{-- Artículo --}}
                <div class="lg:col-span-4">

                    <label class="mb-2 block text-xs font-black uppercase tracking-[0.08em] text-slate-500">
                        Artículo
                    </label>

                    <div class="relative z-50">

                        <x-ui.search
                            class="purchase-product-search"
                            name="product_search"
                            endpoint="{{ route('products.search') }}"
                            placeholder="Buscar producto, SKU o código..." />

                        <input
                            type="hidden"
                            name="items[INDEX][stock_item_id]"
                            class="stock-item-id"
                            required>

                    </div>


                </div>


                {{-- Unidad --}}
                <div class="lg:col-span-2">



                    <div class="lg:col-span-2">

                        <label class="mb-2 block text-xs font-black uppercase tracking-[0.08em] text-slate-500">
                            Unidad
                        </label>

                        <select
                            name="items[INDEX][product_unit_id]"
                            class="app-input product-unit-select"
                            required
                            disabled>

                            <option value="">
                                Primero selecciona un artículo
                            </option>

                        </select>

                    </div>

                </div>


                {{-- Cantidad --}}
                <div class="lg:col-span-2">

                    <label class="mb-2 block text-xs font-black uppercase tracking-[0.08em] text-slate-500">
                        Cantidad
                    </label>

                    <input
                        type="number"
                        step="0.01"
                        min="0.01"
                        value="1"
                        name="items[INDEX][quantity]"
                        class="qty-input app-input"
                        oninput="calculateRowTotal(this)"
                        required>

                </div>


                {{-- Costo --}}
                <div class="lg:col-span-2">

                    <label class="mb-2 block text-xs font-black uppercase tracking-[0.08em] text-slate-500">
                        Costo unitario
                    </label>

                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        value="0"
                        name="items[INDEX][unit_cost]"
                        class="cost-input app-input"
                        oninput="calculateRowTotal(this)"
                        required>

                </div>


                {{-- Subtotal --}}
                <div class="lg:col-span-2">

                    <label class="mb-2 block text-xs font-black uppercase tracking-[0.08em] text-slate-500">
                        Subtotal
                    </label>

                    <div class="flex min-h-12 items-center rounded-xl border border-slate-200 bg-slate-50 px-4">

                        <p class="row-subtotal text-sm font-black text-slate-900">
                            0.00
                        </p>

                    </div>

                </div>

            </div>

        </div>

    </template>


    <script>
    let purchaseRowCount = 0;

    /*
    |--------------------------------------------------------------------------
    | BUSCADOR DINÁMICO DE PRODUCTOS
    |--------------------------------------------------------------------------
    */

    function initializeDynamicProductSearch(row) {

        const searchRoot = row.querySelector('[data-ui-search]');
        const input = searchRoot?.querySelector('input');
        const results = searchRoot?.querySelector('[data-search-results]');
        const loading = searchRoot?.querySelector('[data-search-loading]');

        if (!searchRoot || !input || !results) {
            console.error('No se pudo inicializar el buscador del producto.');
            return;
        }

        // Evitar inicializar dos veces el mismo buscador.
        if (searchRoot.dataset.searchInitialized === 'true') {
            return;
        }

        searchRoot.dataset.searchInitialized = 'true';

        const endpoint = searchRoot.dataset.endpoint;
        const minChars = Number(searchRoot.dataset.minChars || 1);
        const debounceTime = Number(searchRoot.dataset.debounce || 250);

        let timer = null;
        let controller = null;
        let items = [];
        let activeIndex = -1;

        function showResults() {
            results.classList.remove('hidden');
            input.setAttribute('aria-expanded', 'true');
        }

        function hideResults() {
            results.classList.add('hidden');
            input.setAttribute('aria-expanded', 'false');
            activeIndex = -1;
        }

        function setLoading(active) {

            if (!loading) {
                return;
            }

            loading.classList.toggle('hidden', !active);
            loading.classList.toggle('flex', active);
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function renderMessage(message) {

            results.innerHTML = `
                <div class="px-4 py-4 text-sm font-medium text-slate-500">
                    ${escapeHtml(message)}
                </div>
            `;

            showResults();
        }

        function updateActiveResult() {

            const buttons = results.querySelectorAll('[data-search-result]');

            buttons.forEach(function (button, index) {

                const active = index === activeIndex;

                button.classList.toggle('bg-slate-50', active);

                button.setAttribute(
                    'aria-selected',
                    active ? 'true' : 'false'
                );
            });
        }

        function selectItem(index) {

            if (!items[index]) {
                return;
            }

            const item = items[index];

            input.value = item.name || '';

            searchRoot.dispatchEvent(
                new CustomEvent('ui-search-selected', {
                    bubbles: true,
                    detail: item,
                })
            );

            hideResults();
        }

        function renderResults() {

            if (!items.length) {

                renderMessage('No encontramos productos.');

                return;
            }

            results.innerHTML = items.map(function (item, index) {

                const name = escapeHtml(
                    item.name || 'Producto sin nombre'
                );

                const sku = escapeHtml(
                    item.sku || 'Sin SKU'
                );

                const barcode = escapeHtml(
                    item.barcode || ''
                );

                const unit = escapeHtml(
                    item.unit || 'PZA'
                );

                return `
                    <button
                        type="button"
                        class="flex w-full items-center justify-between gap-4 border-b border-slate-100 px-4 py-3.5 text-left transition last:border-b-0 hover:bg-slate-50 focus:bg-slate-50"
                        data-search-result
                        data-index="${index}"
                        role="option"
                        aria-selected="false">

                        <span class="min-w-0">

                            <span class="block truncate text-sm font-black text-slate-900">
                                ${name}
                            </span>

                            <span class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-slate-500">

                                <span class="font-mono font-semibold">
                                    ${sku}
                                </span>

                                ${
                                    barcode
                                        ? `
                                            <span class="text-slate-300">•</span>
                                            <span class="font-mono">
                                                ${barcode}
                                            </span>
                                        `
                                        : ''
                                }

                                <span class="text-slate-300">•</span>

                                <span>${unit}</span>

                            </span>

                        </span>

                    </button>
                `;
            }).join('');

            showResults();

            results
                .querySelectorAll('[data-search-result]')
                .forEach(function (button) {

                    button.addEventListener(
                        'mouseenter',
                        function () {

                            activeIndex =
                                Number(button.dataset.index);

                            updateActiveResult();
                        }
                    );

                    button.addEventListener(
                        'click',
                        function () {

                            const index =
                                Number(button.dataset.index);

                            selectItem(index);
                        }
                    );
                });

            updateActiveResult();
        }

        async function searchProducts(query) {

            query = query.trim();

            if (controller) {
                controller.abort();
                controller = null;
            }

            if (query.length < minChars) {

                items = [];

                results.innerHTML = '';

                hideResults();

                setLoading(false);

                return;
            }

            controller = new AbortController();

            setLoading(true);

            try {

                const url = new URL(
                    endpoint,
                    window.location.origin
                );

                url.searchParams.set('q', query);

                const response = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    signal: controller.signal,
                });

                if (!response.ok) {
                    throw new Error(
                        'No se pudo realizar la búsqueda.'
                    );
                }

                const data = await response.json();

                items = Array.isArray(data.items)
                    ? data.items
                    : [];

                activeIndex = -1;

                renderResults();

            } catch (error) {

                if (error.name === 'AbortError') {
                    return;
                }

                console.error(
                    'Error buscando productos:',
                    error
                );

                items = [];

                renderMessage(
                    'No se pudo realizar la búsqueda.'
                );

            } finally {

                setLoading(false);

                controller = null;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | ESCRITURA
        |--------------------------------------------------------------------------
        */

        input.addEventListener('input', function () {

            clearTimeout(timer);

            activeIndex = -1;

            timer = setTimeout(function () {

                searchProducts(input.value);

            }, debounceTime);
        });

        /*
        |--------------------------------------------------------------------------
        | TECLADO
        |--------------------------------------------------------------------------
        */

        input.addEventListener('keydown', function (event) {

            if (event.key === 'ArrowDown') {

                if (!items.length) {
                    return;
                }

                event.preventDefault();

                activeIndex = Math.min(
                    activeIndex + 1,
                    items.length - 1
                );

                updateActiveResult();

                return;
            }

            if (event.key === 'ArrowUp') {

                if (!items.length) {
                    return;
                }

                event.preventDefault();

                activeIndex = Math.max(
                    activeIndex - 1,
                    0
                );

                updateActiveResult();

                return;
            }

            if (event.key === 'Enter') {

                /*
                 * Si hay una opción seleccionada.
                 */
                if (
                    activeIndex >= 0 &&
                    items[activeIndex]
                ) {

                    event.preventDefault();

                    selectItem(activeIndex);

                    return;
                }

                /*
                 * Si solamente existe un resultado,
                 * lo seleccionamos automáticamente.
                 *
                 * Esto además será útil para el lector
                 * de código de barras.
                 */
                if (items.length === 1) {

                    event.preventDefault();

                    selectItem(0);

                    return;
                }

                return;
            }

            if (event.key === 'Escape') {

                hideResults();

                return;
            }
        });

        /*
        |--------------------------------------------------------------------------
        | CLICK FUERA
        |--------------------------------------------------------------------------
        */

        document.addEventListener('click', function (event) {

            if (!searchRoot.contains(event.target)) {
                hideResults();
            }
        });
    }


    /*
    |--------------------------------------------------------------------------
    | UNIDADES DE COMPRA
    |--------------------------------------------------------------------------
    */

    async function updatePurchaseUnit(row, stockItemId) {

        if (!row) {
            return;
        }

        const unitSelect =
            row.querySelector('.product-unit-select');

        if (!unitSelect) {
            return;
        }

        unitSelect.innerHTML = `
            <option value="">
                Cargando unidades...
            </option>
        `;

        unitSelect.disabled = true;

        if (!stockItemId) {

            unitSelect.innerHTML = `
                <option value="">
                    Primero selecciona un artículo
                </option>
            `;

            return;
        }

        try {

            const response = await fetch(
                `/api/purchases/product-units/${stockItemId}`,
                {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                }
            );

            if (!response.ok) {
                throw new Error(
                    'No se pudieron cargar las unidades.'
                );
            }

            const data = await response.json();

            const units = data.items ?? [];

            unitSelect.innerHTML = '';

            if (units.length === 0) {

                unitSelect.innerHTML = `
                    <option value="">
                        Sin unidad de compra configurada
                    </option>
                `;

                return;
            }

            units.forEach(function (unit) {

                const option =
                    document.createElement('option');

                option.value = unit.id;

                option.textContent = unit.name;

                option.dataset.allowDecimal =
                    unit.allow_decimal ? '1' : '0';

                option.dataset.conversionFactor =
                    unit.conversion_factor;

                unitSelect.appendChild(option);
            });

            unitSelect.disabled = false;

            if (units.length === 1) {
                unitSelect.value = units[0].id;
            }

        } catch (error) {

            console.error(error);

            unitSelect.innerHTML = `
                <option value="">
                    No se pudieron cargar las unidades
                </option>
            `;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | PRODUCTO SELECCIONADO
    |--------------------------------------------------------------------------
    */

    function initializePurchaseProductSearch(row) {

        const search =
            row.querySelector('.purchase-product-search');

        const stockItemId =
            row.querySelector('.stock-item-id');

        if (!search || !stockItemId) {
            return;
        }

        search.addEventListener(
            'ui-search-selected',
            function (event) {

                const item = event.detail;

                const selectedStockItemId =
                    item.stock_item_id ?? '';

                stockItemId.value =
                    selectedStockItemId;

                updatePurchaseUnit(
                    row,
                    selectedStockItemId
                );
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | AGREGAR PARTIDA
    |--------------------------------------------------------------------------
    */

    function addPurchaseRow() {

        const container =
            document.getElementById('items-container');

        const template =
            document.getElementById('purchase-row-template');

        if (!container || !template) {
            return;
        }

        const html = template.innerHTML
            .replace(
                /INDEX/g,
                purchaseRowCount
            )
            .replace(
                '<span class="row-number">1</span>',
                `<span class="row-number">${purchaseRowCount + 1}</span>`
            );

        const wrapper =
            document.createElement('div');

        wrapper.innerHTML = html.trim();

        const row =
            wrapper.firstElementChild;

        if (!row) {
            return;
        }

        container.appendChild(row);

        /*
         * Primero inicializamos el buscador.
         */
        initializeDynamicProductSearch(row);

        /*
         * Después escuchamos qué producto
         * fue seleccionado.
         */
        initializePurchaseProductSearch(row);

        purchaseRowCount++;

        updateEmptyMessage();

        updateGrandTotal();
    }


    /*
    |--------------------------------------------------------------------------
    | ELIMINAR PARTIDA
    |--------------------------------------------------------------------------
    */

    function removePurchaseRow(button) {

        const row =
            button.closest('.item-row');

        if (!row) {
            return;
        }

        row.remove();

        updateRowNumbers();

        updateEmptyMessage();

        updateGrandTotal();
    }


    /*
    |--------------------------------------------------------------------------
    | NUMERACIÓN
    |--------------------------------------------------------------------------
    */

    function updateRowNumbers() {

        document
            .querySelectorAll('.item-row')
            .forEach(function (row, index) {

                const number =
                    row.querySelector('.row-number');

                if (number) {
                    number.textContent = index + 1;
                }
            });
    }


    /*
    |--------------------------------------------------------------------------
    | MENSAJE SIN ARTÍCULOS
    |--------------------------------------------------------------------------
    */

    function updateEmptyMessage() {

        const container =
            document.getElementById('items-container');

        const emptyMessage =
            document.getElementById('empty-items-message');

        if (!container || !emptyMessage) {
            return;
        }

        const hasItems =
            container.querySelector('.item-row');

        emptyMessage.classList.toggle(
            'hidden',
            Boolean(hasItems)
        );
    }


    /*
    |--------------------------------------------------------------------------
    | TOTAL DE PARTIDA
    |--------------------------------------------------------------------------
    */

    function calculateRowTotal(input) {

        const row =
            input.closest('.item-row');

        if (!row) {
            return;
        }

        const quantity =
            parseFloat(
                row.querySelector('.qty-input')?.value
            ) || 0;

        const cost =
            parseFloat(
                row.querySelector('.cost-input')?.value
            ) || 0;

        const subtotal =
            quantity * cost;

        const subtotalElement =
            row.querySelector('.row-subtotal');

        if (subtotalElement) {

            subtotalElement.textContent =
                '$' + subtotal.toFixed(2);
        }

        updateGrandTotal();
    }


    /*
    |--------------------------------------------------------------------------
    | TOTAL GENERAL
    |--------------------------------------------------------------------------
    */

    function updateGrandTotal() {

        let total = 0;

        document
            .querySelectorAll('.item-row')
            .forEach(function (row) {

                const quantity =
                    parseFloat(
                        row.querySelector('.qty-input')?.value
                    ) || 0;

                const cost =
                    parseFloat(
                        row.querySelector('.cost-input')?.value
                    ) || 0;

                total += quantity * cost;
            });

        const totalElement =
            document.getElementById(
                'grand-total-display'
            );

        if (totalElement) {

            totalElement.innerHTML =
                '$' + total.toFixed(2) +
                ' <span class="text-sm font-bold text-slate-400">MXN</span>';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | PROVEEDOR
    |--------------------------------------------------------------------------
    */

    function initializePurchaseSupplierSearch() {

        const supplierSearch =
            document.getElementById(
                'purchase-supplier-search'
            );

        const supplierId =
            document.getElementById(
                'supplier_id'
            );

        if (!supplierSearch || !supplierId) {
            return;
        }

        supplierSearch.addEventListener(
            'ui-search-selected',
            function (event) {

                const item = event.detail;

                supplierId.value =
                    item.supplier_id ?? '';
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | INICIALIZACIÓN
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'DOMContentLoaded',
        function () {

            /*
             * El buscador de proveedor ya existe
             * directamente en la página.
             */
            initializePurchaseSupplierSearch();

            /*
             * La primera partida se crea
             * dinámicamente.
             */
            addPurchaseRow();
        }
    );
</script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const supplierSearch = document.getElementById(
                'purchase-supplier-search'
            );

            const supplierId = document.getElementById(
                'supplier_id'
            );

            if (supplierSearch && supplierId) {

                supplierSearch.addEventListener(
                    'ui-search-selected',
                    function(event) {

                        const item = event.detail;

                        supplierId.value = item.supplier_id ?? '';

                    }
                );
            }

        });
    </script>

</x-layouts.app>
