<x-layouts.app title="Productos | ABARROTESBASE">

    <div class="space-y-6">

        {{-- ========================================================= --}}
        {{-- HEADER --}}
        {{-- ========================================================= --}}

        <x-layout.page-header
            eyebrow="Inventario"
            title="Productos"
            description="Administra el catálogo, códigos de barras, precios y configuración de venta."
        >
            <x-slot:actions>

                <a
                    href="{{ route('products.create') }}"
                    class="inline-flex min-h-12 items-center justify-center rounded-xl bg-emerald-600 px-5 text-sm font-black text-white shadow-sm transition hover:bg-emerald-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600"
                >
                    <span class="mr-2 text-lg leading-none">+</span>
                    Nuevo producto
                </a>

            </x-slot:actions>
        </x-layout.page-header>


        {{-- ========================================================= --}}
        {{-- FILTROS --}}
        {{-- ========================================================= --}}

        <x-ui.card padding="p-0" class="overflow-hidden">

            <div class="border-b border-slate-200 bg-slate-50/70 px-5 py-4 sm:px-6">

                <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">

                    <div>
                        <h2 class="text-base font-black text-slate-900">
                            Buscar productos
                        </h2>

                        <p class="text-sm text-slate-500">
                            Busca por nombre, SKU o código de barras.
                        </p>
                    </div>

                    @if ($search || $categoryId || $brandId)
                        <a
                            href="{{ route('products.index') }}"
                            class="text-sm font-bold text-rose-600 transition hover:text-rose-700"
                        >
                            Limpiar filtros
                        </a>
                    @endif

                </div>

            </div>


            <form
                method="GET"
                action="{{ route('products.index') }}"
                class="grid gap-4 p-5 sm:p-6 lg:grid-cols-12"
            >

                {{-- BUSCADOR --}}
                <div class="lg:col-span-6">

                    <label
                        for="search"
                        class="mb-2 block text-sm font-bold text-slate-800"
                    >
                        Producto, SKU o código de barras
                    </label>

                    <x-ui.input
                        id="search"
                        name="search"
                        type="search"
                        :value="$search"
                        placeholder="Ej. Coca-Cola, COC-001 o 7501234567890"
                        autocomplete="off"
                    />

                </div>


                {{-- CATEGORÍA --}}
                <div class="lg:col-span-3">

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
                            Todas las categorías
                        </option>

                        @foreach ($categories as $category)

                            <option
                                value="{{ $category->id }}"
                                @selected($categoryId === $category->id)
                            >
                                {{ $category->name }}
                            </option>

                        @endforeach

                    </select>

                </div>


                {{-- MARCA --}}
                <div class="lg:col-span-3">

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
                            Todas las marcas
                        </option>

                        @foreach ($brands as $brand)

                            <option
                                value="{{ $brand->id }}"
                                @selected($brandId === $brand->id)
                            >
                                {{ $brand->name }}
                            </option>

                        @endforeach

                    </select>

                </div>


                {{-- BOTONES --}}
                <div class="flex flex-col gap-3 sm:flex-row sm:justify-end lg:col-span-12">

                    <button
                        type="submit"
                        class="inline-flex min-h-11 items-center justify-center rounded-xl bg-slate-950 px-6 text-sm font-black text-white transition hover:bg-slate-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-950"
                    >
                        Buscar productos
                    </button>

                    @if ($search || $categoryId || $brandId)

                        <a
                            href="{{ route('products.index') }}"
                            class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-5 text-sm font-bold text-slate-700 transition hover:bg-slate-50"
                        >
                            Limpiar
                        </a>

                    @endif

                </div>

            </form>

        </x-ui.card>


        {{-- ========================================================= --}}
        {{-- CATÁLOGO --}}
        {{-- ========================================================= --}}

        <x-ui.card padding="p-0" class="overflow-hidden">

            {{-- CABECERA --}}
            <div class="flex flex-col gap-3 border-b border-slate-200 bg-white px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">

                <div>

                    <p class="text-xs font-black uppercase tracking-[0.14em] text-emerald-700">
                        Catálogo
                    </p>

                    <h2 class="mt-1 text-lg font-black text-slate-900">
                        Productos registrados
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Consulta y administra los productos disponibles.
                    </p>

                </div>


                <div class="shrink-0">

                    <span class="inline-flex min-h-9 items-center rounded-full bg-slate-100 px-3 text-sm font-bold text-slate-600">
                        {{ $products->total() }}
                        {{ $products->total() === 1 ? 'producto' : 'productos' }}
                    </span>

                </div>

            </div>


            {{-- TABLA --}}
            <x-ui.table
                caption="Catálogo de productos"
            >

                <x-slot:head>

                    <tr>

                        <th
                            scope="col"
                            class="whitespace-nowrap px-5 py-3.5 text-xs font-black uppercase tracking-wider text-slate-500 sm:px-6"
                        >
                            Producto
                        </th>

                        <th
                            scope="col"
                            class="whitespace-nowrap px-5 py-3.5 text-xs font-black uppercase tracking-wider text-slate-500"
                        >
                            Código
                        </th>

                        <th
                            scope="col"
                            class="whitespace-nowrap px-5 py-3.5 text-xs font-black uppercase tracking-wider text-slate-500"
                        >
                            Clasificación
                        </th>

                        <th
                            scope="col"
                            class="whitespace-nowrap px-5 py-3.5 text-right text-xs font-black uppercase tracking-wider text-slate-500"
                        >
                            Precio
                        </th>

                        <th
                            scope="col"
                            class="whitespace-nowrap px-5 py-3.5 text-center text-xs font-black uppercase tracking-wider text-slate-500"
                        >
                            Estado
                        </th>

                        <th
                            scope="col"
                            class="whitespace-nowrap px-5 py-3.5 text-right text-xs font-black uppercase tracking-wider text-slate-500"
                        >
                            Acciones
                        </th>

                    </tr>

                </x-slot:head>


                @forelse ($products as $product)

                    @php
                        $stockItem = $product->stockItem;

                        $primaryBarcode =
                            $stockItem?->barcodes->firstWhere('is_primary', true)?->barcode
                            ?? $stockItem?->barcodes->first()?->barcode
                            ?? null;

                        $priceAmount =
                            $stockItem?->prices->first()?->amount;
                    @endphp


                    <tr class="group transition hover:bg-slate-50/70">


                        {{-- ================================================= --}}
                        {{-- PRODUCTO --}}
                        {{-- ================================================= --}}

                        <td class="px-5 py-4 sm:px-6">

                            <div class="max-w-xs">

                                <p class="truncate text-sm font-black text-slate-900">
                                    {{ $product->name }}
                                </p>

                                <p class="mt-1 font-mono text-xs font-semibold text-slate-400">
                                    SKU:
                                    {{ $product->sku ?: 'Sin SKU' }}
                                </p>

                            </div>

                        </td>


                        {{-- ================================================= --}}
                        {{-- CÓDIGO --}}
                        {{-- ================================================= --}}

                        <td class="px-5 py-4">

                            @if ($primaryBarcode)

                                <p class="font-mono text-sm font-bold text-slate-700">
                                    {{ $primaryBarcode }}
                                </p>

                            @else

                                <span class="text-sm font-semibold text-slate-400">
                                    Sin código
                                </span>

                            @endif


                            @if ($product->product_type === 'bulk')

                                <span class="mt-1 inline-flex rounded-md border border-amber-200 bg-amber-50 px-2 py-1 text-xs font-bold text-amber-700">
                                    A granel
                                </span>

                            @else

                                <span class="mt-1 inline-flex rounded-md border border-slate-200 bg-slate-100 px-2 py-1 text-xs font-bold text-slate-600">
                                    Pieza
                                </span>

                            @endif

                        </td>


                        {{-- ================================================= --}}
                        {{-- CLASIFICACIÓN --}}
                        {{-- ================================================= --}}

                        <td class="px-5 py-4">

                            <div class="space-y-1">

                                <span class="inline-flex max-w-[180px] truncate rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-700">
                                    {{ $product->category?->name ?? 'Sin categoría' }}
                                </span>

                                @if ($product->brand)

                                    <p class="text-xs font-semibold text-slate-400">
                                        {{ $product->brand->name }}
                                    </p>

                                @endif

                            </div>

                        </td>


                        {{-- ================================================= --}}
                        {{-- PRECIO --}}
                        {{-- ================================================= --}}

                        <td class="px-5 py-4 text-right">

                            @if ($priceAmount !== null)

                                <p class="whitespace-nowrap text-base font-black text-emerald-700">
                                    $ {{ number_format((float) $priceAmount, 2) }}
                                </p>

                                <p class="text-xs font-bold text-slate-400">
                                    MXN
                                </p>

                            @else

                                <span class="text-sm font-semibold text-rose-600">
                                    Sin precio
                                </span>

                            @endif

                        </td>


                        {{-- ================================================= --}}
                        {{-- ESTADO --}}
                        {{-- ================================================= --}}

                        <td class="px-5 py-4 text-center">

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

                        </td>


                        {{-- ================================================= --}}
                        {{-- ACCIONES --}}
                        {{-- ================================================= --}}

                        <td class="px-5 py-4 text-right sm:px-6">

                            <div class="flex items-center justify-end gap-2">

                                <a
                                    href="{{ route('products.edit', $product) }}"
                                    class="inline-flex min-h-10 items-center justify-center rounded-lg border border-slate-300 bg-white px-3 text-sm font-bold text-slate-700 transition hover:border-slate-400 hover:bg-slate-50 hover:text-slate-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600"
                                >
                                    Editar
                                </a>

                            </div>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td
                            colspan="6"
                            class="px-6 py-16 text-center"
                        >

                            <div class="mx-auto max-w-md">

                                <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-slate-100 text-2xl font-black text-slate-400">
                                    +
                                </div>

                                <h3 class="mt-4 text-base font-black text-slate-900">
                                    No hay productos
                                </h3>

                                <p class="mt-1 text-sm leading-6 text-slate-500">
                                    No encontramos productos que coincidan con los filtros actuales.
                                </p>

                                @if ($search || $categoryId || $brandId)

                                    <a
                                        href="{{ route('products.index') }}"
                                        class="mt-4 inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-5 text-sm font-bold text-slate-700 transition hover:bg-slate-50"
                                    >
                                        Limpiar filtros
                                    </a>

                                @else

                                    <a
                                        href="{{ route('products.create') }}"
                                        class="mt-4 inline-flex min-h-11 items-center justify-center rounded-xl bg-emerald-600 px-5 text-sm font-bold text-white transition hover:bg-emerald-700"
                                    >
                                        Registrar primer producto
                                    </a>

                                @endif

                            </div>

                        </td>

                    </tr>

                @endforelse

            </x-ui.table>


            {{-- ========================================================= --}}
            {{-- PAGINACIÓN --}}
            {{-- ========================================================= --}}

            @if ($products->hasPages())

                <div class="border-t border-slate-200 bg-slate-50/60 px-5 py-4 sm:px-6">

                    {{ $products->links() }}

                </div>

            @endif

        </x-ui.card>

    </div>

</x-layouts.app>
