<x-layouts.app title="Caja | ABARROTESBASE">

    <x-layout.page-header
        eyebrow="Operación"
        title="Caja"
        description="Administra la sesión de caja, movimientos, arqueo y cierre de operación."
    >
        <x-slot:actions>

            <a
                href="{{ route('cash.history') }}"
                class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-50"
            >
                Historial
            </a>

            @if ($activeSession)

                <span class="inline-flex items-center gap-2 rounded-xl bg-emerald-50 px-3 py-2 text-xs font-bold text-emerald-700">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>

                    @if ($activeSession->status === 'counting')
                        En arqueo
                    @else
                        Sesión abierta
                    @endif
                </span>

            @endif

        </x-slot:actions>
    </x-layout.page-header>


    {{-- Mensaje de éxito --}}
    @if (session('success'))

        <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
            {{ session('success') }}
        </div>

    @endif


    {{-- Errores --}}
    @if ($errors->any())

        <div class="mt-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">

            <p class="font-bold">
                No fue posible realizar la operación.
            </p>

            <ul class="mt-2 list-disc pl-5">

                @foreach ($errors->all() as $error)

                    <li>{{ $error }}</li>

                @endforeach

            </ul>

        </div>

    @endif


    @if (!$activeSession)

        {{-- Sin sesión --}}
        <x-ui.card class="mt-6">

            <div class="mx-auto max-w-xl py-12 text-center">

                <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-emerald-50 text-xl font-black text-emerald-700">
                    $
                </div>

                <h2 class="mt-5 text-xl font-black text-slate-900">
                    No hay una sesión de caja abierta
                </h2>

                <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500">
                    Abre una sesión para comenzar a registrar ventas,
                    ingresos, retiros y demás movimientos de efectivo.
                </p>

                <button
                    type="button"
                    id="open-cash-modal"
                    class="mt-6 inline-flex items-center justify-center rounded-xl bg-emerald-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-700"
                >
                    Abrir caja
                </button>

            </div>

        </x-ui.card>

    @else

        {{-- Estado de arqueo --}}
        @if ($activeSession->status === 'counting')

            <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4">

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

                    <div>
                        <p class="text-sm font-black text-amber-900">
                            Caja en proceso de arqueo
                        </p>

                        <p class="mt-1 text-sm text-amber-800">
                            La operación está congelada. Cuenta físicamente el efectivo
                            antes de cerrar la sesión.
                        </p>
                    </div>

                    <span class="shrink-0 rounded-lg bg-amber-100 px-3 py-2 text-xs font-bold text-amber-800">
                        Sin nuevos movimientos
                    </span>

                </div>

            </div>

        @endif


        {{-- Resumen --}}
        <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">

            <x-ui.card>

                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                    Fondo inicial
                </p>

                <p class="mt-2 text-2xl font-black text-slate-900">
                    ${{ number_format((float) $activeSession->opening_float, 2) }}
                </p>

                <p class="mt-1 text-xs text-slate-500">
                    Efectivo de apertura
                </p>

            </x-ui.card>


            <x-ui.card>

                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                    Entradas
                </p>

                <p class="mt-2 text-2xl font-black text-emerald-600">
                    +${{ number_format($cashIn, 2) }}
                </p>

                <p class="mt-1 text-xs text-slate-500">
                    Dinero ingresado a caja
                </p>

            </x-ui.card>


            <x-ui.card>

                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                    Salidas
                </p>

                <p class="mt-2 text-2xl font-black text-rose-600">
                    -${{ number_format($cashOut, 2) }}
                </p>

                <p class="mt-1 text-xs text-slate-500">
                    Dinero retirado de caja
                </p>

            </x-ui.card>


            <x-ui.card>

                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                    Efectivo esperado
                </p>

                <p class="mt-2 text-2xl font-black text-slate-900">
                    ${{ number_format($theoreticalCash, 2) }}
                </p>

                <p class="mt-1 text-xs text-slate-500">
                    Según movimientos registrados
                </p>

            </x-ui.card>

        </div>


        {{-- Acciones --}}
        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-end">

            @if ($activeSession->status === 'open')

                <button
                    type="button"
                    id="open-movement-modal"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50"
                >
                    + Movimiento
                </button>

                <button
                    type="button"
                    id="open-counting-modal"
                    class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-5 py-3 text-sm font-bold text-white transition hover:bg-slate-800"
                >
                    Hacer corte
                </button>

            @elseif ($activeSession->status === 'counting')

                <button
                    type="button"
                    id="open-close-modal"
                    class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-700"
                >
                    Finalizar corte
                </button>

            @endif

        </div>


        {{-- Movimientos --}}
        <x-ui.card class="mt-6" padding="p-0">

            <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">

                <div>

                    <h2 class="font-bold text-slate-900">
                        Movimientos
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Movimientos registrados durante esta sesión.
                    </p>

                </div>

                <span class="text-xs font-semibold text-slate-400">
                    {{ $movements->count() }} movimientos
                </span>

            </div>


            <div class="overflow-x-auto">

                <table class="w-full text-left text-sm">

                    <thead class="border-b border-slate-200 bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500">

                        <tr>

                            <th class="px-5 py-3">
                                Fecha
                            </th>

                            <th class="px-5 py-3">
                                Movimiento
                            </th>

                            <th class="px-5 py-3">
                                Notas
                            </th>

                            <th class="px-5 py-3 text-right">
                                Importe
                            </th>

                        </tr>

                    </thead>

                    <tbody class="divide-y divide-slate-100">

                        @forelse ($movements as $movement)

                            @php

                                $isOut = in_array($movement->movement_type, [
                                    'sale_change',
                                    'return_payment',
                                    'expense',
                                    'withdrawal',
                                    'deposit',
                                ], true);

                            @endphp

                            <tr class="hover:bg-slate-50">

                                <td class="px-5 py-4 text-xs text-slate-500">
                                    {{ $movement->occurred_at->format('d/m/Y H:i') }}
                                </td>

                                <td class="px-5 py-4">

                                    <span class="font-semibold text-slate-800">

                                        {{ match ($movement->movement_type) {

                                            'opening_float' => 'Fondo inicial',
                                            'sale_payment' => 'Pago de venta',
                                            'sale_change' => 'Cambio entregado',
                                            'return_payment' => 'Devolución',
                                            'expense' => 'Gasto',
                                            'withdrawal' => 'Retiro',
                                            'income' => 'Ingreso',
                                            'deposit' => 'Depósito bancario',
                                            'closing_adjustment' => 'Ajuste de cierre',

                                            default => $movement->movement_type,

                                        } }}

                                    </span>

                                </td>

                                <td class="px-5 py-4 text-xs text-slate-500">
                                    {{ $movement->notes ?: 'Sin notas' }}
                                </td>

                                <td class="px-5 py-4 text-right font-black {{ $isOut ? 'text-rose-600' : 'text-emerald-600' }}">

                                    {{ $isOut ? '-' : '+' }}${{ number_format((float) $movement->amount, 2) }}

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="4"
                                    class="px-5 py-10 text-center text-sm text-slate-400"
                                >
                                    No hay movimientos registrados.
                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </x-ui.card>

    @endif


    {{-- ========================================================= --}}
    {{-- MODAL: ABRIR CAJA --}}
    {{-- ========================================================= --}}

    <div
        id="cash-modal"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/50 px-4"
    >

        <div class="w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl">

            <div class="flex items-start justify-between">

                <div>

                    <h2 class="text-lg font-black text-slate-900">
                        Abrir sesión de caja
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Define la caja y el fondo inicial.
                    </p>

                </div>

                <button
                    type="button"
                    id="close-cash-modal"
                    class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700"
                >
                    ✕
                </button>

            </div>


            <form
                method="POST"
                action="{{ route('cash.open') }}"
                class="mt-6 space-y-5"
            >

                @csrf


                <div>

                    <label
                        for="branch_id"
                        class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600"
                    >
                        Sucursal
                    </label>

                    <select
                        id="branch_id"
                        name="branch_id"
                        required
                        class="app-input"
                    >

                        <option value="">
                            Selecciona una sucursal
                        </option>

                        @foreach ($branches as $branch)

                            <option value="{{ $branch->id }}">
                                {{ $branch->name }}
                            </option>

                        @endforeach

                    </select>

                </div>


                <div>

                    <label
                        for="register_id"
                        class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600"
                    >
                        Caja
                    </label>

                    <select
                        id="register_id"
                        name="register_id"
                        required
                        class="app-input"
                    >

                        <option value="">
                            Selecciona una caja
                        </option>

                        @foreach ($registers as $register)

                            <option
                                value="{{ $register->id }}"
                                data-branch="{{ $register->branch_id }}"
                            >
                                {{ $register->name }}
                            </option>

                        @endforeach

                    </select>

                </div>


                <div>

                    <label
                        for="opening_float"
                        class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600"
                    >
                        Fondo inicial
                    </label>

                    <div class="relative">

                        <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-sm font-bold text-slate-400">
                            $
                        </span>

                        <input
                            id="opening_float"
                            type="number"
                            name="opening_float"
                            step="0.01"
                            min="0"
                            value="0.00"
                            required
                            class="app-input pl-8"
                        >

                    </div>

                </div>


                <div>

                    <label
                        for="notes"
                        class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600"
                    >
                        Notas
                    </label>

                    <textarea
                        id="notes"
                        name="notes"
                        rows="3"
                        class="app-input resize-none"
                        placeholder="Observaciones de la apertura..."
                    ></textarea>

                </div>


                <div class="flex justify-end gap-3 pt-2">

                    <button
                        type="button"
                        id="cancel-cash-modal"
                        class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50"
                    >
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        class="rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-emerald-700"
                    >
                        Abrir caja
                    </button>

                </div>

            </form>

        </div>

    </div>


    @if ($activeSession && $activeSession->status === 'open')

        {{-- ========================================================= --}}
        {{-- MODAL: MOVIMIENTO --}}
        {{-- ========================================================= --}}

        <div
            id="movement-modal"
            class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/50 px-4"
        >

            <div class="w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl">

                <div class="flex items-start justify-between">

                    <div>

                        <h2 class="text-lg font-black text-slate-900">
                            Registrar movimiento
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Registra una entrada o salida manual de efectivo.
                        </p>

                    </div>

                    <button
                        type="button"
                        data-close-modal="movement-modal"
                        class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700"
                    >
                        ✕
                    </button>

                </div>


                <form
                    method="POST"
                    action="{{ route('cash.movements.store') }}"
                    class="mt-6 space-y-5"
                >

                    @csrf


                    <div>

                        <label
                            for="movement_type"
                            class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600"
                        >
                            Tipo de movimiento
                        </label>

                        <select
                            id="movement_type"
                            name="movement_type"
                            required
                            class="app-input"
                        >

                            <option value="">
                                Selecciona un movimiento
                            </option>

                            <option value="income">
                                Ingreso de efectivo
                            </option>

                            <option value="withdrawal">
                                Retiro de efectivo
                            </option>

                            <option value="deposit">
                                Depósito bancario
                            </option>

                            <option value="expense">
                                Gasto pagado desde caja
                            </option>

                        </select>

                    </div>


                    <div>

                        <label
                            for="movement_amount"
                            class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600"
                        >
                            Importe
                        </label>

                        <div class="relative">

                            <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-sm font-bold text-slate-400">
                                $
                            </span>

                            <input
                                id="movement_amount"
                                type="number"
                                name="amount"
                                step="0.01"
                                min="0.01"
                                required
                                class="app-input pl-8"
                                placeholder="0.00"
                            >

                        </div>

                    </div>


                    <div>

                        <label
                            for="reason_code"
                            class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600"
                        >
                            Motivo
                        </label>

                        <input
                            id="reason_code"
                            type="text"
                            name="reason_code"
                            maxlength="50"
                            class="app-input"
                            placeholder="Ej. compra urgente, retiro de efectivo..."
                        >

                    </div>


                    <div>

                        <label
                            for="movement_notes"
                            class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600"
                        >
                            Notas
                        </label>

                        <textarea
                            id="movement_notes"
                            name="notes"
                            rows="3"
                            maxlength="1000"
                            class="app-input resize-none"
                            placeholder="Describe el movimiento..."
                        ></textarea>

                    </div>


                    <div class="flex justify-end gap-3 pt-2">

                        <button
                            type="button"
                            data-close-modal="movement-modal"
                            class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50"
                        >
                            Cancelar
                        </button>

                        <button
                            type="submit"
                            class="rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-emerald-700"
                        >
                            Registrar movimiento
                        </button>

                    </div>

                </form>

            </div>

        </div>


        {{-- ========================================================= --}}
        {{-- MODAL: INICIAR CORTE --}}
        {{-- ========================================================= --}}

        <div
            id="counting-modal"
            class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/50 px-4"
        >

            <div class="w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl">

                <div class="flex items-start justify-between">

                    <div>

                        <h2 class="text-lg font-black text-slate-900">
                            Iniciar corte de caja
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            La caja dejará de aceptar operaciones y comenzará el arqueo.
                        </p>

                    </div>

                    <button
                        type="button"
                        data-close-modal="counting-modal"
                        class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700"
                    >
                        ✕
                    </button>

                </div>


                <div class="mt-6 rounded-xl border border-slate-200 bg-slate-50 p-4">

                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                        Efectivo esperado
                    </p>

                    <p class="mt-2 text-3xl font-black text-slate-900">
                        ${{ number_format($theoreticalCash, 2) }}
                    </p>

                    <p class="mt-2 text-xs leading-5 text-slate-500">
                        Este importe se guardará como efectivo teórico
                        al comenzar el arqueo.
                    </p>

                </div>


                <div class="mt-6 flex justify-end gap-3">

                    <button
                        type="button"
                        data-close-modal="counting-modal"
                        class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50"
                    >
                        Cancelar
                    </button>

                    <form
                        method="POST"
                        action="{{ route('cash.counting', $activeSession) }}"
                    >

                        @csrf

                        <button
                            type="submit"
                            class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-bold text-white hover:bg-slate-800"
                        >
                            Iniciar arqueo
                        </button>

                    </form>

                </div>

            </div>

        </div>

    @endif


    @if ($activeSession && $activeSession->status === 'counting')

        {{-- ========================================================= --}}
        {{-- MODAL: CERRAR CAJA --}}
        {{-- ========================================================= --}}

        <div
            id="close-modal"
            class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/50 px-4"
        >

            <div class="w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl">

                <div class="flex items-start justify-between">

                    <div>

                        <h2 class="text-lg font-black text-slate-900">
                            Finalizar corte
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Introduce el efectivo contado físicamente.
                        </p>

                    </div>

                    <button
                        type="button"
                        data-close-modal="close-modal"
                        class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700"
                    >
                        ✕
                    </button>

                </div>


                <div class="mt-6 grid gap-3 sm:grid-cols-2">

                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">

                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                            Efectivo esperado
                        </p>

                        <p class="mt-2 text-2xl font-black text-slate-900">
                            ${{ number_format((float) $activeSession->theoretical_total, 2) }}
                        </p>

                    </div>


                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">

                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                            Efectivo contado
                        </p>

                        <p
                            id="counted-preview"
                            class="mt-2 text-2xl font-black text-slate-900"
                        >
                            $0.00
                        </p>

                    </div>

                </div>


                <form
                    method="POST"
                    action="{{ route('cash.close', $activeSession) }}"
                    class="mt-6 space-y-5"
                >

                    @csrf


                    <div>

                        <label
                            for="counted_total"
                            class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600"
                        >
                            Efectivo contado
                        </label>

                        <div class="relative">

                            <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-sm font-bold text-slate-400">
                                $
                            </span>

                            <input
                                id="counted_total"
                                type="number"
                                name="counted_total"
                                step="0.01"
                                min="0"
                                required
                                class="app-input pl-8"
                                placeholder="0.00"
                            >

                        </div>

                    </div>


                    <div>

                        <label
                            for="close_notes"
                            class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600"
                        >
                            Notas del corte
                        </label>

                        <textarea
                            id="close_notes"
                            name="notes"
                            rows="3"
                            maxlength="2000"
                            class="app-input resize-none"
                            placeholder="Explica cualquier diferencia o situación relevante..."
                        ></textarea>

                    </div>


                    <div class="flex justify-end gap-3 pt-2">

                        <button
                            type="button"
                            data-close-modal="close-modal"
                            class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50"
                        >
                            Cancelar
                        </button>

                        <button
                            type="submit"
                            class="rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-emerald-700"
                        >
                            Cerrar caja
                        </button>

                    </div>

                </form>

            </div>

        </div>

    @endif


    <script>

        document.addEventListener('DOMContentLoaded', () => {

            function openModal(id) {

                const modal = document.getElementById(id);

                if (!modal) {
                    return;
                }

                modal.classList.remove('hidden');
                modal.classList.add('flex');

            }


            function closeModal(id) {

                const modal = document.getElementById(id);

                if (!modal) {
                    return;
                }

                modal.classList.add('hidden');
                modal.classList.remove('flex');

            }


            // Apertura

            document
                .getElementById('open-cash-modal')
                ?.addEventListener('click', () => {
                    openModal('cash-modal');
                });


            document
                .getElementById('close-cash-modal')
                ?.addEventListener('click', () => {
                    closeModal('cash-modal');
                });


            document
                .getElementById('cancel-cash-modal')
                ?.addEventListener('click', () => {
                    closeModal('cash-modal');
                });


            // Movimiento

            document
                .getElementById('open-movement-modal')
                ?.addEventListener('click', () => {
                    openModal('movement-modal');
                });


            // Iniciar corte

            document
                .getElementById('open-counting-modal')
                ?.addEventListener('click', () => {
                    openModal('counting-modal');
                });


            // Finalizar corte

            document
                .getElementById('open-close-modal')
                ?.addEventListener('click', () => {
                    openModal('close-modal');
                });


            // Botones genéricos de cierre

            document
                .querySelectorAll('[data-close-modal]')
                .forEach(button => {

                    button.addEventListener('click', () => {

                        closeModal(
                            button.dataset.closeModal
                        );

                    });

                });


            // Cerrar al hacer click fuera

            document
                .querySelectorAll('[id$="-modal"]')
                .forEach(modal => {

                    modal.addEventListener('click', event => {

                        if (event.target === modal) {
                            closeModal(modal.id);
                        }

                    });

                });


            // Filtro de cajas por sucursal

            const branchSelect =
                document.getElementById('branch_id');

            const registerSelect =
                document.getElementById('register_id');


            branchSelect?.addEventListener('change', () => {

                const branchId = branchSelect.value;

                Array
                    .from(registerSelect.options)
                    .forEach(option => {

                        if (!option.value) {
                            option.hidden = false;
                            return;
                        }

                        option.hidden =
                            option.dataset.branch !== branchId;

                    });

                registerSelect.value = '';

            });


            // Preview del efectivo contado

            const countedInput =
                document.getElementById('counted_total');

            const countedPreview =
                document.getElementById('counted-preview');


            countedInput?.addEventListener('input', () => {

                const value =
                    parseFloat(countedInput.value) || 0;

                countedPreview.textContent =
                    '$' + value.toLocaleString('es-MX', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });

            });


            // ESC para cerrar modales

            document.addEventListener('keydown', event => {

                if (event.key !== 'Escape') {
                    return;
                }

                document
                    .querySelectorAll('[id$="-modal"]')
                    .forEach(modal => {

                        if (!modal.classList.contains('hidden')) {
                            closeModal(modal.id);
                        }

                    });

            });

        });

    </script>

</x-layouts.app>
