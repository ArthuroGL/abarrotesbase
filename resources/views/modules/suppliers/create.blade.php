<x-layouts.app title="Nuevo Proveedor | ABARROTESBASE">

    <div class="space-y-6">

        {{-- =========================================================
             HEADER
        ========================================================== --}}
        <x-layout.page-header
            eyebrow="Proveedores"
            title="Registrar proveedor"
            description="Agrega un proveedor para utilizarlo posteriormente en compras y control de abastecimiento."
        >
            <x-slot:actions>
                <a
                    href="{{ route('suppliers.index') }}"
                    class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50 hover:text-slate-950 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-500"
                >
                    Volver a proveedores
                </a>
            </x-slot:actions>
        </x-layout.page-header>


        {{-- =========================================================
             VALIDATION ERRORS
        ========================================================== --}}
        @if ($errors->any())
            <div
                class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4"
                role="alert"
            >
                <div class="flex items-start gap-3">

                    <div class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-rose-600 text-xs font-black text-white">
                        !
                    </div>

                    <div class="min-w-0">

                        <p class="text-sm font-black text-rose-900">
                            No se pudo registrar el proveedor.
                        </p>

                        <ul class="mt-2 space-y-1 text-sm font-medium text-rose-700">
                            @foreach ($errors->all() as $error)
                                <li class="flex gap-2">
                                    <span aria-hidden="true">•</span>
                                    <span>{{ $error }}</span>
                                </li>
                            @endforeach
                        </ul>

                    </div>

                </div>
            </div>
        @endif


        {{-- =========================================================
             FORMULARIO
        ========================================================== --}}
        <form
            method="POST"
            action="{{ route('suppliers.store') }}"
            class="space-y-6"
        >
            @csrf


            {{-- =====================================================
                 INFORMACIÓN GENERAL
            ====================================================== --}}
            <x-ui.card padding="p-5 sm:p-6 lg:p-8">

                <div class="mb-6 flex items-start gap-4 border-b border-slate-200 pb-5">

                    <div class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-emerald-50 text-lg font-black text-emerald-700">
                        1
                    </div>

                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.14em] text-emerald-700">
                            Identificación
                        </p>

                        <h2 class="mt-1 text-lg font-black text-slate-950">
                            Información general y fiscal
                        </h2>

                        <p class="mt-1 text-sm leading-6 text-slate-500">
                            Datos utilizados para identificar al proveedor dentro del sistema.
                        </p>
                    </div>

                </div>


                <div class="grid gap-5 sm:grid-cols-12">

                    {{-- Código --}}
                    <div class="sm:col-span-4">

                        <label
                            for="code"
                            class="mb-2 block text-sm font-bold text-slate-700"
                        >
                            Código de proveedor
                        </label>

                        <x-ui.input
                            id="code"
                            name="code"
                            type="text"
                            :value="old('code')"
                            placeholder="PRV-0001"
                            autocomplete="off"
                            :error="$errors->has('code')"
                        />

                        <p class="mt-2 text-xs leading-5 text-slate-500">
                            Déjalo vacío para generar el código automáticamente.
                        </p>

                        @error('code')
                            <p class="mt-1 text-xs font-semibold text-rose-600">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- Razón social --}}
                    <div class="sm:col-span-8">

                        <label
                            for="business_name"
                            class="mb-2 block text-sm font-bold text-slate-700"
                        >
                            Razón social / nombre comercial
                            <span class="text-rose-600">*</span>
                        </label>

                        <x-ui.input
                            id="business_name"
                            name="business_name"
                            type="text"
                            :value="old('business_name')"
                            placeholder="Ej. Comercializadora Abarrotera del Norte S.A. de C.V."
                            autocomplete="organization"
                            required
                            :error="$errors->has('business_name')"
                        />

                        @error('business_name')
                            <p class="mt-1 text-xs font-semibold text-rose-600">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- RFC --}}
                    <div class="sm:col-span-6">

                        <label
                            for="rfc"
                            class="mb-2 block text-sm font-bold text-slate-700"
                        >
                            RFC
                        </label>

                        <x-ui.input
                            id="rfc"
                            name="rfc"
                            type="text"
                            :value="old('rfc')"
                            placeholder="CAN901025T12"
                            autocomplete="off"
                            maxlength="20"
                            class="uppercase"
                            :error="$errors->has('rfc')"
                        />

                        <p class="mt-2 text-xs leading-5 text-slate-500">
                            Opcional. Se recomienda registrarlo para proveedores con facturación.
                        </p>

                        @error('rfc')
                            <p class="mt-1 text-xs font-semibold text-rose-600">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- Crédito --}}
                    <div class="sm:col-span-6">

                        <label
                            for="payment_terms_days"
                            class="mb-2 block text-sm font-bold text-slate-700"
                        >
                            Días de crédito
                            <span class="text-rose-600">*</span>
                        </label>

                        <x-ui.input
                            id="payment_terms_days"
                            name="payment_terms_days"
                            type="number"
                            :value="old('payment_terms_days', 0)"
                            min="0"
                            max="365"
                            step="1"
                            inputmode="numeric"
                            required
                            :error="$errors->has('payment_terms_days')"
                        />

                        <p class="mt-2 text-xs leading-5 text-slate-500">
                            0 = contado · 30 = crédito a 30 días.
                        </p>

                        @error('payment_terms_days')
                            <p class="mt-1 text-xs font-semibold text-rose-600">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                </div>

            </x-ui.card>


            {{-- =====================================================
                 CONTACTO
            ====================================================== --}}
            <x-ui.card padding="p-5 sm:p-6 lg:p-8">

                <div class="mb-6 flex items-start gap-4 border-b border-slate-200 pb-5">

                    <div class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-sky-50 text-lg font-black text-sky-700">
                        2
                    </div>

                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.14em] text-sky-700">
                            Comunicación
                        </p>

                        <h2 class="mt-1 text-lg font-black text-slate-950">
                            Datos de contacto y ubicación
                        </h2>

                        <p class="mt-1 text-sm leading-6 text-slate-500">
                            Información para contactar al vendedor y coordinar entregas.
                        </p>
                    </div>

                </div>


                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">

                    {{-- Contacto --}}
                    <div>

                        <label
                            for="contact_name"
                            class="mb-2 block text-sm font-bold text-slate-700"
                        >
                            Nombre del vendedor / agente
                        </label>

                        <x-ui.input
                            id="contact_name"
                            name="contact_name"
                            type="text"
                            :value="old('contact_name')"
                            placeholder="Carlos Mendoza"
                            autocomplete="name"
                            :error="$errors->has('contact_name')"
                        />

                        @error('contact_name')
                            <p class="mt-1 text-xs font-semibold text-rose-600">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- Teléfono --}}
                    <div>

                        <label
                            for="phone"
                            class="mb-2 block text-sm font-bold text-slate-700"
                        >
                            Teléfono
                        </label>

                        <x-ui.input
                            id="phone"
                            name="phone"
                            type="tel"
                            :value="old('phone')"
                            placeholder="55 1234 5678"
                            autocomplete="tel"
                            :error="$errors->has('phone')"
                        />

                        @error('phone')
                            <p class="mt-1 text-xs font-semibold text-rose-600">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- Email --}}
                    <div>

                        <label
                            for="email"
                            class="mb-2 block text-sm font-bold text-slate-700"
                        >
                            Correo electrónico
                        </label>

                        <x-ui.input
                            id="email"
                            name="email"
                            type="email"
                            :value="old('email')"
                            placeholder="ventas@proveedor.com"
                            autocomplete="email"
                            :error="$errors->has('email')"
                        />

                        @error('email')
                            <p class="mt-1 text-xs font-semibold text-rose-600">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                </div>


                {{-- Dirección --}}
                <div class="mt-5">

                    <label
                        for="address"
                        class="mb-2 block text-sm font-bold text-slate-700"
                    >
                        Dirección de bodega / entrega
                    </label>

                    <textarea
                        id="address"
                        name="address"
                        rows="3"
                        maxlength="1000"
                        class="app-input min-h-28 resize-none"
                        placeholder="Av. Central #450, Col. Industrial, Ciudad de México..."
                    >{{ old('address') }}</textarea>

                    <p class="mt-2 text-xs leading-5 text-slate-500">
                        Registra la dirección utilizada para recibir mercancía cuando sea diferente al domicilio fiscal.
                    </p>

                    @error('address')
                        <p class="mt-1 text-xs font-semibold text-rose-600">
                            {{ $message }}
                        </p>
                    @enderror

                </div>

            </x-ui.card>


            {{-- =====================================================
                 RESUMEN / ACCIONES
            ====================================================== --}}
            <x-ui.card padding="p-5 sm:p-6">

                <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">

                    <div>
                        <p class="text-sm font-black text-slate-900">
                            Proveedor activo
                        </p>

                        <p class="mt-1 max-w-2xl text-sm leading-6 text-slate-500">
                            Los nuevos proveedores se registran activos y estarán disponibles para futuras compras.
                        </p>
                    </div>


                    <div class="flex flex-col-reverse gap-3 sm:flex-row">

                        <a
                            href="{{ route('suppliers.index') }}"
                            class="inline-flex min-h-12 items-center justify-center rounded-xl border border-slate-300 bg-white px-5 text-sm font-bold text-slate-700 transition hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-500"
                        >
                            Cancelar
                        </a>

                        <x-ui.button
                            type="submit"
                            variant="primary"
                            size="lg"
                        >
                            Guardar proveedor
                        </x-ui.button>

                    </div>

                </div>

            </x-ui.card>

        </form>

    </div>

</x-layouts.app>
