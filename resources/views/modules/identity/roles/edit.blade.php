<x-layouts.app title="Permisos del rol | ABARROTESBASE">

    <div class="space-y-6">

        {{-- ========================================================= --}}
        {{-- HEADER --}}
        {{-- ========================================================= --}}

        <x-layout.page-header
            eyebrow="Administración · Roles"
            title="{{ $role->name }}"
            description="Configura los permisos que tendrá este perfil dentro de la organización."
        >
            <x-slot:actions>

                <a
                    href="{{ route('roles.index') }}"
                    class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-5 text-sm font-bold text-slate-700 transition hover:bg-slate-50"
                >
                    Volver a roles
                </a>

            </x-slot:actions>
        </x-layout.page-header>


        {{-- ========================================================= --}}
        {{-- MENSAJES --}}
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


        @if (session('info'))

            <div class="rounded-2xl border border-sky-200 bg-sky-50 px-5 py-4">

                <p class="text-sm font-black text-sky-900">
                    Información
                </p>

                <p class="mt-1 text-sm text-sky-700">
                    {{ session('info') }}
                </p>

            </div>

        @endif


        @if ($errors->any())

            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4">

                <p class="text-sm font-black text-rose-900">
                    No fue posible guardar los permisos.
                </p>

                <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-rose-700">

                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach

                </ul>

            </div>

        @endif


        {{-- ========================================================= --}}
        {{-- INFORMACIÓN DEL ROL --}}
        {{-- ========================================================= --}}

        <x-ui.card>

            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">

                <div>

                    <div class="flex flex-wrap items-center gap-2">

                        <h2 class="text-lg font-black text-slate-900">
                            {{ $role->name }}
                        </h2>

                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-black uppercase tracking-wider text-slate-600">
                            {{ $role->code }}
                        </span>

                    </div>

                    @if ($role->description)

                        <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
                            {{ $role->description }}
                        </p>

                    @endif

                </div>


                @if ($role->code === 'admin')

                    <span class="inline-flex shrink-0 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-black text-emerald-700">
                        Acceso total
                    </span>

                @else

                    <span
                        id="permission-counter"
                        class="inline-flex shrink-0 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-black text-slate-700"
                    >
                        {{ count($selectedPermissionIds) }} seleccionados
                    </span>

                @endif

            </div>

        </x-ui.card>


        {{-- ========================================================= --}}
        {{-- ADMIN --}}
        {{-- ========================================================= --}}

        @if ($role->code === 'admin')

            <x-ui.card>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-5 py-5">

                    <h2 class="text-base font-black text-slate-900">
                        Administrador
                    </h2>

                    <p class="mt-1 text-sm leading-6 text-slate-600">
                        Este rol dispone de acceso total al sistema mediante la
                        regla administrativa del middleware. Sus permisos no se
                        modifican desde esta pantalla.
                    </p>

                </div>

            </x-ui.card>

        @else


            {{-- ===================================================== --}}
            {{-- FORMULARIO --}}
            {{-- ===================================================== --}}

            <form
                method="POST"
                action="{{ route('roles.update', $role->id) }}"
                id="role-permissions-form"
                class="space-y-6"
            >

                @csrf
                @method('PUT')


                {{-- ================================================= --}}
                {{-- MÓDULOS --}}
                {{-- ================================================= --}}

                @foreach ($permissionGroups as $module => $permissions)

                    <x-ui.card>

                        <div class="border-b border-slate-200 pb-4">

                            <div class="flex items-center justify-between gap-4">

                                <div>

                                    <h2 class="text-base font-black capitalize text-slate-900">
                                        {{ $module }}
                                    </h2>

                                    <p class="mt-1 text-sm text-slate-500">
                                        Permisos disponibles para este módulo.
                                    </p>

                                </div>


                                <button
                                    type="button"
                                    class="text-sm font-bold text-slate-500 transition hover:text-slate-900"
                                    data-module-toggle="{{ $module }}"
                                >
                                    Seleccionar todos
                                </button>

                            </div>

                        </div>


                        <div class="mt-5 grid gap-3">

                            @foreach ($permissions as $permission)

                                <label
                                    class="flex cursor-pointer items-start gap-4 rounded-2xl border border-slate-200 bg-white p-4 transition hover:border-slate-300 hover:bg-slate-50"
                                >

                                    <input
                                        type="checkbox"
                                        name="permissions[]"
                                        value="{{ $permission->id }}"
                                        data-permission-checkbox
                                        data-module="{{ $module }}"
                                        @checked(in_array(
                                            (string) $permission->id,
                                            $selectedPermissionIds,
                                            true
                                        ))
                                        class="mt-1 h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                                    >

                                    <span class="min-w-0">

                                        <span class="block text-sm font-black text-slate-900">
                                            {{ $permission->name }}
                                        </span>

                                        <span class="mt-0.5 block text-xs font-bold text-slate-400">
                                            {{ $permission->code }}
                                        </span>

                                        @if ($permission->description)

                                            <span class="mt-1 block text-sm leading-5 text-slate-500">
                                                {{ $permission->description }}
                                            </span>

                                        @endif

                                    </span>

                                </label>

                            @endforeach

                        </div>

                    </x-ui.card>

                @endforeach


                {{-- ================================================= --}}
                {{-- ACCIONES --}}
                {{-- ================================================= --}}

                <div class="sticky bottom-4 z-20">

                    <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white/95 p-4 shadow-[0_15px_40px_rgba(15,23,42,0.12)] backdrop-blur sm:flex-row sm:items-center sm:justify-between">

                        <p class="text-sm font-semibold text-slate-500">
                            Los cambios afectan a todos los usuarios que tengan este rol.
                        </p>

                        <div class="flex flex-col gap-2 sm:flex-row">

                            <a
                                href="{{ route('roles.index') }}"
                                class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-5 text-sm font-bold text-slate-700 transition hover:bg-slate-50"
                            >
                                Cancelar
                            </a>

                            <x-ui.button
                                type="submit"
                                variant="primary"
                            >
                                Guardar permisos
                            </x-ui.button>

                        </div>

                    </div>

                </div>

            </form>

        @endif

    </div>


    {{-- ========================================================= --}}
    {{-- JS --}}
    {{-- ========================================================= --}}

    @if ($role->code !== 'admin')

        <script>
            document.addEventListener('DOMContentLoaded', () => {

                const checkboxes = Array.from(
                    document.querySelectorAll('[data-permission-checkbox]')
                );

                const counter =
                    document.getElementById('permission-counter');


                const updateCounter = () => {

                    if (!counter) {
                        return;
                    }

                    const selected = checkboxes.filter(
                        checkbox => checkbox.checked
                    ).length;

                    counter.textContent =
                        `${selected} seleccionados`;
                };


                document
                    .querySelectorAll('[data-module-toggle]')
                    .forEach(button => {

                        button.addEventListener('click', () => {

                            const module =
                                button.dataset.moduleToggle;

                            const moduleCheckboxes =
                                checkboxes.filter(
                                    checkbox =>
                                        checkbox.dataset.module === module
                                );

                            const shouldCheck =
                                moduleCheckboxes.some(
                                    checkbox => !checkbox.checked
                                );

                            moduleCheckboxes.forEach(checkbox => {
                                checkbox.checked = shouldCheck;
                            });

                            updateCounter();
                        });

                    });


                checkboxes.forEach(checkbox => {

                    checkbox.addEventListener(
                        'change',
                        updateCounter
                    );

                });


                updateCounter();

            });
        </script>

    @endif

</x-layouts.app>
