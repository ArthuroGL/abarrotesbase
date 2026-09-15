<x-layouts.app title="Detalle de Corte | ABARROTESBASE">

    @php
        $difference = (float) $session->difference_total;

        if ($difference < 0) {
            $differenceLabel = 'Faltante';
            $differenceDescription = 'El efectivo contado fue menor al esperado.';
            $differenceText = 'text-rose-600';
            $differenceBg = 'bg-rose-50';
            $differenceBorder = 'border-rose-200';
            $differenceBadge = 'bg-rose-100 text-rose-700 ring-rose-200';
        } elseif ($difference > 0) {
            $differenceLabel = 'Sobrante';
            $differenceDescription = 'El efectivo contado fue mayor al esperado.';
            $differenceText = 'text-amber-600';
            $differenceBg = 'bg-amber-50';
            $differenceBorder = 'border-amber-200';
            $differenceBadge = 'bg-amber-100 text-amber-700 ring-amber-200';
        } else {
            $differenceLabel = 'Corte exacto';
            $differenceDescription = 'El efectivo contado coincide con el efectivo esperado.';
            $differenceText = 'text-emerald-600';
            $differenceBg = 'bg-emerald-50';
            $differenceBorder = 'border-emerald-200';
            $differenceBadge = 'bg-emerald-100 text-emerald-700 ring-emerald-200';
        }
    @endphp


    {{-- Encabezado --}}
    <x-layout.page-header
        eyebrow="Caja"
        title="Detalle del corte"
        description="Consulta el resultado completo de la sesión de caja."
    >
        <x-slot:actions>

            <x-ui.button
                variant="secondary"
                type="button"
                onclick="window.location.href='{{ route('cash.history') }}'"
            >
                Historial
            </x-ui.button>

            <x-ui.button
                variant="primary"
                type="button"
                onclick="window.location.href='{{ route('cash.index') }}'"
            >
                Caja
            </x-ui.button>

        </x-slot:actions>
    </x-layout.page-header>


    {{-- ========================================================= --}}
    {{-- INFORMACIÓN DE LA SESIÓN --}}
    {{-- ========================================================= --}}

    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <x-ui.card>

            <p class="text-xs font-black uppercase tracking-wider text-slate-500">
                Caja
            </p>

            <p class="mt-2 text-lg font-black tracking-tight text-slate-900">
                {{ $session->register?->name ?? 'Sin caja' }}
            </p>

            <p class="mt-1 text-xs text-slate-500">
                {{ $session->branch?->name ?? 'Sin sucursal' }}
            </p>

        </x-ui.card>


        <x-ui.card>

            <p class="text-xs font-black uppercase tracking-wider text-slate-500">
                Responsable
            </p>

            <p class="mt-2 text-lg font-black tracking-tight text-slate-900">
                {{ $session->responsibleUser?->name ?? 'Sin responsable' }}
            </p>

            <p class="mt-1 text-xs text-slate-500">
                Apertura:
                {{ $session->opened_at?->format('d/m/Y H:i') ?? 'Sin fecha' }}
            </p>

        </x-ui.card>


        <x-ui.card>

            <p class="text-xs font-black uppercase tracking-wider text-slate-500">
                Cierre
            </p>

            <p class="mt-2 text-lg font-black tracking-tight text-slate-900">
                {{ $session->closed_at?->format('d/m/Y H:i') ?? 'Sin fecha' }}
            </p>

            <p class="mt-1 text-xs text-slate-500">
                Cerrado por:
                {{ $session->closedBy?->name ?? 'Sin información' }}
            </p>

        </x-ui.card>


        <x-ui.card>

            <p class="text-xs font-black uppercase tracking-wider text-slate-500">
                Estado
            </p>

            <div class="mt-3">
                <span class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-3 py-2 text-xs font-black text-slate-700 ring-1 ring-inset ring-slate-200">
                    <span class="h-2 w-2 rounded-full bg-slate-500"></span>
                    Sesión cerrada
                </span>
            </div>

        </x-ui.card>

    </div>


    {{-- ========================================================= --}}
    {{-- RESULTADO DEL CORTE --}}
    {{-- ========================================================= --}}

    <div class="mt-6 grid gap-4 lg:grid-cols-3">

        {{-- Esperado --}}
        <x-ui.card>

            <p class="text-xs font-black uppercase tracking-wider text-slate-500">
                Efectivo esperado
            </p>

            <p class="mt-2 text-3xl font-black tracking-tight text-slate-900">
                ${{ number_format((float) $session->theoretical_total, 2) }}
            </p>

            <p class="mt-1 text-xs text-slate-500">
                Saldo teórico según los movimientos registrados.
            </p>

        </x-ui.card>


        {{-- Contado --}}
        <x-ui.card>

            <p class="text-xs font-black uppercase tracking-wider text-slate-500">
                Efectivo contado
            </p>

            <p class="mt-2 text-3xl font-black tracking-tight text-slate-900">
                ${{ number_format((float) $session->counted_total, 2) }}
            </p>

            <p class="mt-1 text-xs text-slate-500">
                Efectivo encontrado físicamente durante el arqueo.
            </p>

        </x-ui.card>


        {{-- Diferencia destacada --}}
        <section class="rounded-2xl border {{ $differenceBorder }} {{ $differenceBg }} p-5 shadow-sm">

            <div class="flex items-start justify-between gap-4">

                <div>

                    <p class="text-xs font-black uppercase tracking-wider text-slate-600">
                        Diferencia
                    </p>

                    <p class="mt-2 text-3xl font-black tracking-tight {{ $differenceText }}">
                        {{ $difference > 0 ? '+' : '' }}${{ number_format($difference, 2) }}
                    </p>

                </div>

                <span class="inline-flex shrink-0 rounded-xl px-3 py-2 text-xs font-black ring-1 ring-inset {{ $differenceBadge }}">
                    {{ $differenceLabel }}
                </span>

            </div>

            <p class="mt-3 text-xs leading-5 text-slate-600">
                {{ $differenceDescription }}
            </p>

        </section>

    </div>


    {{-- ========================================================= --}}
    {{-- RESUMEN DE MOVIMIENTOS --}}
    {{-- ========================================================= --}}

    <x-ui.card class="mt-6">

        <div class="flex flex-col gap-1 border-b border-slate-200 pb-4">

            <h2 class="text-lg font-black tracking-tight text-slate-900">
                Resumen de movimientos
            </h2>

            <p class="text-sm text-slate-500">
                Totales registrados durante esta sesión.
            </p>

        </div>


        <div class="mt-5 grid gap-4 sm:grid-cols-3">

            <div class="rounded-xl bg-emerald-50 p-4 ring-1 ring-inset ring-emerald-100">

                <p class="text-xs font-black uppercase tracking-wider text-emerald-700">
                    Entradas
                </p>

                <p class="mt-2 text-xl font-black tabular-nums text-emerald-700">
                    +${{ number_format($cashIn, 2) }}
                </p>

                <p class="mt-1 text-xs text-emerald-700/70">
                    Incrementos de efectivo.
                </p>

            </div>


            <div class="rounded-xl bg-rose-50 p-4 ring-1 ring-inset ring-rose-100">

                <p class="text-xs font-black uppercase tracking-wider text-rose-700">
                    Salidas
                </p>

                <p class="mt-2 text-xl font-black tabular-nums text-rose-700">
                    -${{ number_format($cashOut, 2) }}
                </p>

                <p class="mt-1 text-xs text-rose-700/70">
                    Disminuciones de efectivo.
                </p>

            </div>


            <div class="rounded-xl bg-slate-50 p-4 ring-1 ring-inset ring-slate-200">

                <p class="text-xs font-black uppercase tracking-wider text-slate-500">
                    Movimientos
                </p>

                <p class="mt-2 text-xl font-black tabular-nums text-slate-900">
                    {{ $movements->count() }}
                </p>

                <p class="mt-1 text-xs text-slate-500">
                    Registros de la sesión.
                </p>

            </div>

        </div>

    </x-ui.card>


    {{-- ========================================================= --}}
    {{-- NOTAS --}}
    {{-- ========================================================= --}}

    @if ($session->notes)

        <x-ui.card class="mt-6">

            <p class="text-xs font-black uppercase tracking-wider text-slate-500">
                Notas del corte
            </p>

            <div class="mt-3 rounded-xl bg-slate-50 p-4 ring-1 ring-inset ring-slate-200">

                <p class="whitespace-pre-line text-sm leading-6 text-slate-700">
                    {{ $session->notes }}
                </p>

            </div>

        </x-ui.card>

    @endif


    {{-- ========================================================= --}}
    {{-- MOVIMIENTOS --}}
    {{-- ========================================================= --}}

    <x-ui.card class="mt-6" padding="p-0">

        <div class="flex flex-col gap-2 border-b border-slate-200 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">

            <div>

                <h2 class="text-lg font-black tracking-tight text-slate-900">
                    Movimientos de la sesión
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Registro completo de entradas y salidas.
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
                            Usuario
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
                                <span class="font-bold text-slate-800">
                                    {{ $movementLabel }}
                                </span>
                            </td>

                            <td class="px-5 py-4 text-xs font-medium text-slate-600">
                                {{ $movement->createdBy?->name ?? 'Sistema' }}
                            </td>

                            <td class="max-w-sm px-5 py-4 text-xs text-slate-500">
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
                            <td colspan="5" class="px-5 py-14 text-center text-sm text-slate-400">
                                No hay movimientos registrados.
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

                        <p class="shrink-0 text-base font-black tabular-nums {{ $isOut ? 'text-rose-600' : 'text-emerald-600' }}">
                            {{ $isOut ? '-' : '+' }}${{ number_format((float) $movement->amount, 2) }}
                        </p>

                    </div>


                    <div class="mt-3 rounded-xl bg-slate-50 p-3">

                        <p class="text-[11px] font-black uppercase tracking-wider text-slate-400">
                            Usuario
                        </p>

                        <p class="mt-1 text-xs font-semibold text-slate-700">
                            {{ $movement->createdBy?->name ?? 'Sistema' }}
                        </p>

                    </div>


                    @if ($movement->notes)

                        <p class="mt-3 text-xs leading-5 text-slate-500">
                            {{ $movement->notes }}
                        </p>

                    @endif

                </article>

            @empty

                <div class="px-5 py-14 text-center text-sm text-slate-400">
                    No hay movimientos registrados.
                </div>

            @endforelse

        </div>

    </x-ui.card>

</x-layouts.app>
