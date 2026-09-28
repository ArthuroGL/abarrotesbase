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

        @if (session('success'))
        <x-ui.alert
            type="success"
            title="Operación completada">
            {{ session('success') }}
        </x-ui.alert>
        @endif


        {{-- ========================================================= --}}
        {{-- ERRORES --}}
        {{-- ========================================================= --}}

        @if ($errors->any())

        <x-ui.alert
            type="error"
            title="No fue posible completar la operación">
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
            variant="primary" />


        {{-- ========================================================= --}}
        {{-- CONTENEDOR --}}
        {{-- ========================================================= --}}

        <x-ui.card padding="p-0" class="overflow-hidden">

            {{-- ENCABEZADO --}}
            <div class="flex flex-col gap-3 border-b border-slate-200 bg-white px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">

                <div>
                    <p class="text-xs font-black uppercase tracking-[0.14em] text-emerald-700">
                        Administración
                    </p>

                    <h2 class="mt-1 text-lg font-black text-slate-900">
                        Usuarios registrados
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Usuarios pertenecientes a la organización actual.
                    </p>
                </div>

                <div class="shrink-0">
                    <span class="inline-flex min-h-9 items-center rounded-full bg-slate-100 px-3 text-sm font-bold text-slate-600">
                        {{ $users->count() }}
                        {{ $users->count() === 1 ? 'usuario' : 'usuarios' }}
                    </span>
                </div>

            </div>


            {{-- TABLA --}}
            <div class="min-w-0 px-3 py-3 sm:px-5">

                <x-ui.table
                    caption="Usuarios registrados"
                    maxHeight="clamp(280px, calc(100vh - 560px), 520px)">

                    <x-slot:head>

                        <tr>

                            <th
                                scope="col"
                                class="whitespace-nowrap px-4 py-3.5 text-left text-xs font-black uppercase tracking-wider text-slate-500 sm:px-5">
                                Usuario
                            </th>

                            <th
                                scope="col"
                                class="whitespace-nowrap px-4 py-3.5 text-left text-xs font-black uppercase tracking-wider text-slate-500 sm:px-5">
                                Rol
                            </th>

                            <th
                                scope="col"
                                class="whitespace-nowrap px-4 py-3.5 text-left text-xs font-black uppercase tracking-wider text-slate-500 sm:px-5">
                                Estado
                            </th>

                            <th
                                scope="col"
                                class="whitespace-nowrap px-4 py-3.5 text-left text-xs font-black uppercase tracking-wider text-slate-500 sm:px-5">
                                Último acceso
                            </th>

                            <th
                                scope="col"
                                class="whitespace-nowrap px-4 py-3.5 text-right text-xs font-black uppercase tracking-wider text-slate-500 sm:px-5">
                                Acciones
                            </th>

                        </tr>

                    </x-slot:head>


                    @forelse ($users as $user)

                    <tr class="group transition hover:bg-slate-50/70">

                        {{-- USUARIO --}}
                        <td class="px-5 py-4">

                            <div class="min-w-0">

                                <p class="truncate text-sm font-black text-slate-900">
                                    {{ $user->name }}
                                </p>

                                <p class="mt-1 max-w-64 truncate text-xs font-semibold text-slate-400">
                                    {{ $user->email }}
                                </p>

                            </div>

                        </td>


                        {{-- ROL --}}
                        <td class="px-5 py-4">

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


                        {{-- ESTADO --}}
                        <td class="px-5 py-4">

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


                        {{-- ÚLTIMO ACCESO --}}
                        <td class="px-5 py-4 text-sm text-slate-500">

                            @if ($user->last_login_at)

                            {{ \Carbon\Carbon::parse($user->last_login_at)->format('d/m/Y H:i') }}

                            @else

                            <span class="text-slate-400">
                                Nunca
                            </span>

                            @endif

                        </td>


                        {{-- ACCIONES --}}
                        <td class="px-5 py-4">

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
                                    class="inline-flex min-h-10 items-center justify-center rounded-xl border border-rose-200 bg-white px-4 text-sm font-bold text-rose-600 transition hover:bg-rose-50">
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
                                    class="inline-flex min-h-10 items-center justify-center rounded-xl border border-emerald-200 bg-white px-4 text-sm font-bold text-emerald-600 transition hover:bg-emerald-50">
                                    Reactivar
                                </button>

                                @endif

                                @endif

                            </div>

                        </td>

                    </tr>

                    @empty

                    <tr>

                        <td colspan="5" class="px-6 py-16 text-center">

                            <div class="mx-auto max-w-md">

                                <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-slate-100 text-xl font-black text-slate-400">
                                    —
                                </div>

                                <h3 class="mt-4 text-base font-black text-slate-900">
                                    No hay usuarios registrados
                                </h3>

                                <p class="mt-1 text-sm leading-6 text-slate-500">
                                    Crea el primer usuario para comenzar a administrar los accesos.
                                </p>

                                <a
                                    href="{{ route('users.create') }}"
                                    class="mt-4 inline-flex min-h-11 items-center justify-center rounded-xl bg-emerald-600 px-5 text-sm font-bold text-white transition hover:bg-emerald-700">
                                    Crear usuario
                                </a>

                            </div>

                        </td>

                    </tr>

                    @endforelse

                </x-ui.table>

            </div>

        </x-ui.card>

    </div>

</x-layouts.app>
