<x-layouts.app title="Roles y permisos | ABARROTESBASE">

    <div class="space-y-6">

        {{-- ========================================================= --}}
        {{-- HEADER --}}
        {{-- ========================================================= --}}

        <x-layout.page-header
            eyebrow="Administración"
            title="Roles y permisos"
            description="Consulta los perfiles de acceso y administra los permisos de cada rol.">
            <x-slot:actions>

                <a
                    href="{{ route('users.index') }}"
                    class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-5 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                    Usuarios
                </a>

            </x-slot:actions>
        </x-layout.page-header>


        {{-- ========================================================= --}}
        {{-- MENSAJE --}}
        {{-- ========================================================= --}}

        @if (session('success'))

        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4">

            <p class="text-sm font-black text-emerald-900">
                Operación completada
            </p>

            <p class="mt-1 text-sm text-emerald-700">
                {{ session('success') }}
            </p>

        </div>

        @endif


        {{-- ========================================================= --}}
        {{-- ROLES --}}
        {{-- ========================================================= --}}

        <div class="grid gap-5 lg:grid-cols-2">

            @forelse ($roles as $role)

            <x-ui.card>

                <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">

                    <div class="min-w-0">

                        <div class="flex flex-wrap items-center gap-2">

                            <h2 class="text-lg font-black text-slate-900">
                                {{ $role->name }}
                            </h2>

                            @if ($role->is_system)

                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-black uppercase tracking-wider text-slate-600">
                                Sistema
                            </span>

                            @endif

                        </div>

                        <p class="mt-1 text-xs font-bold uppercase tracking-wider text-slate-400">
                            {{ $role->code }}
                        </p>

                        @if ($role->description)

                        <p class="mt-3 text-sm leading-6 text-slate-600">
                            {{ $role->description }}
                        </p>

                        @endif

                    </div>


                    <div class="shrink-0">

                        <span class="inline-flex rounded-full bg-slate-100 px-3 py-1.5 text-xs font-black text-slate-700">

                            {{ (int) $role->permissions_count }}

                            {{ (int) $role->permissions_count === 1
                                    ? 'permiso'
                                    : 'permisos' }}

                        </span>

                    </div>

                </div>


                <div class="mt-6 flex items-center justify-between border-t border-slate-100 pt-5">

                    @if ($role->code === 'admin')

                    <p class="text-sm font-semibold text-slate-500">
                        Acceso total al sistema
                    </p>

                    @else

                    <p class="text-sm text-slate-500">
                        Permisos configurables
                    </p>

                    @endif


                    <a
                        href="{{ route('roles.edit', $role->id) }}"
                        class="inline-flex min-h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                        Administrar permisos
                    </a>

                </div>

            </x-ui.card>

            @empty

            <div class="lg:col-span-2">

                <x-ui.card>

                    <div class="py-10 text-center">

                        <p class="font-black text-slate-900">
                            No hay roles configurados
                        </p>

                        <p class="mt-1 text-sm text-slate-500">
                            Ejecuta el seeder de RBAC para cargar los roles iniciales.
                        </p>

                    </div>

                </x-ui.card>

            </div>

            @endforelse

        </div>

    </div>

</x-layouts.app>
