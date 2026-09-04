<x-layouts.app title="Detalle de Corte | ABARROTESBASE">

    <x-layout.page-header
        eyebrow="Caja"
        title="Detalle del corte"
        description="Consulta el resultado completo de la sesión de caja."
    >
        <x-slot:actions>

            <a
                href="{{ route('cash.history') }}"
                class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50"
            >
                Historial
            </a>

            <a
                href="{{ route('cash.index') }}"
                class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white hover:bg-slate-800"
            >
                Caja
            </a>

        </x-slot:actions>
    </x-layout.page-header>


    {{-- Información general --}}
    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <x-ui.card>

            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                Caja
            </p>

            <p class="mt-2 font-black text-slate-900">
                {{ $session->register?->name ?? 'Sin caja' }}
            </p>

            <p class="mt-1 text-xs text-slate-500">
                {{ $session->branch?->name ?? 'Sin sucursal' }}
            </p>

        </x-ui.card>


        <x-ui.card>

            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                Responsable
            </p>

            <p class="mt-2 font-black text-slate-900">
                {{ $session->responsibleUser?->name ?? 'Sin responsable' }}
            </p>

            <p class="mt-1 text-xs text-slate-500">
                Apertura:
                {{ $session->opened_at?->format('d/m/Y H:i') }}
            </p>

        </x-ui.card>


        <x-ui.card>

            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                Cierre
            </p>

            <p class="mt-2 font-black text-slate-900">
                {{ $session->closed_at?->format('d/m/Y H:i') }}
            </p>

            <p class="mt-1 text-xs text-slate-500">
                Cerrado por:
                {{ $session->closedBy?->name ?? 'Sin información' }}
            </p>

        </x-ui.card>


        <x-ui.card>

            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                Estado
            </p>

            <span class="mt-2 inline-flex rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-700">
                Cerrada
            </span>

        </x-ui.card>

    </div>


    {{-- Resultado --}}
    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <x-ui.card>

            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                Fondo inicial
            </p>

            <p class="mt-2 text-2xl font-black text-slate-900">
                ${{ number_format((float) $session->opening_float, 2) }}
            </p>

        </x-ui.card>


        <x-ui.card>

            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                Efectivo esperado
            </p>

            <p class="mt-2 text-2xl font-black text-slate-900">
                ${{ number_format((float) $session->theoretical_total, 2) }}
            </p>

        </x-ui.card>


        <x-ui.card>

            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                Efectivo contado
            </p>

            <p class="mt-2 text-2xl font-black text-slate-900">
                ${{ number_format((float) $session->counted_total, 2) }}
            </p>

        </x-ui.card>


        @php
            $difference = (float) $session->difference_total;
        @endphp

        <x-ui.card>

            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                Diferencia
            </p>

            <p class="mt-2 text-2xl font-black {{ $difference < 0 ? 'text-rose-600' : ($difference > 0 ? 'text-amber-600' : 'text-emerald-600') }}">

                {{ $difference > 0 ? '+' : '' }}
                ${{ number_format($difference, 2) }}

            </p>

            <p class="mt-1 text-xs text-slate-500">

                @if ($difference < 0)
                    Faltante
                @elseif ($difference > 0)
                    Sobrante
                @else
                    Corte exacto
                @endif

            </p>

        </x-ui.card>

    </div>


    {{-- Resumen de movimientos --}}
    <x-ui.card class="mt-6">

        <div class="grid gap-5 sm:grid-cols-3">

            <div>

                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                    Entradas
                </p>

                <p class="mt-2 text-xl font-black text-emerald-600">
                    +${{ number_format($cashIn, 2) }}
                </p>

            </div>


            <div>

                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                    Salidas
                </p>

                <p class="mt-2 text-xl font-black text-rose-600">
                    -${{ number_format($cashOut, 2) }}
                </p>

            </div>


            <div>

                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                    Movimientos
                </p>

                <p class="mt-2 text-xl font-black text-slate-900">
                    {{ $movements->count() }}
                </p>

            </div>

        </div>

    </x-ui.card>


    {{-- Notas --}}
    @if ($session->notes)

        <x-ui.card class="mt-6">

            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                Notas
            </p>

            <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-700">
                {{ $session->notes }}
            </p>

        </x-ui.card>

    @endif


    {{-- Movimientos --}}
    <x-ui.card class="mt-6" padding="p-0">

        <div class="border-b border-slate-200 px-5 py-4">

            <h2 class="font-bold text-slate-900">
                Movimientos de la sesión
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Registro completo de entradas y salidas.
            </p>

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
                            Usuario
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

                        <tr>

                            <td class="px-5 py-4 text-xs text-slate-500">
                                {{ $movement->occurred_at->format('d/m/Y H:i') }}
                            </td>


                            <td class="px-5 py-4 font-semibold text-slate-800">

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

                            </td>


                            <td class="px-5 py-4 text-xs text-slate-600">
                                {{ $movement->createdBy?->name ?? 'Sistema' }}
                            </td>


                            <td class="px-5 py-4 text-xs text-slate-500">
                                {{ $movement->notes ?: 'Sin notas' }}
                            </td>


                            <td class="px-5 py-4 text-right font-black {{ $isOut ? 'text-rose-600' : 'text-emerald-600' }}">

                                {{ $isOut ? '-' : '+' }}
                                ${{ number_format((float) $movement->amount, 2) }}

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="5"
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

</x-layouts.app>
