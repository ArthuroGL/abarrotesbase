<x-layouts.app title="Auditoría | ABARROTESBASE">

    <div class="space-y-6">

        {{-- ========================================================= --}}
        {{-- HEADER --}}
        {{-- ========================================================= --}}

        <x-layout.page-header
            eyebrow="Administración"
            title="Auditoría"
            description="Consulta el historial de acciones administrativas realizadas en el sistema.">
        </x-layout.page-header>

        {{-- ========================================================= --}}
        {{-- FILTROS --}}
        {{-- ========================================================= --}}

        <x-ui.card padding="p-0" class="relative z-30 overflow-visible">

            <div class="border-b border-slate-200 bg-slate-50/70 px-5 py-4 sm:px-6">

                <div class="flex flex-col gap-1">

                    <h2 class="text-base font-black text-slate-900">
                        Buscar en auditoría
                    </h2>

                    <p class="text-sm text-slate-500">
                        Busca acciones, usuarios, registros o direcciones IP.
                    </p>

                </div>

            </div>


            <form
                method="GET"
                action="{{ route('audit.index') }}"
                class="grid gap-4 p-5 sm:p-6 lg:grid-cols-12">

                {{-- BUSCADOR --}}
                <div class="lg:col-span-7">

                    <label
                        for="audit-search"
                        class="mb-2 block text-sm font-bold text-slate-800">
                        Buscar
                    </label>

                    <x-ui.input
                        id="audit-search"
                        name="search"
                        type="search"
                        :value="$search"
                        placeholder="Usuario, correo, acción, registro o IP..."
                        autocomplete="off" />

                </div>


                {{-- ACCIÓN --}}
                <div class="lg:col-span-3">

                    <label
                        for="action"
                        class="mb-2 block text-sm font-bold text-slate-800">
                        Tipo de acción
                    </label>

                    <select
                        id="action"
                        name="action"
                        class="app-input">

                        <option value="">
                            Todas las acciones
                        </option>

                        @foreach ($actions as $availableAction)

                        <option
                            value="{{ $availableAction }}"
                            @selected($action===$availableAction)>
                            {{ $availableAction }}
                        </option>

                        @endforeach

                    </select>

                </div>


                {{-- BOTONES --}}
                <div class="flex items-end gap-3 lg:col-span-2">

                    <button
                        type="submit"
                        class="inline-flex min-h-11 flex-1 items-center justify-center rounded-xl bg-slate-950 px-5 text-sm font-black text-white transition hover:bg-slate-800">
                        Buscar
                    </button>

                    @if ($search || $action)

                    <a
                        href="{{ route('audit.index') }}"
                        class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                        Limpiar
                    </a>

                    @endif

                </div>

            </form>

        </x-ui.card>


        {{-- ========================================================= --}}
        {{-- AUDITORÍA DESKTOP --}}
        {{-- ========================================================= --}}

        <x-ui.card padding="p-0" class="overflow-hidden">

            {{-- CABECERA --}}

            <div class="flex flex-col gap-3 border-b border-slate-200 bg-white px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">

                <div>

                    <p class="text-xs font-black uppercase tracking-[0.14em] text-emerald-700">
                        Historial
                    </p>

                    <h2 class="mt-1 text-lg font-black text-slate-900">
                        Acciones registradas
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Consulta las operaciones realizadas dentro del sistema.
                    </p>

                </div>


                <div class="shrink-0">

                    <span class="inline-flex min-h-9 items-center rounded-full bg-slate-100 px-3 text-sm font-bold text-slate-600">
                        {{ $logs->total() }}
                        {{ $logs->total() === 1 ? 'registro' : 'registros' }}
                    </span>

                </div>

            </div>


            {{-- TABLA --}}

            <div class="min-w-0 px-3 py-3 sm:px-5">

                <x-ui.table
                    caption="Historial de auditoría"
                    maxHeight="clamp(320px, calc(100vh - 500px), 620px)">

                    <x-slot:head>

                        <tr>

                            <th
                                scope="col"
                                class="whitespace-nowrap px-4 py-3.5 text-left text-xs font-black uppercase tracking-wider text-slate-500 sm:px-5">
                                Fecha
                            </th>

                            <th
                                scope="col"
                                class="whitespace-nowrap px-4 py-3.5 text-left text-xs font-black uppercase tracking-wider text-slate-500 sm:px-5">
                                Usuario
                            </th>

                            <th
                                scope="col"
                                class="whitespace-nowrap px-4 py-3.5 text-left text-xs font-black uppercase tracking-wider text-slate-500 sm:px-5">
                                Acción
                            </th>

                            <th
                                scope="col"
                                class="whitespace-nowrap px-4 py-3.5 text-left text-xs font-black uppercase tracking-wider text-slate-500 sm:px-5">
                                Registro
                            </th>

                            <th
                                scope="col"
                                class="whitespace-nowrap px-4 py-3.5 text-left text-xs font-black uppercase tracking-wider text-slate-500 sm:px-5">
                                IP
                            </th>

                            <th
                                scope="col"
                                class="whitespace-nowrap px-4 py-3.5 text-right text-xs font-black uppercase tracking-wider text-slate-500 sm:px-5">
                                Detalle
                            </th>

                        </tr>

                    </x-slot:head>


                    @forelse ($logs as $log)

                    <tr class="group transition hover:bg-slate-50/70">

                        {{-- FECHA --}}

                        <td class="whitespace-nowrap px-5 py-4">

                            <p class="text-sm font-bold text-slate-700">
                                {{ \Carbon\Carbon::parse($log->occurred_at)->format('d/m/Y') }}
                            </p>

                            <p class="mt-0.5 text-xs font-semibold text-slate-400">
                                {{ \Carbon\Carbon::parse($log->occurred_at)->format('H:i:s') }}
                            </p>

                        </td>


                        {{-- USUARIO --}}

                        <td class="px-5 py-4">

                            <div class="min-w-0">

                                <p class="truncate text-sm font-black text-slate-900">
                                    {{ $log->actor_name ?? 'Sistema' }}
                                </p>

                                @if ($log->actor_email)

                                <p class="mt-1 max-w-48 truncate text-xs font-semibold text-slate-400">
                                    {{ $log->actor_email }}
                                </p>

                                @endif

                            </div>

                        </td>


                        {{-- ACCIÓN --}}

                        <td class="px-5 py-4">

                            <span class="inline-flex max-w-48 truncate rounded-full bg-slate-100 px-3 py-1.5 text-xs font-black text-slate-700">
                                {{ $log->action }}
                            </span>

                        </td>


                        {{-- REGISTRO --}}

                        <td class="px-5 py-4">

                            <p class="text-sm font-bold text-slate-700">
                                {{ $log->auditable_type ?? '—' }}
                            </p>

                            @if ($log->auditable_id)

                            <p class="mt-1 max-w-48 truncate font-mono text-xs font-semibold text-slate-400">
                                {{ $log->auditable_id }}
                            </p>

                            @endif

                        </td>


                        {{-- IP --}}

                        <td class="whitespace-nowrap px-5 py-4">

                            <span class="font-mono text-xs font-semibold text-slate-500">
                                {{ $log->ip_address ?? '—' }}
                            </span>

                        </td>


                        {{-- DETALLE --}}

                        <td class="px-5 py-4 text-right">

                            <button
                                type="button"
                                class="inline-flex min-h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700 transition hover:bg-slate-50"
                                data-audit-open
                                data-before='@json($log->before ? json_decode($log->before, true) : null)'
                                data-after='@json($log->after ? json_decode($log->after, true) : null)'
                                data-action="{{ $log->action }}">
                                Ver detalle
                            </button>

                        </td>

                    </tr>

                    @empty

                    <tr>

                        <td
                            colspan="6"
                            class="px-6 py-16 text-center">

                            <div class="mx-auto max-w-md">

                                <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-slate-100 text-xl font-black text-slate-400">
                                    —
                                </div>

                                <h3 class="mt-4 text-base font-black text-slate-900">
                                    No hay registros de auditoría
                                </h3>

                                <p class="mt-1 text-sm leading-6 text-slate-500">
                                    No encontramos acciones que coincidan con los filtros actuales.
                                </p>

                                @if ($search || $action)

                                <a
                                    href="{{ route('audit.index') }}"
                                    class="mt-4 inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-5 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                                    Limpiar filtros
                                </a>

                                @endif

                            </div>

                        </td>

                    </tr>

                    @endforelse

                </x-ui.table>

            </div>


            {{-- PAGINACIÓN --}}

            <x-ui.table-pagination
                :paginator="$logs"
                :per-page-options="[10, 25, 50, 100]" />

        </x-ui.card>

        {{-- ========================================================= --}}
        {{-- MODAL --}}
        {{-- ========================================================= --}}

        <div
            id="audit-modal"
            class="fixed inset-0 z-50 hidden"
            aria-hidden="true">

            <div
                class="absolute inset-0 bg-slate-950/50"
                data-audit-close></div>


            <div class="relative mx-auto flex min-h-full max-w-3xl items-center p-4">

                <div class="w-full rounded-2xl bg-white shadow-2xl">

                    <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 sm:px-6">

                        <div>

                            <p class="text-xs font-black uppercase tracking-wider text-slate-400">
                                Auditoría
                            </p>

                            <h2
                                id="audit-modal-title"
                                class="mt-1 text-lg font-black text-slate-900">
                                Detalle
                            </h2>

                        </div>


                        <button
                            type="button"
                            class="grid h-10 w-10 place-items-center rounded-xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                            data-audit-close
                            aria-label="Cerrar">
                            ×
                        </button>

                    </div>


                    <div class="grid gap-5 p-5 sm:grid-cols-2 sm:p-6">

                        <div>

                            <p class="text-xs font-black uppercase tracking-wider text-slate-400">
                                Antes
                            </p>

                            <pre
                                id="audit-before"
                                class="mt-2 max-h-96 overflow-auto rounded-xl bg-slate-950 p-4 text-xs leading-5 text-slate-200"></pre>

                        </div>


                        <div>

                            <p class="text-xs font-black uppercase tracking-wider text-slate-400">
                                Después
                            </p>

                            <pre
                                id="audit-after"
                                class="mt-2 max-h-96 overflow-auto rounded-xl bg-slate-950 p-4 text-xs leading-5 text-slate-200"></pre>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <script>
        document.addEventListener('DOMContentLoaded', () => {

            const modal = document.getElementById('audit-modal');
            const title = document.getElementById('audit-modal-title');
            const before = document.getElementById('audit-before');
            const after = document.getElementById('audit-after');


            const closeModal = () => {

                modal?.classList.add('hidden');

                document.body.classList.remove('overflow-hidden');

            };


            document
                .querySelectorAll('[data-audit-open]')
                .forEach(button => {

                    button.addEventListener('click', () => {

                        let beforeData = null;
                        let afterData = null;

                        try {
                            beforeData = JSON.parse(
                                button.dataset.before || 'null'
                            );

                            afterData = JSON.parse(
                                button.dataset.after || 'null'
                            );

                        } catch (error) {

                            beforeData = null;
                            afterData = null;
                        }


                        title.textContent =
                            button.dataset.action || 'Auditoría';


                        before.textContent =
                            beforeData !== null ?
                            JSON.stringify(beforeData, null, 2) :
                            'Sin información';


                        after.textContent =
                            afterData !== null ?
                            JSON.stringify(afterData, null, 2) :
                            'Sin información';


                        modal.classList.remove('hidden');

                        document.body.classList.add('overflow-hidden');

                    });

                });


            document
                .querySelectorAll('[data-audit-close]')
                .forEach(element => {

                    element.addEventListener(
                        'click',
                        closeModal
                    );

                });


            document.addEventListener('keydown', event => {

                if (event.key === 'Escape') {
                    closeModal();
                }

            });

        });
    </script>

</x-layouts.app>
