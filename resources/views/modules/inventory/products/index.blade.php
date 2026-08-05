<x-layouts.app title="Catálogo de Productos - ABARROTESBASE">
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Catálogo de Productos</h1>
                <p class="mt-1 text-sm text-slate-500">
                    Administra los productos registrados, sus precios, categorías y códigos de barra.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('products.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-emerald-500 px-4 py-2.5 text-sm font-bold text-slate-950 shadow-sm transition hover:bg-emerald-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Nuevo producto
                </a>
            </div>
        </div>

        {{-- Filtros y Buscador --}}
        <x-ui.card padding="p-4" class="shadow-sm border-slate-200 bg-white">
            <form method="GET" action="{{ route('products.index') }}" class="grid gap-3 sm:grid-cols-12">
                <div class="sm:col-span-6 md:col-span-7">
                    <label for="search" class="sr-only">Buscar</label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                        </div>
                        <input
                            type="text"
                            name="search"
                            id="search"
                            value="{{ $search }}"
                            placeholder="Buscar por nombre, SKU o código de barras..."
                            class="app-input pl-10">
                    </div>
                </div>

                <div class="sm:col-span-4 md:col-span-3">
                    <select
                        name="category_id"
                        class="app-input">
                        <option value="">Todas las categorías</option>
                        @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" @selected($categoryId===$cat->id)>
                            {{ $cat->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2 sm:col-span-2">
                    <button type="submit" class="w-full rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white transition hover:bg-slate-800">
                        Filtrar
                    </button>
                    @if($search || $categoryId)
                    <a href="{{ route('products.index') }}" class="rounded-xl border border-slate-200 p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-800" title="Limpiar filtros">
                        ✕
                    </a>
                    @endif
                </div>
            </form>
        </x-ui.card>

        {{-- Tabla de Productos --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-700">
                    <thead class="border-b border-slate-200 bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th scope="col" class="px-6 py-3.5">Producto</th>
                            <th scope="col" class="px-6 py-3.5">Categoría / Marca</th>
                            <th scope="col" class="px-6 py-3.5">Código de barras</th>
                            <th scope="col" class="px-6 py-3.5 text-right">Precio venta</th>
                            <th scope="col" class="px-6 py-3.5 text-center">Estado</th>
                            <th scope="col" class="px-6 py-3.5 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($products as $product)
                        @php
                        $primaryBarcode = $product->stockItem?->barcodes->firstWhere('is_primary', true)?->barcode
                        ?? $product->stockItem?->barcodes->first()?->barcode
                        ?? 'Sin código';
                        $priceAmount = $product->stockItem?->prices->first()?->amount;
                        @endphp
                        <tr class="transition hover:bg-slate-50/80">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900">{{ $product->name }}</div>
                                <div class="text-xs text-slate-400">SKU: {{ $product->sku ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700">
                                    {{ $product->category?->name ?? 'Sin categoría' }}
                                </span>
                                @if ($product->brand)
                                <span class="block text-xs font-medium text-slate-400 mt-0.5">{{ $product->brand->name }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-mono text-xs font-bold text-slate-600">
                                {{ $primaryBarcode }}
                            </td>
                            <td class="px-6 py-4 text-right font-black text-emerald-600">
                                @if ($priceAmount !== null)
                                ${{ number_format((float)$priceAmount, 2) }} <span class="text-[10px] font-bold text-slate-400">MXN</span>
                                @else
                                <span class="text-xs font-normal text-slate-400">Sin precio</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if ($product->is_active)
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 border border-emerald-200">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                    Activo
                                </span>
                                @else
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-500 border border-slate-200">
                                    <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                    Inactivo
                                </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('products.edit', $product) }}" class="inline-flex rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700" title="Editar producto">
                                   Editar ✏️
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400 font-medium">
                                No se encontraron productos registrados.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($products->hasPages())
            <div class="border-t border-slate-100 px-6 py-4 bg-slate-50/50">
                {{ $products->links() }}
            </div>
            @endif
        </div>
    </div>
</x-layouts.app>
