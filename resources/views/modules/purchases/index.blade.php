<x-layouts.app title="Compras | ABARROTESBASE">

    <div class="space-y-6">

        {{-- =========================================================
             HEADER
        ========================================================== --}}
        <x-layout.page-header
            eyebrow="Abastecimiento"
            title="Compras"
            description="Administra las órdenes de compra, proveedores y recepción de mercancía.">

            <x-slot:actions>
                <a
                    href="{{ route('purchases.create') }}"
                    class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">
                    <span class="text-lg leading-none">+</span>
                    Nueva compra
                </a>
            </x-slot:actions>

        </x-layout.page-header>


        {{-- =========================================================
             RESUMEN
        ========================================================== --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">

            <x-ui.card padding="p-5" class="relative overflow-hidden">
                <div class="absolute inset-y-0 left-0 w-1 bg-slate-400"></div>

                <p class="text-xs font-black uppercase tracking-[0.12em] text-slate-500">
                    Órdenes encontradas
                </p>

                <p class="mt-2 text-3xl font-black tracking-tight text-slate-950">
                    {{ number_format($purchases->total()) }}
                </p>

                <p class="mt-1 text-sm font-medium text-slate-500">
                    Resultado del catálogo
                </p>
            </x-ui.card>


            <x-ui.card padding="p-5" class="relative overflow-hidden">
                <div class="absolute inset-y-0 left-0 w-1 bg-amber-500"></div>

                <p class="text-xs font-black uppercase tracking-[0.12em] text-slate-500">
                    Por recibir
                </p>

                <p class="mt-2 text-3xl font-black tracking-tight text-slate-950">
                    {{ $purchases->where('status', 'approved')->count() }}
                </p>

                <p class="mt-1 text-sm font-medium text-slate-500">
                    En la página actual
                </p>
            </x-ui.card>


            <x-ui.card padding="p-5" class="relative overflow-hidden">
                <div class="absolute inset-y-0 left-0 w-1 bg-emerald-500"></div>

                <p class="text-xs font-black uppercase tracking-[0.12em] text-slate-500">
                    Recibidas
                </p>

                <p class="mt-2 text-3xl font-black tracking-tight text-slate-950">
                    {{ $purchases->where('status', 'received')->count() }}
                </p>

                <p class="mt-1 text-sm font-medium text-slate-500">
                    En la página actual
                </p>
            </x-ui.card>


            <x-ui.card padding="p-5" class="relative overflow-hidden">
                <div class="absolute inset-y-0 left-0 w-1 bg-sky-500"></div>

                <p class="text-xs font-black uppercase tracking-[0.12em] text-slate-500">
                    Vista
                </p>

                <p class="mt-2 text-2xl font-black tracking-tight text-slate-950">
                    {{ $status ? ucfirst($status) : 'Todas' }}
                </p>

                <p class="mt-1 text-sm font-medium text-slate-500">
                    Estado seleccionado
                </p>
            </x-ui.card>

        </div>


        {{-- =========================================================
             FILTROS
        ========================================================== --}}
        <x-ui.card padding="p-5 sm:p-6">

            <div class="mb-5">
                <p class="text-sm font-black text-slate-950">
                    Buscar y filtrar
                </p>

                <p class="mt-1 text-sm text-slate-500">
                    Localiza órdenes por número, referencia de proveedor o proveedor.
                </p>
            </div>

            <form
                method="GET"
                action="{{ route('purchases.index') }}"
                class="grid gap-4 lg:grid-cols-12">

                {{-- Buscador --}}
                <div class="lg:col-span-8">

                    <label
                        for="purchase-search"
                        class="mb-2 block text-sm font-bold text-slate-700">
                        Orden, factura o proveedor
                    </label>

                    <div class="relative">

                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                            <svg
                                class="h-5 w-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="2"
                                stroke="currentColor"
                                aria-hidden="true">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                        </div>

                        <x-ui.input
                            id="purchase-search"
                            name="search"
                            type="search"
                            :value="$search"
                            placeholder="Ej. OC-A8F32D1C, FAC-99823 o Coca-Cola..."
                            autocomplete="off"
                            class="pl-11" />

                    </div>

                </div>


                {{-- Estado --}}
                <div class="lg:col-span-4">

                    <label
                        for="purchase-status"
                        class="mb-2 block text-sm font-bold text-slate-700">
                        Estado
                    </label>

                    <select
                        id="purchase-status"
                        name="status"
                        class="app-input"
                        onchange="this.form.submit()">

                        <option value="">
                            Todos los estados
                        </option>

                        <option
                            value="draft"
                            @selected($status === 'draft')>
                            Borrador
                        </option>

                        <option
                            value="approved"
                            @selected($status === 'approved')>
                            Por recibir
                        </option>

                        <option
                            value="received"
                            @selected($status === 'received')>
                            Recibida
                        </option>

                        <option
                            value="cancelled"
                            @selected($status === 'cancelled')>
                            Cancelada
                        </option>

                    </select>

                </div>


                {{-- Limpiar --}}
                @if ($search || $status)

                    <div class="flex justify-end lg:col-span-12">

                        <a
                            href="{{ route('purchases.index') }}"
                            class="inline-flex min-h-11 items-center justify-center rounded-xl px-4 text-sm font-bold text-rose-600 transition hover:bg-rose-50">
                            Limpiar filtros
                        </a>

                    </div>

                @endif

            </form>

        </x-ui.card>


        {{-- =========================================================
             LISTADO
        ========================================================== --}}
        <x-ui.card padding="p-0" class="overflow-hidden">

            <div class="flex flex-col gap-3 border-b border-slate-200 bg-white px-5 py-5 sm:px-6 lg:flex-row lg:items-center lg:justify-between">

                <div>
                    <p class="text-base font-black text-slate-950">
                        Órdenes de compra
                    </p>

                    <p class="mt-1 text-sm text-slate-500">
                        {{ number_format($purchases->total()) }} órdenes encontradas
                    </p>
                </div>

                @if ($search || $status)
                    <span class="inline-flex w-fit items-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">
                        Filtros activos
                    </span>
                @endif

            </div>


            {{-- =====================================================
                 TABLA DESKTOP
            ====================================================== --}}
            <div class="hidden overflow-x-auto lg:block">

                <table class="min-w-[1050px] w-full text-left">

                    <caption class="sr-only">
                        Órdenes de compra
                    </caption>

                    <thead class="border-b border-slate-200 bg-slate-50">

                        <tr class="text-xs font-black uppercase tracking-[0.08em] text-slate-500">

                            <th scope="col" class="px-6 py-4">
                                Orden / Fecha
                            </th>

                            <th scope="col" class="px-6 py-4">
                                Proveedor
                            </th>

                            <th scope="col" class="px-6 py-4">
                                Referencia
                            </th>

                            <th scope="col" class="px-6 py-4 text-right">
                                Total
                            </th>

                            <th scope="col" class="px-6 py-4 text-center">
                                Estado
                            </th>

                            <th scope="col" class="px-6 py-4 text-right">
                                Acción
                            </th>

                        </tr>

                    </thead>


                    <tbody class="divide-y divide-slate-100">

                        @forelse ($purchases as $purchase)

                            @php
                                $statusConfig = match ($purchase->status) {
                                    'received' => [
                                        'label' => 'Recibida',
                                        'class' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                                        'dot' => 'bg-emerald-500',
                                    ],
                                    'approved' => [
                                        'label' => 'Por recibir',
                                        'class' => 'border-amber-200 bg-amber-50 text-amber-700',
                                        'dot' => 'bg-amber-500',
                                    ],
                                    'cancelled' => [
                                        'label' => 'Cancelada',
                                        'class' => 'border-rose-200 bg-rose-50 text-rose-700',
                                        'dot' => 'bg-rose-500',
                                    ],
                                    default => [
                                        'label' => 'Borrador',
                                        'class' => 'border-slate-200 bg-slate-100 text-slate-600',
                                        'dot' => 'bg-slate-400',
                                    ],
                                };
                            @endphp

                            <tr class="transition hover:bg-slate-50/70">

                                {{-- Orden --}}
                                <td class="px-6 py-5 align-middle">

                                    <p class="text-sm font-black text-slate-950">
                                        {{ $purchase->purchase_number }}
                                    </p>

                                    <p class="mt-1 text-xs font-medium text-slate-400">
                                        {{ $purchase->created_at->format('d/m/Y H:i') }}
                                    </p>

                                </td>


                                {{-- Proveedor --}}
                                <td class="px-6 py-5 align-middle">

                                    <p class="max-w-xs truncate text-sm font-bold text-slate-800">
                                        {{ $purchase->supplier?->business_name ?? 'Proveedor no disponible' }}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-400">
                                        {{ $purchase->supplier?->rfc ?? 'Sin RFC' }}
                                    </p>

                                </td>


                                {{-- Referencia --}}
                                <td class="px-6 py-5 align-middle">

                                    @if ($purchase->supplier_reference)

                                        <span class="font-mono text-xs font-bold text-slate-700">
                                            {{ $purchase->supplier_reference }}
                                        </span>

                                    @else

                                        <span class="text-xs font-medium text-slate-400">
                                            Sin referencia
                                        </span>

                                    @endif

                                </td>


                                {{-- Total --}}
                                <td class="px-6 py-5 text-right align-middle">

                                    <p class="text-sm font-black text-slate-950">
                                        ${{ number_format((float) $purchase->total, 2) }}
                                    </p>

                                    <p class="mt-0.5 text-[11px] font-medium text-slate-400">
                                        {{ $purchase->currency_code }}
                                    </p>

                                </td>


                                {{-- Estado --}}
                                <td class="px-6 py-5 text-center align-middle">

                                    <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-black {{ $statusConfig['class'] }}">

                                        <span class="h-2 w-2 rounded-full {{ $statusConfig['dot'] }}"></span>

                                        {{ $statusConfig['label'] }}

                                    </span>

                                </td>


                                {{-- Acción --}}
                                <td class="px-6 py-5 text-right align-middle">

                                    <a
                                        href="{{ route('purchases.show', $purchase) }}"
                                        class="inline-flex min-h-10 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 text-sm font-bold text-slate-700 shadow-sm transition hover:border-slate-400 hover:bg-slate-50 hover:text-slate-950 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">
                                        Ver detalle
                                    </a>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="6" class="px-6 py-16 text-center">

                                    <div class="mx-auto max-w-md">

                                        <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-slate-100 text-xl font-black text-slate-400">
                                            ∅
                                        </div>

                                        <p class="mt-4 text-base font-black text-slate-900">
                                            No encontramos órdenes de compra
                                        </p>

                                        <p class="mt-2 text-sm leading-6 text-slate-500">
                                            No existen compras que coincidan con los filtros seleccionados.
                                        </p>

                                        @if ($search || $status)

                                            <a
                                                href="{{ route('purchases.index') }}"
                                                class="mt-5 inline-flex min-h-11 items-center justify-center rounded-xl bg-slate-900 px-5 text-sm font-bold text-white transition hover:bg-slate-800">
                                                Limpiar filtros
                                            </a>

                                        @endif

                                    </div>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>


            {{-- =====================================================
                 CARDS MOBILE / TABLET
            ====================================================== --}}
            <div class="divide-y divide-slate-100 lg:hidden">

                @forelse ($purchases as $purchase)

                    @php
                        $statusConfig = match ($purchase->status) {
                            'received' => [
                                'label' => 'Recibida',
                                'class' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                            ],
                            'approved' => [
                                'label' => 'Por recibir',
                                'class' => 'border-amber-200 bg-amber-50 text-amber-700',
                            ],
                            'cancelled' => [
                                'label' => 'Cancelada',
                                'class' => 'border-rose-200 bg-rose-50 text-rose-700',
                            ],
                            default => [
                                'label' => 'Borrador',
                                'class' => 'border-slate-200 bg-slate-100 text-slate-600',
                            ],
                        };
                    @endphp

                    <article class="p-5">

                        <div class="flex items-start justify-between gap-4">

                            <div class="min-w-0">

                                <p class="font-mono text-xs font-bold text-slate-500">
                                    {{ $purchase->purchase_number }}
                                </p>

                                <h2 class="mt-1 truncate text-base font-black text-slate-950">
                                    {{ $purchase->supplier?->business_name ?? 'Proveedor no disponible' }}
                                </h2>

                                <p class="mt-1 text-xs text-slate-400">
                                    {{ $purchase->created_at->format('d/m/Y H:i') }}
                                </p>

                            </div>

                            <span class="shrink-0 rounded-full border px-2.5 py-1 text-xs font-black {{ $statusConfig['class'] }}">
                                {{ $statusConfig['label'] }}
                            </span>

                        </div>


                        <div class="mt-5 grid grid-cols-2 gap-3">

                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">

                                <p class="text-xs font-black uppercase tracking-[0.1em] text-slate-400">
                                    Referencia
                                </p>

                                <p class="mt-1 truncate text-sm font-bold text-slate-800">
                                    {{ $purchase->supplier_reference ?: 'Sin referencia' }}
                                </p>

                            </div>


                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">

                                <p class="text-xs font-black uppercase tracking-[0.1em] text-slate-400">
                                    Total
                                </p>

                                <p class="mt-1 text-sm font-black text-slate-950">
                                    ${{ number_format((float) $purchase->total, 2) }}
                                    <span class="text-[10px] font-medium text-slate-400">
                                        {{ $purchase->currency_code }}
                                    </span>
                                </p>

                            </div>

                        </div>


                        <a
                            href="{{ route('purchases.show', $purchase) }}"
                            class="mt-4 flex min-h-12 w-full items-center justify-center rounded-xl bg-slate-900 px-4 text-sm font-black text-white transition hover:bg-slate-800">
                            Ver detalle de la compra
                        </a>

                    </article>

                @empty

                    <div class="px-5 py-16 text-center">

                        <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-slate-100 text-xl font-black text-slate-400">
                            ∅
                        </div>

                        <p class="mt-4 text-base font-black text-slate-900">
                            No encontramos órdenes de compra
                        </p>

                    </div>

                @endforelse

            </div>


            {{-- =====================================================
                 PAGINACIÓN
            ====================================================== --}}
            @if ($purchases->hasPages())

                <div class="border-t border-slate-200 bg-slate-50/70 px-5 py-4 sm:px-6">
                    {{ $purchases->links() }}
                </div>

            @endif

        </x-ui.card>

    </div>

</x-layouts.app>
