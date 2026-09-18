<x-layouts.app title="Devoluciones">

    <div class="space-y-6">

        <x-layout.page-header
            eyebrow="Operación"
            title="Devoluciones"
            description="Busca una venta y selecciona los productos que deseas devolver."
        />

        <x-ui.card>

            <div class="max-w-3xl">

                <label
                    for="sale-search"
                    class="mb-2 block text-sm font-bold text-slate-700"
                >
                    Folio de venta
                </label>

                <div class="flex flex-col gap-3 sm:flex-row">

                    <input
                        id="sale-search"
                        type="text"
                        class="app-input flex-1"
                        placeholder="Ej. V-202609-000123"
                        autocomplete="off"
                    >

                    <x-ui.button
                        id="search-sale-btn"
                        type="button"
                    >
                        Buscar venta
                    </x-ui.button>

                </div>

                <p class="mt-2 text-xs text-slate-500">
                    Introduce el folio de la venta para consultar sus productos.
                </p>

            </div>

        </x-ui.card>

        <div
            id="search-message"
            class="hidden rounded-xl px-5 py-4 text-sm font-semibold"
        ></div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {

            const input = document.getElementById('sale-search');
            const button = document.getElementById('search-sale-btn');
            const message = document.getElementById('search-message');

            function showMessage(text, type = 'error') {
                message.textContent = text;

                message.className =
                    'rounded-xl px-5 py-4 text-sm font-semibold ' +
                    (
                        type === 'success'
                            ? 'bg-emerald-50 text-emerald-700'
                            : 'bg-rose-50 text-rose-700'
                    );
            }

            function clearMessage() {
                message.textContent = '';
                message.classList.add('hidden');
            }

            async function searchSale() {

                const value = input.value.trim();

                clearMessage();

                if (!value) {
                    showMessage('Introduce el folio de una venta.');
                    return;
                }

                button.disabled = true;
                button.textContent = 'Buscando...';

                try {

                    /*
                     * Buscamos primero por folio para obtener el ID.
                     *
                     * El endpoint se agregará en el controller.
                     */
                    const response = await fetch(
                        `{{ route('returns.index') }}?sale=${encodeURIComponent(value)}`,
                        {
                            headers: {
                                'Accept': 'application/json'
                            }
                        }
                    );

                    /*
                     * Mientras terminamos el endpoint de búsqueda,
                     * usamos la respuesta para detectar errores HTTP.
                     */
                    if (response.redirected) {
                        window.location.href = response.url;
                        return;
                    }

                    if (!response.ok) {
                        throw new Error(
                            'No se encontró una venta con ese folio.'
                        );
                    }

                } catch (error) {

                    showMessage(
                        error.message || 'No fue posible buscar la venta.'
                    );

                } finally {

                    button.disabled = false;
                    button.textContent = 'Buscar venta';

                }
            }

            button.addEventListener('click', searchSale);

            input.addEventListener('keydown', (event) => {

                if (event.key === 'Enter') {
                    event.preventDefault();
                    searchSale();
                }

            });

        });
    </script>

</x-layouts.app>
