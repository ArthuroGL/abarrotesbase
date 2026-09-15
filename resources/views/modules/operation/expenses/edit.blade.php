<x-layouts.app title="Editar gasto">

    <x-layout.page-header
        eyebrow="Operación"
        title="Editar gasto"
        description="Corrige la información del gasto antes de volver a aprobarlo."
    />

    <x-ui.card
        class="mt-6"
        padding="p-5 sm:p-6"
    >

        <form
            method="POST"
            action="{{ route('expenses.update', $expense) }}"
        >

            @csrf
            @method('PUT')

            @include('modules.operation.expenses._form')


            <div class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:justify-end">

                <x-ui.button
                    variant="secondary"
                    type="button"
                    onclick="window.location.href='{{ route('expenses.show', $expense) }}'"
                >
                    Cancelar
                </x-ui.button>

                <x-ui.button
                    variant="primary"
                    type="submit"
                >
                    Guardar cambios
                </x-ui.button>

            </div>

        </form>

    </x-ui.card>

</x-layouts.app>
