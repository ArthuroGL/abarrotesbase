<x-layouts.app title="Editar gasto">
    <x-layout.page-header eyebrow="Operación" title="Editar gasto" description="Corrige el gasto antes de volver a aprobarlo."/>
    <form method="POST" action="{{ route('expenses.update',$expense) }}" class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">@csrf @method('PUT')
        @include('modules.operation.expenses._form')
        <div class="mt-6 flex justify-end gap-3"><a href="{{ route('expenses.show',$expense) }}" class="rounded-xl px-5 py-3 font-bold text-slate-600">Cancelar</a><button class="rounded-xl bg-emerald-600 px-5 py-3 font-bold text-white">Guardar cambios</button></div>
    </form>
</x-layouts.app>
