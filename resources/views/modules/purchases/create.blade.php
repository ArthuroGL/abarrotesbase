<x-layouts.app title="Nueva Compra - SUMA">
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Nueva Compra</h1>
                <p class="mt-1 text-sm text-slate-500">Captura la orden de compra emitida a tu proveedor.</p>
            </div>
            <a href="{{ route('purchases.index') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100">
                Cancelar
            </a>
        </div>

        @if ($errors->any())
        <div class="rounded-xl bg-rose-50 p-4 border border-rose-200 text-xs font-bold text-rose-800 space-y-1">
            <p class="font-extrabold">Por favor corrige los siguientes errores:</p>
            <ul class="list-disc list-inside font-normal">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('purchases.store') }}" class="space-y-6">
            @csrf

            {{-- Cabecera --}}
            <x-ui.card padding="p-5" class="bg-white border-slate-200 shadow-sm space-y-4">
                <div class="grid gap-4 md:grid-cols-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Proveedor *</label>
                        <select name="supplier_id" class="app-input text-xs w-full" required>
                            <option value="">Selecciona un proveedor</option>
                            @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->business_name }} ({{ $supplier->rfc ?? 'Sin RFC' }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Folio / Factura Proveedor</label>
                        <input type="text" name="supplier_reference" value="{{ old('supplier_reference') }}" placeholder="Ej. FAC-99823" class="app-input text-xs w-full">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Notas / Observaciones</label>
                        <input type="text" name="notes" value="{{ old('notes') }}" placeholder="Ej. Entrega programada por la tarde" class="app-input text-xs w-full">
                    </div>
                </div>
            </x-ui.card>

            {{-- Partidas --}}
            <x-ui.card padding="p-5" class="bg-white border-slate-200 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="font-bold text-slate-900 text-sm">Partidas de la Compra</h3>
                    <button type="button" onclick="addPurchaseRow()" class="inline-flex items-center gap-1 rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-bold text-white hover:bg-slate-800 transition cursor-pointer">
                        + Agregar Artículo
                    </button>
                </div>

                {{-- Contenedor donde JS agregará las filas --}}
                <div id="items-container" class="space-y-3"></div>

                {{-- Totales --}}
                <div class="flex justify-end pt-4 border-t border-slate-100">
                    <div class="w-full max-w-xs space-y-1 text-right">
                        <div class="flex justify-between text-xs text-slate-500">
                            <span>Total estimado:</span>
                            <span id="grant-total-display" class="font-bold text-slate-900 text-base">$0.00 MXN</span>
                        </div>
                    </div>
                </div>
            </x-ui.card>

            <div class="flex items-center justify-end gap-3">
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-emerald-500 px-6 py-2.5 text-sm font-bold text-slate-950 shadow-sm hover:bg-emerald-400 transition cursor-pointer">
                    Guardar Orden de Compra
                </button>
            </div>
        </form>
    </div>

    {{-- Plantilla HTML Oculta para crear filas --}}
    <template id="row-template">
        <div class="item-row grid gap-3 rounded-xl border border-slate-200 p-3 sm:grid-cols-12 items-center bg-slate-50/50">
            {{-- Artículo --}}
            <div class="sm:col-span-4">
                <label class="block text-[10px] font-bold text-slate-500 uppercase">Artículo *</label>
                <select name="items[INDEX][stock_item_id]" class="app-input text-xs w-full mt-1" required>
                    <option value="">Selecciona artículo...</option>
                    @if ($stockItems->isNotEmpty())
                    @foreach ($stockItems as $si)
                    <option value="{{ $si->id }}">
                        {{ $si->product?->name ?? 'Producto ID: ' . $si->product_id }}
                    </option>
                    @endforeach
                    @else
                    @foreach ($products as $p)
                    <option value="{{ $p->id }}">
                        {{ $p->name }} (SKU: {{ $p->sku ?? 'S/N' }})
                    </option>
                    @endforeach
                    @endif
                </select>
            </div>

            {{-- Unidad --}}
            <div class="sm:col-span-2">
                <label class="block text-[10px] font-bold text-slate-500 uppercase">Unidad *</label>
                <select name="items[INDEX][product_unit_id]" class="app-input text-xs w-full mt-1" required>
                    <option value="">Selecciona unidad...</option>
                    @foreach ($productUnits as $u)
                    <option value="{{ $u->id }}">
                        {{ $u->unit?->name ?? $u->name ?? 'Unidad' }}
                    </option>
                    @endforeach
                </select>
            </div>
            {{-- Cantidad --}}
            <div class="sm:col-span-2">
                <label class="block text-[10px] font-bold text-slate-500 uppercase">Cantidad *</label>
                <input type="number" step="0.01" min="0.01" value="1" name="items[INDEX][quantity]" oninput="calculateRowTotal(this)" class="qty-input app-input text-xs w-full mt-1" required>
            </div>

            {{-- Costo Unitario --}}
            <div class="sm:col-span-2">
                <label class="block text-[10px] font-bold text-slate-500 uppercase">Costo Unit. ($) *</label>
                <input type="number" step="0.01" min="0" value="0" name="items[INDEX][unit_cost]" oninput="calculateRowTotal(this)" class="cost-input app-input text-xs w-full mt-1" required>
            </div>

            {{-- Subtotal --}}
            <div class="sm:col-span-1 text-right">
                <label class="block text-[10px] font-bold text-slate-500 uppercase">Subtotal</label>
                <p class="row-subtotal mt-2 font-bold text-xs text-slate-800">$0.00 MXN</p>
            </div>

            {{-- Eliminar --}}
            <div class="sm:col-span-1 text-right">
                <button type="button" onclick="removePurchaseRow(this)" class="mt-2 text-rose-600 hover:text-rose-800 text-xs font-bold" title="Eliminar partida">✕</button>
            </div>
        </div>
    </template>

    <script>
        let rowCount = 0;

        function addPurchaseRow() {
            const container = document.getElementById('items-container');
            const template = document.getElementById('row-template').innerHTML;

            // Reemplazar la palabra INDEX por el número actual de fila
            const newRowHtml = template.replace(/INDEX/g, rowCount);

            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = newRowHtml.trim();
            const rowElement = tempDiv.firstChild;

            container.appendChild(rowElement);
            rowCount++;
            updateGrandTotal();
        }

        function removePurchaseRow(button) {
            const container = document.getElementById('items-container');
            if (container.querySelectorAll('.item-row').length > 1) {
                button.closest('.item-row').remove();
                updateGrandTotal();
            } else {
                alert('Debes mantener al menos un artículo en la compra.');
            }
        }

        function calculateRowTotal(input) {
            const row = input.closest('.item-row');
            const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
            const cost = parseFloat(row.querySelector('.cost-input').value) || 0;
            const subtotal = qty * cost;

            row.querySelector('.row-subtotal').innerText = '$' + subtotal.toFixed(2) + ' MXN';
            updateGrandTotal();
        }

        function updateGrandTotal() {
            let total = 0;
            document.querySelectorAll('.item-row').forEach(row => {
                const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
                const cost = parseFloat(row.querySelector('.cost-input').value) || 0;
                total += (qty * cost);
            });

            document.getElementById('grant-total-display').innerText = '$' + total.toFixed(2) + ' MXN';
        }

        // Agregar la primera fila automáticamente al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            addPurchaseRow();
        });
    </script>
</x-layouts.app>
