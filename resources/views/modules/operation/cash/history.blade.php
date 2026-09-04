<x-layouts.app title="Historial de Caja | ABARROTESBASE">

    <x-layout.page-header
        eyebrow="Operación"
        title="Historial de caja"
        description="Consulta las sesiones de caja cerradas y sus resultados."
    >
        <x-slot:actions>

            <a
                href="{{ route('cash.index') }}"
                class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-50"
            >
                Volver a caja
            </a>

        </x-slot:actions>
    </x-layout.page-header>


    @if (session('success'))

        <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
            {{ session('success') }}
        </div>

    @endif


    <x-ui.card class="mt-6" padding="p-0">

        <div class="border-b border-slate-200 px-5 py-4">

            <h2 class="font-bold text-slate-900">
                Sesiones cerradas
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Historial de cortes realizados.
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
                            Caja
                        </th>

                        <th class="px-5 py-3">
                            Responsable
                        </th>

                        <th class="px-5 py-3 text-right">
                            Esperado
                        </th>

                        <th class="px-5 py-3 text-right">
                            Contado
                        </th>

                        <th class="px-5 py-3 text-right">
                            Diferencia
                        </th>

                        <th class="px-5 py-3 text-right">
                            Acción
                        </th>

                    </tr>

                </thead>


                <tbody class="divide-y divide-slate-100">

                    @forelse ($sessions as $session)

                        @php
                            $difference = (float) $session->difference_total;
                        @endphp

                        <tr class="hover:bg-slate-50">

                            <td class="px-5 py-4">

                                <p class="font-semibold text-slate-800">
                                    {{ $session->closed_at?->format('d/m/Y') }}
                                </p>

                                <p class="text-xs text-slate-500">
                                    {{ $session->closed_at?->format('H:i') }}
                                </p>

                            </td>


                            <td class="px-5 py-4">

                                <p class="font-semibold text-slate-800">
                                    {{ $session->register?->name ?? 'Sin caja' }}
                                </p>

                                <p class="text-xs text-slate-500">
                                    {{ $session->branch?->name ?? 'Sin sucursal' }}
                                </p>

                            </td>


                            <td class="px-5 py-4 text-slate-700">
                                {{ $session->responsibleUser?->name ?? 'Sin responsable' }}
                            </td>


                            <td class="px-5 py-4 text-right font-semibold text-slate-800">
                                ${{ number_format((float) $session->theoretical_total, 2) }}
                            </td>


                            <td class="px-5 py-4 text-right font-semibold text-slate-800">
                                ${{ number_format((float) $session->counted_total, 2) }}
                            </td>


                            <td class="px-5 py-4 text-right">

                                <span class="font-black {{ $difference < 0 ? 'text-rose-600' : ($difference > 0 ? 'text-amber-600' : 'text-emerald-600') }}">

                                    {{ $difference > 0 ? '+' : '' }}
                                    ${{ number_format($difference, 2) }}

                                </span>

                            </td>


                            <td class="px-5 py-4 text-right">

                                <a
                                    href="{{ route('cash.show', $session) }}"
                                    class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50"
                                >
                                    Ver corte
                                </a>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="7"
                                class="px-5 py-12 text-center text-sm text-slate-400"
                            >
                                No hay cortes registrados todavía.
                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        @if ($sessions->hasPages())

            <div class="border-t border-slate-200 px-5 py-4">
                {{ $sessions->links() }}
            </div>

        @endif

    </x-ui.card>

</x-layouts.app>
