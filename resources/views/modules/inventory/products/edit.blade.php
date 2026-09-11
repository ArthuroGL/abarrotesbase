<x-layouts.app title="Editar Producto | ABARROTESBASE">

    @php
        $currentBarcode =
            $product->stockItem?->barcodes->firstWhere('is_primary', true)?->barcode
            ?? $product->stockItem?->barcodes->first()?->barcode
            ?? '';

        $currentPrice =
            $product->stockItem?->prices->first()?->amount ?? '';

        $currentUnitId =
            $product->stockItem?->inventory_unit_id ?? '';
    @endphp


    <div class="mx-auto max-w-6xl space-y-6">

        {{-- ========================================================= --}}
        {{-- HEADER --}}
        {{-- ========================================================= --}}

        <x-layout.page-header
            eyebrow="Inventario · Productos"
            title="Editar producto"
            :description="'Actualiza la información comercial y de venta de ' . $product->name . '.'"
        >
            <x-slot:actions>

                <a
                    href="{{ route('products.index') }}"
                    class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 text-sm font-bold text-slate-700 transition hover:bg-slate-50"
                >
                    ← Volver al catálogo
                </a>

            </x-slot:actions>
        </x-layout.page-header>


        {{-- ========================================================= --}}
        {{-- FORMULARIO + RESUMEN --}}
        {{-- ========================================================= --}}

        <form
            method="POST"
            action="{{ route('products.update', $product) }}"
            class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_300px]"
        >

            @csrf
            @method('PUT')


            {{-- ===================================================== --}}
            {{-- COLUMNA PRINCIPAL --}}
            {{-- ===================================================== --}}

            <div class="space-y-6">


                {{-- ================================================= --}}
                {{-- 1. IDENTIFICACIÓN --}}
                {{-- ================================================= --}}

                <x-ui.card
                    padding="p-0"
                    class="overflow-hidden"
                >

                    {{-- Encabezado --}}
                    <div class="border-b border-slate-200 bg-slate-50/70 px-5 py-5 sm:px-7">

                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                            <div>

                                <p class="text-xs font-black uppercase tracking-[0.14em] text-emerald-700">
                                    Información básica
                                </p>

                                <h2 class="mt-1 text-lg font-black text-slate-900">
                                    Identificación del producto
                                </h2>

                                <p class="mt-1 text-sm leading-6 text-slate-500">
                                    Datos utilizados para identificar el producto dentro del catálogo.
                                </p>

                            </div>


                            {{-- Estado --}}
                            <label
                                class="flex min-h-12 cursor-pointer items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 transition hover:border-slate-300"
                            >

                                <input
                                    type="checkbox"
                                    name="is_active"
                                    value="1"
                                    @checked(old('is_active', $product->is_active))
                                    class="h-5 w-5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                                >

                                <span>

                                    <span class="block text-sm font-black text-slate-800">
                                        Producto activo
                                    </span>

                                    <span class="block text-xs font-medium text-slate-500">
                                        Disponible para venta
                                    </span>

                                </span>

                            </label>

                        </div>

                    </div>


                    {{-- Campos --}}
                    <div class="grid gap-5 px-5 py-6 sm:px-7 sm:py-7">


                        {{-- Nombre --}}
                        <div>

                            <label
                                for="name"
                                class="mb-2 block text-sm font-bold text-slate-800"
                            >
                                Nombre del producto
                                <span class="text-rose-500">*</span>
                            </label>

                            <x-ui.input
                                id="name"
                                name="name"
                                type="text"
                                :value="old('name', $product->name)"
                                required
                                autocomplete="off"
                                placeholder="Ej. Galletas Chokis 100 g"
                                :error="$errors->has('name')"
                            />

                            <p class="mt-2 text-xs leading-5 text-slate-400">
                                Usa un nombre claro que permita identificar rápidamente el producto en Ventas.
                            </p>

                            @error('name')
                                <p class="mt-2 text-sm font-semibold text-rose-600">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        {{-- Barcode + SKU --}}
                        <div class="grid gap-5 md:grid-cols-2">


                            {{-- Código de barras --}}
                            <div>

                                <label
                                    for="barcode"
                                    class="mb-2 block text-sm font-bold text-slate-800"
                                >
                                    Código de barras
                                    <span class="text-rose-500">*</span>
                                </label>

                                <x-ui.input
                                    id="barcode"
                                    name="barcode"
                                    type="text"
                                    inputmode="numeric"
                                    :value="old('barcode', $currentBarcode)"
                                    required
                                    autocomplete="off"
                                    placeholder="7501000000000"
                                    class="font-mono text-base tracking-wide"
                                    :error="$errors->has('barcode')"
                                />

                                <p class="mt-2 text-xs leading-5 text-slate-400">
                                    Código utilizado principalmente para escanear el producto en el punto de venta.
                                </p>

                                @error('barcode')
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
                                    type="text"
                                    :value="old('sku', $product->sku)"
                                    autocomplete="off"
                                    placeholder="Ej. GAL-001"
                                    class="font-mono text-base"
                                    :error="$errors->has('sku')"
                                />

                                <p class="mt-2 text-xs leading-5 text-slate-400">
                                    Código interno para identificar el producto dentro del negocio.
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


                {{-- ================================================= --}}
                {{-- 2. CLASIFICACIÓN --}}
                {{-- ================================================= --}}

                <x-ui.card
                    padding="p-0"
                    class="overflow-hidden"
                >

                    <div class="border-b border-slate-200 bg-slate-50/70 px-5 py-5 sm:px-7">

                        <p class="text-xs font-black uppercase tracking-[0.14em] text-emerald-700">
                            Organización
                        </p>

                        <h2 class="mt-1 text-lg font-black text-slate-900">
                            Clasificación del producto
                        </h2>

                        <p class="mt-1 text-sm leading-6 text-slate-500">
                            Define cómo se organiza y cómo se maneja la cantidad del producto.
                        </p>

                    </div>


                    <div class="grid gap-5 px-5 py-6 sm:grid-cols-2 sm:px-7 sm:py-7">


                        {{-- Categoría --}}
                        <div>

                            <label
                                for="category_id"
                                class="mb-2 block text-sm font-bold text-slate-800"
                            >
                                Categoría
                            </label>

                            <select
                                id="category_id"
                                name="category_id"
                                class="app-input"
                            >

                                <option value="">
                                    Sin categoría
                                </option>

                                @foreach ($categories as $category)

                                    <option
                                        value="{{ $category->id }}"
                                        @selected(old('category_id', $product->category_id) === $category->id)
                                    >
                                        {{ $category->name }}
                                    </option>

                                @endforeach

                            </select>

                        </div>


                        {{-- Marca --}}
                        <div>

                            <label
                                for="brand_id"
                                class="mb-2 block text-sm font-bold text-slate-800"
                            >
                                Marca
                            </label>

                            <select
                                id="brand_id"
                                name="brand_id"
                                class="app-input"
                            >

                                <option value="">
                                    Sin marca
                                </option>

                                @foreach ($brands as $brand)

                                    <option
                                        value="{{ $brand->id }}"
                                        @selected(old('brand_id', $product->brand_id) === $brand->id)
                                    >
                                        {{ $brand->name }}
                                    </option>

                                @endforeach

                            </select>

                        </div>


                        {{-- Unidad --}}
                        <div>

                            <label
                                for="unit_id"
                                class="mb-2 block text-sm font-bold text-slate-800"
                            >
                                Unidad de venta
                                <span class="text-rose-500">*</span>
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
                                        @selected(old('unit_id', $currentUnitId) === $unit->id)
                                    >
                                        {{ $unit->name }} ({{ $unit->code }})
                                    </option>

                                @endforeach

                            </select>

                            <p class="mt-2 text-xs text-slate-400">
                                Ejemplo: pieza, kilogramo, litro, metro.
                            </p>

                        </div>


                        {{-- Tipo --}}
                        <div>

                            <label
                                for="product_type"
                                class="mb-2 block text-sm font-bold text-slate-800"
                            >
                                Tipo de producto
                                <span class="text-rose-500">*</span>
                            </label>

                            <select
                                id="product_type"
                                name="product_type"
                                required
                                class="app-input"
                            >

                                <option
                                    value="simple"
                                    @selected(old('product_type', $product->product_type) === 'simple')
                                >
                                    Pieza individual
                                </option>

                                <option
                                    value="bulk"
                                    @selected(old('product_type', $product->product_type) === 'bulk')
                                >
                                    Granel / fraccionado
                                </option>

                            </select>

                            <p class="mt-2 text-xs text-slate-400">
                                Los productos a granel permiten cantidades decimales.
                            </p>

                        </div>

                    </div>

                </x-ui.card>


                {{-- ================================================= --}}
                {{-- 3. CONFIGURACIÓN COMERCIAL --}}
                {{-- ================================================= --}}

                <x-ui.card
                    padding="p-0"
                    class="overflow-hidden"
                >

                    <div class="border-b border-slate-200 bg-slate-50/70 px-5 py-5 sm:px-7">

                        <p class="text-xs font-black uppercase tracking-[0.14em] text-emerald-700">
                            Venta
                        </p>

                        <h2 class="mt-1 text-lg font-black text-slate-900">
                            Precio e impuestos
                        </h2>

                        <p class="mt-1 text-sm leading-6 text-slate-500">
                            Configura el precio que verá el cliente y la tasa de impuesto aplicable.
                        </p>

                    </div>


                    <div class="grid gap-5 px-5 py-6 sm:grid-cols-2 sm:px-7 sm:py-7">


                        {{-- Precio --}}
                        <div>

                            <label
                                for="price"
                                class="mb-2 block text-sm font-bold text-slate-800"
                            >
                                Precio público
                                <span class="text-rose-500">*</span>
                            </label>

                            <div class="relative">

                                <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-lg font-black text-slate-500">
                                    $
                                </span>

                                <x-ui.input
                                    id="price"
                                    name="price"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    :value="old('price', $currentPrice)"
                                    required
                                    inputmode="decimal"
                                    placeholder="0.00"
                                    class="pl-9 text-lg font-black"
                                    :error="$errors->has('price')"
                                />

                            </div>

                            <p class="mt-2 text-xs text-slate-400">
                                Precio de venta al público en pesos mexicanos.
                            </p>

                            @error('price')
                                <p class="mt-2 text-sm font-semibold text-rose-600">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        {{-- Impuesto --}}
                        <div>

                            <label
                                for="tax_rate_id"
                                class="mb-2 block text-sm font-bold text-slate-800"
                            >
                                Tasa de impuesto
                            </label>

                            <select
                                id="tax_rate_id"
                                name="tax_rate_id"
                                class="app-input"
                            >

                                <option value="">
                                    Sin impuesto aplicable
                                </option>

                                @foreach ($taxRates as $tax)

                                    <option
                                        value="{{ $tax->id }}"
                                        @selected(old('tax_rate_id', $product->tax_rate_id) === $tax->id)
                                    >
                                        {{ $tax->name }}
                                    </option>

                                @endforeach

                            </select>

                            <p class="mt-2 text-xs text-slate-400">
                                Selecciona la tasa configurada para este producto.
                            </p>

                        </div>

                    </div>

                </x-ui.card>


                {{-- ================================================= --}}
                {{-- ACCIONES --}}
                {{-- ================================================= --}}

                <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:items-center sm:justify-between">

                    <a
                        href="{{ route('products.index') }}"
                        class="inline-flex min-h-12 items-center justify-center rounded-xl border border-slate-300 bg-white px-5 text-base font-bold text-slate-700 transition hover:bg-slate-50"
                    >
                        Cancelar
                    </a>


                    <x-ui.button
                        type="submit"
                        variant="primary"
                        size="lg"
                        class="w-full sm:w-auto"
                    >
                        Guardar cambios
                    </x-ui.button>

                </div>

            </div>


            {{-- ===================================================== --}}
            {{-- RESUMEN LATERAL --}}
            {{-- ===================================================== --}}

            <aside class="lg:sticky lg:top-24 lg:self-start">

                <x-ui.card
                    padding="p-0"
                    class="overflow-hidden"
                >

                    <div class="border-b border-slate-200 bg-slate-950 px-5 py-5">

                        <p class="text-xs font-black uppercase tracking-[0.14em] text-emerald-400">
                            Resumen
                        </p>

                        <h2 class="mt-1 text-lg font-black text-white">
                            Producto actual
                        </h2>

                    </div>


                    <div class="space-y-5 p-5">


                        {{-- Nombre --}}
                        <div>

                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                Producto
                            </p>

                            <p class="mt-1 text-base font-black leading-6 text-slate-900">
                                {{ $product->name }}
                            </p>

                        </div>


                        {{-- SKU --}}
                        <div>

                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                SKU
                            </p>

                            <p class="mt-1 font-mono text-sm font-bold text-slate-700">
                                {{ $product->sku ?: 'Sin SKU' }}
                            </p>

                        </div>


                        {{-- Barcode --}}
                        <div>

                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                Código de barras
                            </p>

                            <p class="mt-1 font-mono text-sm font-bold tracking-wide text-slate-700">
                                {{ $currentBarcode ?: 'Sin código' }}
                            </p>

                        </div>


                        {{-- Tipo --}}
                        <div>

                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                Tipo
                            </p>

                            <div class="mt-2">

                                @if ($product->product_type === 'bulk')

                                    <span class="inline-flex rounded-lg border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-black text-amber-700">
                                        A granel
                                    </span>

                                @else

                                    <span class="inline-flex rounded-lg border border-slate-200 bg-slate-100 px-3 py-1.5 text-xs font-black text-slate-600">
                                        Pieza individual
                                    </span>

                                @endif

                            </div>

                        </div>


                        {{-- Estado --}}
                        <div>

                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                Estado actual
                            </p>

                            <div class="mt-2">

                                @if ($product->is_active)

                                    <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-black text-emerald-700">
                                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                        Activo
                                    </span>

                                @else

                                    <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-100 px-3 py-1.5 text-xs font-black text-slate-500">
                                        <span class="h-2 w-2 rounded-full bg-slate-400"></span>
                                        Inactivo
                                    </span>

                                @endif

                            </div>

                        </div>


                        {{-- Precio --}}
                        <div class="border-t border-slate-200 pt-5">

                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                Precio actual
                            </p>

                            @if ($currentPrice !== '' && $currentPrice !== null)

                                <p class="mt-1 text-2xl font-black text-emerald-700">
                                    $ {{ number_format((float) $currentPrice, 2) }}
                                </p>

                                <p class="text-xs font-bold text-slate-400">
                                    MXN
                                </p>

                            @else

                                <p class="mt-1 text-sm font-bold text-rose-600">
                                    Sin precio configurado
                                </p>

                            @endif

                        </div>

                    </div>

                </x-ui.card>


                {{-- Ayuda --}}
                <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-4">

                    <p class="text-sm font-black text-slate-800">
                        Sobre el código de barras
                    </p>

                    <p class="mt-2 text-xs leading-5 text-slate-500">
                        En Ventas, el código de barras es la forma más rápida de localizar un producto.
                        Si el lector está conectado, normalmente basta con escanearlo.
                    </p>

                </div>

            </aside>

        </form>

    </div>

</x-layouts.app>
