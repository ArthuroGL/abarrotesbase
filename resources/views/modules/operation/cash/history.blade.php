<x-layouts.app title="Historial de Caja | ABARROTESBASE">

    <x-layout.page-header
        eyebrow="Operación"
        title="Historial de caja"
        description="Consulta las sesiones de caja cerradas y sus resultados."
    >
        <x-slot:actions>
            <x-ui.button
                variant="secondary"
                type="button"
                onclick="window.location.href='{{ route('cash.index') }}'"
            >
                Volver a caja
            </x-ui.button>
        </x-slot:actions>
    </x-layout.page-header>


    {{-- Mensaje de éxito --}}
    @if (session('success'))
        <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4">
            <div class="flex items-start gap-3">
                <div class="mt-0.5 grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-emerald-100 text-sm font-black text-emerald-700">
                    ✓
                </div>

                <div>
                    <p class="text-sm font-bold text-emerald-900">
                        Operación completada
                    </p>

                    <p class="mt-1 text-sm text-emerald-700">
                        {{ session('success') }}
                    </p>
                </div>
            </div>
        </div>
    @endif


    {{-- Contenido --}}
    <x-ui.card class="mt-6" padding="p-0">

        {{-- Encabezado --}}
        <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">

            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-black tracking-tight text-slate-900">
                        Sesiones cerradas
                    </h2>

                    <span class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-500">
                        {{ $sessions->total() }}
                    </span>
                </div>

                <p class="mt-1 text-sm text-slate-500">
                    Historial de cortes realizados durante la operación.
                </p>
            </div>

        </div>


        {{-- Tabla escritorio --}}
        <div class="hidden overflow-x-auto md:block">

            <table class="w-full text-left text-sm">

                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr class="text-[11px] font-black uppercase tracking-wider text-slate-500">

                        <th class="px-5 py-3.5">
                            Fecha
                        </th>

                        <th class="px-5 py-3.5">
                            Caja
                        </th>

                        <th class="px-5 py-3.5">
                            Responsable
                        </th>

                        <th class="px-5 py-3.5 text-right">
                            Esperado
                        </th>

                        <th class="px-5 py-3.5 text-right">
                            Contado
                        </th>

                        <th class="px-5 py-3.5 text-right">
                            Diferencia
                        </th>

                        <th class="px-5 py-3.5 text-right">
                            Acción
                        </th>

                    </tr>
                </thead>


                <tbody class="divide-y divide-slate-100">

                    @forelse ($sessions as $session)

                        @php
                            $difference = (float) $session->difference_total;

                            if ($difference < 0) {
                                $differenceLabel = 'Faltante';
                                $differenceClass = 'text-rose-600';
                                $differenceBadge = 'bg-rose-50 text-rose-700 ring-rose-200';
                            } elseif ($difference > 0) {
                                $differenceLabel = 'Sobrante';
                                $differenceClass = 'text-amber-600';
                                $differenceBadge = 'bg-amber-50 text-amber-700 ring-amber-200';
                            } else {
                                $differenceLabel = 'Corte exacto';
                                $differenceClass = 'text-emerald-600';
                                $differenceBadge = 'bg-emerald-50 text-emerald-700 ring-emerald-200';
                            }
                        @endphp

                        <tr class="transition hover:bg-slate-50/80">

                            {{-- Fecha --}}
                            <td class="px-5 py-4">

                                <p class="font-bold text-slate-800">
                                    {{ $session->closed_at?->format('d/m/Y') ?? 'Sin fecha' }}
                                </p>

                                <p class="mt-0.5 text-xs text-slate-500">
                                    {{ $session->closed_at?->format('H:i') ?? '--:--' }}
                                </p>

                            </td>


                            {{-- Caja --}}
                            <td class="px-5 py-4">

                                <p class="font-bold text-slate-800">
                                    {{ $session->register?->name ?? 'Sin caja' }}
                                </p>

                                <p class="mt-0.5 text-xs text-slate-500">
                                    {{ $session->branch?->name ?? 'Sin sucursal' }}
                                </p>

                            </td>


                            {{-- Responsable --}}
                            <td class="px-5 py-4">

                                <p class="font-semibold text-slate-700">
                                    {{ $session->responsibleUser?->name ?? 'Sin responsable' }}
                                </p>

                            </td>


                            {{-- Esperado --}}
                            <td class="px-5 py-4 text-right">

                                <p class="font-bold tabular-nums text-slate-800">
                                    ${{ number_format((float) $session->theoretical_total, 2) }}
                                </p>

                            </td>


                            {{-- Contado --}}
                            <td class="px-5 py-4 text-right">

                                <p class="font-bold tabular-nums text-slate-800">
                                    ${{ number_format((float) $session->counted_total, 2) }}
                                </p>

                            </td>


                            {{-- Diferencia --}}
                            <td class="px-5 py-4 text-right">

                                <div class="flex flex-col items-end gap-1">

                                    <span class="font-black tabular-nums {{ $differenceClass }}">
                                        {{ $difference > 0 ? '+' : '' }}${{ number_format($difference, 2) }}
                                    </span>

                                    <span class="inline-flex rounded-lg px-2 py-1 text-[11px] font-bold ring-1 ring-inset {{ $differenceBadge }}">
                                        {{ $differenceLabel }}
                                    </span>

                                </div>

                            </td>


                            {{-- Acción --}}
                            <td class="px-5 py-4 text-right">

                                <x-ui.button
                                    variant="secondary"
                                    size="sm"
                                    type="button"
                                    onclick="window.location.href='{{ route('cash.show', $session) }}'"
                                >
                                    Ver corte
                                </x-ui.button>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="7" class="px-5 py-16">

                                <div class="mx-auto max-w-md text-center">

                                    <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-slate-100 text-xl font-black text-slate-500">
                                        $
                                    </div>

                                    <h3 class="mt-5 text-base font-black text-slate-900">
                                        No hay cortes registrados
                                    </h3>

                                    <p class="mt-2 text-sm leading-6 text-slate-500">
                                        Las sesiones de caja aparecerán aquí después de realizar y cerrar un arqueo.
                                    </p>

                                    <div class="mt-5">
                                        <x-ui.button
                                            variant="secondary"
                                            type="button"
                                            onclick="window.location.href='{{ route('cash.index') }}'"
                                        >
                                            Ir a caja
                                        </x-ui.button>
                                    </div>

                                </div>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- Cards móvil --}}
        <div class="divide-y divide-slate-100 md:hidden">

            @forelse ($sessions as $session)

                @php
                    $difference = (float) $session->difference_total;

                    if ($difference < 0) {
                        $differenceLabel = 'Faltante';
                        $differenceClass = 'text-rose-600';
                        $differenceBadge = 'bg-rose-50 text-rose-700 ring-rose-200';
                    } elseif ($difference > 0) {
                        $differenceLabel = 'Sobrante';
                        $differenceClass = 'text-amber-600';
                        $differenceBadge = 'bg-amber-50 text-amber-700 ring-amber-200';
                    } else {
                        $differenceLabel = 'Corte exacto';
                        $differenceClass = 'text-emerald-600';
                        $differenceBadge = 'bg-emerald-50 text-emerald-700 ring-emerald-200';
                    }
                @endphp

                <article class="p-5">

                    {{-- Encabezado --}}
                    <div class="flex items-start justify-between gap-4">

                        <div class="min-w-0">

                            <p class="font-black text-slate-900">
                                {{ $session->register?->name ?? 'Sin caja' }}
                            </p>

                            <p class="mt-1 text-xs text-slate-500">
                                {{ $session->branch?->name ?? 'Sin sucursal' }}
                            </p>

                        </div>

                        <span class="shrink-0 rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600">
                            Cerrada
                        </span>

                    </div>


                    {{-- Fecha --}}
                    <div class="mt-4 rounded-xl bg-slate-50 p-3">

                        <p class="text-[11px] font-black uppercase tracking-wider text-slate-400">
                            Fecha de cierre
                        </p>

                        <p class="mt-1 text-sm font-bold text-slate-800">
                            {{ $session->closed_at?->format('d/m/Y H:i') ?? 'Sin fecha' }}
                        </p>

                        <p class="mt-1 text-xs text-slate-500">
                            Responsable:
                            {{ $session->responsibleUser?->name ?? 'Sin responsable' }}
                        </p>

                    </div>


                    {{-- Totales --}}
                    <div class="mt-4 grid grid-cols-2 gap-3">

                        <div class="rounded-xl border border-slate-200 p-3">

                            <p class="text-[11px] font-black uppercase tracking-wider text-slate-400">
                                Esperado
                            </p>

                            <p class="mt-1 text-base font-black tabular-nums text-slate-900">
                                ${{ number_format((float) $session->theoretical_total, 2) }}
                            </p>

                        </div>


                        <div class="rounded-xl border border-slate-200 p-3">

                            <p class="text-[11px] font-black uppercase tracking-wider text-slate-400">
                                Contado
                            </p>

                            <p class="mt-1 text-base font-black tabular-nums text-slate-900">
                                ${{ number_format((float) $session->counted_total, 2) }}
                            </p>

                        </div>

                    </div>


                    {{-- Diferencia --}}
                    <div class="mt-3 flex items-center justify-between rounded-xl border p-3 {{ str_contains($differenceBadge, 'rose') ? 'border-rose-200 bg-rose-50' : (str_contains($differenceBadge, 'amber') ? 'border-amber-200 bg-amber-50' : 'border-emerald-200 bg-emerald-50') }}">

                        <div>

                            <p class="text-[11px] font-black uppercase tracking-wider text-slate-500">
                                Diferencia
                            </p>

                            <p class="mt-1 text-xs font-bold text-slate-600">
                                {{ $differenceLabel }}
                            </p>

                        </div>

                        <p class="text-lg font-black tabular-nums {{ $differenceClass }}">
                            {{ $difference > 0 ? '+' : '' }}${{ number_format($difference, 2) }}
                        </p>

                    </div>


                    {{-- Acción --}}
                    <div class="mt-4">

                        <x-ui.button
                            variant="secondary"
                            type="button"
                            class="w-full"
                            onclick="window.location.href='{{ route('cash.show', $session) }}'"
                        >
                            Ver detalle del corte
                        </x-ui.button>

                    </div>

                </article>

            @empty

                <div class="px-5 py-14 text-center">

                    <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-slate-100 text-xl font-black text-slate-500">
                        $
                    </div>

                    <h3 class="mt-5 text-base font-black text-slate-900">
                        No hay cortes registrados
                    </h3>

                    <p class="mt-2 text-sm leading-6 text-slate-500">
                        Las sesiones cerradas aparecerán aquí.
                    </p>

                </div>

            @endforelse

        </div>


        {{-- Paginación --}}
        @if ($sessions->hasPages())

            <div class="border-t border-slate-200 px-5 py-4 sm:px-6">
                {{ $sessions->links() }}
            </div>

        @endif

    </x-ui.card>

</x-layouts.app>
