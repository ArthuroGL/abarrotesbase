<x-layouts.app title="Caja | ABARROTESBASE">

    @php
    $isOpen = $activeSession?->status === 'open';
    $isCounting = $activeSession?->status === 'counting';
    @endphp


    {{-- ========================================================= --}}
    {{-- HEADER --}}
    {{-- ========================================================= --}}

    <x-layout.page-header
        eyebrow="Operación"
        title="Caja"
        description="Administra la sesión de caja, movimientos, arqueo y cierre de operación.">
        <x-slot:actions>

            <x-ui.button
                variant="secondary"
                type="button"
                onclick="window.location.href='{{ route('cash.history') }}'">
                Historial
            </x-ui.button>


            @if ($activeSession)

            <span class="inline-flex min-h-10 items-center gap-2 rounded-xl bg-emerald-50 px-3 text-xs font-black text-emerald-700 ring-1 ring-inset ring-emerald-200">

                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>

                @if ($isCounting)
                En arqueo
                @else
                Sesión abierta
                @endif

            </span>

            @endif

        </x-slot:actions>
    </x-layout.page-header>


    {{-- ========================================================= --}}
    {{-- ALERTA DE ÉXITO --}}
    {{-- ========================================================= --}}

    @if (session('success'))

    <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4">

        <div class="flex items-start gap-3">

            <div class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-emerald-100 text-sm font-black text-emerald-700">
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
    {{-- ERRORES --}}
    {{-- ========================================================= --}}

    @if ($errors->any())

    <div class="mt-6 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4">

        <div class="flex items-start gap-3">

            <div class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-rose-100 text-sm font-black text-rose-700">
                !
            </div>

            <div class="min-w-0">

                <p class="text-sm font-black text-rose-900">
                    No fue posible realizar la operación
                </p>

                <ul class="mt-2 space-y-1 text-sm text-rose-700">

                    @foreach ($errors->all() as $error)

                    <li>
                        {{ $error }}
                    </li>

                    @endforeach

                </ul>

            </div>

        </div>

    </div>

    @endif


    {{-- ========================================================= --}}
    {{-- SIN SESIÓN --}}
    {{-- ========================================================= --}}

    @if (!$activeSession)

    <x-ui.card class="mt-6">

        <div class="mx-auto max-w-xl py-12 text-center">

            <div class="mx-auto grid h-16 w-16 place-items-center rounded-2xl bg-emerald-50 text-2xl font-black text-emerald-700 ring-1 ring-inset ring-emerald-100">
                $
            </div>

            <p class="mt-5 text-xs font-black uppercase tracking-[0.14em] text-emerald-700">
                Punto de venta
            </p>

            <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-900">
                No hay una sesión de caja abierta
            </h2>

            <p class="mx-auto mt-3 max-w-md text-sm leading-6 text-slate-500">
                Abre una sesión para comenzar a registrar ventas,
                ingresos, retiros y demás movimientos de efectivo.
            </p>

            <div class="mt-6">

                <x-ui.button
                    variant="primary"
                    type="button"
                    id="open-cash-modal">
                    Abrir caja
                </x-ui.button>

            </div>

        </div>

    </x-ui.card>


    @else


    {{-- ===================================================== --}}
    {{-- ESTADO DE ARQUEO --}}
    {{-- ===================================================== --}}

    @if ($isCounting)

    <section class="mt-6 overflow-hidden rounded-2xl border border-amber-200 bg-amber-50">

        <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">

            <div class="flex items-start gap-3">

                <div class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-amber-100 text-sm font-black text-amber-700">
                    !
                </div>

                <div>

                    <p class="text-sm font-black text-amber-900">
                        Caja en proceso de arqueo
                    </p>

                    <p class="mt-1 max-w-2xl text-sm leading-5 text-amber-800">
                        La operación está congelada. Cuenta físicamente
                        el efectivo antes de cerrar la sesión.
                    </p>

                </div>

            </div>

            <span class="shrink-0 rounded-xl bg-amber-100 px-3 py-2 text-xs font-black text-amber-800 ring-1 ring-inset ring-amber-200">
                Sin nuevos movimientos
            </span>

        </div>

    </section>

    @endif


    {{-- ===================================================== --}}
    {{-- RESUMEN --}}
    {{-- ===================================================== --}}

    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4 2xl:grid-cols-5">


        {{-- Fondo inicial --}}
        <x-ui.card>

            <p class="text-xs font-black uppercase tracking-wider text-slate-500">
                Fondo inicial
            </p>

            <p class="mt-2 text-2xl font-black tabular-nums text-slate-900">
                ${{ number_format((float) $activeSession->opening_float, 2) }}
            </p>

            <p class="mt-1 text-xs text-slate-500">
                Efectivo de apertura
            </p>

        </x-ui.card>


        {{-- Ventas en efectivo --}}
        <x-ui.card>

            <p class="text-xs font-black uppercase tracking-wider text-slate-500">
                Ventas en efectivo
            </p>

            <p class="mt-2 text-2xl font-black tabular-nums text-emerald-600">
                +${{ number_format($cashSales, 2) }}
            </p>

            <p class="mt-1 text-xs text-slate-500">
                Cobros realizados en efectivo
            </p>

        </x-ui.card>


        {{-- Entradas --}}
        <x-ui.card>

            <p class="text-xs font-black uppercase tracking-wider text-slate-500">
                Entradas acumuladas
            </p>

            <p class="mt-2 text-2xl font-black tabular-nums text-emerald-600">
                +${{ number_format($cashIn, 2) }}
            </p>

            <p class="mt-1 text-xs text-slate-500">
                Movimientos que incrementan efectivo
            </p>

        </x-ui.card>


        {{-- Salidas --}}
        <x-ui.card>

            <p class="text-xs font-black uppercase tracking-wider text-slate-500">
                Salidas acumuladas
            </p>

            <p class="mt-2 text-2xl font-black tabular-nums text-rose-600">
                -${{ number_format($cashOut, 2) }}
            </p>

            <p class="mt-1 text-xs text-slate-500">
                Movimientos que disminuyen efectivo
            </p>

        </x-ui.card>


        {{-- Efectivo esperado --}}
        <x-ui.card>

            <p class="text-xs font-black uppercase tracking-wider text-slate-500">
                Efectivo esperado
            </p>

            <p class="mt-2 text-2xl font-black tabular-nums text-slate-900">
                ${{ number_format($theoreticalCash, 2) }}
            </p>

            <p class="mt-1 text-xs text-slate-500">
                Saldo físico esperado
            </p>

        </x-ui.card>

    </div>


    {{-- ===================================================== --}}
    {{-- ACCIONES --}}
    {{-- ===================================================== --}}

    <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-end">

        @if ($isOpen)

        <x-ui.button
            variant="secondary"
            type="button"
            id="open-movement-modal">
            + Movimiento
        </x-ui.button>

        <x-ui.button
            variant="primary"
            type="button"
            id="open-counting-modal">
            Hacer corte
        </x-ui.button>

        @elseif ($isCounting)

        <x-ui.button
            variant="primary"
            type="button"
            id="open-close-modal">
            Finalizar corte
        </x-ui.button>

        @endif

    </div>


    {{-- ===================================================== --}}
    {{-- MOVIMIENTOS --}}
    {{-- ===================================================== --}}

    <x-ui.card class="mt-6" padding="p-0">

        <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">

            <div>

                <h2 class="text-lg font-black tracking-tight text-slate-900">
                    Movimientos
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Movimientos registrados durante esta sesión.
                </p>

            </div>

            <span class="w-fit rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600">
                {{ $movements->count() }} movimientos
            </span>

        </div>


        {{-- Desktop --}}
        <div class="hidden overflow-x-auto md:block">

            <table class="w-full text-left text-sm">

                <thead class="border-b border-slate-200 bg-slate-50">

                    <tr class="text-[11px] font-black uppercase tracking-wider text-slate-500">

                        <th class="px-5 py-3.5">
                            Fecha
                        </th>

                        <th class="px-5 py-3.5">
                            Movimiento
                        </th>

                        <th class="px-5 py-3.5">
                            Notas
                        </th>

                        <th class="px-5 py-3.5 text-right">
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

                    $movementLabel = match ($movement->movement_type) {
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
                    };

                    @endphp

                    <tr class="transition hover:bg-slate-50/80">

                        <td class="px-5 py-4 text-xs text-slate-500">
                            {{ $movement->occurred_at->format('d/m/Y H:i') }}
                        </td>

                        <td class="px-5 py-4">

                            <p class="font-bold text-slate-800">
                                {{ $movementLabel }}
                            </p>

                        </td>

                        <td class="max-w-md px-5 py-4 text-xs text-slate-500">

                            <span class="line-clamp-2">
                                {{ $movement->notes ?: 'Sin notas' }}
                            </span>

                        </td>

                        <td class="px-5 py-4 text-right">

                            <span class="font-black tabular-nums {{ $isOut ? 'text-rose-600' : 'text-emerald-600' }}">
                                {{ $isOut ? '-' : '+' }}${{ number_format((float) $movement->amount, 2) }}
                            </span>

                        </td>

                    </tr>

                    @empty

                    <tr>

                        <td colspan="4" class="px-5 py-14 text-center">

                            <p class="text-sm font-bold text-slate-500">
                                No hay movimientos registrados.
                            </p>

                            <p class="mt-1 text-xs text-slate-400">
                                Los movimientos de esta sesión aparecerán aquí.
                            </p>

                        </td>

                    </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- Móvil --}}
        <div class="divide-y divide-slate-100 md:hidden">

            @forelse ($movements as $movement)

            @php

            $isOut = in_array($movement->movement_type, [
            'sale_change',
            'return_payment',
            'expense',
            'withdrawal',
            'deposit',
            ], true);

            $movementLabel = match ($movement->movement_type) {
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
            };

            @endphp

            <article class="p-5">

                <div class="flex items-start justify-between gap-4">

                    <div class="min-w-0">

                        <p class="font-black text-slate-900">
                            {{ $movementLabel }}
                        </p>

                        <p class="mt-1 text-xs text-slate-500">
                            {{ $movement->occurred_at->format('d/m/Y H:i') }}
                        </p>

                    </div>

                    <p class="shrink-0 font-black tabular-nums {{ $isOut ? 'text-rose-600' : 'text-emerald-600' }}">
                        {{ $isOut ? '-' : '+' }}${{ number_format((float) $movement->amount, 2) }}
                    </p>

                </div>


                @if ($movement->notes)

                <div class="mt-3 rounded-xl bg-slate-50 p-3">

                    <p class="text-xs leading-5 text-slate-600">
                        {{ $movement->notes }}
                    </p>

                </div>

                @endif

            </article>

            @empty

            <div class="px-5 py-14 text-center">

                <p class="text-sm font-bold text-slate-500">
                    No hay movimientos registrados.
                </p>

            </div>

            @endforelse

        </div>

    </x-ui.card>

    @endif


    {{-- ========================================================= --}}
    {{-- MODAL: ABRIR CAJA --}}
    {{-- ========================================================= --}}

    <x-ui.modal
        id="cash-modal"
        size="md"
        title="Abrir sesión de caja"
        description="Define la sucursal, caja y fondo inicial de la operación."
        close-id="close-cash-modal">

        <form
            id="open-cash-form"
            method="POST"
            action="{{ route('cash.open') }}"
            class="space-y-5 p-5 sm:p-6">

            @csrf


            {{-- Sucursal --}}
            <div>

                <label
                    for="branch_id"
                    class="mb-1.5 block text-xs font-black uppercase tracking-wider text-slate-600">
                    Sucursal
                </label>

                <select
                    id="branch_id"
                    name="branch_id"
                    required
                    class="app-input">

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


            {{-- Caja --}}
            <div>

                <label
                    for="register_id"
                    class="mb-1.5 block text-xs font-black uppercase tracking-wider text-slate-600">
                    Caja
                </label>

                <select
                    id="register_id"
                    name="register_id"
                    required
                    class="app-input">

                    <option value="">
                        Selecciona una caja
                    </option>

                    @foreach ($registers as $register)

                    <option
                        value="{{ $register->id }}"
                        data-branch="{{ $register->branch_id }}">
                        {{ $register->name }}
                    </option>

                    @endforeach

                </select>

            </div>


            {{-- Fondo inicial --}}
            <div>

                <label
                    for="opening_float"
                    class="mb-1.5 block text-xs font-black uppercase tracking-wider text-slate-600">
                    Fondo inicial
                </label>

                <x-ui.input
                    id="opening_float"
                    type="number"
                    name="opening_float"
                    step="0.01"
                    min="0"
                    value="0.00"
                    inputmode="decimal"
                    required
                    placeholder="0.00" />

            </div>


            {{-- Notas --}}
            <div>

                <label
                    for="notes"
                    class="mb-1.5 block text-xs font-black uppercase tracking-wider text-slate-600">
                    Notas
                </label>

                <textarea
                    id="notes"
                    name="notes"
                    rows="3"
                    maxlength="1000"
                    class="app-input resize-none"
                    placeholder="Observaciones de la apertura..."></textarea>

            </div>

        </form>


        <x-slot:footer>

            <div class="flex justify-end gap-3">

                <x-ui.button
                    variant="secondary"
                    type="button"
                    data-close-modal="cash-modal">
                    Cancelar
                </x-ui.button>

                <x-ui.button
                    variant="primary"
                    type="submit"
                    form="open-cash-form">
                    Abrir caja
                </x-ui.button>

            </div>

        </x-slot:footer>

    </x-ui.modal>


    {{-- ========================================================= --}}
    {{-- MODAL: MOVIMIENTO --}}
    {{-- ========================================================= --}}

    @if ($isOpen)

    <x-ui.modal
        id="movement-modal"
        size="md"
        title="Registrar movimiento"
        description="Registra una entrada o salida manual de efectivo."
        close-id="close-movement-modal">

        <form
            id="movement-form"
            method="POST"
            action="{{ route('cash.movements.store') }}"
            class="space-y-5 p-5 sm:p-6">

            @csrf


            {{-- Tipo --}}
            <div>

                <label
                    for="movement_type"
                    class="mb-1.5 block text-xs font-black uppercase tracking-wider text-slate-600">
                    Tipo de movimiento
                </label>

                <select
                    id="movement_type"
                    name="movement_type"
                    required
                    class="app-input">

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


            {{-- Importe --}}
            <div>

                <label
                    for="movement_amount"
                    class="mb-1.5 block text-xs font-black uppercase tracking-wider text-slate-600">
                    Importe
                </label>

                <x-ui.input
                    id="movement_amount"
                    type="number"
                    name="amount"
                    step="0.01"
                    min="0.01"
                    inputmode="decimal"
                    required
                    placeholder="0.00" />

            </div>


            {{-- Motivo --}}
            <div>

                <label
                    for="reason_code"
                    class="mb-1.5 block text-xs font-black uppercase tracking-wider text-slate-600">
                    Motivo
                </label>

                <input
                    id="reason_code"
                    type="text"
                    name="reason_code"
                    maxlength="50"
                    class="app-input"
                    placeholder="Ej. compra urgente, retiro de efectivo...">

            </div>


            {{-- Notas --}}
            <div>

                <label
                    for="movement_notes"
                    class="mb-1.5 block text-xs font-black uppercase tracking-wider text-slate-600">
                    Notas
                </label>

                <textarea
                    id="movement_notes"
                    name="notes"
                    rows="3"
                    maxlength="1000"
                    class="app-input resize-none"
                    placeholder="Describe el movimiento..."></textarea>

            </div>

        </form>


        <x-slot:footer>

            <div class="flex justify-end gap-3">

                <x-ui.button
                    variant="secondary"
                    type="button"
                    data-close-modal="movement-modal">
                    Cancelar
                </x-ui.button>

                <x-ui.button
                    variant="primary"
                    type="submit"
                    form="movement-form">
                    Registrar movimiento
                </x-ui.button>

            </div>

        </x-slot:footer>

    </x-ui.modal>

    @endif


    {{-- ========================================================= --}}
    {{-- MODAL: INICIAR CORTE --}}
    {{-- ========================================================= --}}

    @if ($isOpen)

    <x-ui.modal
        id="counting-modal"
        size="sm"
        title="Iniciar corte de caja"
        description="La caja dejará de aceptar operaciones y comenzará el arqueo."
        close-id="close-counting-modal">

        <div class="p-5 sm:p-6">

            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">

                <p class="text-xs font-black uppercase tracking-wider text-slate-500">
                    Efectivo esperado
                </p>

                <p class="mt-2 text-3xl font-black tabular-nums tracking-tight text-slate-900">
                    ${{ number_format($theoreticalCash, 2) }}
                </p>

                <p class="mt-2 text-xs leading-5 text-slate-500">
                    Este importe se guardará como efectivo teórico
                    al comenzar el arqueo.
                </p>

            </div>

        </div>


        <x-slot:footer>

            <div class="flex justify-end gap-3">

                <x-ui.button
                    variant="secondary"
                    type="button"
                    data-close-modal="counting-modal">
                    Cancelar
                </x-ui.button>


                <form
                    id="counting-form"
                    method="POST"
                    action="{{ route('cash.counting', $activeSession) }}">

                    @csrf

                </form>

                <x-ui.button
                    variant="primary"
                    type="submit"
                    form="counting-form">
                    Iniciar arqueo
                </x-ui.button>

            </div>

        </x-slot:footer>

    </x-ui.modal>

    @endif


    {{-- ========================================================= --}}
    {{-- MODAL: FINALIZAR CORTE --}}
    {{-- ========================================================= --}}

    @if ($isCounting)

    <x-ui.modal
        id="close-modal"
        size="md"
        title="Finalizar corte"
        description="Introduce el efectivo contado físicamente para comparar contra el saldo esperado."
        close-id="close-close-modal">

        <div class="p-5 sm:p-6">

            <div class="grid gap-3 sm:grid-cols-2">


                {{-- Esperado --}}
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">

                    <p class="text-xs font-black uppercase tracking-wider text-slate-500">
                        Efectivo esperado
                    </p>

                    <p class="mt-2 text-2xl font-black tabular-nums text-slate-900">
                        ${{ number_format((float) $activeSession->theoretical_total, 2) }}
                    </p>

                </div>


                {{-- Contado --}}
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">

                    <p class="text-xs font-black uppercase tracking-wider text-slate-500">
                        Efectivo contado
                    </p>

                    <p
                        id="counted-preview"
                        class="mt-2 text-2xl font-black tabular-nums text-slate-900">
                        0.00
                    </p>

                </div>

            </div>


            {{-- Diferencia --}}
            <div
                id="counting-difference-box"
                class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">

                <div class="flex items-center justify-between gap-4">

                    <div>

                        <p class="text-xs font-black uppercase tracking-wider text-slate-500">
                            Diferencia
                        </p>

                        <p
                            id="counting-difference-label"
                            class="mt-1 text-xs font-semibold text-slate-500">
                            Ingresa el efectivo contado.
                        </p>

                    </div>

                    <p
                        id="counting-difference"
                        class="text-xl font-black tabular-nums text-slate-900">
                        0.00
                    </p>

                </div>

            </div>


            <form
                id="close-cash-form"
                method="POST"
                action="{{ route('cash.close', $activeSession) }}"
                class="mt-5 space-y-5">

                @csrf


                {{-- Efectivo contado --}}
                <div>

                    <label
                        for="counted_total"
                        class="mb-1.5 block text-xs font-black uppercase tracking-wider text-slate-600">
                        Efectivo contado
                    </label>

                    <x-ui.input
                        id="counted_total"
                        type="number"
                        name="counted_total"
                        step="0.01"
                        min="0"
                        inputmode="decimal"
                        required
                        placeholder="0.00" />

                </div>


                {{-- Notas --}}
                <div>

                    <label
                        for="close_notes"
                        class="mb-1.5 block text-xs font-black uppercase tracking-wider text-slate-600">
                        Notas del corte
                    </label>

                    <textarea
                        id="close_notes"
                        name="notes"
                        rows="3"
                        maxlength="2000"
                        class="app-input resize-none"
                        placeholder="Explica cualquier diferencia o situación relevante..."></textarea>

                </div>

            </form>

        </div>


        <x-slot:footer>

            <div class="flex justify-end gap-3">

                <x-ui.button
                    variant="secondary"
                    type="button"
                    data-close-modal="close-modal">
                    Cancelar
                </x-ui.button>

                <x-ui.button
                    variant="primary"
                    type="submit"
                    form="close-cash-form">
                    Cerrar caja
                </x-ui.button>

            </div>

        </x-slot:footer>

    </x-ui.modal>

    @endif


    {{-- ========================================================= --}}
    {{-- JAVASCRIPT --}}
    {{-- ========================================================= --}}

    <script>
        document.addEventListener('DOMContentLoaded', () => {

            /*
             * =====================================================
             * FORMATO DE CAMPOS MONETARIOS
             * =====================================================
             */

            function formatMoneyInput(input) {
                if (!input) {
                    return;
                }

                const value = input.value.trim();

                if (value === '') {
                    return;
                }

                const number = Number(value);

                if (!Number.isFinite(number)) {
                    input.value = '';
                    return;
                }

                input.value = number.toFixed(2);
            }

            [
                'opening_float',
                'movement_amount',
                'counted_total',
            ].forEach((id) => {
                const input = document.getElementById(id);

                input?.addEventListener('blur', () => {
                    formatMoneyInput(input);
                });
            });

            /*
             * =====================================================
             * MODALES
             * =====================================================
             */

            function openModal(id) {

                const modal = document.getElementById(id);

                if (!modal) {
                    return;
                }

                modal.classList.remove('hidden');
                modal.classList.add('flex');

                modal.setAttribute('aria-hidden', 'false');

                document.body.classList.add('overflow-hidden');
            }


            function closeModal(id) {

                const modal = document.getElementById(id);

                if (!modal) {
                    return;
                }

                modal.classList.add('hidden');
                modal.classList.remove('flex');

                modal.setAttribute('aria-hidden', 'true');

                const hasOpenModal =
                    document.querySelector(
                        '[id$="-modal"]:not(.hidden)'
                    );

                if (!hasOpenModal) {
                    document.body.classList.remove('overflow-hidden');
                }
            }


            /*
             * Apertura de caja
             */
            document
                .getElementById('open-cash-modal')
                ?.addEventListener('click', () => {
                    openModal('cash-modal');
                });


            /*
             * Movimiento
             */
            document
                .getElementById('open-movement-modal')
                ?.addEventListener('click', () => {
                    openModal('movement-modal');
                });


            /*
             * Iniciar corte
             */
            document
                .getElementById('open-counting-modal')
                ?.addEventListener('click', () => {
                    openModal('counting-modal');
                });


            /*
             * Finalizar corte
             */
            document
                .getElementById('open-close-modal')
                ?.addEventListener('click', () => {
                    openModal('close-modal');

                    updateCountingDifference();
                });


            /*
             * Botones de cierre
             */
            document
                .querySelectorAll('[data-close-modal]')
                .forEach(button => {

                    button.addEventListener('click', () => {

                        closeModal(
                            button.dataset.closeModal
                        );

                    });

                });


            /*
             * Cerrar haciendo click en el backdrop
             */
            document
                .querySelectorAll('[id$="-modal"]')
                .forEach(modal => {

                    modal.addEventListener('click', event => {

                        if (event.target === modal) {
                            closeModal(modal.id);
                        }

                    });

                });


            /*
             * ESC
             */
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


            /*
             * =====================================================
             * FILTRO SUCURSAL / CAJA
             * =====================================================
             */

            const branchSelect =
                document.getElementById('branch_id');

            const registerSelect =
                document.getElementById('register_id');


            branchSelect?.addEventListener('change', () => {

                const branchId =
                    branchSelect.value;

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


            /*
             * =====================================================
             * ARQUEO
             * =====================================================
             */

            const countedInput =
                document.getElementById('counted_total');

            const countedPreview =
                document.getElementById('counted-preview');

            const countingDifference =
                document.getElementById('counting-difference');

            const countingDifferenceLabel =
                document.getElementById('counting-difference-label');

            const countingDifferenceBox =
                document.getElementById('counting-difference-box');


            const theoreticalCash = Number(@json ( $activeSession ?-> theoretical_total ?? $theoreticalCash ?? 0 ));


            function formatMoney(value) {

                return '$' + Number(value).toLocaleString(
                    'es-MX', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2,
                    }
                );

            }


            function updateCountingDifference() {

                if (!countedInput) {
                    return;
                }

                const counted =
                    parseFloat(countedInput.value) || 0;

                const difference =
                    Math.round(
                        (counted - theoreticalCash) * 100
                    ) / 100;


                countedPreview.textContent =
                    formatMoney(counted);


                countingDifference.textContent =
                    (difference > 0 ? '+' : '') +
                    formatMoney(difference);


                /*
                 * Faltante
                 */
                if (difference < 0) {

                    countingDifferenceLabel.textContent =
                        'Faltante de caja.';

                    countingDifferenceBox.className =
                        'mt-4 rounded-2xl border border-rose-200 bg-rose-50 p-4';

                    countingDifference.className =
                        'text-xl font-black tabular-nums text-rose-600';

                    return;
                }


                /*
                 * Sobrante
                 */
                if (difference > 0) {

                    countingDifferenceLabel.textContent =
                        'Sobrante de caja.';

                    countingDifferenceBox.className =
                        'mt-4 rounded-2xl border border-amber-200 bg-amber-50 p-4';

                    countingDifference.className =
                        'text-xl font-black tabular-nums text-amber-600';

                    return;
                }


                /*
                 * Corte exacto
                 */
                countingDifferenceLabel.textContent =
                    'El efectivo coincide con el saldo esperado.';

                countingDifferenceBox.className =
                    'mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 p-4';

                countingDifference.className =
                    'text-xl font-black tabular-nums text-emerald-600';

            }

            countedInput?.addEventListener('input', updateCountingDifference);

            countedInput?.addEventListener('blur', () => {
                formatMoneyInput(countedInput);
                updateCountingDifference();
            });

            /*  countedInput?.addEventListener(
                 'input',
                 updateCountingDifference
             ); */


        });
    </script>

</x-layouts.app>
