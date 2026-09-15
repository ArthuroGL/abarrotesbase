<x-layouts.app title="Registrar gasto">

    <x-layout.page-header
        eyebrow="Operación"
        title="Registrar gasto"
        description="Registra primero el gasto; el pago se procesa por separado para mantener Caja consistente."
    />

    <x-ui.card
        class="mt-6"
        padding="p-5 sm:p-6"
    >

        <form
            method="POST"
            action="{{ route('expenses.store') }}"
        >

            @csrf

            @include('modules.operation.expenses._form')


            <div class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:justify-end">

                <x-ui.button
                    variant="secondary"
                    type="button"
                    onclick="window.location.href='{{ route('expenses.index') }}'"
                >
                    Cancelar
                </x-ui.button>

                <x-ui.button
                    variant="primary"
                    type="submit"
                >
                    Guardar gasto
                </x-ui.button>

            </div>

        </form>

    </x-ui.card>

</x-layouts.app>
