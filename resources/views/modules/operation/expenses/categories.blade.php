<x-layouts.app title="Categorías de gastos">
    <x-layout.page-header eyebrow="Configuración" title="Categorías de gastos" description="Clasifica egresos y define cuáles requieren aprobación."/>
    @if(session('success'))<div class="mt-5 rounded-xl bg-emerald-50 p-4 text-sm font-bold text-emerald-800">{{ session('success') }}</div>@endif
    <div class="mt-6 grid gap-6 lg:grid-cols-[360px_1fr]">
        <form method="POST" action="{{ route('expenses.categories.store') }}" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">@csrf
            <input name="code" required placeholder="Código" class="mb-3 w-full rounded-xl border-slate-200">
            <input name="name" required placeholder="Nombre" class="mb-3 w-full rounded-xl border-slate-200">
<label class="flex gap-2 text-sm font-bold"><input type="checkbox" name="requires_approval" value="1"> Requiere aprobación</label>
<button class="mt-4 w-full rounded-xl bg-emerald-600 px-4 py-3 font-bold text-white">Crear categoría</button>
</form>
<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"><table class="min-w-full text-sm"><thead class="bg-slate-50"><tr><th class="p-4 text-left">Código</th><th class="p-4 text-left">Categoría</th><th class="p-4 text-left">Gastos</th><th class="p-4 text-left">Aprobación</th></tr></thead><tbody class="divide-y">@foreach($categories as $c)<tr><td class="p-4 font-mono text-xs">{{ $c->code }}</td><td class="p-4 font-bold">{{ $c->name }}</td><td class="p-4">{{ $c->expenses_count }}</td><td class="p-4">{{ $c->requires_approval?'Sí':'No' }}</td></tr>@endforeach</tbody></table></div>
</div></x-layouts.app>
