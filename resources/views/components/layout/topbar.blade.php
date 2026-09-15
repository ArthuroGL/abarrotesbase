<header class="sticky top-0 z-20 flex h-20 items-center gap-3 border-b border-slate-200 bg-white/95 px-4 backdrop-blur sm:gap-4 sm:px-6 lg:px-8">

    {{-- Sidebar --}}
    <button
        type="button"
        id="sidebar-toggle"
        class="grid h-11 w-11 shrink-0 place-items-center rounded-xl text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600"
        aria-label="Contraer menú"
        title="Contraer menú"
    >
        <span
            id="sidebar-toggle-icon"
            class="text-xl leading-none"
            aria-hidden="true"
        >
            ☰
        </span>
    </button>


    {{-- Sucursal --}}
    <div class="hidden min-w-0 shrink-0 md:block">
        <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400">
            Sucursal activa
        </p>

        <button
            type="button"
            class="mt-0.5 inline-flex items-center gap-2 text-sm font-bold text-slate-800"
            title="La selección de sucursal se habilitará con el módulo de acceso"
        >
            <span class="inline-block h-2 w-2 rounded-full bg-emerald-500"></span>

            Sucursal principal
        </button>
    </div>


    {{-- Buscador global --}}
    <div class="relative ml-auto w-full max-w-xl">
        <label class="relative block">

            <span class="sr-only">
                Buscar en el sistema
            </span>

            <input
                id="global-search"
                class="app-input h-12 pl-11 pr-20"
                type="search"
                autocomplete="off"
                placeholder="Buscar productos, ventas, compras..."
            >

            <span
                class="pointer-events-none absolute inset-y-0 left-4 grid place-items-center text-slate-400"
                aria-hidden="true"
            >
                ⌕
            </span>

            <kbd
                class="pointer-events-none absolute right-3 top-1/2 hidden -translate-y-1/2 rounded-lg border border-slate-200 bg-white px-2 py-1 text-[10px] font-bold text-slate-400 shadow-sm sm:block"
            >
                /
            </kbd>

        </label>

        {{-- Resultados --}}
        <div
            id="global-search-results"
            class="absolute left-0 right-0 top-[calc(100%+8px)] hidden overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl"
        >
        </div>
    </div>


    {{-- Notificaciones --}}
    <button
        type="button"
        class="grid h-11 w-11 shrink-0 place-items-center rounded-xl text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600"
        title="Notificaciones próximamente"
        aria-label="Notificaciones"
    >
        ◌
    </button>


    {{-- Usuario --}}
    <div class="flex shrink-0 items-center gap-3 border-l border-slate-200 pl-3 sm:pl-4">

        <span class="grid h-10 w-10 place-items-center rounded-full bg-emerald-100 text-sm font-black text-emerald-800">
            A
        </span>

        <div class="hidden sm:block">
            <p class="text-sm font-bold text-slate-800">
                Administrador
            </p>

            <p class="text-xs text-slate-400">
                Administrador
            </p>
        </div>

    </div>

</header>

<script>
document.addEventListener('DOMContentLoaded', () => {

    const input = document.getElementById('global-search');
    const results = document.getElementById('global-search-results');

    if (!input || !results) {
        return;
    }

    let timer = null;

    const escapeHtml = (value) => {
        const div = document.createElement('div');
        div.textContent = value ?? '';
        return div.innerHTML;
    };

    const hideResults = () => {
        results.classList.add('hidden');
        results.innerHTML = '';
    };

    const showLoading = () => {
        results.classList.remove('hidden');

        results.innerHTML = `
            <div class="px-5 py-4 text-sm text-slate-500">
                Buscando...
            </div>
        `;
    };

    const renderResults = (items) => {

        if (!items.length) {
            results.classList.remove('hidden');

            results.innerHTML = `
                <div class="px-5 py-6 text-center">
                    <p class="text-sm font-bold text-slate-700">
                        No encontramos resultados
                    </p>

                    <p class="mt-1 text-xs text-slate-400">
                        Intenta con otro nombre, SKU o número.
                    </p>
                </div>
            `;

            return;
        }

        const groups = {};

        items.forEach(item => {
            if (!groups[item.group]) {
                groups[item.group] = [];
            }

            groups[item.group].push(item);
        });

        let html = '';

        Object.entries(groups).forEach(([group, groupItems]) => {

            html += `
                <div class="border-b border-slate-100 last:border-b-0">

                    <div class="px-5 py-2.5 text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">
                        ${escapeHtml(group)}
                    </div>
            `;

            groupItems.forEach(item => {

                html += `
                    <a
                        href="${item.url}"
                        class="flex items-center gap-3 px-5 py-3 transition hover:bg-slate-50"
                    >

                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-emerald-50 text-sm font-bold text-emerald-700">
                            ${getTypeIcon(item.type)}
                        </span>

                        <span class="min-w-0 flex-1">

                            <span class="block truncate text-sm font-bold text-slate-800">
                                ${escapeHtml(item.title)}
                            </span>

                            <span class="mt-0.5 block truncate text-xs text-slate-400">
                                ${escapeHtml(item.subtitle)}
                            </span>

                        </span>

                        <span class="text-slate-300">
                            →
                        </span>

                    </a>
                `;
            });

            html += `</div>`;
        });

        results.innerHTML = html;
        results.classList.remove('hidden');
    };

    const getTypeIcon = (type) => {

        const icons = {
            product: '◇',
            sale: '≡',
            supplier: '▱',
            expense: '$',
            purchase: '⊞',
            customer: '♙',
        };

        return icons[type] ?? '•';
    };

    const search = async () => {

        const term = input.value.trim();

        if (term.length < 2) {
            hideResults();
            return;
        }

        showLoading();

        try {

            const response = await fetch(
                `{{ route('global.search') }}?q=${encodeURIComponent(term)}`,
                {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                }
            );

            if (!response.ok) {
                throw new Error('Error en búsqueda');
            }

            const data = await response.json();

            renderResults(data.results ?? []);

        } catch (error) {

            results.classList.remove('hidden');

            results.innerHTML = `
                <div class="px-5 py-5 text-sm text-rose-600">
                    No fue posible realizar la búsqueda.
                </div>
            `;

            console.error(error);
        }
    };

    input.addEventListener('input', () => {

        clearTimeout(timer);

        timer = setTimeout(search, 250);

    });


    /*
    |--------------------------------------------------------------------------
    | "/" enfoca el buscador
    |--------------------------------------------------------------------------
    */

    document.addEventListener('keydown', (event) => {

        if (
            event.key === '/' &&
            document.activeElement !== input &&
            !['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement?.tagName)
        ) {
            event.preventDefault();
            input.focus();
        }

        if (event.key === 'Escape') {
            hideResults();
            input.blur();
        }

    });


    /*
    |--------------------------------------------------------------------------
    | Cerrar al hacer clic fuera
    |--------------------------------------------------------------------------
    */

    document.addEventListener('click', (event) => {

        if (
            !input.contains(event.target) &&
            !results.contains(event.target)
        ) {
            hideResults();
        }

    });

});
</script>
