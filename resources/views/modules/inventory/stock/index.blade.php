<x-layouts.app title="Existencias de Inventario - SUMA">
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Existencias en Sucursal</h1>
                <p class="mt-1 text-sm text-slate-500">
                    Consulta el stock físico disponible, reservado y niveles de reorden por artículo.
                </p>
            </div>
        </div>

        {{-- Alertas Flash --}}
        @if (session('status'))
            <div class="rounded-2xl bg-emerald-50 p-4 text-emerald-800 border border-emerald-200 font-semibold text-sm">
                {{ session('status') }}
            </div>
        @endif

        {{-- Filtros y Buscador --}}
        <x-ui.card padding="p-4" class="shadow-sm border-slate-200 bg-white">
            <form method="GET" action="{{ route('stock.index') }}" class="grid gap-3 sm:grid-cols-12">
                {{-- Buscador General --}}
                <div class="sm:col-span-12 lg:col-span-6">
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
                            placeholder="Buscar por artículo, SKU o código de barras..."
                            class="app-input pl-10 text-xs">
                    </div>
                </div>

                {{-- Filtro Categoría --}}
                <div class="sm:col-span-6 lg:col-span-6">
                    <select name="category_id" class="app-input text-xs" onchange="this.form.submit()">
                        <option value="">Todas las categorías</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" @selected($categoryId === $cat->id)>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @if($search || $categoryId)
                    <div class="sm:col-span-12 flex items-center justify-end">
                        <a href="{{ route('stock.index') }}" class="inline-flex items-center gap-1 text-xs text-rose-600 hover:underline font-semibold">
                            ✕ Limpiar filtros
                        </a>
                    </div>
                @endif
            </form>
        </x-ui.card>

        {{-- Tabla de Existencias --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-700">
                    <thead class="border-b border-slate-200 bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th scope="col" class="px-6 py-3.5">Artículo / SKU</th>
                            <th scope="col" class="px-6 py-3.5">Categoría & Marca</th>
                            <th scope="col" class="px-6 py-3.5 text-center">Físico</th>
                            <th scope="col" class="px-6 py-3.5 text-center">Reservado</th>
                            <th scope="col" class="px-6 py-3.5 text-center">Disponible</th>
                            <th scope="col" class="px-6 py-3.5 text-right">Costo Promedio</th>
                            <th scope="col" class="px-6 py-3.5 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse ($items as $item)
                            @php
                                $balance = $item->inventoryBalance;
                                $reorder = $item->reorderLevel;
                                $unit = $item->inventoryUnit?->code ?? 'PZA';

                                $onHand = (float) ($balance?->on_hand_quantity ?? 0);
                                $reserved = (float) ($balance?->reserved_quantity ?? 0);
                                $available = (float) ($balance?->available_quantity ?? $onHand - $reserved);
                                $minQty = (float) ($reorder?->minimum_quantity ?? 0);
                                $cost = (float) ($balance?->weighted_average_cost ?? 0);
                            @endphp
                            <tr class="transition hover:bg-slate-50/80">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900">{{ $item->product?->name }}</div>
                                    <div class="text-xs text-slate-400 font-mono">SKU: {{ $item->product?->sku ?? 'N/A' }}</div>
                                </td>

                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700">
                                        {{ $item->product?->category?->name ?? 'Sin categoría' }}
                                    </span>
                                    @if ($item->product?->brand)
                                        <span class="block text-xs font-medium text-slate-400 mt-0.5">{{ $item->product->brand->name }}</span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 text-center font-bold text-slate-800">
                                    {{ number_format($onHand, 2) }} <span class="text-[10px] text-slate-400 font-normal">{{ $unit }}</span>
                                </td>

                                <td class="px-6 py-4 text-center text-amber-600 font-semibold">
                                    {{ number_format($reserved, 2) }} <span class="text-[10px] text-amber-500/70 font-normal">{{ $unit }}</span>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    @if($available <= 0)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-500 border border-slate-200">
                                            <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                            Agotado
                                        </span>
                                    @elseif($minQty > 0 && $available <= $minQty)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-bold text-amber-700 border border-amber-200" title="Bajo el mínimo de {{ $minQty }}">
                                            ⚠️ {{ number_format($available, 2) }} {{ $unit }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 border border-emerald-200">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            {{ number_format($available, 2) }} {{ $unit }}
                                        </span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 text-right font-mono text-xs font-bold text-slate-700">
                                    ${{ number_format($cost, 2) }} <span class="text-[10px] font-normal text-slate-400">MXN</span>
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <button
                                        type="button"
                                        onclick="openAdjustModal('{{ $item->id }}', '{{ addslashes($item->product?->name ?? '') }}')"
                                        class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition"
                                        title="Ajustar stock">
                                        ⚡ Ajustar
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-slate-400 font-medium">
                                    No se encontraron existencias registradas o que coincidan con los filtros.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($items->hasPages())
                <div class="border-t border-slate-100 px-6 py-4 bg-slate-50/50">
                    {{ $items->links() }}
                </div>
            @endif
        </div>

        {{-- Modal Ajuste de Stock --}}
        <div id="adjustModal" class="fixed inset-0 z-50 hidden items-center justify-center overflow-y-auto bg-slate-950/60 p-4 backdrop-blur-sm">
            <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl space-y-4 border border-slate-100">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="font-bold text-slate-900 text-base">Ajuste de Existencias</h3>
                    <button type="button" onclick="closeAdjustModal()" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition">✕</button>
                </div>

                <form method="POST" action="{{ route('stock.adjust') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="stock_item_id" id="modal_stock_item_id">

                    <div class="rounded-xl bg-slate-50 p-3 border border-slate-100">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Artículo seleccionado</p>
                        <p class="font-bold text-slate-900 text-sm mt-0.5" id="modal_item_name"></p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tipo de Movimiento</label>
                        <select name="movement_type" class="app-input text-xs w-full" required>
                            <option value="initial_load">Carga Inicial de Stock</option>
                            <option value="adjustment_in">Entrada / Ajuste (+)</option>
                            <option value="adjustment_out">Salida / Mermas (-)</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Cantidad</label>
                            <input type="number" step="0.000001" name="quantity" class="app-input text-xs w-full" placeholder="0.00" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Costo Unitario ($)</label>
                            <input type="number" step="0.01" name="unit_cost" placeholder="0.00" class="app-input text-xs w-full">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Notas / Motivo</label>
                        <textarea name="notes" rows="2" class="app-input text-xs w-full resize-none" placeholder="Ej. Inventario inicial de apertura"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                        <button type="button" onclick="closeAdjustModal()" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 transition">
                            Cancelar
                        </button>
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-emerald-500 px-4 py-2 text-xs font-bold text-slate-950 shadow-sm hover:bg-emerald-400 transition">
                            Guardar Ajuste
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Script Vanilla JavaScript Nativo --}}
    <script>
        function openAdjustModal(itemId, itemName) {
            document.getElementById('modal_stock_item_id').value = itemId;
            document.getElementById('modal_item_name').innerText = itemName;

            const modal = document.getElementById('adjustModal');
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
        }

        function closeAdjustModal() {
            const modal = document.getElementById('adjustModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        }
    </script>
</x-layouts.app>
