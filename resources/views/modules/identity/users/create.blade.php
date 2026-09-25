<x-layouts.app title="Nuevo usuario | ABARROTESBASE">

    <div class="space-y-6">

        {{-- ========================================================= --}}
        {{-- HEADER --}}
        {{-- ========================================================= --}}

        <x-layout.page-header
            eyebrow="Administración · Usuarios"
            title="Nuevo usuario"
            description="Crea una cuenta y asigna el rol y la sucursal donde podrá operar."
        >
            <x-slot:actions>

                <a
                    href="{{ route('users.index') }}"
                    class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-5 text-sm font-bold text-slate-700 transition hover:bg-slate-50"
                >
                    Volver a usuarios
                </a>

            </x-slot:actions>
        </x-layout.page-header>


        {{-- ========================================================= --}}
        {{-- ERRORES --}}
        {{-- ========================================================= --}}

        @if ($errors->any())

            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4">

                <p class="text-sm font-black text-rose-900">
                    Revisa la información capturada.
                </p>

                <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-rose-700">

                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach

                </ul>

            </div>

        @endif


        {{-- ========================================================= --}}
        {{-- FORMULARIO --}}
        {{-- ========================================================= --}}

        <form
            method="POST"
            action="{{ route('users.store') }}"
            class="space-y-6"
        >

            @csrf


            {{-- ===================================================== --}}
            {{-- INFORMACIÓN DEL USUARIO --}}
            {{-- ===================================================== --}}

            <x-ui.card>

                <div class="border-b border-slate-200 pb-5">

                    <h2 class="text-base font-black text-slate-900">
                        Información del usuario
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Datos utilizados para identificar y autenticar la cuenta.
                    </p>

                </div>


                <div class="mt-6 grid gap-5 md:grid-cols-2">

                    {{-- Nombre --}}

                    <div class="md:col-span-2">

                        <label
                            for="name"
                            class="mb-2 block text-sm font-bold text-slate-800"
                        >
                            Nombre completo
                        </label>

                        <x-ui.input
                            id="name"
                            name="name"
                            type="text"
                            value="{{ old('name') }}"
                            placeholder="Ej. Juan Pérez García"
                            autocomplete="name"
                            required
                        />

                        @error('name')
                            <p class="mt-1.5 text-sm font-semibold text-rose-600">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- Correo --}}

                    <div>

                        <label
                            for="email"
                            class="mb-2 block text-sm font-bold text-slate-800"
                        >
                            Correo electrónico
                        </label>

                        <x-ui.input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            placeholder="usuario@ejemplo.com"
                            autocomplete="email"
                            required
                        />

                        @error('email')
                            <p class="mt-1.5 text-sm font-semibold text-rose-600">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- Contraseña --}}

                    <div>

                        <label
                            for="password"
                            class="mb-2 block text-sm font-bold text-slate-800"
                        >
                            Contraseña
                        </label>

                        <x-ui.input
                            id="password"
                            name="password"
                            type="password"
                            placeholder="Mínimo 8 caracteres"
                            autocomplete="new-password"
                            required
                        />

                        @error('password')
                            <p class="mt-1.5 text-sm font-semibold text-rose-600">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- Confirmar contraseña --}}

                    <div>

                        <label
                            for="password_confirmation"
                            class="mb-2 block text-sm font-bold text-slate-800"
                        >
                            Confirmar contraseña
                        </label>

                        <x-ui.input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            placeholder="Repite la contraseña"
                            autocomplete="new-password"
                            required
                        />

                    </div>

                </div>

            </x-ui.card>


            {{-- ===================================================== --}}
            {{-- ACCESO --}}
            {{-- ===================================================== --}}

            <x-ui.card>

                <div class="border-b border-slate-200 pb-5">

                    <h2 class="text-base font-black text-slate-900">
                        Acceso y operación
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Define el perfil y la sucursal a la que pertenecerá el usuario.
                    </p>

                </div>


                <div class="mt-6 grid gap-5 md:grid-cols-2">

                    {{-- Rol --}}

                    <div>

                        <label
                            for="role_id"
                            class="mb-2 block text-sm font-bold text-slate-800"
                        >
                            Rol
                        </label>

                        <select
                            id="role_id"
                            name="role_id"
                            required
                            class="block min-h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-800 shadow-sm outline-none transition focus:border-slate-400 focus:ring-2 focus:ring-slate-200"
                        >

                            <option value="">
                                Selecciona un rol
                            </option>

                            @foreach ($roles as $role)

                                <option
                                    value="{{ $role->id }}"
                                    @selected(old('role_id') === $role->id)
                                >
                                    {{ $role->name }}
                                </option>

                            @endforeach

                        </select>

                        @error('role_id')
                            <p class="mt-1.5 text-sm font-semibold text-rose-600">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- Sucursal --}}

                    <div>

                        <label
                            for="branch_id"
                            class="mb-2 block text-sm font-bold text-slate-800"
                        >
                            Sucursal
                        </label>

                        <select
                            id="branch_id"
                            name="branch_id"
                            required
                            class="block min-h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-800 shadow-sm outline-none transition focus:border-slate-400 focus:ring-2 focus:ring-slate-200"
                        >

                            <option value="">
                                Selecciona una sucursal
                            </option>

                            @foreach ($branches as $branch)

                                <option
                                    value="{{ $branch->id }}"
                                    @selected(old('branch_id') === $branch->id)
                                >
                                    {{ $branch->name }}
                                    @if ($branch->code)
                                        · {{ $branch->code }}
                                    @endif
                                </option>

                            @endforeach

                        </select>

                        @error('branch_id')
                            <p class="mt-1.5 text-sm font-semibold text-rose-600">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                </div>


                {{-- Aviso --}}

                <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">

                    <p class="text-sm font-black text-slate-800">
                        Sobre los permisos
                    </p>

                    <p class="mt-1 text-sm leading-6 text-slate-600">
                        Los permisos se obtienen automáticamente a partir del rol seleccionado.
                        No es necesario asignarlos uno por uno al usuario.
                    </p>

                </div>

            </x-ui.card>


            {{-- ===================================================== --}}
            {{-- ACCIONES --}}
            {{-- ===================================================== --}}

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

                <a
                    href="{{ route('users.index') }}"
                    class="inline-flex min-h-12 items-center justify-center rounded-xl border border-slate-200 bg-white px-6 text-sm font-bold text-slate-700 transition hover:bg-slate-50"
                >
                    Cancelar
                </a>

                <x-ui.button
                    type="submit"
                    variant="primary"
                >
                    Crear usuario
                </x-ui.button>

            </div>

        </form>

    </div>

</x-layouts.app>
