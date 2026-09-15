<x-layouts.app title="Categorías de gastos">

    <x-layout.page-header
        eyebrow="Configuración"
        title="Categorías de gastos"
        description="Clasifica egresos y define cuáles requieren aprobación.">
        <x-slot:actions>

            <x-ui.button
                variant="secondary"
                type="button"
                onclick="window.location.href='{{ route('expenses.index') }}'">
                Volver a gastos
            </x-ui.button>

        </x-slot:actions>
    </x-layout.page-header>

    @if (session('success'))

    <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4">

        <div class="flex items-start gap-3">

            <div class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-emerald-100 font-black text-emerald-700">
                ✓
            </div>

            <div>

                <p class="text-sm font-black text-emerald-900">
                    Operación completada
                </p>

                <p class="mt-1 text-sm text-emerald-700">
                    {{ session('success') }}
                </p>

            </div>

        </div>

    </div>

    @endif


    <div class="mt-6 grid gap-6 lg:grid-cols-[360px_1fr]">


        {{-- ===================================================== --}}
        {{-- CREAR --}}
        {{-- ===================================================== --}}

        <x-ui.card>

            <div class="mb-5">

                <p class="text-xs font-black uppercase tracking-wider text-emerald-700">
                    Nueva categoría
                </p>

                <h2 class="mt-1 text-xl font-black tracking-tight text-slate-900">
                    Crear categoría
                </h2>

                <p class="mt-1 text-sm leading-6 text-slate-500">
                    Define cómo se clasificarán los gastos.
                </p>

            </div>


            <form
                method="POST"
                action="{{ route('expenses.categories.store') }}"
                class="space-y-4">

                @csrf


                <div>

                    <label
                        for="category-code"
                        class="mb-1.5 block text-xs font-black uppercase tracking-wider text-slate-600">
                        Código
                    </label>

                    <x-ui.input
                        id="category-code"
                        name="code"
                        maxlength="50"
                        placeholder="Ej. SERVICIOS"
                        required />

                </div>


                <div>

                    <label
                        for="category-name"
                        class="mb-1.5 block text-xs font-black uppercase tracking-wider text-slate-600">
                        Nombre
                    </label>

                    <x-ui.input
                        id="category-name"
                        name="name"
                        maxlength="150"
                        placeholder="Ej. Servicios básicos"
                        required />

                </div>


                <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4">

                    <input
                        type="checkbox"
                        name="requires_approval"
                        value="1"
                        class="h-5 w-5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">

                    <span>

                        <span class="block text-sm font-black text-slate-800">
                            Requiere aprobación
                        </span>

                        <span class="mt-0.5 block text-xs text-slate-500">
                            El gasto quedará pendiente antes de poder pagarse.
                        </span>

                    </span>

                </label>


                <x-ui.button
                    type="submit"
                    variant="primary"
                    class="w-full">
                    Crear categoría
                </x-ui.button>

            </form>

        </x-ui.card>


        {{-- ===================================================== --}}
        {{-- LISTADO --}}
        {{-- ===================================================== --}}

        <x-ui.card
            padding="p-0">

            <div class="border-b border-slate-200 px-5 py-5 sm:px-6">

                <h2 class="text-lg font-black tracking-tight text-slate-900">
                    Categorías existentes
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Clasificaciones disponibles para registrar gastos.
                </p>

            </div>


            {{-- Desktop --}}
            <div class="hidden overflow-x-auto md:block">

                <table class="w-full text-left text-sm">

                    <thead class="border-b border-slate-200 bg-slate-50">

                        <tr class="text-[11px] font-black uppercase tracking-wider text-slate-500">

                            <th class="px-5 py-3.5">
                                Código
                            </th>

                            <th class="px-5 py-3.5">
                                Categoría
                            </th>

                            <th class="px-5 py-3.5">
                                Gastos
                            </th>

                            <th class="px-5 py-3.5">
                                Aprobación
                            </th>

                        </tr>

                    </thead>


                    <tbody class="divide-y divide-slate-100">

                        @forelse ($categories as $category)

                        <tr class="hover:bg-slate-50/80">

                            <td class="px-5 py-4">
                                <span class="rounded-lg bg-slate-100 px-2.5 py-1 font-mono text-xs font-bold text-slate-600">
                                    {{ $category->code }}
                                </span>
                            </td>

                            <td class="px-5 py-4">

                                <p class="font-bold text-slate-900">
                                    {{ $category->name }}
                                </p>

                            </td>

                            <td class="px-5 py-4 text-slate-600">
                                {{ $category->expenses_count }}
                            </td>

                            <td class="px-5 py-4">

                                @if ($category->requires_approval)

                                <span class="rounded-lg bg-amber-50 px-2.5 py-1 text-xs font-black text-amber-700 ring-1 ring-inset ring-amber-200">
                                    Sí
                                </span>

                                @else

                                <span class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-black text-slate-600 ring-1 ring-inset ring-slate-200">
                                    No
                                </span>

                                @endif

                            </td>

                        </tr>

                        @empty

                        <tr>

                            <td
                                colspan="4"
                                class="px-5 py-14 text-center text-sm font-bold text-slate-500">
                                No hay categorías registradas.
                            </td>

                        </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>


            {{-- Móvil --}}
            <div class="divide-y divide-slate-100 md:hidden">

                @forelse ($categories as $category)

                <article class="p-5">

                    <div class="flex items-start justify-between gap-4">

                        <div>

                            <p class="font-black text-slate-900">
                                {{ $category->name }}
                            </p>

                            <p class="mt-1 font-mono text-xs text-slate-500">
                                {{ $category->code }}
                            </p>

                        </div>

                        @if ($category->requires_approval)

                        <span class="shrink-0 rounded-lg bg-amber-50 px-2.5 py-1 text-xs font-black text-amber-700 ring-1 ring-inset ring-amber-200">
                            Aprobación
                        </span>

                        @else

                        <span class="shrink-0 rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-black text-slate-600 ring-1 ring-inset ring-slate-200">
                            Directo
                        </span>

                        @endif

                    </div>


                    <div class="mt-4 border-t border-slate-100 pt-3">

                        <p class="text-xs font-bold text-slate-400">
                            Gastos registrados
                        </p>

                        <p class="mt-1 font-black text-slate-800">
                            {{ $category->expenses_count }}
                        </p>

                    </div>

                </article>

                @empty

                <div class="px-5 py-14 text-center text-sm font-bold text-slate-500">
                    No hay categorías registradas.
                </div>

                @endforelse

            </div>

        </x-ui.card>

    </div>

</x-layouts.app>
