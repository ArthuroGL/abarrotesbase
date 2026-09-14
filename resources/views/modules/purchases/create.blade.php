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

                        <select
                            id="supplier_id"
                            name="supplier_id"
                            class="app-input"
                            required>

                            <option value="">
                                Selecciona un proveedor
                            </option>

                            @foreach ($suppliers as $supplier)

                            <option
                                value="{{ $supplier->id }}"
                                @selected(old('supplier_id')==$supplier->id)>

                                {{ $supplier->business_name }}

                                @if ($supplier->rfc)
                                · {{ $supplier->rfc }}
                                @endif

                            </option>

                            @endforeach

                        </select>

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
            <x-ui.card padding="p-0" class="overflow-hidden">

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
                            $0.00
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


    @php
    $purchaseUnitsJson = $productUnitsByStockItem->map(
        fn ($units) => $units->map(
            fn ($productUnit) => [
                'id' => $productUnit->id,
                'name' => $productUnit->unit?->name ?? 'Unidad',
                'conversion_factor' => (float) $productUnit->conversion_factor,
                'allow_decimal' => (bool) $productUnit->allow_decimal,
            ]
        )->values()
    );
@endphp

<script>
    const purchaseUnitsByStockItem = @json($purchaseUnitsJson);
</script>

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

                    <select
                        name="items[INDEX][stock_item_id]"
                        class="app-input stock-item-select"
                        required
                        onchange="updatePurchaseUnit(this)">

                        <option value="">
                            Selecciona artículo
                        </option>

                        @foreach ($stockItems as $si)

                        <option value="{{ $si->id }}">
                            {{ $si->product?->name ?? 'Producto' }}

                            @if ($si->product?->sku)
                            · {{ $si->product->sku }}
                            @endif
                        </option>

                        @endforeach

                    </select>

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
                            $0.00
                        </p>

                    </div>

                </div>

            </div>

        </div>

    </template>


    <script>
        let purchaseRowCount = 0;

        function updatePurchaseUnit(stockItemSelect) {

            const row = stockItemSelect.closest('.item-row');

            if (!row) {
                return;
            }

            const unitSelect = row.querySelector('.product-unit-select');

            if (!unitSelect) {
                return;
            }

            const stockItemId = stockItemSelect.value;

            unitSelect.innerHTML = '';

            unitSelect.disabled = true;

            if (!stockItemId) {

                unitSelect.innerHTML = `
            <option value="">
                Primero selecciona un artículo
            </option>
        `;

                return;
            }

            const units = purchaseUnitsByStockItem[stockItemId] ?? [];

            if (units.length === 0) {

                unitSelect.innerHTML = `
            <option value="">
                Sin unidad de compra configurada
            </option>
        `;

                return;
            }

            units.forEach(unit => {

                const option = document.createElement('option');

                option.value = unit.id;
                option.textContent = unit.name;

                option.dataset.allowDecimal =
                    unit.allow_decimal ? '1' : '0';

                option.dataset.conversionFactor =
                    unit.conversion_factor;

                unitSelect.appendChild(option);

            });

            unitSelect.disabled = false;

            /*
             * Si solo existe una unidad para el artículo,
             * la seleccionamos automáticamente.
             */
            if (units.length === 1) {

                unitSelect.value = units[0].id;

            }

        }

        function addPurchaseRow() {

            const container = document.getElementById('items-container');
            const template = document.getElementById('purchase-row-template');

            if (!container || !template) {
                return;
            }

            const html = template.innerHTML
                .replace(/INDEX/g, purchaseRowCount)
                .replace(
                    '<span class="row-number">1</span>',
                    `<span class="row-number">${purchaseRowCount + 1}</span>`
                );

            const wrapper = document.createElement('div');

            wrapper.innerHTML = html.trim();

            const row = wrapper.firstElementChild;

            container.appendChild(row);

            purchaseRowCount++;



            updateEmptyMessage();
            updateGrandTotal();
        }


        function removePurchaseRow(button) {

            const row = button.closest('.item-row');

            if (!row) {
                return;
            }

            row.remove();

            updateRowNumbers();
            updateEmptyMessage();
            updateGrandTotal();
        }


        function updateRowNumbers() {

            document
                .querySelectorAll('.item-row')
                .forEach((row, index) => {

                    const number = row.querySelector('.row-number');

                    if (number) {
                        number.textContent = index + 1;
                    }

                });
        }


        function updateEmptyMessage() {

            const container = document.getElementById('items-container');
            const emptyMessage = document.getElementById('empty-items-message');

            if (!container || !emptyMessage) {
                return;
            }

            const hasItems = container.querySelector('.item-row');

            emptyMessage.classList.toggle('hidden', Boolean(hasItems));
        }


        function calculateRowTotal(input) {

            const row = input.closest('.item-row');

            if (!row) {
                return;
            }

            const quantity =
                parseFloat(row.querySelector('.qty-input')?.value) || 0;

            const cost =
                parseFloat(row.querySelector('.cost-input')?.value) || 0;

            const subtotal = quantity * cost;

            const subtotalElement =
                row.querySelector('.row-subtotal');

            if (subtotalElement) {
                subtotalElement.textContent =
                    '$' + subtotal.toFixed(2);
            }

            updateGrandTotal();
        }


        function updateGrandTotal() {

            let total = 0;

            document
                .querySelectorAll('.item-row')
                .forEach(row => {

                    const quantity =
                        parseFloat(row.querySelector('.qty-input')?.value) || 0;

                    const cost =
                        parseFloat(row.querySelector('.cost-input')?.value) || 0;

                    total += quantity * cost;

                });

            const totalElement =
                document.getElementById('grand-total-display');

            if (totalElement) {

                totalElement.innerHTML =
                    '$' + total.toFixed(2) +
                    ' <span class="text-sm font-bold text-slate-400">MXN</span>';
            }
        }


        document.addEventListener('DOMContentLoaded', function() {

            addPurchaseRow();

        });
    </script>

</x-layouts.app>
