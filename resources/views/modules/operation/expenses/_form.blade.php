<div class="grid gap-5 md:grid-cols-2">

    {{-- Tipo --}}
    <div>

        <label
            for="expense_type"
            class="mb-1.5 block text-xs font-black uppercase tracking-wider text-slate-600"
        >
            Tipo
        </label>

        <select
            id="expense_type"
            name="expense_type"
            required
            class="app-input"
        >

            @foreach ($types as $key => $label)

                <option
                    value="{{ $key }}"
                    @selected(old('expense_type', $expense->expense_type ?? 'operational') === $key)
                >
                    {{ $label }}
                </option>

            @endforeach

        </select>

    </div>


    {{-- Categoría --}}
    <div>

        <label
            for="expense_category_id"
            class="mb-1.5 block text-xs font-black uppercase tracking-wider text-slate-600"
        >
            Categoría
        </label>

        <select
            id="expense_category_id"
            name="expense_category_id"
            required
            class="app-input"
        >

            <option value="">
                Selecciona una categoría
            </option>

            @foreach ($categories as $category)

                <option
                    value="{{ $category->id }}"
                    @selected(old('expense_category_id', $expense->expense_category_id ?? '') === $category->id)
                >
                    {{ $category->name }}

                    @if ($category->requires_approval)
                        · requiere aprobación
                    @endif
                </option>

            @endforeach

        </select>

    </div>


    {{-- Importe --}}
    <div>

        <label
            for="expense_amount"
            class="mb-1.5 block text-xs font-black uppercase tracking-wider text-slate-600"
        >
            Importe
        </label>

        <x-ui.input
            id="expense_amount"
            type="number"
            name="amount"
            min="0.01"
            step="0.01"
            inputmode="decimal"
            value="{{ old('amount', $expense->amount ?? '') }}"
            placeholder="0.00"
            required
        />

        <p class="mt-1.5 text-xs text-slate-400">
            Captura el importe total del gasto.
        </p>

    </div>


    {{-- Fecha --}}
    <div>

        <label
            for="expense_date"
            class="mb-1.5 block text-xs font-black uppercase tracking-wider text-slate-600"
        >
            Fecha
        </label>

        <x-ui.input
            id="expense_date"
            type="date"
            name="expense_date"
            value="{{ old(
                'expense_date',
                isset($expense)
                    ? $expense->expense_date->format('Y-m-d')
                    : now()->format('Y-m-d')
            ) }}"
            required
        />

    </div>


    {{-- Proveedor --}}
    <div>

        <label
            for="supplier_id"
            class="mb-1.5 block text-xs font-black uppercase tracking-wider text-slate-600"
        >
            Proveedor
        </label>

        <select
            id="supplier_id"
            name="supplier_id"
            class="app-input"
        >

            <option value="">
                Sin proveedor registrado
            </option>

            @foreach ($suppliers as $supplier)

                <option
                    value="{{ $supplier->id }}"
                    @selected(old('supplier_id', $expense->supplier_id ?? '') === $supplier->id)
                >
                    {{ $supplier->business_name }}
                </option>

            @endforeach

        </select>

    </div>


    {{-- Origen --}}
    <div>

        <label
            for="source_name"
            class="mb-1.5 block text-xs font-black uppercase tracking-wider text-slate-600"
        >
            Origen / lugar
        </label>

        <x-ui.input
            id="source_name"
            name="source_name"
            maxlength="150"
            value="{{ old('source_name', $expense->source_name ?? '') }}"
            placeholder="Central de Abastos, Zorro Abarrotero..."
        />

    </div>


    {{-- Beneficiario --}}
    <div>

        <label
            for="beneficiary"
            class="mb-1.5 block text-xs font-black uppercase tracking-wider text-slate-600"
        >
            Beneficiario
        </label>

        <x-ui.input
            id="beneficiary"
            name="beneficiary"
            maxlength="255"
            value="{{ old('beneficiary', $expense->beneficiary ?? '') }}"
            placeholder="Persona o negocio que recibe el pago"
        />

    </div>


    {{-- Referencia --}}
    <div>

        <label
            for="reference"
            class="mb-1.5 block text-xs font-black uppercase tracking-wider text-slate-600"
        >
            Referencia / ticket
        </label>

        <x-ui.input
            id="reference"
            name="reference"
            maxlength="120"
            value="{{ old('reference', $expense->reference ?? '') }}"
            placeholder="Folio, ticket o referencia..."
        />

    </div>


    {{-- Descripción --}}
    <div class="md:col-span-2">

        <label
            for="description"
            class="mb-1.5 block text-xs font-black uppercase tracking-wider text-slate-600"
        >
            Descripción
        </label>

        <textarea
            id="description"
            name="description"
            rows="4"
            maxlength="5000"
            required
            class="app-input resize-none"
            placeholder="Describe el gasto y su finalidad..."
        >{{ old('description', $expense->description ?? '') }}</textarea>

    </div>

</div>


<script>
    (() => {
        const amountInput = document.getElementById('expense_amount');

        if (!amountInput) {
            return;
        }

        amountInput.addEventListener('blur', () => {
            const value = amountInput.value.trim();

            if (value === '') {
                return;
            }

            const number = Number(value);

            if (!Number.isFinite(number)) {
                amountInput.value = '';
                return;
            }

            amountInput.value = number.toFixed(2);
        });
    })();
</script>
