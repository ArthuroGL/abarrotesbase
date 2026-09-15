<x-layouts.app title="Gastos">

    <x-layout.page-header
        eyebrow="Operación"
        title="Gastos"
        description="Controla egresos, surtidos directos y pagos de la sucursal."
    >
        <x-slot:actions>

            <x-ui.button
                variant="secondary"
                type="button"
                onclick="window.location.href='{{ route('expenses.categories.index') }}'"
            >
                Categorías
            </x-ui.button>

            <x-ui.button
                variant="primary"
                type="button"
                onclick="window.location.href='{{ route('expenses.create') }}'"
            >
                Registrar gasto
            </x-ui.button>

        </x-slot:actions>
    </x-layout.page-header>


    {{-- ========================================================= --}}
    {{-- MENSAJE --}}
    {{-- ========================================================= --}}

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


    {{-- ========================================================= --}}
    {{-- RESUMEN --}}
    {{-- ========================================================= --}}

    <div class="mt-6 grid gap-4 sm:grid-cols-3">

        <x-ui.card>
            <p class="text-xs font-black uppercase tracking-wider text-slate-500">
                Aprobado · mes
            </p>

            <p class="mt-2 text-2xl font-black tabular-nums text-slate-900">
                ${{ number_format((float) $monthTotal, 2) }}
            </p>

            <p class="mt-1 text-xs text-slate-500">
                Gastos aprobados durante el mes
            </p>
        </x-ui.card>


        <x-ui.card>
            <p class="text-xs font-black uppercase tracking-wider text-slate-500">
                Pagado · mes
            </p>

            <p class="mt-2 text-2xl font-black tabular-nums text-emerald-600">
                ${{ number_format((float) $monthPaid, 2) }}
            </p>

            <p class="mt-1 text-xs text-slate-500">
                Gastos que ya fueron liquidados
            </p>
        </x-ui.card>


        <x-ui.card class="border-amber-200 bg-amber-50">

            <p class="text-xs font-black uppercase tracking-wider text-amber-700">
                Pendiente
            </p>

            <p class="mt-2 text-2xl font-black tabular-nums text-amber-900">
                ${{ number_format((float) $pending, 2) }}
            </p>

            <p class="mt-1 text-xs text-amber-700">
                Gastos pendientes de aprobación
            </p>

        </x-ui.card>

    </div>


    {{-- ========================================================= --}}
    {{-- FILTROS --}}
    {{-- ========================================================= --}}

    <x-ui.card class="mt-6">

        <form
            method="GET"
            class="grid gap-4 md:grid-cols-2 xl:grid-cols-4"
        >

            <div class="xl:col-span-2">

                <label
                    for="expense-search"
                    class="mb-1.5 block text-xs font-black uppercase tracking-wider text-slate-600"
                >
                    Buscar
                </label>

                <x-ui.input
                    id="expense-search"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Folio, beneficiario, origen o referencia..."
                />

            </div>


            <div>

                <label
                    for="expense-type"
                    class="mb-1.5 block text-xs font-black uppercase tracking-wider text-slate-600"
                >
                    Tipo
                </label>

                <select
                    id="expense-type"
                    name="expense_type"
                    class="app-input"
                >
                    <option value="">
                        Todos los tipos
                    </option>

                    @foreach ($types as $key => $label)
                        <option
                            value="{{ $key }}"
                            @selected(request('expense_type') === $key)
                        >
                            {{ $label }}
                        </option>
                    @endforeach
                </select>

            </div>


            <div>

                <label
                    for="expense-status"
                    class="mb-1.5 block text-xs font-black uppercase tracking-wider text-slate-600"
                >
                    Estado
                </label>

                <select
                    id="expense-status"
                    name="status"
                    class="app-input"
                >
                    <option value="">
                        Todos los estados
                    </option>

                    @foreach ([
                        'pending_approval' => 'Pendiente',
                        'approved' => 'Aprobado',
                        'paid' => 'Pagado',
                        'rejected' => 'Rechazado',
                        'cancelled' => 'Cancelado',
                    ] as $key => $label)

                        <option
                            value="{{ $key }}"
                            @selected(request('status') === $key)
                        >
                            {{ $label }}
                        </option>

                    @endforeach

                </select>

            </div>


            <div class="flex justify-end md:col-span-2 xl:col-span-4">

                <x-ui.button
                    type="submit"
                    variant="secondary"
                >
                    Aplicar filtros
                </x-ui.button>

            </div>

        </form>

    </x-ui.card>


    {{-- ========================================================= --}}
    {{-- LISTADO --}}
    {{-- ========================================================= --}}

    <x-ui.card
        class="mt-6"
        padding="p-0"
    >

        <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">

            <div>
                <h2 class="text-lg font-black tracking-tight text-slate-900">
                    Gastos registrados
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Consulta y administra los egresos de la sucursal.
                </p>
            </div>

            <span class="w-fit rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600">
                {{ $expenses->total() }} registros
            </span>

        </div>


        {{-- ===================================================== --}}
        {{-- DESKTOP --}}
        {{-- ===================================================== --}}

        <div class="hidden overflow-x-auto md:block">

            <table class="w-full text-left text-sm">

                <thead class="border-b border-slate-200 bg-slate-50">

                    <tr class="text-[11px] font-black uppercase tracking-wider text-slate-500">

                        <th class="px-5 py-3.5">
                            Folio
                        </th>

                        <th class="px-5 py-3.5">
                            Fecha
                        </th>

                        <th class="px-5 py-3.5">
                            Concepto
                        </th>

                        <th class="px-5 py-3.5">
                            Origen
                        </th>

                        <th class="px-5 py-3.5">
                            Estado
                        </th>

                        <th class="px-5 py-3.5 text-right">
                            Importe
                        </th>

                        <th class="px-5 py-3.5 text-right">
                            Acción
                        </th>

                    </tr>

                </thead>


                <tbody class="divide-y divide-slate-100">

                    @forelse ($expenses as $expense)

                        @php
                            $statusLabel = match ($expense->status) {
                                'pending_approval' => 'Pendiente',
                                'approved' => 'Aprobado',
                                'paid' => 'Pagado',
                                'rejected' => 'Rechazado',
                                'cancelled' => 'Cancelado',
                                'draft' => 'Borrador',
                                default => ucfirst(str_replace('_', ' ', $expense->status)),
                            };

                            $statusClass = match ($expense->status) {
                                'paid' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                                'approved' => 'bg-sky-50 text-sky-700 ring-sky-200',
                                'pending_approval' => 'bg-amber-50 text-amber-700 ring-amber-200',
                                'rejected', 'cancelled' => 'bg-rose-50 text-rose-700 ring-rose-200',
                                default => 'bg-slate-100 text-slate-600 ring-slate-200',
                            };
                        @endphp

                        <tr class="transition hover:bg-slate-50/80">

                            <td class="px-5 py-4">
                                <span class="font-black text-slate-900">
                                    {{ $expense->expense_number }}
                                </span>
                            </td>


                            <td class="px-5 py-4 text-xs text-slate-500">
                                {{ $expense->expense_date->format('d/m/Y') }}
                            </td>


                            <td class="px-5 py-4">

                                <p class="font-bold text-slate-900">
                                    {{ $expense->category?->name ?? 'Sin categoría' }}
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $types[$expense->expense_type] ?? $expense->expense_type }}
                                </p>

                            </td>


                            <td class="max-w-xs px-5 py-4">

                                <p class="truncate text-sm text-slate-700">
                                    {{ $expense->supplier?->business_name ?? $expense->source_name ?? $expense->beneficiary ?? 'Sin origen' }}
                                </p>

                            </td>


                            <td class="px-5 py-4">

                                <span class="inline-flex rounded-lg px-2.5 py-1 text-xs font-black ring-1 ring-inset {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>

                            </td>


                            <td class="px-5 py-4 text-right">

                                <span class="font-black tabular-nums text-slate-900">
                                    ${{ number_format((float) $expense->amount, 2) }}
                                </span>

                            </td>


                            <td class="px-5 py-4 text-right">

                                <a
                                    href="{{ route('expenses.show', $expense) }}"
                                    class="font-bold text-emerald-700 hover:text-emerald-800"
                                >
                                    Ver detalle
                                </a>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td
                                colspan="7"
                                class="px-5 py-14 text-center"
                            >
                                <p class="text-sm font-bold text-slate-500">
                                    No hay gastos registrados.
                                </p>

                                <p class="mt-1 text-xs text-slate-400">
                                    Ajusta los filtros o registra un nuevo gasto.
                                </p>
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- ===================================================== --}}
        {{-- MÓVIL --}}
        {{-- ===================================================== --}}

        <div class="divide-y divide-slate-100 md:hidden">

            @forelse ($expenses as $expense)

                @php
                    $statusLabel = match ($expense->status) {
                        'pending_approval' => 'Pendiente',
                        'approved' => 'Aprobado',
                        'paid' => 'Pagado',
                        'rejected' => 'Rechazado',
                        'cancelled' => 'Cancelado',
                        'draft' => 'Borrador',
                        default => ucfirst(str_replace('_', ' ', $expense->status)),
                    };

                    $statusClass = match ($expense->status) {
                        'paid' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                        'approved' => 'bg-sky-50 text-sky-700 ring-sky-200',
                        'pending_approval' => 'bg-amber-50 text-amber-700 ring-amber-200',
                        'rejected', 'cancelled' => 'bg-rose-50 text-rose-700 ring-rose-200',
                        default => 'bg-slate-100 text-slate-600 ring-slate-200',
                    };
                @endphp

                <article class="p-5">

                    <div class="flex items-start justify-between gap-4">

                        <div class="min-w-0">

                            <p class="font-black text-slate-900">
                                {{ $expense->category?->name ?? 'Sin categoría' }}
                            </p>

                            <p class="mt-1 text-xs text-slate-500">
                                {{ $expense->expense_number }} · {{ $expense->expense_date->format('d/m/Y') }}
                            </p>

                        </div>

                        <span class="shrink-0 rounded-lg px-2.5 py-1 text-xs font-black ring-1 ring-inset {{ $statusClass }}">
                            {{ $statusLabel }}
                        </span>

                    </div>


                    <div class="mt-4 grid gap-3 sm:grid-cols-2">

                        <div>
                            <p class="text-[11px] font-black uppercase tracking-wider text-slate-400">
                                Origen
                            </p>

                            <p class="mt-1 text-sm text-slate-700">
                                {{ $expense->supplier?->business_name ?? $expense->source_name ?? $expense->beneficiary ?? 'Sin origen' }}
                            </p>
                        </div>


                        <div>
                            <p class="text-[11px] font-black uppercase tracking-wider text-slate-400">
                                Tipo
                            </p>

                            <p class="mt-1 text-sm text-slate-700">
                                {{ $types[$expense->expense_type] ?? $expense->expense_type }}
                            </p>
                        </div>

                    </div>


                    <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-4">

                        <span class="text-xs font-bold text-slate-400">
                            Importe
                        </span>

                        <span class="font-black tabular-nums text-slate-900">
                            ${{ number_format((float) $expense->amount, 2) }}
                        </span>

                    </div>


                    <div class="mt-4">

                        <x-ui.button
                            variant="secondary"
                            type="button"
                            class="w-full"
                            onclick="window.location.href='{{ route('expenses.show', $expense) }}'"
                        >
                            Ver detalle
                        </x-ui.button>

                    </div>

                </article>

            @empty

                <div class="px-5 py-14 text-center">

                    <p class="text-sm font-bold text-slate-500">
                        No hay gastos registrados.
                    </p>

                </div>

            @endforelse

        </div>


        <div class="border-t border-slate-200 p-4">
            {{ $expenses->links() }}
        </div>

    </x-ui.card>

</x-layouts.app>
