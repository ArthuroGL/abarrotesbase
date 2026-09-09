<x-layouts.app title="Gastos">
    <x-layout.page-header eyebrow="Operación" title="Gastos" description="Controla egresos, surtidos directos y pagos de la sucursal.">
        <x-slot:actions><a href="{{ route('expenses.categories.index') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 font-bold text-slate-700">Categorías</a><a href="{{ route('expenses.create') }}" class="rounded-xl bg-emerald-600 px-4 py-2.5 font-bold text-white">Registrar gasto</a></x-slot:actions>
    </x-layout.page-header>



    @if(session('success'))<div class="mt-5 rounded-xl bg-emerald-50 p-4 text-sm font-bold text-emerald-800">{{ session('success') }}</div>@endif
    <div class="mt-6 grid gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border bg-white p-5">
            <p class="text-xs font-bold uppercase text-slate-500">Aprobado · mes</p>
            <p class="mt-2 text-2xl font-black">${{ number_format($monthTotal,2) }}</p>
        </div>
        <div class="rounded-2xl border bg-white p-5">
            <p class="text-xs font-bold uppercase text-slate-500">Pagado · mes</p>
            <p class="mt-2 text-2xl font-black text-emerald-700">${{ number_format($monthPaid,2) }}</p>
        </div>
        <div class="rounded-2xl border bg-amber-50 p-5">
            <p class="text-xs font-bold uppercase text-amber-700">Pendiente</p>
            <p class="mt-2 text-2xl font-black text-amber-900">${{ number_format($pending,2) }}</p>
        </div>
    </div>
    <form method="GET" class="mt-6 grid gap-3 rounded-2xl border bg-white p-5 md:grid-cols-4"><input name="q" value="{{ request('q') }}" placeholder="Buscar folio, beneficiario..." class="rounded-xl border-slate-200 md:col-span-2"><select name="expense_type" class="rounded-xl border-slate-200">
            <option value="">Todos los tipos</option>@foreach($types as $k=>$v)<option value="{{ $k }}" @selected(request('expense_type')===$k)>{{ $v }}</option>@endforeach
        </select><select name="status" class="rounded-xl border-slate-200">
            <option value="">Todos los estados</option>@foreach(['pending_approval'=>'Pendiente','approved'=>'Aprobado','paid'=>'Pagado','rejected'=>'Rechazado','cancelled'=>'Cancelado'] as $k=>$v)<option value="{{ $k }}" @selected(request('status')===$k)>{{ $v }}</option>@endforeach
        </select>
        <div class="md:col-span-4 flex justify-end"><button class="rounded-xl bg-slate-900 px-4 py-2.5 font-bold text-white">Filtrar</button></div>
    </form>
    <div class="mt-6 overflow-hidden rounded-2xl border bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="p-4 text-left">Folio</th>
                        <th class="p-4 text-left">Fecha</th>
                        <th class="p-4 text-left">Concepto</th>
                        <th class="p-4 text-left">Origen</th>
                        <th class="p-4 text-left">Estado</th>
                        <th class="p-4 text-right">Importe</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody class="divide-y">@forelse($expenses as $e)<tr class="hover:bg-slate-50">
                        <td class="p-4 font-bold">{{ $e->expense_number }}</td>
                        <td class="p-4">{{ $e->expense_date->format('d/m/Y') }}</td>
                        <td class="p-4"><b>{{ $e->category?->name }}</b>
                            <div class="text-xs text-slate-500">{{ $types[$e->expense_type] }}</div>
                        </td>
                        <td class="p-4">{{ $e->supplier?->business_name ?? $e->source_name ?? $e->beneficiary ?? '—' }}</td>
                        <td class="p-4">{{ ucfirst(str_replace('_',' ',$e->status)) }}</td>
                        <td class="p-4 text-right font-black">${{ number_format($e->amount,2) }}</td>
                        <td class="p-4 text-right"><a class="font-bold text-emerald-700" href="{{ route('expenses.show',$e) }}">Ver</a></td>
                    </tr>@empty<tr>
                        <td colspan="7" class="p-10 text-center text-slate-500">No hay gastos.</td>
                    </tr>@endforelse</tbody>
            </table>
        </div>
        <div class="p-4">{{ $expenses->links() }}</div>
    </div>
</x-layouts.app>
