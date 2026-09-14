<x-layouts.app title="Existencias | ABARROTESBASE">

    <div class="space-y-6">

        {{-- =========================================================
             HEADER
        ========================================================== --}}
        <x-layout.page-header
            eyebrow="Inventario"
            title="Existencias"
            description="Consulta el inventario físico de la sucursal actual y registra ajustes cuando sea necesario." />

        {{-- =========================================================
             FLASH MESSAGE
        ========================================================== --}}
        @if (session('status'))
        <div
            class="flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-4 text-sm font-semibold text-emerald-800"
            role="status">
            <span class="mt-0.5 grid h-5 w-5 shrink-0 place-items-center rounded-full bg-emerald-600 text-xs font-black text-white">
                ✓
            </span>

            <span>{{ session('status') }}</span>
        </div>
        @endif

        {{-- =========================================================
             VALIDATION ERRORS
        ========================================================== --}}
        @if ($errors->any())
        <div
            class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-4 text-sm text-rose-800"
            role="alert">
            <p class="font-black">No se pudo realizar el ajuste.</p>

            <ul class="mt-2 list-disc space-y-1 pl-5 font-medium">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif


        {{-- =========================================================
             KPI CARDS
        ========================================================== --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">

            {{-- Total --}}
            <x-ui.card padding="p-5" class="relative overflow-hidden">
                <div class="absolute inset-y-0 left-0 w-1 bg-slate-400"></div>

                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.12em] text-slate-500">
                            Artículos
                        </p>

                        <p class="mt-2 text-3xl font-black tracking-tight text-slate-950">
                            {{ number_format($totalItems) }}
                        </p>

                        <p class="mt-1 text-sm font-medium text-slate-500">
                            Inventariables activos
                        </p>
                    </div>

                    <div class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-slate-100 text-lg font-black text-slate-600">
                        #
                    </div>
                </div>
            </x-ui.card>


            {{-- Disponibles --}}
            <x-ui.card padding="p-5" class="relative overflow-hidden">
                <div class="absolute inset-y-0 left-0 w-1 bg-emerald-500"></div>

                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.12em] text-slate-500">
                            Disponibles
                        </p>

                        <p class="mt-2 text-3xl font-black tracking-tight text-slate-950">
                            {{ number_format($availableItems) }}
                        </p>

                        <p class="mt-1 text-sm font-medium text-slate-500">
                            En nivel normal
                        </p>
                    </div>

                    <div class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-emerald-50 text-lg font-black text-emerald-700">
                        ✓
                    </div>
                </div>
            </x-ui.card>


            {{-- Stock bajo --}}
            <x-ui.card padding="p-5" class="relative overflow-hidden">
                <div class="absolute inset-y-0 left-0 w-1 bg-amber-500"></div>

                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.12em] text-slate-500">
                            Stock bajo
                        </p>

                        <p class="mt-2 text-3xl font-black tracking-tight text-slate-950">
                            {{ number_format($lowStockItems) }}
                        </p>

                        <p class="mt-1 text-sm font-medium text-slate-500">
                            Requieren atención
                        </p>
                    </div>

                    <div class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-amber-50 text-lg font-black text-amber-700">
                        !
                    </div>
                </div>
            </x-ui.card>


            {{-- Agotados --}}
            <x-ui.card padding="p-5" class="relative overflow-hidden">
                <div class="absolute inset-y-0 left-0 w-1 bg-rose-500"></div>

                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.12em] text-slate-500">
                            Agotados
                        </p>

                        <p class="mt-2 text-3xl font-black tracking-tight text-slate-950">
                            {{ number_format($outOfStockItems) }}
                        </p>

                        <p class="mt-1 text-sm font-medium text-slate-500">
                            Sin existencia disponible
                        </p>
                    </div>

                    <div class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-rose-50 text-lg font-black text-rose-700">
                        0
                    </div>
                </div>
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
                    Localiza rápidamente un artículo por nombre, SKU o código de barras.
                </p>
            </div>

            <form
                method="GET"
                action="{{ route('stock.index') }}"
                class="grid gap-4 lg:grid-cols-12">

                {{-- Buscador --}}
                <div class="lg:col-span-6">
                    <label
                        for="search"
                        class="mb-2 block text-sm font-bold text-slate-700">
                        Producto, SKU o código de barras
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
                            id="search"
                            name="search"
                            type="search"
                            :value="$search"
                            placeholder="Ej. Coca-Cola, COCA600 o 750..."
                            autocomplete="off"
                            class="pl-11" />
                    </div>
                </div>


                {{-- Categoría --}}
                <div class="lg:col-span-3">
                    <label
                        for="category_id"
                        class="mb-2 block text-sm font-bold text-slate-700">
                        Categoría
                    </label>

                    <select
                        id="category_id"
                        name="category_id"
                        class="app-input"
                        onchange="this.form.submit()">
                        <option value="">Todas las categorías</option>

                        @foreach ($categories as $category)
                        <option
                            value="{{ $category->id }}"
                            @selected($categoryId===$category->id)
                            >
                            {{ $category->name }}
                        </option>
                        @endforeach
                    </select>
                </div>


                {{-- Estado --}}
                <div class="lg:col-span-3">
                    <label
                        for="status"
                        class="mb-2 block text-sm font-bold text-slate-700">
                        Estado
                    </label>

                    <select
                        id="status"
                        name="status"
                        class="app-input"
                        onchange="this.form.submit()">
                        <option value="">Todos los estados</option>

                        <option
                            value="available"
                            @selected($stockStatus==='available' )>
                            Disponible
                        </option>

                        <option
                            value="low"
                            @selected($stockStatus==='low' )>
                            Stock bajo
                        </option>

                        <option
                            value="out"
                            @selected($stockStatus==='out' )>
                            Agotado
                        </option>
                    </select>
                </div>


                {{-- Acciones --}}
                @if ($search || $categoryId || $stockStatus)
                <div class="flex justify-end lg:col-span-12">
                    <a
                        href="{{ route('stock.index') }}"
                        class="inline-flex min-h-11 items-center justify-center rounded-xl px-4 text-sm font-bold text-rose-600 transition hover:bg-rose-50">
                        Limpiar filtros
                    </a>
                </div>
                @endif

            </form>
        </x-ui.card>


        {{-- =========================================================
             INVENTARIO
        ========================================================== --}}
        <x-ui.card padding="p-0" class="overflow-hidden">

            {{-- Encabezado de tabla --}}
            <div class="flex flex-col gap-3 border-b border-slate-200 bg-white px-5 py-5 sm:px-6 lg:flex-row lg:items-center lg:justify-between">

                <div>
                    <p class="text-base font-black text-slate-950">
                        Existencias registradas
                    </p>

                    <p class="mt-1 text-sm text-slate-500">
                        {{ $items->total() }} artículos encontrados
                    </p>
                </div>

                @if ($search || $categoryId || $stockStatus)
                <span class="inline-flex w-fit items-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">
                    Filtros activos
                </span>
                @endif

            </div>


            {{-- =====================================================
                 TABLA DESKTOP / TABLET
            ====================================================== --}}
            <div class="hidden overflow-x-auto lg:block">

                <table class="min-w-[980px] w-full text-left">

                    <caption class="sr-only">
                        Existencias de inventario
                    </caption>

                    <thead class="border-b border-slate-200 bg-slate-50">

                        <tr class="text-xs font-black uppercase tracking-[0.08em] text-slate-500">

                            <th scope="col" class="px-6 py-4">
                                Producto
                            </th>

                            <th scope="col" class="px-6 py-4">
                                Clasificación
                            </th>

                            <th scope="col" class="px-6 py-4 text-right">
                                Físico
                            </th>

                            <th scope="col" class="px-6 py-4 text-right">
                                Reservado
                            </th>

                            <th scope="col" class="px-6 py-4">
                                Disponible
                            </th>

                            <th scope="col" class="px-6 py-4 text-right">
                                Costo promedio
                            </th>

                            <th scope="col" class="px-6 py-4 text-right">
                                Acción
                            </th>

                        </tr>

                    </thead>


                    <tbody class="divide-y divide-slate-100">

                        @forelse ($items as $item)

                        @php
                        $balance = $item->inventoryBalance;
                        $reorder = $item->reorderLevel;

                        $unit = $item->inventoryUnit?->code ?? 'PZA';

                        $onHand = (float) ($balance?->on_hand_quantity ?? 0);
                        $reserved = (float) ($balance?->reserved_quantity ?? 0);

                        $available = (float) (
                        $balance?->available_quantity
                        ?? ($onHand - $reserved)
                        );

                        $available = max(0, $available);

                        $minQty = (float) ($reorder?->minimum_quantity ?? 0);
                        $cost = (float) ($balance?->weighted_average_cost ?? 0);
                        @endphp

                        <tr class="transition hover:bg-slate-50/70">

                            {{-- Producto --}}
                            <td class="px-6 py-5 align-middle">

                                <div class="max-w-sm">

                                    <p class="text-sm font-black text-slate-950">
                                        {{ $item->product?->name ?? 'Producto sin nombre' }}
                                    </p>

                                    <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1">

                                        <span class="font-mono text-xs font-semibold text-slate-500">
                                            SKU:
                                            {{ $item->product?->sku ?? 'N/A' }}
                                        </span>

                                        @php
                                        $primaryBarcode =
                                        $item->barcodes->firstWhere('is_primary', true)
                                        ?? $item->barcodes->first();
                                        @endphp

                                        @if ($primaryBarcode)
                                        <span class="font-mono text-xs text-slate-400">
                                            {{ $primaryBarcode->barcode }}
                                        </span>
                                        @endif

                                    </div>

                                </div>

                            </td>


                            {{-- Clasificación --}}
                            <td class="px-6 py-5 align-middle">

                                <span class="inline-flex items-center rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-bold text-slate-700">
                                    {{ $item->product?->category?->name ?? 'Sin categoría' }}
                                </span>

                                @if ($item->product?->brand)
                                <p class="mt-1.5 text-xs font-medium text-slate-500">
                                    {{ $item->product->brand->name }}
                                </p>
                                @endif

                            </td>


                            {{-- Físico --}}
                            <td class="px-6 py-5 text-right align-middle">

                                <p class="text-base font-black tabular-nums text-slate-950">
                                    {{ number_format($onHand, 2) }}
                                </p>

                                <p class="mt-0.5 text-xs font-semibold text-slate-400">
                                    {{ $unit }}
                                </p>

                            </td>


                            {{-- Reservado --}}
                            <td class="px-6 py-5 text-right align-middle">

                                <p class="text-base font-bold tabular-nums text-amber-700">
                                    {{ number_format($reserved, 2) }}
                                </p>

                                <p class="mt-0.5 text-xs font-semibold text-amber-500/70">
                                    {{ $unit }}
                                </p>

                            </td>


                            {{-- Disponible --}}
                            <td class="px-6 py-5 align-middle">

                                @if ($available <= 0)

                                    <div class="inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2">
                                    <span class="h-2 w-2 rounded-full bg-rose-500"></span>

                                    <span>
                                        <span class="block text-xs font-black text-rose-700">
                                            Agotado
                                        </span>

                                        <span class="block text-xs font-semibold text-rose-600">
                                            0 {{ $unit }}
                                        </span>
                                    </span>
            </div>

            @elseif ($minQty > 0 && $available <= $minQty)

                <div
                class="inline-flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2"
                title="Nivel mínimo: {{ number_format($minQty, 2) }} {{ $unit }}">
                <span class="h-2 w-2 rounded-full bg-amber-500"></span>

                <span>
                    <span class="block text-xs font-black text-amber-700">
                        Stock bajo
                    </span>

                    <span class="block text-xs font-bold text-amber-700 tabular-nums">
                        {{ number_format($available, 2) }} {{ $unit }}
                    </span>
                </span>
    </div>

    @else

    <div class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2">

        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>

        <span>
            <span class="block text-xs font-black text-emerald-700">
                Disponible
            </span>

            <span class="block text-xs font-bold text-emerald-700 tabular-nums">
                {{ number_format($available, 2) }} {{ $unit }}
            </span>
        </span>

    </div>

    @endif

    </td>


    {{-- Costo --}}
    <td class="px-6 py-5 text-right align-middle">

        <p class="text-sm font-black tabular-nums text-slate-800">
            ${{ number_format($cost, 2) }}
        </p>

        <p class="mt-0.5 text-xs font-medium text-slate-400">
            MXN
        </p>

    </td>


    {{-- Acción --}}
    <td class="px-6 py-5 text-right align-middle">

        <button
            type="button"
            onclick="openAdjustModal(
                                            @js($item->id),
                                            @js($item->product?->name ?? 'Producto'),
                                            @js(number_format($available, 2)),
                                            @js($unit)
                                        )"
            class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 text-sm font-bold text-slate-700 shadow-sm transition hover:border-slate-400 hover:bg-slate-50 hover:text-slate-950 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">
            Ajustar
        </button>

    </td>

    </tr>

    @empty

    <tr>
        <td colspan="7" class="px-6 py-16 text-center">

            <div class="mx-auto max-w-md">

                <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-slate-100 text-xl font-black text-slate-400">
                    ∅
                </div>

                <p class="mt-4 text-base font-black text-slate-900">
                    No encontramos existencias
                </p>

                <p class="mt-2 text-sm leading-6 text-slate-500">
                    No existen artículos que coincidan con los filtros seleccionados.
                </p>

                @if ($search || $categoryId || $stockStatus)
                <a
                    href="{{ route('stock.index') }}"
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
                 CARDS MOBILE
            ====================================================== --}}
    <div class="divide-y divide-slate-100 lg:hidden">

        @forelse ($items as $item)

        @php
        $balance = $item->inventoryBalance;
        $reorder = $item->reorderLevel;

        $unit = $item->inventoryUnit?->code ?? 'PZA';

        $onHand = (float) ($balance?->on_hand_quantity ?? 0);
        $reserved = (float) ($balance?->reserved_quantity ?? 0);

        $available = (float) (
        $balance?->available_quantity
        ?? ($onHand - $reserved)
        );

        $available = max(0, $available);

        $minQty = (float) ($reorder?->minimum_quantity ?? 0);
        $cost = (float) ($balance?->weighted_average_cost ?? 0);
        @endphp

        <article class="p-5">

            {{-- Producto --}}
            <div class="flex items-start justify-between gap-4">

                <div class="min-w-0">

                    <h3 class="truncate text-base font-black text-slate-950">
                        {{ $item->product?->name ?? 'Producto sin nombre' }}
                    </h3>

                    <p class="mt-1 font-mono text-xs font-semibold text-slate-500">
                        SKU:
                        {{ $item->product?->sku ?? 'N/A' }}
                    </p>

                </div>


                {{-- Estado --}}
                @if ($available <= 0)

                    <span class="shrink-0 rounded-full border border-rose-200 bg-rose-50 px-2.5 py-1 text-xs font-black text-rose-700">
                    Agotado
                    </span>

                    @elseif ($minQty > 0 && $available <= $minQty)

                        <span class="shrink-0 rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-black text-amber-700">
                        Stock bajo
                        </span>

                        @else

                        <span class="shrink-0 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-black text-emerald-700">
                            Disponible
                        </span>

                        @endif

            </div>


            {{-- Clasificación --}}
            <div class="mt-3 flex flex-wrap items-center gap-2">

                <span class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-700">
                    {{ $item->product?->category?->name ?? 'Sin categoría' }}
                </span>

                @if ($item->product?->brand)
                <span class="text-xs font-medium text-slate-500">
                    {{ $item->product->brand->name }}
                </span>
                @endif

            </div>


            {{-- Disponible principal --}}
            <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-4">

                <p class="text-xs font-black uppercase tracking-[0.1em] text-slate-500">
                    Disponible
                </p>

                <div class="mt-1 flex items-end gap-2">

                    <span class="text-3xl font-black tracking-tight text-slate-950 tabular-nums">
                        {{ number_format($available, 2) }}
                    </span>

                    <span class="pb-1 text-sm font-bold text-slate-500">
                        {{ $unit }}
                    </span>

                </div>

            </div>


            {{-- Detalles --}}
            <div class="mt-4 grid grid-cols-3 gap-3">

                <div>
                    <p class="text-xs font-semibold text-slate-400">
                        Físico
                    </p>

                    <p class="mt-1 text-sm font-black text-slate-800 tabular-nums">
                        {{ number_format($onHand, 2) }}
                    </p>
                </div>


                <div>
                    <p class="text-xs font-semibold text-slate-400">
                        Reservado
                    </p>

                    <p class="mt-1 text-sm font-black text-amber-700 tabular-nums">
                        {{ number_format($reserved, 2) }}
                    </p>
                </div>


                <div>
                    <p class="text-xs font-semibold text-slate-400">
                        Costo
                    </p>

                    <p class="mt-1 text-sm font-black text-slate-800 tabular-nums">
                        ${{ number_format($cost, 2) }}
                    </p>
                </div>

            </div>


            {{-- Acción --}}
            <button
                type="button"
                onclick="openAdjustModal(
                                @js($item->id),
                                @js($item->product?->name ?? 'Producto'),
                                @js(number_format($available, 2)),
                                @js($unit)
                            )"
                class="mt-5 flex min-h-12 w-full items-center justify-center rounded-xl bg-slate-900 px-4 text-sm font-black text-white shadow-sm transition hover:bg-slate-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">
                Ajustar existencia
            </button>

        </article>

        @empty

        <div class="px-5 py-16 text-center">

            <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-slate-100 text-xl font-black text-slate-400">
                ∅
            </div>

            <p class="mt-4 text-base font-black text-slate-900">
                No encontramos existencias
            </p>

            <p class="mt-2 text-sm leading-6 text-slate-500">
                No existen artículos que coincidan con los filtros seleccionados.
            </p>

        </div>

        @endforelse

    </div>


    {{-- =====================================================
                 PAGINACIÓN
            ====================================================== --}}
    @if ($items->hasPages())

    <div class="border-t border-slate-200 bg-slate-50/70 px-5 py-4 sm:px-6">
        {{ $items->links() }}
    </div>

    @endif

    </x-ui.card>

    {{-- =========================================================
     MODAL: AJUSTAR EXISTENCIA
========================================================= --}}

    <x-ui.modal
        id="adjustModal"
        size="md"
        title="Ajustar existencia"
        description="Registra una entrada, salida o carga inicial de inventario."
        close-id="close-adjust-modal">

        <form
            method="POST"
            action="{{ route('stock.adjust') }}"
            id="adjust-stock-form"
            class="flex min-h-0 flex-col">

            @csrf

            <input
                type="hidden"
                name="stock_item_id"
                id="modal_stock_item_id">


            {{-- BODY --}}
            <div class="space-y-5 px-5 py-5 sm:px-6">

                {{-- Producto seleccionado --}}
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">

                    <p class="text-xs font-black uppercase tracking-[0.12em] text-slate-400">
                        Producto seleccionado
                    </p>

                    <p
                        id="modal_item_name"
                        class="mt-1 text-base font-black text-slate-950 sm:text-lg">
                        -
                    </p>

                    <div class="mt-3 flex items-center justify-between border-t border-slate-200 pt-3">

                        <span class="text-sm font-bold text-slate-500">
                            Disponible actual
                        </span>

                        <span
                            id="modal_available"
                            class="text-base font-black tabular-nums text-slate-950">
                            -
                        </span>

                    </div>

                </div>


                {{-- Tipo de movimiento --}}
                <div>

                    <label
                        for="movement_type"
                        class="mb-2 block text-sm font-bold text-slate-700">
                        Tipo de movimiento
                    </label>

                    <select
                        id="movement_type"
                        name="movement_type"
                        required
                        class="app-input">

                        <option value="initial_load">
                            Carga inicial de stock
                        </option>

                        <option value="adjustment_in">
                            Entrada / ajuste (+)
                        </option>

                        <option value="adjustment_out">
                            Salida / merma (-)
                        </option>

                    </select>

                    <p class="mt-2 text-xs leading-5 text-slate-500">
                        Utiliza una entrada para aumentar la existencia y una salida
                        para disminuirla.
                    </p>

                </div>


                {{-- Cantidad / costo --}}
                <div class="grid gap-4 sm:grid-cols-2">

                    {{-- Cantidad --}}
                    <div>

                        <label
                            for="quantity"
                            class="mb-2 block text-sm font-bold text-slate-700">
                            Cantidad
                        </label>

                        <input
                            id="quantity"
                            type="number"
                            name="quantity"
                            step="0.000001"
                            min="0.000001"
                            inputmode="decimal"
                            required
                            class="app-input"
                            placeholder="0.00">

                    </div>


                    {{-- Costo --}}
                    <div>

                        <label
                            for="unit_cost"
                            class="mb-2 block text-sm font-bold text-slate-700">
                            Costo unitario
                        </label>

                        <div class="relative">

                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-sm font-bold text-slate-400">
                                $
                            </span>

                            <input
                                id="unit_cost"
                                type="number"
                                name="unit_cost"
                                step="0.01"
                                min="0"
                                inputmode="decimal"
                                class="app-input pl-8"
                                placeholder="0.00">

                        </div>

                        <p class="mt-2 text-xs text-slate-400">
                            Opcional
                        </p>

                    </div>

                </div>


                {{-- Notas --}}
                <div>

                    <label
                        for="stock_notes"
                        class="mb-2 block text-sm font-bold text-slate-700">
                        Motivo / notas
                    </label>

                    <textarea
                        id="stock_notes"
                        name="notes"
                        rows="4"
                        maxlength="500"
                        class="app-input min-h-28 resize-none"
                        placeholder="Ej. Inventario inicial, producto dañado, conteo físico..."></textarea>

                    <div class="mt-2 flex items-center justify-between gap-3">

                        <p class="text-xs text-slate-400">
                            Máximo 500 caracteres.
                        </p>

                        <span
                            id="stock-notes-counter"
                            class="text-xs font-semibold text-slate-400">
                            0 / 500
                        </span>

                    </div>

                </div>

            </div>


            {{-- FOOTER --}}
            <x-slot:footer>
                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

                    <button
                        type="button"
                        id="cancel-adjust-modal"
                        class="inline-flex min-h-12 items-center justify-center rounded-xl border border-slate-300 bg-white px-5 text-sm font-bold text-slate-700 transition hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-500">
                        Cancelar
                    </button>

                    <x-ui.button
                        type="submit"
                        form="adjust-stock-form"
                        variant="primary"
                        size="lg"
                        class="w-full sm:w-auto">
                        Guardar ajuste
                    </x-ui.button>

                </div>
            </x-slot:footer>

        </form>

    </x-ui.modal>


    {{-- =============================================================
         JAVASCRIPT
    ============================================================== --}}
    <script>
        function openAdjustModal(itemId, itemName, available, unit) {
            const modal = document.getElementById('adjustModal');

            const itemIdInput = document.getElementById('modal_stock_item_id');
            const itemNameElement = document.getElementById('modal_item_name');
            const availableElement = document.getElementById('modal_available');
            const quantityInput = document.getElementById('quantity');

            if (!modal || !itemIdInput || !itemNameElement) {
                return;
            }

            itemIdInput.value = itemId;
            itemNameElement.textContent = itemName;

            if (availableElement) {
                availableElement.textContent = `${available} ${unit}`;
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');

            modal.setAttribute('aria-hidden', 'false');

            document.body.classList.add('overflow-hidden');

            window.setTimeout(() => {
                quantityInput?.focus();
            }, 100);
        }


        function closeAdjustModal() {
            const modal = document.getElementById('adjustModal');

            if (!modal) {
                return;
            }

            modal.classList.add('hidden');
            modal.classList.remove('flex');

            modal.setAttribute('aria-hidden', 'true');

            document.body.classList.remove('overflow-hidden');

            resetAdjustForm();
        }


        function resetAdjustForm() {
            const form = document.getElementById('adjust-stock-form');

            if (!form) {
                return;
            }

            form.reset();

            const itemId = document.getElementById('modal_stock_item_id');
            const itemName = document.getElementById('modal_item_name');
            const available = document.getElementById('modal_available');

            if (itemId) {
                itemId.value = '';
            }

            if (itemName) {
                itemName.textContent = '-';
            }

            if (available) {
                available.textContent = '-';
            }

            updateNotesCounter();
        }


        function updateNotesCounter() {
            const textarea = document.getElementById('stock_notes');
            const counter = document.getElementById('stock-notes-counter');

            if (!textarea || !counter) {
                return;
            }

            counter.textContent = `${textarea.value.length} / 500`;
        }


        document.addEventListener('DOMContentLoaded', function() {

            const closeButton = document.getElementById('close-adjust-modal');
            const cancelButton = document.getElementById('cancel-adjust-modal');
            const modal = document.getElementById('adjustModal');
            const notes = document.getElementById('stock_notes');

            closeButton?.addEventListener('click', closeAdjustModal);

            cancelButton?.addEventListener('click', closeAdjustModal);

            notes?.addEventListener('input', updateNotesCounter);

            updateNotesCounter();


            document.addEventListener('keydown', function(event) {

                if (event.key !== 'Escape') {
                    return;
                }

                if (modal && !modal.classList.contains('hidden')) {
                    closeAdjustModal();
                }

            });

        });
    </script>

</x-layouts.app>
