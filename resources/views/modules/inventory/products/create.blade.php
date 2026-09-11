<x-layouts.app title="Nuevo producto | ABARROTESBASE">

    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

    <div class="mx-auto max-w-5xl space-y-6">

        {{-- HEADER --}}
        <x-layout.page-header
            eyebrow="Inventario"
            title="Nuevo producto"
            description="Registra la información necesaria para identificar, vender y controlar este producto."
        >
            <x-slot:actions>
                <a
                    href="{{ route('products.index') }}"
                    class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 text-sm font-bold text-slate-700 transition hover:bg-slate-50"
                >
                    Volver al catálogo
                </a>
            </x-slot:actions>
        </x-layout.page-header>


        {{-- MENSAJE GENERAL DE ERRORES --}}
        @if ($errors->any())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4">
                <p class="font-bold text-rose-800">
                    Revisa la información del producto.
                </p>

                <p class="mt-1 text-sm text-rose-700">
                    Hay uno o más campos que necesitan corrección antes de guardar.
                </p>
            </div>
        @endif


        <form
            method="POST"
            action="{{ route('products.store') }}"
            class="space-y-6"
        >
            @csrf

            {{-- ========================================================= --}}
            {{-- IDENTIFICACIÓN --}}
            {{-- ========================================================= --}}
            <x-ui.card padding="p-0" class="overflow-hidden">

                <div class="border-b border-slate-200 bg-slate-50/70 px-5 py-4 sm:px-6">
                    <p class="text-xs font-black uppercase tracking-[0.14em] text-emerald-700">
                        Paso 1
                    </p>

                    <h2 class="mt-1 text-lg font-black text-slate-900">
                        Identificación del producto
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Escanea el código o escríbelo manualmente. Intentaremos completar la información disponible.
                    </p>
                </div>


                <div class="space-y-6 p-5 sm:p-6">

                    {{-- BARCODE --}}
                    <div>
                        <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                            <label
                                for="barcode"
                                class="text-sm font-bold text-slate-800"
                            >
                                Código de barras
                                <span class="text-rose-600">*</span>
                            </label>

                            <button
                                type="button"
                                id="open-scanner"
                                class="inline-flex min-h-10 items-center justify-center rounded-xl border border-emerald-200 bg-emerald-50 px-3 text-sm font-bold text-emerald-700 transition hover:bg-emerald-100"
                            >
                                Escanear con cámara
                            </button>
                        </div>

                        <div class="relative">
                            <x-ui.input
                                id="barcode"
                                name="barcode"
                                :value="old('barcode', $initialBarcode ?? '')"
                                required
                                autocomplete="off"
                                inputmode="numeric"
                                placeholder="Escanea o escribe el código..."
                                class="pr-12 font-mono text-lg font-bold"
                                :error="$errors->has('barcode')"
                            />

                            <div
                                id="barcode-loader"
                                class="pointer-events-none absolute inset-y-0 right-4 hidden items-center"
                            >
                                <span class="h-5 w-5 animate-spin rounded-full border-2 border-slate-200 border-t-emerald-600"></span>
                            </div>
                        </div>

                        <p class="mt-2 text-sm text-slate-500">
                            También puedes usar un lector físico y presionar Enter.
                        </p>

                        @error('barcode')
                            <p class="mt-2 text-sm font-semibold text-rose-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>


                    {{-- ESTADO DE CONSULTA --}}
                    <div
                        id="lookup-status"
                        class="hidden rounded-xl border px-4 py-3 text-sm"
                    ></div>


                    {{-- PREVIEW EXTERNO --}}
                    <div
                        id="product-preview"
                        class="hidden rounded-2xl border border-slate-200 bg-slate-50 p-4"
                    >
                        <div class="flex items-center gap-4">

                            <img
                                id="product-preview-image"
                                src=""
                                alt=""
                                class="hidden h-16 w-16 shrink-0 rounded-xl border border-slate-200 bg-white object-contain"
                            >

                            <div class="min-w-0">
                                <p class="font-bold text-slate-900">
                                    Información encontrada
                                </p>

                                <p
                                    id="product-preview-description"
                                    class="mt-1 text-sm text-slate-600"
                                ></p>

                                <p class="mt-1 text-xs text-slate-400">
                                    Verifica los datos antes de guardar el producto.
                                </p>
                            </div>

                        </div>
                    </div>


                    <div class="grid gap-5 sm:grid-cols-2">

                        {{-- NOMBRE --}}
                        <div class="sm:col-span-2">
                            <label
                                for="name"
                                class="mb-2 block text-sm font-bold text-slate-800"
                            >
                                Nombre del producto
                                <span class="text-rose-600">*</span>
                            </label>

                            <x-ui.input
                                id="name"
                                name="name"
                                :value="old('name')"
                                required
                                placeholder="Ej. Coca-Cola Original 600 ml"
                                :error="$errors->has('name')"
                            />

                            @error('name')
                                <p class="mt-2 text-sm font-semibold text-rose-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>


                        {{-- SKU --}}
                        <div>
                            <label
                                for="sku"
                                class="mb-2 block text-sm font-bold text-slate-800"
                            >
                                Código interno / SKU
                            </label>

                            <x-ui.input
                                id="sku"
                                name="sku"
                                :value="old('sku')"
                                placeholder="Ej. COC-7890"
                                :error="$errors->has('sku')"
                            />

                            <p class="mt-2 text-xs text-slate-500">
                                Si lo dejas vacío durante la consulta, intentaremos generar uno.
                            </p>

                            @error('sku')
                                <p class="mt-2 text-sm font-semibold text-rose-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                    </div>

                </div>
            </x-ui.card>


            {{-- ========================================================= --}}
            {{-- CLASIFICACIÓN Y VENTA --}}
            {{-- ========================================================= --}}
            <x-ui.card padding="p-0" class="overflow-hidden">

                <div class="border-b border-slate-200 bg-slate-50/70 px-5 py-4 sm:px-6">
                    <p class="text-xs font-black uppercase tracking-[0.14em] text-emerald-700">
                        Paso 2
                    </p>

                    <h2 class="mt-1 text-lg font-black text-slate-900">
                        Clasificación y venta
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Define cómo se clasifica y cómo será vendido el producto.
                    </p>
                </div>


                <div class="grid gap-5 p-5 sm:grid-cols-2 sm:p-6">

                    {{-- CATEGORÍA --}}
                    <div>
                        <div class="mb-2 flex items-center justify-between gap-3">
                            <label
                                for="category_id"
                                class="text-sm font-bold text-slate-800"
                            >
                                Categoría
                            </label>

                            <button
                                type="button"
                                data-quick-create="category"
                                class="text-sm font-bold text-emerald-700 hover:text-emerald-800"
                            >
                                + Nueva
                            </button>
                        </div>

                        <select
                            id="category_id"
                            name="category_id"
                            class="app-input"
                        >
                            <option value="">Sin categoría</option>

                            @foreach ($categories as $category)
                                <option
                                    value="{{ $category->id }}"
                                    @selected(old('category_id') === $category->id)
                                >
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>


                    {{-- MARCA --}}
                    <div>
                        <div class="mb-2 flex items-center justify-between gap-3">
                            <label
                                for="brand_id"
                                class="text-sm font-bold text-slate-800"
                            >
                                Marca
                            </label>

                            <button
                                type="button"
                                data-quick-create="brand"
                                class="text-sm font-bold text-emerald-700 hover:text-emerald-800"
                            >
                                + Nueva
                            </button>
                        </div>

                        <select
                            id="brand_id"
                            name="brand_id"
                            class="app-input"
                        >
                            <option value="">Sin marca</option>

                            @foreach ($brands as $brand)
                                <option
                                    value="{{ $brand->id }}"
                                    @selected(old('brand_id') === $brand->id)
                                >
                                    {{ $brand->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>


                    {{-- UNIDAD --}}
                    <div>
                        <label
                            for="unit_id"
                            class="mb-2 block text-sm font-bold text-slate-800"
                        >
                            Unidad de venta
                            <span class="text-rose-600">*</span>
                        </label>

                        <select
                            id="unit_id"
                            name="unit_id"
                            required
                            class="app-input"
                        >
                            @foreach ($units as $unit)
                                <option
                                    value="{{ $unit->id }}"
                                    @selected(old('unit_id') === $unit->id)
                                >
                                    {{ $unit->name }} ({{ $unit->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>


                    {{-- TIPO --}}
                    <div>
                        <label
                            for="product_type"
                            class="mb-2 block text-sm font-bold text-slate-800"
                        >
                            Forma de venta
                            <span class="text-rose-600">*</span>
                        </label>

                        <select
                            id="product_type"
                            name="product_type"
                            required
                            class="app-input"
                        >
                            <option
                                value="simple"
                                @selected(old('product_type', 'simple') === 'simple')
                            >
                                Por pieza
                            </option>

                            <option
                                value="bulk"
                                @selected(old('product_type') === 'bulk')
                            >
                                A granel / permite decimales
                            </option>
                        </select>
                    </div>


                    {{-- PRECIO --}}
                    <div>
                        <label
                            for="price"
                            class="mb-2 block text-sm font-bold text-slate-800"
                        >
                            Precio público
                            <span class="text-rose-600">*</span>
                        </label>

                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center font-black text-slate-500">
                                $
                            </span>

                            <x-ui.input
                                id="price"
                                type="number"
                                step="0.01"
                                min="0"
                                name="price"
                                :value="old('price')"
                                required
                                placeholder="0.00"
                                class="pl-9 text-lg font-black"
                                :error="$errors->has('price')"
                            />
                        </div>

                        <p class="mt-2 text-xs font-semibold text-slate-500">
                            Precio de venta en MXN.
                        </p>

                        @error('price')
                            <p class="mt-2 text-sm font-semibold text-rose-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>


                    {{-- IMPUESTO --}}
                    <div>
                        <label
                            for="tax_rate_id"
                            class="mb-2 block text-sm font-bold text-slate-800"
                        >
                            Impuesto
                        </label>

                        <select
                            id="tax_rate_id"
                            name="tax_rate_id"
                            class="app-input"
                        >
                            <option value="">Sin impuesto aplicable</option>

                            @foreach ($taxRates as $tax)
                                <option
                                    value="{{ $tax->id }}"
                                    @selected(old('tax_rate_id') === $tax->id)
                                >
                                    {{ $tax->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                </div>
            </x-ui.card>


            {{-- ACCIONES --}}
            <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end">

                <a
                    href="{{ route('products.index') }}"
                    class="inline-flex min-h-12 items-center justify-center rounded-xl border border-slate-300 bg-white px-5 text-base font-bold text-slate-700 transition hover:bg-slate-50"
                >
                    Cancelar
                </a>

                <x-ui.button
                    type="submit"
                    variant="primary"
                    size="md"
                    class="sm:min-w-48"
                >
                    Guardar producto
                </x-ui.button>

            </div>

        </form>
    </div>


    {{-- ============================================================= --}}
    {{-- MODAL ESCÁNER --}}
    {{-- ============================================================= --}}
    <x-ui.modal
        id="scanner-modal"
        size="sm"
        title="Escanear código de barras"
        description="Coloca el código de barras frente a la cámara."
        close-id="close-scanner"
    >
        <div class="space-y-4 p-5 sm:p-6">

            <div class="relative flex min-h-[260px] items-center justify-center overflow-hidden rounded-2xl bg-slate-950">
                <div
                    id="reader"
                    class="w-full [&>video]:max-h-[320px] [&>video]:object-cover"
                ></div>
            </div>

            <div
                id="scanner-feedback"
                class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-center text-sm font-semibold text-slate-600"
            >
                Apunta al código de barras.
            </div>

        </div>

        <x-slot:footer>
            <button
                type="button"
                id="cancel-scanner"
                class="inline-flex min-h-11 w-full items-center justify-center rounded-xl border border-slate-300 bg-white px-4 text-sm font-bold text-slate-700 transition hover:bg-slate-100"
            >
                Cerrar escáner
            </button>
        </x-slot:footer>
    </x-ui.modal>


    {{-- ============================================================= --}}
    {{-- MODAL CATEGORÍA / MARCA --}}
    {{-- ============================================================= --}}
    <x-ui.modal
        id="quick-modal"
        size="sm"
        title="Nuevo registro"
        description="Agrega el registro sin abandonar el producto."
        close-id="close-quick-modal"
    >
        <div class="p-5 sm:p-6">

            <label
                for="quick-modal-input"
                class="mb-2 block text-sm font-bold text-slate-800"
            >
                Nombre
            </label>

            <x-ui.input
                id="quick-modal-input"
                type="text"
                placeholder="Escribe el nombre..."
                autocomplete="off"
            />

            <p
                id="quick-modal-error"
                class="mt-2 hidden text-sm font-semibold text-rose-600"
            ></p>

        </div>

        <x-slot:footer>
            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

                <button
                    type="button"
                    id="cancel-quick-modal"
                    class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 text-sm font-bold text-slate-700 transition hover:bg-slate-100"
                >
                    Cancelar
                </button>

                <x-ui.button
                    type="button"
                    id="save-quick-modal"
                    variant="primary"
                    size="sm"
                >
                    Guardar
                </x-ui.button>

            </div>
        </x-slot:footer>
    </x-ui.modal>


    <script>
        document.addEventListener('DOMContentLoaded', () => {

            const barcodeInput = document.getElementById('barcode');
            const barcodeLoader = document.getElementById('barcode-loader');
            const lookupStatus = document.getElementById('lookup-status');

            const scannerModal = document.getElementById('scanner-modal');
            const scannerFeedback = document.getElementById('scanner-feedback');

            const quickModal = document.getElementById('quick-modal');
            const quickModalInput = document.getElementById('quick-modal-input');
            const quickModalError = document.getElementById('quick-modal-error');

            let html5QrCode = null;
            let activeQuickType = null;


            /*
             * ---------------------------------------------------------
             * HELPERS DE MODAL
             * ---------------------------------------------------------
             */

            function openModal(modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.classList.add('overflow-hidden');
            }

            function closeModal(modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.classList.remove('overflow-hidden');
            }


            /*
             * ---------------------------------------------------------
             * CONSULTA DE PRODUCTO
             * ---------------------------------------------------------
             */

            async function fetchProductInfo(rawBarcode) {

                const barcode = String(rawBarcode || '').trim();

                if (!barcode) {
                    return;
                }

                barcodeLoader.classList.remove('hidden');
                barcodeLoader.classList.add('flex');

                lookupStatus.classList.add('hidden');

                try {

                    const response = await fetch(
                        `/api/products/lookup/${encodeURIComponent(barcode)}`,
                        {
                            headers: {
                                'Accept': 'application/json'
                            }
                        }
                    );

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error('No se pudo consultar el producto.');
                    }

                    if (!data.found) {

                        lookupStatus.className =
                            'rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800';

                        lookupStatus.textContent =
                            'No encontramos información pública para este código. Puedes registrar el producto manualmente.';

                        return;
                    }


                    lookupStatus.className =
                        'rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800';

                    lookupStatus.textContent =
                        'Encontramos información del producto. Verifica los datos antes de guardar.';


                    /*
                     * Nombre
                     */
                    const nameInput = document.getElementById('name');

                    if (!nameInput.value && data.name) {
                        nameInput.value = data.name;
                    }


                    /*
                     * SKU sugerido
                     */
                    const skuInput = document.getElementById('sku');

                    if (!skuInput.value && data.name) {

                        const prefix = data.name
                            .replace(/[^a-zA-Z0-9]/g, '')
                            .substring(0, 3)
                            .toUpperCase();

                        const suffix = barcode.slice(-4);

                        skuInput.value = `${prefix}-${suffix}`;
                    }


                    /*
                     * Marca
                     */
                    selectMatchingOption(
                        document.getElementById('brand_id'),
                        data.brand
                    );


                    /*
                     * Categoría
                     */
                    selectMatchingOption(
                        document.getElementById('category_id'),
                        data.category_suggestion
                    );


                    /*
                     * Unidad por defecto
                     */
                    const unitSelect = document.getElementById('unit_id');

                    const pieceOption = Array.from(unitSelect.options).find(option => {

                        const text = normalize(option.text);

                        return text.includes('pieza')
                            || text.includes('pza')
                            || text.includes('unidad');
                    });

                    if (pieceOption) {
                        unitSelect.value = pieceOption.value;
                    }


                    /*
                     * Preview
                     */
                    showProductPreview(data);

                } catch (error) {

                    console.error(error);

                    lookupStatus.className =
                        'rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700';

                    lookupStatus.textContent =
                        'No fue posible consultar la información del código. Puedes continuar manualmente.';

                } finally {

                    barcodeLoader.classList.add('hidden');
                    barcodeLoader.classList.remove('flex');
                }
            }


            function normalize(value) {
                return String(value || '')
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .toLowerCase()
                    .trim();
            }


            function selectMatchingOption(select, value) {

                if (!select || !value) {
                    return;
                }

                const target = normalize(value);

                const option = Array.from(select.options).find(item => {

                    const text = normalize(item.text);

                    return text === target
                        || text.includes(target)
                        || target.includes(text);
                });

                if (option && option.value) {
                    select.value = option.value;
                }
            }


            function showProductPreview(data) {

                const preview = document.getElementById('product-preview');
                const image = document.getElementById('product-preview-image');
                const description = document.getElementById('product-preview-description');

                const details = [
                    data.brand,
                    data.quantity,
                    data.category_suggestion
                ].filter(Boolean);

                description.textContent =
                    details.length
                        ? details.join(' · ')
                        : 'Se encontró información asociada al código.';

                if (data.image) {
                    image.src = data.image;
                    image.alt = data.name || 'Producto encontrado';
                    image.classList.remove('hidden');
                } else {
                    image.classList.add('hidden');
                }

                preview.classList.remove('hidden');
            }


            /*
             * Enter / lector físico
             */
            barcodeInput.addEventListener('keydown', event => {

                if (event.key !== 'Enter') {
                    return;
                }

                event.preventDefault();

                fetchProductInfo(barcodeInput.value);
            });


            /*
             * Si se escribió manualmente y se abandona el campo.
             */
            barcodeInput.addEventListener('change', () => {
                fetchProductInfo(barcodeInput.value);
            });


            /*
             * ---------------------------------------------------------
             * ESCÁNER
             * ---------------------------------------------------------
             */

            async function startScanner() {

                scannerFeedback.className =
                    'rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-center text-sm font-semibold text-slate-600';

                scannerFeedback.textContent = 'Buscando código...';

                openModal(scannerModal);

                try {

                    if (!html5QrCode) {
                        html5QrCode = new Html5Qrcode('reader');
                    }

                    await html5QrCode.start(
                        {
                            facingMode: 'environment'
                        },
                        {
                            fps: 15,
                            qrbox: {
                                width: 240,
                                height: 120
                            }
                        },
                        async decodedText => {

                            scannerFeedback.className =
                                'rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-center text-sm font-bold text-emerald-700';

                            scannerFeedback.textContent =
                                `Código leído: ${decodedText}`;

                            barcodeInput.value = decodedText;

                            await stopScanner();

                            fetchProductInfo(decodedText);
                        },
                        () => {}
                    );

                } catch (error) {

                    console.error(error);

                    scannerFeedback.className =
                        'rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-center text-sm font-semibold text-rose-700';

                    scannerFeedback.textContent =
                        'No se pudo iniciar la cámara. Revisa los permisos del navegador.';
                }
            }


            async function stopScanner() {

                try {

                    if (html5QrCode?.isScanning) {
                        await html5QrCode.stop();
                    }

                    html5QrCode?.clear();

                } catch (error) {
                    console.error(error);
                }

                closeModal(scannerModal);
            }


            document
                .getElementById('open-scanner')
                .addEventListener('click', startScanner);

            document
                .getElementById('close-scanner')
                .addEventListener('click', stopScanner);

            document
                .getElementById('cancel-scanner')
                .addEventListener('click', stopScanner);


            /*
             * ---------------------------------------------------------
             * CATEGORÍA / MARCA RÁPIDA
             * ---------------------------------------------------------
             */

            document.querySelectorAll('[data-quick-create]').forEach(button => {

                button.addEventListener('click', () => {

                    activeQuickType = button.dataset.quickCreate;

                    quickModalInput.value = '';
                    quickModalError.classList.add('hidden');

                    openModal(quickModal);

                    setTimeout(() => {
                        quickModalInput.focus();
                    }, 50);
                });
            });


            function closeQuickModal() {
                closeModal(quickModal);
                activeQuickType = null;
            }


            document
                .getElementById('close-quick-modal')
                .addEventListener('click', closeQuickModal);

            document
                .getElementById('cancel-quick-modal')
                .addEventListener('click', closeQuickModal);


            async function saveQuickRecord() {

                const name = quickModalInput.value.trim();

                if (!name || !activeQuickType) {

                    quickModalError.textContent = 'Escribe un nombre.';
                    quickModalError.classList.remove('hidden');

                    return;
                }

                const url = activeQuickType === 'category'
                    ? "{{ route('categories.quick-store') }}"
                    : "{{ route('brands.quick-store') }}";

                try {

                    const response = await fetch(url, {
                        method: 'POST',

                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': "{{ csrf_token() }}"
                        },

                        body: JSON.stringify({
                            name
                        })
                    });

                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        throw new Error(
                            data.message || 'No se pudo guardar el registro.'
                        );
                    }

                    const select = document.getElementById(
                        activeQuickType === 'category'
                            ? 'category_id'
                            : 'brand_id'
                    );

                    const record = data[activeQuickType];

                    select.add(
                        new Option(
                            record.name,
                            record.id,
                            true,
                            true
                        )
                    );

                    closeQuickModal();

                } catch (error) {

                    quickModalError.textContent = error.message;
                    quickModalError.classList.remove('hidden');
                }
            }


            document
                .getElementById('save-quick-modal')
                .addEventListener('click', saveQuickRecord);


            quickModalInput.addEventListener('keydown', event => {

                if (event.key === 'Enter') {
                    event.preventDefault();
                    saveQuickRecord();
                }
            });


            /*
             * ---------------------------------------------------------
             * BARCODE PRECARGADO
             * ---------------------------------------------------------
             */

            if (barcodeInput.value.trim()) {
                fetchProductInfo(barcodeInput.value);
            } else {
                barcodeInput.focus();
            }

        });
    </script>

</x-layouts.app>
