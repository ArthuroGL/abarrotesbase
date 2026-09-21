@props([
'endpoint',
'value' => '',
'placeholder' => 'Buscar...',
'autofocus' => false,
'minChars' => 1,
'debounce' => 250,
])

<div
    {{ $attributes->class([
        'relative w-full',
    ]) }}
    data-ui-search
    data-endpoint="{{ $endpoint }}"
    data-min-chars="{{ $minChars }}"
    data-debounce="{{ $debounce }}">
    <div class="relative z-50">

        <x-ui.input
            id="{{ $attributes->get('id') }}"
            name="{{ $attributes->get('name', 'search') }}"
            type="search"
            :value="$value"
            placeholder="{{ $placeholder }}"
            autocomplete="off"
            spellcheck="false"
            role="combobox"
            aria-autocomplete="list"
            aria-expanded="false"
            aria-controls="{{ $attributes->get('id') }}-results"
            :autofocus="$autofocus"
            class="pr-10" />

        <div
            class="pointer-events-none absolute inset-y-0 right-0 hidden items-center pr-3"
            data-search-loading>
            <span class="h-4 w-4 animate-spin rounded-full border-2 border-slate-300 border-t-emerald-600"></span>
        </div>

    </div>

    <div
        id="{{ $attributes->get('id') }}-results"
        class="absolute left-0 right-0 bottom-full z-50 mt-2 hidden max-h-72 overflow-y-auto overflow-x-hidden rounded-xl border border-slate-200 bg-white shadow-[0_20px_50px_rgba(15,23,42,0.18)]"
        role="listbox"
        data-search-results></div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        document.querySelectorAll('[data-ui-search]').forEach(function(searchRoot) {

            if (searchRoot.dataset.searchInitialized === 'true') {
                return;
            }

            searchRoot.dataset.searchInitialized = 'true';

            const input = searchRoot.querySelector('input');
            const results = searchRoot.querySelector('[data-search-results]');
            const loading = searchRoot.querySelector('[data-search-loading]');

            if (!input || !results) {
                return;
            }

            const endpoint = searchRoot.dataset.endpoint;
            const minChars = Number(searchRoot.dataset.minChars || 1);
            const debounceTime = Number(searchRoot.dataset.debounce || 250);

            let timer = null;
            let controller = null;
            let items = [];
            let activeIndex = -1;

            function showResults() {
                results.classList.remove('hidden');
                input.setAttribute('aria-expanded', 'true');
            }

            function hideResults() {
                results.classList.add('hidden');
                input.setAttribute('aria-expanded', 'false');
                activeIndex = -1;
            }

            function setLoading(active) {
                if (!loading) {
                    return;
                }

                loading.classList.toggle('hidden', !active);
                loading.classList.toggle('flex', active);
            }

            function escapeHtml(value) {
                return String(value ?? '')
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');
            }

            function renderMessage(message) {
                results.innerHTML = `
                <div class="px-4 py-4 text-sm font-medium text-slate-500">
                    ${escapeHtml(message)}
                </div>
            `;

                showResults();
            }

            function renderResults() {

                if (!items.length) {
                    renderMessage('No encontramos productos.');
                    return;
                }

                results.innerHTML = items.map(function(item, index) {

                    const name = escapeHtml(item.name || 'Producto sin nombre');
                    const sku = escapeHtml(item.sku || 'Sin SKU');
                    const barcode = escapeHtml(item.barcode || '');
                    const unit = escapeHtml(item.unit || 'PZA');

                    return `
                    <button
                        type="button"
                        class="flex w-full items-center justify-between gap-4 border-b border-slate-100 px-4 py-3.5 text-left transition last:border-b-0 hover:bg-slate-50 focus:bg-slate-50"
                        data-search-result
                        data-index="${index}"
                        role="option"
                        aria-selected="false"
                    >
                        <span class="min-w-0">
                            <span class="block truncate text-sm font-black text-slate-900">
                                ${name}
                            </span>

                            <span class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-slate-500">
                                <span class="font-mono font-semibold">
                                    ${sku}
                                </span>

                                ${barcode
                                    ? `<span class="text-slate-300">•</span>
                                       <span class="font-mono">${barcode}</span>`
                                    : ''
                                }

                                <span class="text-slate-300">•</span>

                                <span>${unit}</span>
                            </span>
                        </span>
                    </button>
                `;
                }).join('');

                showResults();

                results.querySelectorAll('[data-search-result]')
                    .forEach(function(button) {
                        button.addEventListener('mouseenter', function() {
                            activeIndex = Number(button.dataset.index);
                            updateActiveResult();
                        });

                        button.addEventListener('click', function() {
                            selectItem(activeIndex);
                        });
                    });

                updateActiveResult();
            }

            function updateActiveResult() {

                const buttons = results.querySelectorAll('[data-search-result]');

                buttons.forEach(function(button, index) {

                    const active = index === activeIndex;

                    button.classList.toggle('bg-slate-50', active);
                    button.setAttribute(
                        'aria-selected',
                        active ? 'true' : 'false'
                    );
                });
            }

            function selectItem(index) {

                if (!items[index]) {
                    return;
                }

                const item = items[index];

                /*
                 * Mostramos el nombre seleccionado en el input.
                 */
                input.value = item.name || '';

                /*
                 * Informamos al módulo que utilizó el componente.
                 */
                searchRoot.dispatchEvent(
                    new CustomEvent('ui-search-selected', {
                        bubbles: true,
                        detail: item,
                    })
                );

                hideResults();
            }

            async function search(query) {

                query = query.trim();

                if (controller) {
                    controller.abort();
                    controller = null;
                }

                if (query.length < minChars) {
                    items = [];
                    results.innerHTML = '';
                    hideResults();
                    setLoading(false);
                    return;
                }

                controller = new AbortController();

                setLoading(true);

                try {

                    const url = new URL(endpoint, window.location.origin);

                    url.searchParams.set('q', query);

                    const response = await fetch(url, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        signal: controller.signal,
                    });

                    if (!response.ok) {
                        throw new Error('No se pudo realizar la búsqueda.');
                    }

                    const data = await response.json();

                    items = Array.isArray(data.items) ?
                        data.items : [];

                    activeIndex = -1;

                    renderResults();

                } catch (error) {

                    if (error.name === 'AbortError') {
                        return;
                    }

                    items = [];
                    renderMessage(
                        'No se pudo realizar la búsqueda.'
                    );

                } finally {

                    setLoading(false);
                    controller = null;
                }
            }

            input.addEventListener('input', function() {

                clearTimeout(timer);

                activeIndex = -1;

                timer = setTimeout(function() {
                    search(input.value);
                }, debounceTime);
            });

            input.addEventListener('keydown', function(event) {

                if (event.key === 'ArrowDown') {

                    if (!items.length) {
                        return;
                    }

                    event.preventDefault();

                    activeIndex = Math.min(
                        activeIndex + 1,
                        items.length - 1
                    );

                    updateActiveResult();

                    return;
                }

                if (event.key === 'ArrowUp') {

                    if (!items.length) {
                        return;
                    }

                    event.preventDefault();

                    activeIndex = Math.max(
                        activeIndex - 1,
                        0
                    );

                    updateActiveResult();

                    return;
                }

                if (event.key === 'Enter') {

                    if (activeIndex >= 0 && items[activeIndex]) {

                        event.preventDefault();

                        selectItem(activeIndex);

                        return;
                    }

                    /*
                     * Si no hay sugerencia seleccionada,
                     * dejamos que el formulario haga su submit normal.
                     */
                    return;
                }

                if (event.key === 'Escape') {

                    hideResults();

                    return;
                }
            });

            document.addEventListener('click', function(event) {

                if (!searchRoot.contains(event.target)) {
                    hideResults();
                }
            });

            if (input.value.trim() !== '') {
                search(input.value);
            }

        });

    });
</script>
