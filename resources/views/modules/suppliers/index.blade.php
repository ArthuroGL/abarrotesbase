<x-layouts.app title="Proveedores - SUMA">
    <div class="space-y-6" x-data="supplierModal()">
        {{-- Header & Botón Nuevo --}}
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Catálogo de Proveedores</h1>
                <p class="mt-1 text-sm text-slate-500">
                    Administra las empresas y distribuidores a quienes compras tus productos.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('suppliers.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-emerald-500 px-4 py-2.5 text-sm font-bold text-slate-950 shadow-sm transition hover:bg-emerald-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Nuevo Proveedor
                </a>
            </div>
        </div>

        {{-- Filtros y Buscador --}}
        <x-ui.card padding="p-4" class="shadow-sm border-slate-200 bg-white">
            <form method="GET" action="{{ route('suppliers.index') }}" class="grid gap-3 sm:grid-cols-12">
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
                            placeholder="Buscar por Razón Social, Código, RFC o Contacto..."
                            class="app-input pl-10 text-xs">
                    </div>
                </div>

                <div class="sm:col-span-12 lg:col-span-4">
                    <select name="status" class="app-input text-xs" onchange="this.form.submit()">
                        <option value="">Todos los estatus</option>
                        <option value="active" @selected($status === 'active')>Activos</option>
                        <option value="inactive" @selected($status === 'inactive')>Inactivos</option>
                    </select>
                </div>
            </form>
        </x-ui.card>

        {{-- Tabla de Proveedores --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-700">
                    <thead class="border-b border-slate-200 bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th scope="col" class="px-6 py-3.5">Código / Empresa</th>
                            <th scope="col" class="px-6 py-3.5">RFC</th>
                            <th scope="col" class="px-6 py-3.5">Contacto / Teléfono</th>
                            <th scope="col" class="px-6 py-3.5 text-center">Términos de Pago</th>
                            <th scope="col" class="px-6 py-3.5 text-center">Estatus</th>
                            <th scope="col" class="px-6 py-3.5 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse ($suppliers as $supplier)
                            <tr class="transition hover:bg-slate-50/80">
                                {{-- Razón Social y Código --}}
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900">{{ $supplier->business_name }}</div>
                                    <div class="text-xs text-slate-400 font-mono">Cód: {{ $supplier->code }}</div>
                                </td>

                                {{-- RFC --}}
                                <td class="px-6 py-4 font-mono text-xs text-slate-600">
                                    {{ $supplier->rfc ?? '—' }}
                                </td>

                                {{-- Contacto y Teléfono --}}
                                <td class="px-6 py-4">
                                    <div class="text-slate-800 font-bold text-xs">{{ $supplier->contact_name ?? 'Sin contacto' }}</div>
                                    <div class="text-xs text-slate-400">{{ $supplier->phone ?? $supplier->email ?? 'Sin datos' }}</div>
                                </td>

                                {{-- Términos de Pago --}}
                                <td class="px-6 py-4 text-center">
                                    @if($supplier->payment_terms_days > 0)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-bold text-blue-700 border border-blue-200">
                                            💳 {{ $supplier->payment_terms_days }} días crédito
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600">
                                            Contado (0 días)
                                        </span>
                                    @endif
                                </td>

                                {{-- Estatus --}}
                                <td class="px-6 py-4 text-center">
                                    @if ($supplier->is_active)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 border border-emerald-200">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            Activo
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-50 px-2.5 py-0.5 text-xs font-semibold text-rose-700 border border-rose-200">
                                            <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                                            Inactivo
                                        </span>
                                    @endif
                                </td>

                                {{-- Acciones --}}
                                <td class="px-6 py-4 text-right">
                                    <button
                                        type="button"
                                        @click='openEdit(@json($supplier))'
                                        class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition">
                                        ✏️ Editar
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-400 font-medium">
                                    No se encontraron proveedores registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($suppliers->hasPages())
                <div class="border-t border-slate-100 px-6 py-4 bg-slate-50/50">
                    {{ $suppliers->links() }}
                </div>
            @endif
        </div>

        {{-- Modal Modal Crear/Editar Proveedor --}}
        <template x-teleport="body">
            <div
                x-show="showModal"
                x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-950/60 p-4 backdrop-blur-sm"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100">

                <div
                    @click.away="showModal = false"
                    class="relative w-full max-w-xl rounded-2xl bg-white p-6 shadow-2xl space-y-4 border border-slate-100"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100">

                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h3 class="font-bold text-slate-900 text-base" x-text="isEdit ? 'Editar Proveedor' : 'Nuevo Proveedor'"></h3>
                        <button type="button" @click="showModal = false" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition">✕</button>
                    </div>

                    <form :action="formAction" method="POST" class="space-y-4">
                        @csrf
                        <template x-if="isEdit">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Código Identificador</label>
                                <input type="text" name="code" x-model="form.code" placeholder="Ej. PRV-0001 (Auto)" class="app-input text-xs w-full">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">RFC (Opcional)</label>
                                <input type="text" name="rfc" x-model="form.rfc" placeholder="Ej. ABC123456T12" class="app-input text-xs w-full uppercase">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Razón Social / Nombre Empresa *</label>
                            <input type="text" name="business_name" x-model="form.business_name" placeholder="Ej. Distribuidora de Bebidas del Norte S.A." class="app-input text-xs w-full" required>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Nombre de Contacto</label>
                                <input type="text" name="contact_name" x-model="form.contact_name" placeholder="Ej. Juan Pérez" class="app-input text-xs w-full">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Teléfono</label>
                                <input type="text" name="phone" x-model="form.phone" placeholder="Ej. 8112345678" class="app-input text-xs w-full">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Correo Electrónico</label>
                                <input type="email" name="email" x-model="form.email" placeholder="contacto@proveedor.com" class="app-input text-xs w-full">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Días de Crédito (0 = Contado)</label>
                                <input type="number" name="payment_terms_days" x-model="form.payment_terms_days" placeholder="0" class="app-input text-xs w-full" min="0" required>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Dirección Fiscal / Bodega</label>
                            <textarea name="address" x-model="form.address" rows="2" class="app-input text-xs w-full resize-none" placeholder="Av. Industrial #120, Col. Centro"></textarea>
                        </div>

                        <template x-if="isEdit">
                            <div class="flex items-center gap-2 pt-2">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" id="is_active" value="1" x-model="form.is_active" class="rounded border-slate-300 text-emerald-500 focus:ring-emerald-400">
                                <label for="is_active" class="text-xs font-bold text-slate-700">Proveedor Activo</label>
                            </div>
                        </template>

                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                            <button type="button" @click="showModal = false" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 transition">
                                Cancelar
                            </button>
                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-emerald-500 px-5 py-2 text-xs font-bold text-slate-950 shadow-sm hover:bg-emerald-400 transition">
                                Guardar Proveedor
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>
    </div>

    <script>
        function supplierModal() {
            return {
                showModal: false,
                isEdit: false,
                formAction: '{{ route("suppliers.store") }}',
                form: {
                    id: '',
                    code: '',
                    business_name: '',
                    rfc: '',
                    contact_name: '',
                    phone: '',
                    email: '',
                    payment_terms_days: 0,
                    address: '',
                    is_active: true
                },
                openCreate() {
                    this.isEdit = false;
                    this.formAction = '{{ route("suppliers.store") }}';
                    this.form = { id: '', code: '', business_name: '', rfc: '', contact_name: '', phone: '', email: '', payment_terms_days: 0, address: '', is_active: true };
                    this.showModal = true;
                },
                openEdit(supplier) {
                    this.isEdit = true;
                    this.formAction = `/suppliers/${supplier.id}`;
                    this.form = { ...supplier };
                    this.showModal = true;
                }
            }
        }
    </script>
</x-layouts.app>
