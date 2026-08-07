<x-layouts.app title="Compras y Surtido - SUMA">
    <div class="space-y-6">
        {{-- Header & Botón Principal --}}
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Compras y Surtido</h1>
                <p class="mt-1 text-sm text-slate-500">
                    Administra las órdenes de compra a proveedores e ingresa mercancía al almacén.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('purchases.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-emerald-500 px-4 py-2.5 text-sm font-bold text-slate-950 shadow-sm transition hover:bg-emerald-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Nueva Compra
                </a>
            </div>
        </div>

        {{-- Filtros y Buscador --}}
        <x-ui.card padding="p-4" class="shadow-sm border-slate-200 bg-white">
            <form method="GET" action="{{ route('purchases.index') }}" class="grid gap-3 sm:grid-cols-12">
                <div class="sm:col-span-12 lg:col-span-8">
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                        </div>
                        <input
                            type="text"
                            name="search"
                            value="{{ $search }}"
                            placeholder="Buscar por # O.C., Folio Proveedor o Nombre del Proveedor..."
                            class="app-input pl-10 text-xs">
                    </div>
                </div>

                <div class="sm:col-span-12 lg:col-span-4">
                    <select name="status" class="app-input text-xs" onchange="this.form.submit()">
                        <option value="">Todos los estados</option>
                        <option value="draft" @selected($status === 'draft')>Borrador</option>
                        <option value="approved" @selected($status === 'approved')>Aprobada (Por Recibir)</option>
                        <option value="received" @selected($status === 'received')>Recibida</option>
                        <option value="cancelled" @selected($status === 'cancelled')>Cancelada</option>
                    </select>
                </div>
            </form>
        </x-ui.card>

        {{-- Tabla de Compras --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-700">
                    <thead class="border-b border-slate-200 bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th scope="col" class="px-6 py-3.5">Folio / Fecha</th>
                            <th scope="col" class="px-6 py-3.5">Proveedor / Ref</th>
                            <th scope="col" class="px-6 py-3.5 text-right">Total</th>
                            <th scope="col" class="px-6 py-3.5 text-center">Estado</th>
                            <th scope="col" class="px-6 py-3.5 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse ($purchases as $purchase)
                            <tr class="transition hover:bg-slate-50/80">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900">{{ $purchase->purchase_number }}</div>
                                    <div class="text-xs text-slate-400">{{ $purchase->created_at->format('d/m/Y H:i') }}</div>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-800">{{ $purchase->supplier?->business_name }}</div>
                                    <div class="text-xs text-slate-400">Ref: {{ $purchase->supplier_reference ?? 'Sin referencia' }}</div>
                                </td>

                                <td class="px-6 py-4 text-right font-black text-slate-900">
                                    ${{ number_format((float) $purchase->total, 2) }} <span class="text-[10px] font-normal text-slate-400">MXN</span>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    @if ($purchase->status === 'received')
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 border border-emerald-200">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            Recibida
                                        </span>
                                    @elseif ($purchase->status === 'approved')
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-700 border border-amber-200">
                                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                            Por Recibir
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-500 border border-slate-200">
                                            {{ ucfirst($purchase->status) }}
                                        </span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('purchases.show', $purchase) }}" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition">
                                        Ver Detalle 👁️
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-400 font-medium">
                                    No hay órdenes de compra registradas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($purchases->hasPages())
                <div class="border-t border-slate-100 px-6 py-4 bg-slate-50/50">
                    {{ $purchases->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
