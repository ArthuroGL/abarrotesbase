<x-layouts.app title="Nuevo Proveedor - SUMA">
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Registrar Nuevo Proveedor</h1>
                <p class="mt-1 text-sm text-slate-500">
                    Ingresa los datos fiscales, comerciales y de contacto de tu distribuidor.
                </p>
            </div>
            <div>
                <a href="{{ route('suppliers.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-600 shadow-sm transition hover:bg-slate-50">
                    ← Cancelar y Volver
                </a>
            </div>
        </div>

        {{-- Formulario --}}
        <form method="POST" action="{{ route('suppliers.store') }}" class="space-y-6">
            @csrf

            {{-- Datos Generales y Fiscales --}}
            <x-ui.card padding="p-6" class="bg-white border-slate-200 shadow-sm space-y-4">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-2">
                    1. Información General y Fiscal
                </h3>

                <div class="grid gap-4 sm:grid-cols-12">
                    <div class="sm:col-span-4">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Código de Proveedor</label>
                        <input
                            type="text"
                            name="code"
                            value="{{ old('code') }}"
                            placeholder="Ej. PRV-0001 (Auto si se deja vacío)"
                            class="app-input text-xs w-full @error('code') border-rose-500 @enderror">
                        @error('code')
                            <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-8">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Razón Social / Nombre Comercial *</label>
                        <input
                            type="text"
                            name="business_name"
                            value="{{ old('business_name') }}"
                            placeholder="Ej. Comercializadora Abarrotera del Norte S.A. de C.V."
                            class="app-input text-xs w-full @error('business_name') border-rose-500 @enderror"
                            required>
                        @error('business_name')
                            <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-6">
                        <label class="block text-xs font-bold text-slate-700 mb-1">RFC (Opcional)</label>
                        <input
                            type="text"
                            name="rfc"
                            value="{{ old('rfc') }}"
                            placeholder="Ej. CAN901025T12"
                            class="app-input text-xs w-full uppercase @error('rfc') border-rose-500 @enderror">
                        @error('rfc')
                            <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-6">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Días de Crédito (0 = Pago de Contado)</label>
                        <input
                            type="number"
                            name="payment_terms_days"
                            value="{{ old('payment_terms_days', 0) }}"
                            placeholder="0"
                            min="0"
                            max="365"
                            class="app-input text-xs w-full @error('payment_terms_days') border-rose-500 @enderror"
                            required>
                        @error('payment_terms_days')
                            <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </x-ui.card>

            {{-- Datos de Contacto --}}
            <x-ui.card padding="p-6" class="bg-white border-slate-200 shadow-sm space-y-4">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-2">
                    2. Datos de Contacto y Ubicación
                </h3>

                <div class="grid gap-4 sm:grid-cols-12">
                    <div class="sm:col-span-4">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nombre del Vendedor / Agente</label>
                        <input
                            type="text"
                            name="contact_name"
                            value="{{ old('contact_name') }}"
                            placeholder="Ej. Carlos Mendoza"
                            class="app-input text-xs w-full">
                    </div>

                    <div class="sm:col-span-4">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Teléfono Móvil / Oficina</label>
                        <input
                            type="text"
                            name="phone"
                            value="{{ old('phone') }}"
                            placeholder="Ej. 81 1234 5678"
                            class="app-input text-xs w-full">
                    </div>

                    <div class="sm:col-span-4">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Correo Electrónico</label>
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="ventas@proveedor.com"
                            class="app-input text-xs w-full">
                    </div>

                    <div class="sm:col-span-12">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Dirección de Bodega / Entrega</label>
                        <textarea
                            name="address"
                            rows="2"
                            class="app-input text-xs w-full resize-none"
                            placeholder="Av. Central #450, Col. Industrial, Monterrey N.L.">{{ old('address') }}</textarea>
                    </div>
                </div>
            </x-ui.card>

            {{-- Botón Guardar --}}
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('suppliers.index') }}" class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 transition">
                    Cancelar
                </a>
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-emerald-500 px-6 py-2.5 text-xs font-bold text-slate-950 shadow-sm hover:bg-emerald-400 transition">
                    💾 Guardar Proveedor
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
