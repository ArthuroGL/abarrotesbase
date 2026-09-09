<div class="grid gap-5 md:grid-cols-2">
    <div><label class="label">Tipo</label><select name="expense_type" required class="field">@foreach($types as $k=>$v)<option value="{{ $k }}" @selected(old('expense_type',$expense->expense_type??'operational')===$k)>{{ $v }}</option>@endforeach</select></div>
    <div><label class="label">Categoría</label><select name="expense_category_id" required class="field">
            <option value="">Selecciona</option>@foreach($categories as $c)<option value="{{ $c->id }}" @selected(old('expense_category_id',$expense->expense_category_id??'')===$c->id)>{{ $c->name }}{{ $c->requires_approval?' · requiere aprobación':'' }}</option>@endforeach
        </select></div>
    <div><label class="label">Importe</label><input name="amount" type="number" min=".01" step=".01" required value="{{ old('amount',$expense->amount??'') }}" class="field"></div>
    <div><label class="label">Fecha</label><input name="expense_date" type="date" required value="{{ old('expense_date',isset($expense)?$expense->expense_date->format('Y-m-d'):now()->format('Y-m-d')) }}" class="field"></div>
    <div><label class="label">Proveedor</label><select name="supplier_id" class="field">
            <option value="">Sin proveedor registrado</option>@foreach($suppliers as $supplier)
    <option
        value="{{ $supplier->id }}"
        @selected(old('supplier_id', $expense->supplier_id ?? '') === $supplier->id)
    >
        {{ $supplier->business_name }}
    </option>
@endforeach
        </select></div>
    <div><label class="label">Origen / lugar</label><input name="source_name" maxlength="150" value="{{ old('source_name',$expense->source_name??'') }}" class="field" placeholder="Central de Abastos, Zorro Abarrotero..."></div>
    <div><label class="label">Beneficiario</label><input name="beneficiary" value="{{ old('beneficiary',$expense->beneficiary??'') }}" class="field"></div>
    <div><label class="label">Referencia / ticket</label><input name="reference" value="{{ old('reference',$expense->reference??'') }}" class="field"></div>
    <div class="md:col-span-2"><label class="label">Descripción</label><textarea name="description" rows="4" required class="field">{{ old('description',$expense->description??'') }}</textarea></div>
</div>
<style>
    .label {
        display: block;
        margin-bottom: .375rem;
        font-size: .875rem;
        font-weight: 700;
        color: #334155
    }

    .field {
        width: 100%;
        border-radius: .75rem;
        border: 1px solid #e2e8f0;
        padding: .65rem .8rem
    }
</style>
