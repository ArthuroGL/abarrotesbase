<x-layouts.app title="Detalle de O.C. {{ $purchase->purchase_number }} - SUMA">
    <div class="space-y-6">

        {{-- Header & Acciones --}}
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ $purchase->purchase_number }}</h1>
                    @if ($purchase->status === 'received')
                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 border border-emerald-200">
                            ✓ Recibida
                        </span>
                    @elseif ($purchase->status === 'approved')
                        <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700 border border-amber-200">
                            ⏳ Pendiente de Recepción
                        </span>
                    @elseif ($purchase->status === 'cancelled')
                        <span class="rounded-full bg-rose-50 px-3 py-1 text-xs font-bold text-rose-700 border border-rose-200">
                            ✕ Rechazada / Cancelada
                        </span>
                    @endif
                </div>
                <p class="mt-1 text-sm text-slate-500">
                    Registrada por {{ $purchase->creator?->name }} el {{ $purchase->created_at->format('d/m/Y H:i') }}
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('purchases.index') }}" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 transition">
                    ← Volver a lista
                </a>

                @if ($purchase->status === 'approved')
                    <button
                        type="button"
                        onclick="toggleModal('cancelModal', true)"
                        class="inline-flex items-center gap-2 rounded-xl bg-rose-50 px-4 py-2.5 text-sm font-bold text-rose-700 border border-rose-200 shadow-sm transition hover:bg-rose-100">
                        🚫 Rechazar Compra
                    </button>

                    <button
                        type="button"
                        onclick="toggleModal('receiveModal', true)"
                        class="inline-flex items-center gap-2 rounded-xl bg-emerald-500 px-4 py-2.5 text-sm font-bold text-slate-950 shadow-sm transition hover:bg-emerald-400">
                        ⚡ Marcar como Recibida
                    </button>
                @endif
            </div>
        </div>

        {{-- Alertas Flash --}}
        @if (session('success'))
            <div class="rounded-2xl bg-emerald-50 p-4 text-emerald-800 border border-emerald-200 font-semibold text-sm">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="rounded-2xl bg-rose-50 p-4 text-rose-800 border border-rose-200 font-semibold text-sm">
                {{ session('error') }}
            </div>
        @endif

        {{-- Información General --}}
        <div class="grid gap-6 md:grid-cols-3">
            <x-ui.card padding="p-5" class="bg-white border-slate-200 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Proveedor</p>
                <p class="mt-1 text-base font-bold text-slate-900">{{ $purchase->supplier?->business_name }}</p>
                <p class="text-xs text-slate-500">RFC: {{ $purchase->supplier?->rfc ?? 'N/A' }}</p>
            </x-ui.card>

            <x-ui.card padding="p-5" class="bg-white border-slate-200 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Referencia / Factura</p>
                <p class="mt-1 text-base font-bold text-slate-900">{{ $purchase->supplier_reference ?? 'Sin referencia' }}</p>
                <p class="text-xs text-slate-500">Moneda: {{ $purchase->currency_code }}</p>
            </x-ui.card>

            <x-ui.card padding="p-5" class="bg-white border-slate-200 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Monto Total</p>
                <p class="mt-1 text-2xl font-black text-emerald-600">${{ number_format((float) $purchase->total, 2) }} <span class="text-xs text-slate-400">MXN</span></p>
            </x-ui.card>
        </div>

        {{-- Tabla de Renglones / Partidas --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-4">
                <h3 class="font-bold text-slate-900 text-sm">Artículos Pedidos</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-700">
                    <thead class="border-b border-slate-200 bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th scope="col" class="px-6 py-3.5">#</th>
                            <th scope="col" class="px-6 py-3.5">Artículo</th>
                            <th scope="col" class="px-6 py-3.5 text-center">Unidad</th>
                            <th scope="col" class="px-6 py-3.5 text-center">Cantidad</th>
                            <th scope="col" class="px-6 py-3.5 text-right">Costo Unitario</th>
                            <th scope="col" class="px-6 py-3.5 text-right">Total Línea</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach ($purchase->lines as $line)
                            <tr>
                                <td class="px-6 py-4 text-xs font-bold text-slate-400">{{ $line->line_number }}</td>
                                <td class="px-6 py-4 font-bold text-slate-900">{{ $line->description }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="rounded bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700">
                                        {{ $line->productUnit?->unit?->name ?? 'PZA' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center font-bold text-slate-800">
                                    {{ number_format((float) $line->ordered_quantity, 2) }}
                                </td>
                                <td class="px-6 py-4 text-right font-mono text-xs">
                                    ${{ number_format((float) $line->unit_cost, 2) }}
                                </td>
                                <td class="px-6 py-4 text-right font-mono font-bold text-slate-900">
                                    ${{ number_format((float) $line->line_total, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if ($purchase->notes)
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-xs text-slate-600">
                <span class="font-bold text-slate-900">Notas adicionales:</span>
                <p class="mt-1 whitespace-pre-line">{{ $purchase->notes }}</p>
            </div>
        @endif

        {{-- Modal Confirmación de Recepción --}}
        <div id="receiveModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl space-y-4 border border-slate-100">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="font-bold text-slate-900 text-base">Confirmar Recepción de Mercancía</h3>
                    <button type="button" onclick="toggleModal('receiveModal', false)" class="text-slate-400 hover:text-slate-600">✕</button>
                </div>

                <p class="text-sm text-slate-600 leading-relaxed">
                    ¿Estás seguro de marcar la orden <strong class="text-slate-900">{{ $purchase->purchase_number }}</strong> como recibida?
                    <br><br>
                    Esta acción **sumará las cantidades físicas al inventario** de la sucursal y **recalculará el costo promedio ponderado** de los artículos.
                </p>

                <form method="POST" action="{{ route('purchases.receive', $purchase) }}" class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    @csrf
                    <button type="button" onclick="toggleModal('receiveModal', false)" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100">
                        Cancelar
                    </button>
                    <button type="submit" class="rounded-xl bg-emerald-500 px-4 py-2 text-xs font-bold text-slate-950 hover:bg-emerald-400">
                        Sí, Ingresar al Inventario
                    </button>
                </form>
            </div>
        </div>

        {{-- Modal Rechazar / Cancelar Compra --}}
        <div id="cancelModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl space-y-4 border border-slate-100">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="font-bold text-rose-700 text-base">Rechazar Orden de Compra</h3>
                    <button type="button" onclick="toggleModal('cancelModal', false)" class="text-slate-400 hover:text-slate-600">✕</button>
                </div>

                <p class="text-sm text-slate-600 leading-relaxed">
                    Indica la razón del rechazo o cancelación de la O.C. <strong class="text-slate-900">{{ $purchase->purchase_number }}</strong>.
                </p>

                <form method="POST" action="{{ route('purchases.cancel', $purchase) }}" class="space-y-4 pt-2">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Motivo / Observaciones</label>
                        <textarea name="reason" rows="3" class="w-full rounded-xl border border-slate-200 p-3 text-sm focus:border-rose-500 focus:ring-rose-500" placeholder="Ej: Precios incorrectos, proveedor canceló el pedido..." required></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" onclick="toggleModal('cancelModal', false)" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100">
                            Volver
                        </button>
                        <button type="submit" class="rounded-xl bg-rose-600 px-4 py-2 text-xs font-bold text-white hover:bg-rose-500">
                            Confirmar Rechazo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Script Vanilla JavaScript Nativo --}}
    <script>
        function toggleModal(modalId, show) {
            const modal = document.getElementById(modalId);
            if (!modal) return;
            if (show) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            } else {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        }
    </script>
</x-layouts.app>
