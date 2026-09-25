<x-layouts.app title="Usuarios | ABARROTESBASE">

    <div class="space-y-6">

        {{-- ========================================================= --}}
        {{-- HEADER --}}
        {{-- ========================================================= --}}

        <x-layout.page-header
            eyebrow="Administración"
            title="Usuarios"
            description="Administra las cuentas de acceso, roles, sucursales y estado de los usuarios.">
            <x-slot:actions>

                <a
                    href="{{ route('users.create') }}"
                    class="inline-flex min-h-12 items-center justify-center rounded-xl bg-emerald-600 px-5 text-sm font-black text-white shadow-sm transition hover:bg-emerald-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">
                    <span class="mr-2 text-lg leading-none">+</span>
                    Nuevo usuario
                </a>

            </x-slot:actions>
        </x-layout.page-header>


        {{-- ========================================================= --}}
        {{-- MENSAJE DE ÉXITO --}}
        {{-- ========================================================= --}}

        <x-ui.alert
            type="success"
            title="Operación completada"
        >
            {{ session('success') }}
        </x-ui.alert>


        {{-- ========================================================= --}}
        {{-- ERRORES --}}
        {{-- ========================================================= --}}

        @if ($errors->any())

            <x-ui.alert
                type="error"
                title="No fue posible completar la operación"
            >
                <ul class="list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>

        @endif


        {{-- ========================================================= --}}
        {{-- CONFIRMACIÓN DE ACCIONES --}}
        {{-- ========================================================= --}}

        <x-ui.confirm
            id="users-confirm-modal"
            title="Confirmar acción"
            variant="primary"
        />


{{-- ========================================================= --}}
        {{-- CONTENEDOR --}}
        {{-- ========================================================= --}}

        <x-ui.card padding="p-0" class="overflow-hidden">

            {{-- ===================================================== --}}
            {{-- ENCABEZADO --}}
            {{-- ===================================================== --}}

            <div class="border-b border-slate-200 bg-slate-50/70 px-5 py-4 sm:px-6">

                <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">

                    <div>

                        <h2 class="text-base font-black text-slate-900">
                            Usuarios registrados
                        </h2>

                        <p class="text-sm text-slate-500">
                            Usuarios pertenecientes a la organización actual.
                        </p>

                    </div>

                    <div class="text-sm font-bold text-slate-500">
                        {{ $users->count() }}
                        {{ $users->count() === 1 ? 'usuario' : 'usuarios' }}
                    </div>

                </div>

            </div>


            {{-- ===================================================== --}}
            {{-- TABLA DESKTOP --}}
            {{-- ===================================================== --}}

            <div class="hidden overflow-x-auto md:block">

                <table class="min-w-full divide-y divide-slate-200">

                    <thead class="bg-white">

                        <tr>

                            <th class="px-6 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                Usuario
                            </th>

                            <th class="px-6 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                Rol
                            </th>

                            <th class="px-6 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                Estado
                            </th>

                            <th class="px-6 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                Último acceso
                            </th>

                            <th class="px-6 py-4 text-right text-xs font-black uppercase tracking-wider text-slate-500">
                                Acciones
                            </th>

                        </tr>

                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white">

                        @forelse ($users as $user)

                        <tr class="transition hover:bg-slate-50">

                            {{-- Usuario --}}

                            <td class="px-6 py-4">

                                <div>

                                    <p class="font-black text-slate-900">
                                        {{ $user->name }}
                                    </p>

                                    <p class="mt-0.5 text-sm text-slate-500">
                                        {{ $user->email }}
                                    </p>

                                </div>

                            </td>


                            {{-- Rol --}}

                            <td class="px-6 py-4">

                                <div class="flex flex-wrap gap-2">

                                    @forelse ($user->roles as $role)

                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">
                                        {{ $role }}
                                    </span>

                                    @empty

                                    <span class="text-sm text-slate-400">
                                        Sin rol
                                    </span>

                                    @endforelse

                                </div>

                            </td>


                            {{-- Estado --}}

                            <td class="px-6 py-4">

                                @if ($user->is_active)

                                <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-black text-emerald-700">
                                    Activo
                                </span>

                                @else

                                <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-500">
                                    Inactivo
                                </span>

                                @endif

                            </td>


                            {{-- Último acceso --}}

                            <td class="px-6 py-4 text-sm text-slate-500">

                                @if ($user->last_login_at)

                                {{ \Carbon\Carbon::parse($user->last_login_at)->format('d/m/Y H:i') }}

                                @else

                                Nunca

                                @endif

                            </td>



                            {{-- Acciones --}}

                            <td class="px-6 py-4">

                                <div class="flex items-center justify-end gap-2">

                                    <a
                                        href="{{ route('users.edit', $user->id) }}"
                                        class="inline-flex min-h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">
                                        Editar
                                    </a>

                                    @if ($user->id !== auth()->id())

                                        @if ($user->is_active)

                                            <button
                                                type="button"
                                                data-confirm-open="users-confirm-modal"
                                                data-confirm-title="Desactivar usuario"
                                                data-confirm-description="¿Desactivar al usuario {{ $user->name }}? El usuario dejará de poder iniciar sesión, pero su historial permanecerá disponible."
                                                data-confirm-action="{{ route('users.destroy', $user->id) }}"
                                                data-confirm-method="DELETE"
                                                data-confirm-text="Desactivar"
                                                data-confirm-variant="danger"
                                                class="inline-flex min-h-10 items-center justify-center rounded-xl border border-rose-200 bg-white px-4 text-sm font-bold text-rose-600 transition hover:bg-rose-50"
                                            >
                                                Desactivar
                                            </button>

                                        @else

                                            <button
                                                type="button"
                                                data-confirm-open="users-confirm-modal"
                                                data-confirm-title="Reactivar usuario"
                                                data-confirm-description="¿Reactivar al usuario {{ $user->name }}? El usuario podrá volver a iniciar sesión."
                                                data-confirm-action="{{ route('users.reactivate', $user->id) }}"
                                                data-confirm-method="PATCH"
                                                data-confirm-text="Reactivar"
                                                data-confirm-variant="primary"
                                                class="inline-flex min-h-10 items-center justify-center rounded-xl border border-emerald-200 bg-white px-4 text-sm font-bold text-emerald-600 transition hover:bg-emerald-50"
                                            >
                                                Reactivar
                                            </button>

                                        @endif

                                    @endif

                                </div>

                            </td>

                        </tr>

                        @empty

                        <tr>

                            <td
                                colspan="5"
                                class="px-6 py-14 text-center">

                                <p class="text-base font-black text-slate-900">
                                    No hay usuarios registrados
                                </p>

                                <p class="mt-1 text-sm text-slate-500">
                                    Crea el primer usuario para comenzar a administrar los accesos.
                                </p>

                                <a
                                    href="{{ route('users.create') }}"
                                    class="mt-5 inline-flex min-h-11 items-center justify-center rounded-xl bg-emerald-600 px-5 text-sm font-black text-white transition hover:bg-emerald-700">
                                    Crear usuario
                                </a>

                            </td>

                        </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>


            {{-- ===================================================== --}}
            {{-- TARJETAS MOBILE --}}
            {{-- ===================================================== --}}

            <div class="divide-y divide-slate-200 md:hidden">

                @forelse ($users as $user)

                <div class="p-5">

                    <div class="flex items-start justify-between gap-4">

                        <div class="min-w-0">

                            <p class="truncate font-black text-slate-900">
                                {{ $user->name }}
                            </p>

                            <p class="mt-1 truncate text-sm text-slate-500">
                                {{ $user->email }}
                            </p>

                        </div>

                        @if ($user->is_active)

                        <span class="shrink-0 rounded-full bg-emerald-50 px-3 py-1 text-xs font-black text-emerald-700">
                            Activo
                        </span>

                        @else

                        <span class="shrink-0 rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-500">
                            Inactivo
                        </span>

                        @endif

                    </div>


                    <div class="mt-4 grid gap-3 sm:grid-cols-2">

                        <div>

                            <p class="text-xs font-black uppercase tracking-wider text-slate-400">
                                Rol
                            </p>

                            <div class="mt-1 flex flex-wrap gap-1.5">

                                @forelse ($user->roles as $role)

                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-700">
                                    {{ $role }}
                                </span>

                                @empty

                                <span class="text-sm text-slate-400">
                                    Sin rol
                                </span>

                                @endforelse

                            </div>

                        </div>


                        <div>

                            <p class="text-xs font-black uppercase tracking-wider text-slate-400">
                                Último acceso
                            </p>

                            <p class="mt-1 text-sm font-semibold text-slate-700">

                                @if ($user->last_login_at)
                                {{ \Carbon\Carbon::parse($user->last_login_at)->format('d/m/Y H:i') }}
                                @else
                                Nunca
                                @endif

                            </p>

                        </div>

                    </div>


                    <div class="mt-5 flex flex-col gap-2 sm:flex-row">

                        <a
                            href="{{ route('users.edit', $user->id) }}"
                            class="inline-flex min-h-11 flex-1 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                            Editar usuario
                        </a>

                        @if ($user->id !== auth()->id())

                            @if ($user->is_active)

                                <button
                                    type="button"
                                    data-confirm-open="users-confirm-modal"
                                    data-confirm-title="Desactivar usuario"
                                    data-confirm-description="¿Desactivar al usuario {{ $user->name }}? El usuario dejará de poder iniciar sesión, pero su historial permanecerá disponible."
                                    data-confirm-action="{{ route('users.destroy', $user->id) }}"
                                    data-confirm-method="DELETE"
                                    data-confirm-text="Desactivar"
                                    data-confirm-variant="danger"
                                    class="inline-flex min-h-11 w-full items-center justify-center rounded-xl border border-rose-200 bg-white px-4 text-sm font-bold text-rose-600 transition hover:bg-rose-50"
                                >
                                    Desactivar
                                </button>

                            @else

                                <button
                                    type="button"
                                    data-confirm-open="users-confirm-modal"
                                    data-confirm-title="Reactivar usuario"
                                    data-confirm-description="¿Reactivar al usuario {{ $user->name }}? El usuario podrá volver a iniciar sesión."
                                    data-confirm-action="{{ route('users.reactivate', $user->id) }}"
                                    data-confirm-method="PATCH"
                                    data-confirm-text="Reactivar"
                                    data-confirm-variant="primary"
                                    class="inline-flex min-h-11 w-full items-center justify-center rounded-xl border border-emerald-200 bg-white px-4 text-sm font-bold text-emerald-600 transition hover:bg-emerald-50"
                                >
                                    Reactivar
                                </button>

                            @endif

                        @endif

                    </div>

                </div>

                @empty

                <div class="px-5 py-14 text-center">

                    <p class="font-black text-slate-900">
                        No hay usuarios registrados
                    </p>

                    <p class="mt-1 text-sm text-slate-500">
                        Crea el primer usuario.
                    </p>

                </div>

                @endforelse

            </div>

        </x-ui.card>

    </div>

</x-layouts.app>
