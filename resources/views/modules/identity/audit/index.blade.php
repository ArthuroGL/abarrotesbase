<x-layouts.app title="Auditoría | ABARROTESBASE">

    <div class="space-y-6">

        {{-- ========================================================= --}}
        {{-- HEADER --}}
        {{-- ========================================================= --}}

        <x-layout.page-header
            eyebrow="Administración"
            title="Auditoría"
            description="Consulta el historial de acciones administrativas realizadas en el sistema."
        >
        </x-layout.page-header>


        {{-- ========================================================= --}}
        {{-- FILTRO --}}
        {{-- ========================================================= --}}

        <x-ui.card>

            <form
                method="GET"
                action="{{ route('audit.index') }}"
                class="grid gap-4 sm:grid-cols-2 lg:grid-cols-[1fr_auto]"
            >

                <div>

                    <label
                        for="action"
                        class="mb-2 block text-sm font-bold text-slate-800"
                    >
                        Tipo de acción
                    </label>

                    <select
                        id="action"
                        name="action"
                        class="block min-h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-800 outline-none transition focus:border-slate-400 focus:ring-2 focus:ring-slate-200"
                    >

                        <option value="">
                            Todas las acciones
                        </option>

                        @foreach ($actions as $availableAction)

                            <option
                                value="{{ $availableAction }}"
                                @selected($action === $availableAction)
                            >
                                {{ $availableAction }}
                            </option>

                        @endforeach

                    </select>

                </div>


                <div class="flex items-end">

                    <x-ui.button
                        type="submit"
                        variant="secondary"
                        class="w-full sm:w-auto"
                    >
                        Filtrar
                    </x-ui.button>

                </div>

            </form>

        </x-ui.card>


        {{-- ========================================================= --}}
        {{-- AUDITORÍA DESKTOP --}}
        {{-- ========================================================= --}}

        <x-ui.card padding="p-0" class="overflow-hidden">

            <div class="hidden overflow-x-auto md:block">

                <table class="min-w-full divide-y divide-slate-200">

                    <thead class="bg-slate-50">

                        <tr>

                            <th class="px-6 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                Fecha
                            </th>

                            <th class="px-6 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                Usuario
                            </th>

                            <th class="px-6 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                Acción
                            </th>

                            <th class="px-6 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                Registro
                            </th>

                            <th class="px-6 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                IP
                            </th>

                            <th class="px-6 py-4 text-right text-xs font-black uppercase tracking-wider text-slate-500">
                                Detalle
                            </th>

                        </tr>

                    </thead>


                    <tbody class="divide-y divide-slate-100 bg-white">

                        @forelse ($logs as $log)

                            <tr class="hover:bg-slate-50">

                                <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600">

                                    {{ \Carbon\Carbon::parse($log->occurred_at)->format('d/m/Y H:i:s') }}

                                </td>


                                <td class="px-6 py-4">

                                    <p class="font-black text-slate-900">
                                        {{ $log->actor_name ?? 'Sistema' }}
                                    </p>

                                    @if ($log->actor_email)

                                        <p class="mt-0.5 text-xs text-slate-500">
                                            {{ $log->actor_email }}
                                        </p>

                                    @endif

                                </td>


                                <td class="px-6 py-4">

                                    <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-700">
                                        {{ $log->action }}
                                    </span>

                                </td>


                                <td class="px-6 py-4">

                                    <p class="text-sm font-bold text-slate-700">
                                        {{ $log->auditable_type ?? '—' }}
                                    </p>

                                    @if ($log->auditable_id)

                                        <p class="mt-0.5 max-w-48 truncate font-mono text-xs text-slate-400">
                                            {{ $log->auditable_id }}
                                        </p>

                                    @endif

                                </td>


                                <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-500">
                                    {{ $log->ip_address ?? '—' }}
                                </td>


                                <td class="px-6 py-4 text-right">

                                    <button
                                        type="button"
                                        class="inline-flex min-h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700 transition hover:bg-slate-50"
                                        data-audit-open
                                        data-before='@json($log->before ? json_decode($log->before, true) : null)'
                                        data-after='@json($log->after ? json_decode($log->after, true) : null)'
                                        data-action="{{ $log->action }}"
                                    >
                                        Ver detalle
                                    </button>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="6"
                                    class="px-6 py-14 text-center"
                                >

                                    <p class="font-black text-slate-900">
                                        No hay registros de auditoría
                                    </p>

                                    <p class="mt-1 text-sm text-slate-500">
                                        Las acciones registradas aparecerán aquí.
                                    </p>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>


            {{-- ===================================================== --}}
            {{-- MOBILE --}}
            {{-- ===================================================== --}}

            <div class="divide-y divide-slate-200 md:hidden">

                @forelse ($logs as $log)

                    <div class="space-y-4 p-5">

                        <div class="flex items-start justify-between gap-4">

                            <div>

                                <p class="font-black text-slate-900">
                                    {{ $log->action }}
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    {{ \Carbon\Carbon::parse($log->occurred_at)->format('d/m/Y H:i:s') }}
                                </p>

                            </div>

                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-bold text-slate-600">
                                {{ $log->auditable_type ?? 'Sistema' }}
                            </span>

                        </div>


                        <div class="grid gap-3 sm:grid-cols-2">

                            <div>

                                <p class="text-xs font-black uppercase tracking-wider text-slate-400">
                                    Usuario
                                </p>

                                <p class="mt-1 text-sm font-bold text-slate-700">
                                    {{ $log->actor_name ?? 'Sistema' }}
                                </p>

                            </div>


                            <div>

                                <p class="text-xs font-black uppercase tracking-wider text-slate-400">
                                    IP
                                </p>

                                <p class="mt-1 text-sm text-slate-600">
                                    {{ $log->ip_address ?? '—' }}
                                </p>

                            </div>

                        </div>


                        <button
                            type="button"
                            class="inline-flex min-h-11 w-full items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700"
                            data-audit-open
                            data-before='@json($log->before ? json_decode($log->before, true) : null)'
                            data-after='@json($log->after ? json_decode($log->after, true) : null)'
                            data-action="{{ $log->action }}"
                        >
                            Ver detalle
                        </button>

                    </div>

                @empty

                    <div class="px-5 py-14 text-center">

                        <p class="font-black text-slate-900">
                            No hay registros de auditoría
                        </p>

                    </div>

                @endforelse

            </div>

        </x-ui.card>


        {{-- ========================================================= --}}
        {{-- PAGINACIÓN --}}
        {{-- ========================================================= --}}

        <div>
            {{ $logs->links() }}
        </div>


        {{-- ========================================================= --}}
        {{-- MODAL --}}
        {{-- ========================================================= --}}

        <div
            id="audit-modal"
            class="fixed inset-0 z-50 hidden"
            aria-hidden="true"
        >

            <div
                class="absolute inset-0 bg-slate-950/50"
                data-audit-close
            ></div>


            <div class="relative mx-auto flex min-h-full max-w-3xl items-center p-4">

                <div class="w-full rounded-2xl bg-white shadow-2xl">

                    <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 sm:px-6">

                        <div>

                            <p class="text-xs font-black uppercase tracking-wider text-slate-400">
                                Auditoría
                            </p>

                            <h2
                                id="audit-modal-title"
                                class="mt-1 text-lg font-black text-slate-900"
                            >
                                Detalle
                            </h2>

                        </div>


                        <button
                            type="button"
                            class="grid h-10 w-10 place-items-center rounded-xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                            data-audit-close
                            aria-label="Cerrar"
                        >
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
                                class="mt-2 max-h-96 overflow-auto rounded-xl bg-slate-950 p-4 text-xs leading-5 text-slate-200"
                            ></pre>

                        </div>


                        <div>

                            <p class="text-xs font-black uppercase tracking-wider text-slate-400">
                                Después
                            </p>

                            <pre
                                id="audit-after"
                                class="mt-2 max-h-96 overflow-auto rounded-xl bg-slate-950 p-4 text-xs leading-5 text-slate-200"
                            ></pre>

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
                            beforeData !== null
                                ? JSON.stringify(beforeData, null, 2)
                                : 'Sin información';


                        after.textContent =
                            afterData !== null
                                ? JSON.stringify(afterData, null, 2)
                                : 'Sin información';


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
