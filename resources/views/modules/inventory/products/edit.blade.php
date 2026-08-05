<x-layouts.app title="Editar Producto - ABARROTESBASE">
    <div class="mx-auto max-w-4xl space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-slate-200 pb-5">
            <div>
                <h1 class="text-2xl font-black text-slate-900">Editar Producto</h1>
                <p class="mt-1 text-xs font-semibold text-slate-500">
                    Modifica la información general, precios y parámetros de venta de <span class="text-emerald-600 font-bold">{{ $product->name }}</span>.
                </p>
            </div>
            <a href="{{ route('products.index') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">
                ← Volver al catálogo
            </a>
        </div>

        @php
            $currentBarcode = $product->stockItem?->barcodes->firstWhere('is_primary', true)?->barcode
                ?? $product->stockItem?->barcodes->first()?->barcode
                ?? '';
            $currentPrice = $product->stockItem?->prices->first()?->amount ?? '';
            $currentUnitId = $product->stockItem?->inventory_unit_id ?? '';
        @endphp

        <form method="POST" action="{{ route('products.update', $product) }}" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Bloque 1: Identificación --}}
            <x-ui.card padding="p-6 sm:p-8" class="shadow-sm border-slate-200 bg-white space-y-5">
                <div class="flex items-center justify-between">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-emerald-700">1. Identificación del Producto</h2>

                    {{-- Switch / Checkbox de Estado --}}
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            @checked(old('is_active', $product->is_active))
                            class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                        >
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-700">Producto Activo</span>
                    </label>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    {{-- Nombre --}}
                    <div class="sm:col-span-2">
                        <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Nombre del producto *
                        </label>
                        <x-ui.input id="name" name="name" :value="old('name', $product->name)" required placeholder="Ej. Galletas Chokis 100g" :error="$errors->has('name')" />
                        @error('name')<p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    {{-- Código de Barras --}}
                    <div>
                        <label for="barcode" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Código de Barras *
                        </label>
                        <x-ui.input id="barcode" name="barcode" :value="old('barcode', $currentBarcode)" required placeholder="7501000000000" :error="$errors->has('barcode')" />
                        @error('barcode')<p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    {{-- SKU --}}
                    <div>
                        <label for="sku" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Código Interno / SKU
                        </label>
                        <x-ui.input id="sku" name="sku" :value="old('sku', $product->sku)" placeholder="Ej. GAL-001" :error="$errors->has('sku')" />
                        @error('sku')<p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </x-ui.card>

            {{-- Bloque 2: Clasificación y Precios --}}
            <x-ui.card padding="p-6 sm:p-8" class="shadow-sm border-slate-200 bg-white space-y-5">
                <h2 class="text-xs font-bold uppercase tracking-wider text-emerald-700">2. Clasificación & Venta</h2>

                <div class="grid gap-5 sm:grid-cols-2">
                    {{-- Categoría --}}
                    <div>
                        <label for="category_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Categoría
                        </label>
                        <select id="category_id" name="category_id" class="app-input">
                            <option value="">-- Sin categoría --</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" @selected(old('category_id', $product->category_id) === $cat->id)>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Marca --}}
                    <div>
                        <label for="brand_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Marca
                        </label>
                        <select id="brand_id" name="brand_id" class="app-input">
                            <option value="">-- Sin marca --</option>
                            @foreach ($brands as $b)
                                <option value="{{ $b->id }}" @selected(old('brand_id', $product->brand_id) === $b->id)>{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Unidad de medida --}}
                    <div>
                        <label for="unit_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Unidad de venta *
                        </label>
                        <select id="unit_id" name="unit_id" required class="app-input">
                            @foreach ($units as $u)
                                <option value="{{ $u->id }}" @selected(old('unit_id', $currentUnitId) === $u->id)>{{ $u->name }} ({{ $u->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Tipo de Producto --}}
                    <div>
                        <label for="product_type" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Tipo de Producto *
                        </label>
                        <select id="product_type" name="product_type" required class="app-input">
                            <option value="simple" @selected(old('product_type', $product->product_type) === 'simple')>Pieza Individual (Entero)</option>
                            <option value="bulk" @selected(old('product_type', $product->product_type) === 'bulk')>Granel / Fraccionado (Permite decimales)</option>
                        </select>
                    </div>

                    {{-- Precio Venta --}}
                    <div>
                        <label for="price" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Precio Público (MXN) *
                        </label>
                        <x-ui.input id="price" type="number" step="0.01" name="price" :value="old('price', $currentPrice)" required placeholder="0.00" :error="$errors->has('price')" />
                        @error('price')<p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    {{-- Impuesto --}}
                    <div>
                        <label for="tax_rate_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Tasa de Impuesto
                        </label>
                        <select id="tax_rate_id" name="tax_rate_id" class="app-input">
                            <option value="">-- Sin impuesto aplicable --</option>
                            @foreach ($taxRates as $tax)
                                <option value="{{ $tax->id }}" @selected(old('tax_rate_id', $product->tax_rate_id) === $tax->id)>{{ $tax->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </x-ui.card>

            {{-- Submit --}}
            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('products.index') }}" class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-xs font-bold text-slate-700 transition hover:bg-slate-50">
                    Cancelar
                </a>
                <x-ui.button type="submit" variant="primary" class="px-6 py-2.5">
                    Guardar Cambios
                </x-ui.button>
            </div>
        </form>
    </div>
</x-layouts.app>
