<x-layouts.app title="Registrar gasto">
    <x-layout.page-header eyebrow="Operación" title="Registrar gasto" description="Registra primero el gasto; el pago se procesa por separado para mantener Caja consistente."/>
    <form method="POST" action="{{ route('expenses.store') }}" class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">@csrf
        @include('modules.operation.expenses._form')
        <div class="mt-6 flex justify-end gap-3"><a href="{{ route('expenses.index') }}" class="rounded-xl px-5 py-3 font-bold text-slate-600">Cancelar</a><button class="rounded-xl bg-emerald-600 px-5 py-3 font-bold text-white">Guardar gasto</button></div>
    </form>
</x-layouts.app>
