<x-layouts.app title="Proveedores | ABARROTESBASE">

    <div class="space-y-6">

        {{-- =========================================================
             HEADER
        ========================================================== --}}
        <x-layout.page-header
            eyebrow="Catálogo"
            title="Proveedores"
            description="Administra las empresas y distribuidores utilizados para abastecer el inventario.">
            <x-slot:actions>
                <a
                    href="{{ route('suppliers.create') }}"
                    class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">
                    <span class="text-lg leading-none">+</span>
                    Nuevo proveedor
                </a>
            </x-slot:actions>
        </x-layout.page-header>


        {{-- =========================================================
             FLASH MESSAGE
        ========================================================== --}}
        @if (session('success'))
        <div
            class="flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-4 text-sm font-semibold text-emerald-800"
            role="status">
            <span class="mt-0.5 grid h-5 w-5 shrink-0 place-items-center rounded-full bg-emerald-600 text-xs font-black text-white">
                ✓
            </span>

            <span>{{ session('success') }}</span>
        </div>
        @endif


        {{-- =========================================================
             VALIDATION ERRORS
        ========================================================== --}}
        @if ($errors->any())
        <div
            class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4"
            role="alert">
            <p class="text-sm font-black text-rose-900">
                No se pudo completar la operación.
            </p>

            <ul class="mt-2 space-y-1 text-sm font-medium text-rose-700">
                @foreach ($errors->all() as $error)
                <li>• {{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif


        {{-- =========================================================
             RESUMEN
        ========================================================== --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">

            <x-ui.card padding="p-5" class="relative overflow-hidden">

                <div class="absolute inset-y-0 left-0 w-1 bg-slate-400"></div>

                <p class="text-xs font-black uppercase tracking-[0.12em] text-slate-500">
                    Proveedores registrados
                </p>

                <p class="mt-2 text-3xl font-black tracking-tight text-slate-950">
                    {{ number_format($suppliers->total()) }}
                </p>

                <p class="mt-1 text-sm font-medium text-slate-500">
                    Coincidencias del catálogo
                </p>

            </x-ui.card>


            <x-ui.card padding="p-5" class="relative overflow-hidden">

                <div class="absolute inset-y-0 left-0 w-1 bg-emerald-500"></div>

                <p class="text-xs font-black uppercase tracking-[0.12em] text-slate-500">
                    Página actual
                </p>

                <p class="mt-2 text-3xl font-black tracking-tight text-slate-950">
                    {{ number_format($suppliers->count()) }}
                </p>

                <p class="mt-1 text-sm font-medium text-slate-500">
                    Proveedores mostrados
                </p>

            </x-ui.card>


            <x-ui.card padding="p-5" class="relative overflow-hidden sm:col-span-2 lg:col-span-1">

                <div class="absolute inset-y-0 left-0 w-1 bg-sky-500"></div>

                <p class="text-xs font-black uppercase tracking-[0.12em] text-slate-500">
                    Vista
                </p>

                <p class="mt-2 text-2xl font-black tracking-tight text-slate-950">
                    Catálogo
                </p>

                <p class="mt-1 text-sm font-medium text-slate-500">
                    Ordenado por razón social
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
                    Localiza proveedores por razón social, código, RFC o contacto.
                </p>

            </div>


            <form
                method="GET"
                action="{{ route('suppliers.index') }}"
                class="grid gap-4 lg:grid-cols-12">

                {{-- Buscador --}}
                <div class="lg:col-span-8">

                    <label
                        for="search"
                        class="mb-2 block text-sm font-bold text-slate-700">
                        Proveedor, código, RFC o contacto
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
                            placeholder="Ej. Coca-Cola, PRV-0001, RFC o Carlos..."
                            autocomplete="off"
                            class="pl-11" />

                    </div>

                </div>


                {{-- Estado --}}
                <div class="lg:col-span-4">

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
                        <option value="">
                            Todos los estados
                        </option>

                        <option
                            value="active"
                            @selected($status==='active' )>
                            Activos
                        </option>

                        <option
                            value="inactive"
                            @selected($status==='inactive' )>
                            Inactivos
                        </option>
                    </select>

                </div>


                {{-- Acciones --}}
                @if ($search || $status)
                <div class="flex justify-end lg:col-span-12">

                    <a
                        href="{{ route('suppliers.index') }}"
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

            {{-- Header --}}
            <div class="flex flex-col gap-3 border-b border-slate-200 bg-white px-5 py-5 sm:px-6 lg:flex-row lg:items-center lg:justify-between">

                <div>

                    <p class="text-base font-black text-slate-950">
                        Proveedores registrados
                    </p>

                    <p class="mt-1 text-sm text-slate-500">
                        {{ number_format($suppliers->total()) }} proveedores encontrados
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
                        Catálogo de proveedores
                    </caption>

                    <thead class="border-b border-slate-200 bg-slate-50">

                        <tr class="text-xs font-black uppercase tracking-[0.08em] text-slate-500">

                            <th scope="col" class="px-6 py-4">
                                Proveedor
                            </th>

                            <th scope="col" class="px-6 py-4">
                                RFC
                            </th>

                            <th scope="col" class="px-6 py-4">
                                Contacto
                            </th>

                            <th scope="col" class="px-6 py-4 text-center">
                                Crédito
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

                        @forelse ($suppliers as $supplier)

                        <tr class="transition hover:bg-slate-50/70">

                            {{-- Proveedor --}}
                            <td class="px-6 py-5 align-middle">

                                <div class="max-w-md">

                                    <p class="text-sm font-black text-slate-950">
                                        {{ $supplier->business_name }}
                                    </p>

                                    <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1">

                                        <span class="font-mono text-xs font-semibold text-slate-500">
                                            {{ $supplier->code }}
                                        </span>

                                        @if ($supplier->address)
                                        <span class="text-xs text-slate-400">
                                            Dirección registrada
                                        </span>
                                        @endif

                                    </div>

                                </div>

                            </td>


                            {{-- RFC --}}
                            <td class="px-6 py-5 align-middle">

                                @if ($supplier->rfc)

                                <span class="font-mono text-xs font-bold text-slate-700">
                                    {{ $supplier->rfc }}
                                </span>

                                @else

                                <span class="text-xs font-medium text-slate-400">
                                    No registrado
                                </span>

                                @endif

                            </td>


                            {{-- Contacto --}}
                            <td class="px-6 py-5 align-middle">

                                <p class="text-sm font-bold text-slate-800">
                                    {{ $supplier->contact_name ?: 'Sin contacto' }}
                                </p>

                                <div class="mt-1 flex flex-wrap gap-x-3 gap-y-1 text-xs text-slate-400">

                                    @if ($supplier->phone)
                                    <span>{{ $supplier->phone }}</span>
                                    @endif

                                    @if ($supplier->email)
                                    <span>{{ $supplier->email }}</span>
                                    @endif

                                    @if (!$supplier->phone && !$supplier->email)
                                    <span>Sin datos de contacto</span>
                                    @endif

                                </div>

                            </td>


                            {{-- Crédito --}}
                            <td class="px-6 py-5 text-center align-middle">

                                @if ((int) $supplier->payment_terms_days > 0)

                                <span class="inline-flex items-center rounded-full border border-sky-200 bg-sky-50 px-3 py-1.5 text-xs font-black text-sky-700">
                                    {{ $supplier->payment_terms_days }} días
                                </span>

                                @else

                                <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-600">
                                    Contado
                                </span>

                                @endif

                            </td>


                            {{-- Estado --}}
                            <td class="px-6 py-5 text-center align-middle">

                                @if ($supplier->is_active)

                                <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-black text-emerald-700">

                                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>

                                    Activo

                                </span>

                                @else

                                <span class="inline-flex items-center gap-2 rounded-full border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-black text-rose-700">

                                    <span class="h-2 w-2 rounded-full bg-rose-500"></span>

                                    Inactivo

                                </span>

                                @endif

                            </td>


                            {{-- Acción --}}
                            <td class="px-6 py-5 text-right align-middle">

                                <button
                                    type="button"
                                    onclick="openSupplierEditModal(
                                            @js($supplier->id),
                                            @js($supplier->code),
                                            @js($supplier->business_name),
                                            @js($supplier->rfc),
                                            @js($supplier->contact_name),
                                            @js($supplier->phone),
                                            @js($supplier->email),
                                            @js($supplier->payment_terms_days),
                                            @js($supplier->address),
                                            @js((bool) $supplier->is_active)
                                        )"
                                    class="inline-flex min-h-10 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 text-sm font-bold text-slate-700 shadow-sm transition hover:border-slate-400 hover:bg-slate-50 hover:text-slate-950 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">
                                    Editar
                                </button>

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
                                        No encontramos proveedores
                                    </p>

                                    <p class="mt-2 text-sm leading-6 text-slate-500">
                                        No existen proveedores que coincidan con los filtros seleccionados.
                                    </p>

                                    @if ($search || $status)

                                    <a
                                        href="{{ route('suppliers.index') }}"
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

                @forelse ($suppliers as $supplier)

                <article class="p-5">

                    {{-- Cabecera --}}
                    <div class="flex items-start justify-between gap-4">

                        <div class="min-w-0">

                            <h2 class="text-base font-black text-slate-950">
                                {{ $supplier->business_name }}
                            </h2>

                            <p class="mt-1 font-mono text-xs font-semibold text-slate-500">
                                {{ $supplier->code }}
                            </p>

                        </div>


                        @if ($supplier->is_active)

                        <span class="shrink-0 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-black text-emerald-700">
                            Activo
                        </span>

                        @else

                        <span class="shrink-0 rounded-full border border-rose-200 bg-rose-50 px-2.5 py-1 text-xs font-black text-rose-700">
                            Inactivo
                        </span>

                        @endif

                    </div>


                    {{-- RFC --}}
                    <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-4">

                        <p class="text-xs font-black uppercase tracking-[0.1em] text-slate-400">
                            RFC
                        </p>

                        <p class="mt-1 font-mono text-sm font-bold text-slate-800">
                            {{ $supplier->rfc ?: 'No registrado' }}
                        </p>

                    </div>


                    {{-- Contacto --}}
                    <div class="mt-4 grid grid-cols-2 gap-4">

                        <div>

                            <p class="text-xs font-semibold text-slate-400">
                                Contacto
                            </p>

                            <p class="mt-1 text-sm font-bold text-slate-800">
                                {{ $supplier->contact_name ?: 'Sin contacto' }}
                            </p>

                        </div>


                        <div>

                            <p class="text-xs font-semibold text-slate-400">
                                Teléfono
                            </p>

                            <p class="mt-1 text-sm font-bold text-slate-800">
                                {{ $supplier->phone ?: 'No registrado' }}
                            </p>

                        </div>

                    </div>


                    {{-- Email --}}
                    @if ($supplier->email)

                    <div class="mt-4">

                        <p class="text-xs font-semibold text-slate-400">
                            Correo
                        </p>

                        <p class="mt-1 break-all text-sm font-bold text-slate-800">
                            {{ $supplier->email }}
                        </p>

                    </div>

                    @endif


                    {{-- Crédito --}}
                    <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-4">

                        <div>

                            <p class="text-xs font-semibold text-slate-400">
                                Condición de pago
                            </p>

                            <p class="mt-1 text-sm font-black text-slate-800">

                                @if ((int) $supplier->payment_terms_days > 0)
                                Crédito a {{ $supplier->payment_terms_days }} días
                                @else
                                Contado
                                @endif

                            </p>

                        </div>

                    </div>


                    {{-- Acción --}}
                    <button
                        type="button"
                        onclick="openSupplierEditModal(
                                @js($supplier->id),
                                @js($supplier->code),
                                @js($supplier->business_name),
                                @js($supplier->rfc),
                                @js($supplier->contact_name),
                                @js($supplier->phone),
                                @js($supplier->email),
                                @js($supplier->payment_terms_days),
                                @js($supplier->address),
                                @js((bool) $supplier->is_active)
                            )"
                        class="mt-5 flex min-h-12 w-full items-center justify-center rounded-xl bg-slate-900 px-4 text-sm font-black text-white shadow-sm transition hover:bg-slate-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">
                        Editar proveedor
                    </button>

                </article>

                @empty

                <div class="px-5 py-16 text-center">

                    <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-slate-100 text-xl font-black text-slate-400">
                        ∅
                    </div>

                    <p class="mt-4 text-base font-black text-slate-900">
                        No encontramos proveedores
                    </p>

                    <p class="mt-2 text-sm leading-6 text-slate-500">
                        No existen proveedores que coincidan con los filtros seleccionados.
                    </p>

                </div>

                @endforelse

            </div>


            {{-- =====================================================
                 PAGINACIÓN
            ====================================================== --}}
            @if ($suppliers->hasPages())

            <div class="border-t border-slate-200 bg-slate-50/70 px-5 py-4 sm:px-6">
                {{ $suppliers->links() }}
            </div>

            @endif

        </x-ui.card>


        {{-- =========================================================
             MODAL EDITAR PROVEEDOR
        ========================================================== --}}
        <x-ui.modal
            id="supplierEditModal"
            size="lg"
            title="Editar proveedor"
            description="Actualiza la información comercial, fiscal y de contacto del proveedor."
            close-id="close-supplier-edit-modal">

            {{-- FORM --}}
            <form
                id="supplier-edit-form"
                method="POST"
                action="">
                @csrf

                @method('PUT')

                <input
                    type="hidden"
                    name="supplier_id"
                    id="supplier_edit_id">


                <div class="space-y-6 px-5 py-5 sm:px-6">

                    {{-- =================================================
                         IDENTIFICACIÓN
                    ================================================== --}}
                    <div>

                        <div class="mb-4">

                            <p class="text-xs font-black uppercase tracking-[0.12em] text-emerald-700">
                                Identificación
                            </p>

                            <p class="mt-1 text-sm font-black text-slate-950">
                                Datos generales
                            </p>

                        </div>


                        <div class="grid gap-4 sm:grid-cols-2">

                            {{-- Código --}}
                            <div>

                                <label
                                    for="supplier_edit_code"
                                    class="mb-2 block text-sm font-bold text-slate-700">
                                    Código
                                </label>

                                <x-ui.input
                                    id="supplier_edit_code"
                                    name="code"
                                    type="text"
                                    required
                                    autocomplete="off" />

                            </div>


                            {{-- RFC --}}
                            <div>

                                <label
                                    for="supplier_edit_rfc"
                                    class="mb-2 block text-sm font-bold text-slate-700">
                                    RFC
                                </label>

                                <x-ui.input
                                    id="supplier_edit_rfc"
                                    name="rfc"
                                    type="text"
                                    maxlength="20"
                                    autocomplete="off"
                                    class="uppercase" />

                            </div>


                            {{-- Razón social --}}
                            <div class="sm:col-span-2">

                                <label
                                    for="supplier_edit_business_name"
                                    class="mb-2 block text-sm font-bold text-slate-700">
                                    Razón social / nombre comercial
                                    <span class="text-rose-600">*</span>
                                </label>

                                <x-ui.input
                                    id="supplier_edit_business_name"
                                    name="business_name"
                                    type="text"
                                    required
                                    autocomplete="organization" />

                            </div>

                        </div>

                    </div>


                    {{-- =================================================
                         CONTACTO
                    ================================================== --}}
                    <div class="border-t border-slate-200 pt-6">

                        <div class="mb-4">

                            <p class="text-xs font-black uppercase tracking-[0.12em] text-sky-700">
                                Contacto
                            </p>

                            <p class="mt-1 text-sm font-black text-slate-950">
                                Comunicación y condiciones comerciales
                            </p>

                        </div>


                        <div class="grid gap-4 sm:grid-cols-2">

                            {{-- Contacto --}}
                            <div>

                                <label
                                    for="supplier_edit_contact_name"
                                    class="mb-2 block text-sm font-bold text-slate-700">
                                    Vendedor / agente
                                </label>

                                <x-ui.input
                                    id="supplier_edit_contact_name"
                                    name="contact_name"
                                    type="text"
                                    autocomplete="name" />

                            </div>


                            {{-- Teléfono --}}
                            <div>

                                <label
                                    for="supplier_edit_phone"
                                    class="mb-2 block text-sm font-bold text-slate-700">
                                    Teléfono
                                </label>

                                <x-ui.input
                                    id="supplier_edit_phone"
                                    name="phone"
                                    type="tel"
                                    autocomplete="tel" />

                            </div>


                            {{-- Email --}}
                            <div>

                                <label
                                    for="supplier_edit_email"
                                    class="mb-2 block text-sm font-bold text-slate-700">
                                    Correo electrónico
                                </label>

                                <x-ui.input
                                    id="supplier_edit_email"
                                    name="email"
                                    type="email"
                                    autocomplete="email" />

                            </div>


                            {{-- Crédito --}}
                            <div>

                                <label
                                    for="supplier_edit_payment_terms_days"
                                    class="mb-2 block text-sm font-bold text-slate-700">
                                    Días de crédito
                                    <span class="text-rose-600">*</span>
                                </label>

                                <x-ui.input
                                    id="supplier_edit_payment_terms_days"
                                    name="payment_terms_days"
                                    type="number"
                                    min="0"
                                    max="365"
                                    step="1"
                                    required />

                            </div>


                            {{-- Dirección --}}
                            <div class="sm:col-span-2">

                                <label
                                    for="supplier_edit_address"
                                    class="mb-2 block text-sm font-bold text-slate-700">
                                    Dirección de bodega / entrega
                                </label>

                                <textarea
                                    id="supplier_edit_address"
                                    name="address"
                                    rows="3"
                                    maxlength="1000"
                                    class="app-input min-h-28 resize-none"
                                    placeholder="Dirección utilizada para recibir mercancía..."></textarea>

                            </div>

                        </div>

                    </div>


                    {{-- =================================================
                         ESTADO
                    ================================================== --}}
                    <div class="border-t border-slate-200 pt-6">

                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">

                            <div class="flex items-start justify-between gap-4">

                                <div>

                                    <p class="text-sm font-black text-slate-900">
                                        Estado del proveedor
                                    </p>

                                    <p class="mt-1 text-xs leading-5 text-slate-500">
                                        Los proveedores inactivos no deberían aparecer como opción para nuevas compras.
                                    </p>

                                </div>


                                <label class="relative inline-flex shrink-0 cursor-pointer items-center">

                                    <input
                                        type="hidden"
                                        name="is_active"
                                        value="0">

                                    <input
                                        type="checkbox"
                                        id="supplier_edit_is_active"
                                        name="is_active"
                                        value="1"
                                        class="peer sr-only">

                                    <span class="h-7 w-12 rounded-full bg-slate-300 transition peer-checked:bg-emerald-600 peer-focus-visible:outline peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-emerald-600"></span>

                                    <span class="absolute left-1 top-1 h-5 w-5 rounded-full bg-white shadow-sm transition peer-checked:translate-x-5"></span>

                                </label>

                            </div>

                        </div>

                    </div>

                </div>

            </form>


            {{-- =====================================================
                 FOOTER
            ====================================================== --}}
            <x-slot:footer>

                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                    {{-- Eliminar --}}
                    <form
                        id="supplier-delete-form"
                        method="POST"
                        action="">
                        @csrf
                        @method('DELETE')

                        <button
                            type="button"
                            id="open-delete-supplier-confirm"
                            class="inline-flex min-h-11 w-full items-center justify-center rounded-xl border border-rose-200 bg-rose-50 px-4 text-sm font-bold text-rose-700 transition hover:border-rose-300 hover:bg-rose-100 hover:text-rose-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-rose-600 sm:w-auto">
                            Eliminar proveedor
                        </button>
                    </form>


                    {{-- Acciones principales --}}
                    <div class="flex flex-col-reverse gap-3 sm:flex-row">

                        <button
                            type="button"
                            id="cancel-supplier-edit-modal"
                            class="inline-flex min-h-12 items-center justify-center rounded-xl border border-slate-300 bg-white px-5 text-sm font-bold text-slate-700 transition hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-500">
                            Cancelar
                        </button>

                        <x-ui.button
                            type="submit"
                            form="supplier-edit-form"
                            variant="primary"
                            size="lg">
                            Guardar cambios
                        </x-ui.button>

                    </div>

                </div>

            </x-slot:footer>

        </x-ui.modal>

        <x-ui.confirm
            id="deleteSupplierConfirm"
            size="sm"
            title="Eliminar proveedor"
            description="Esta acción eliminará el proveedor del catálogo."
            confirm-text="Eliminar proveedor"
            cancel-text="Cancelar"
            variant="danger"
            confirm-id="confirm-delete-supplier"
            cancel-id="cancel-delete-supplier"
            close-id="close-delete-supplier">
            <p class="text-sm leading-6 text-slate-600">
                ¿Estás seguro de que deseas eliminar este proveedor?
            </p>

            <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">

                <p class="text-xs font-black uppercase tracking-[0.1em] text-slate-400">
                    Proveedor seleccionado
                </p>

                <p
                    id="delete_supplier_name"
                    class="mt-1 text-sm font-black text-slate-900">
                    -
                </p>

            </div>

            <p class="mt-4 text-xs leading-5 text-slate-500">
                El proveedor dejará de estar disponible en el catálogo.
                Si existen compras relacionadas, sus registros históricos deberán conservarse.
            </p>
        </x-ui.confirm>


    </div>


    {{-- =============================================================
         JAVASCRIPT
    ============================================================== --}}
    <script>
        const deleteSupplierButton =
            document.getElementById('open-delete-supplier-confirm');

        const deleteSupplierForm =
            document.getElementById('supplier-delete-form');

        const deleteConfirmModal =
            document.getElementById('deleteSupplierConfirm');

        const confirmDeleteSupplier =
            document.getElementById('confirm-delete-supplier');

        const cancelDeleteSupplier =
            document.getElementById('cancel-delete-supplier');

        const closeDeleteSupplier =
            document.getElementById('close-delete-supplier');


        function openDeleteSupplierConfirm() {

            if (!deleteConfirmModal) {
                return;
            }

            deleteConfirmModal.classList.remove('hidden');
            deleteConfirmModal.classList.add('flex');

            deleteConfirmModal.setAttribute('aria-hidden', 'false');

            document.body.classList.add('overflow-hidden');
        }


        function closeDeleteSupplierConfirm() {

            if (!deleteConfirmModal) {
                return;
            }

            deleteConfirmModal.classList.add('hidden');
            deleteConfirmModal.classList.remove('flex');

            deleteConfirmModal.setAttribute('aria-hidden', 'true');

            document.body.classList.remove('overflow-hidden');
        }


        deleteSupplierButton?.addEventListener(
            'click',
            function() {

                /*
                 * Cerramos primero el modal de edición.
                 */
                closeSupplierEditModal();

                /*
                 * Abrimos confirmación.
                 */
                openDeleteSupplierConfirm();
            }
        );


        confirmDeleteSupplier?.addEventListener(
            'click',
            function() {

                if (!deleteSupplierForm) {
                    return;
                }

                const action = deleteSupplierForm.getAttribute('action');

                if (!action) {
                    console.error('El formulario de eliminación no tiene action.');

                    return;
                }

                confirmDeleteSupplier.disabled = true;
                confirmDeleteSupplier.textContent = 'Eliminando...';

                deleteSupplierForm.submit();
            }
        );


        cancelDeleteSupplier?.addEventListener(
            'click',
            closeDeleteSupplierConfirm
        );


        closeDeleteSupplier?.addEventListener(
            'click',
            closeDeleteSupplierConfirm
        );


        deleteConfirmModal?.addEventListener(
            'click',
            function(event) {

                if (event.target === deleteConfirmModal) {
                    closeDeleteSupplierConfirm();
                }

            }
        );

        function openSupplierEditModal(
            id,
            code,
            businessName,
            rfc,
            contactName,
            phone,
            email,
            paymentTermsDays,
            address,
            isActive
        ) {

            const modal = document.getElementById('supplierEditModal');
            const form = document.getElementById('supplier-edit-form');

            if (!modal || !form) {
                return;
            }


            /*
             * Acción del formulario.
             *
             * Ejemplo:
             * /suppliers/uuid-del-proveedor
             */
            form.action = `{{ route('suppliers.update', '__SUPPLIER__') }}`
                .replace('__SUPPLIER__', id);

            const deleteForm = document.getElementById('supplier-delete-form');

            if (deleteForm) {
                deleteForm.action = `{{ route('suppliers.destroy', '__SUPPLIER__') }}`
                    .replace('__SUPPLIER__', id);
            }

            const deleteSupplierName =
                document.getElementById('delete_supplier_name');

            if (deleteSupplierName) {
                deleteSupplierName.textContent = businessName ?? 'Proveedor';
            }

            /*
             * Identificador
             */
            document.getElementById('supplier_edit_id').value = id;


            /*
             * Campos
             */
            document.getElementById('supplier_edit_code').value =
                code ?? '';

            document.getElementById('supplier_edit_business_name').value =
                businessName ?? '';

            document.getElementById('supplier_edit_rfc').value =
                rfc ?? '';

            document.getElementById('supplier_edit_contact_name').value =
                contactName ?? '';

            document.getElementById('supplier_edit_phone').value =
                phone ?? '';

            document.getElementById('supplier_edit_email').value =
                email ?? '';

            document.getElementById('supplier_edit_payment_terms_days').value =
                paymentTermsDays ?? 0;

            document.getElementById('supplier_edit_address').value =
                address ?? '';

            document.getElementById('supplier_edit_is_active').checked =
                Boolean(isActive);


            /*
             * Mostrar modal
             */
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            modal.setAttribute('aria-hidden', 'false');

            document.body.classList.add('overflow-hidden');


            /*
             * Enfocar razón social
             */
            window.setTimeout(() => {
                document.getElementById('supplier_edit_business_name')?.focus();
            }, 100);
        }


        function closeSupplierEditModal() {

            const modal = document.getElementById('supplierEditModal');

            if (!modal) {
                return;
            }

            modal.classList.add('hidden');
            modal.classList.remove('flex');

            modal.setAttribute('aria-hidden', 'true');

            document.body.classList.remove('overflow-hidden');
        }


        document.addEventListener('DOMContentLoaded', function() {

            const modal = document.getElementById('supplierEditModal');

            const closeButton =
                document.getElementById('close-supplier-edit-modal');

            const cancelButton =
                document.getElementById('cancel-supplier-edit-modal');


            closeButton?.addEventListener(
                'click',
                closeSupplierEditModal
            );


            cancelButton?.addEventListener(
                'click',
                closeSupplierEditModal
            );


            /*
             * Cerrar haciendo click sobre el fondo.
             */
            modal?.addEventListener('click', function(event) {

                if (event.target === modal) {
                    closeSupplierEditModal();
                }

            });


            /*
             * Cerrar con Escape.
             */
            document.addEventListener('keydown', function(event) {

                if (event.key !== 'Escape') {
                    return;
                }

                if (modal && !modal.classList.contains('hidden')) {
                    closeSupplierEditModal();
                }

                if (
                    deleteConfirmModal &&
                    !deleteConfirmModal.classList.contains('hidden')
                ) {
                    closeDeleteSupplierConfirm();
                    return;
                }

            });

        });
    </script>

</x-layouts.app>
