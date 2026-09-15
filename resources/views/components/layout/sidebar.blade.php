@php
    $sections = [
        [
            'label' => 'Principal',
            'items' => [
                ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'chart'],
            ],
        ],
        [
            'label' => 'Operación',
            'items' => [
                ['label' => 'Ventas', 'route' => 'sales.index', 'icon' => 'receipt'],
                ['label' => 'Caja', 'route' => 'cash.index', 'icon' => 'cash'],
                ['label' => 'Gastos', 'route' => 'expenses.index', 'icon' => 'wallet'],
            ],
        ],
        [
            'label' => 'Inventario',
            'items' => [
                ['label' => 'Productos', 'route' => 'products.index', 'icon' => 'cube'],
                ['label' => 'Existencias', 'route' => 'stock.index', 'icon' => 'boxes'],
                ['label' => 'Compras', 'route' => 'purchases.index', 'icon' => 'cart'],
                ['label' => 'Proveedores', 'route' => 'suppliers.index', 'icon' => 'truck'],
            ],
        ],
        [
            'label' => 'Administración',
            'items' => [
                ['label' => 'Clientes', 'route' => null, 'icon' => 'users'],
                ['label' => 'Reportes', 'route' => null, 'icon' => 'report'],
                ['label' => 'Configuración', 'route' => null, 'icon' => 'settings'],
            ],
        ],
    ];
@endphp


<aside
    id="app-sidebar"
    class="app-sidebar fixed inset-y-0 left-0 z-40 flex w-72 -translate-x-full flex-col border-r border-slate-800 bg-slate-950 text-slate-200 transition-[width,transform] duration-300 ease-in-out lg:sticky lg:top-0 lg:h-screen lg:translate-x-0"
    aria-label="Navegación principal">

    {{-- =========================
         HEADER
    ========================== --}}
    <div class="flex h-20 shrink-0 items-center justify-between border-b border-slate-800 px-4">

        <a
            href="{{ route('dashboard') }}"
            class="sidebar-logo flex min-w-0 items-center gap-3 rounded-xl p-2"
            aria-label="Ir al dashboard">

            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-emerald-500 font-black text-slate-950">
                A
            </span>

            <span class="sidebar-label min-w-0 overflow-hidden whitespace-nowrap">
                <span class="block text-sm font-black tracking-[0.16em] text-white">
                    ABARROTES
                </span>

                <span class="block text-xs font-semibold tracking-[0.2em] text-emerald-400">
                    Margarita
                </span>
            </span>

        </a>

        {{-- BOTÓN MÓVIL --}}
        <button
            type="button"
            class="grid h-10 w-10 shrink-0 place-items-center rounded-xl text-slate-400 transition hover:bg-slate-800 hover:text-white lg:hidden"
            data-sidebar-close
            aria-label="Cerrar menú">

            ×

        </button>

    </div>


    {{-- =========================
         NAVEGACIÓN
    ========================== --}}
    <nav class="min-h-0 flex-1 overflow-y-auto px-3 py-5">

        @foreach ($sections as $section)

            <div class="sidebar-section mb-6">

                <p class="sidebar-section-label mb-2 px-3 text-[11px] font-bold uppercase tracking-[0.14em] text-slate-500">
                    {{ $section['label'] }}
                </p>


                <div class="space-y-1">

                    @foreach ($section['items'] as $item)

                        @php($isActive = $item['route'] && request()->routeIs($item['route']))

                        @if ($item['route'])

                            <a
                                href="{{ route($item['route']) }}"
                                title="{{ $item['label'] }}"
                                @class([
                                    'nav-link sidebar-nav-link',
                                    'nav-link-active' => $isActive,
                                ])>

                                <x-ui.nav-icon :name="$item['icon']" />

                                <span class="sidebar-label min-w-0 overflow-hidden whitespace-nowrap">
                                    {{ $item['label'] }}
                                </span>

                            </a>

                        @else

                            <span
                                title="{{ $item['label'] }}"
                                class="nav-link sidebar-nav-link cursor-not-allowed opacity-55">

                                <x-ui.nav-icon :name="$item['icon']" />

                                <span class="sidebar-label min-w-0 overflow-hidden whitespace-nowrap">
                                    {{ $item['label'] }}
                                </span>

                                @isset($item['badge'])

                                    <span class="sidebar-label ml-auto rounded-full bg-slate-800 px-2 py-0.5 text-[10px] font-bold text-slate-400">
                                        {{ $item['badge'] }}
                                    </span>

                                @endisset

                            </span>

                        @endif

                    @endforeach

                </div>

            </div>

        @endforeach

    </nav>


    {{-- =========================
         FOOTER
    ========================== --}}
    <div class="shrink-0 border-t border-slate-800 p-3">

        <div class="sidebar-footer rounded-xl bg-slate-900 p-3 text-xs leading-5 text-slate-400">

            <span class="sidebar-label font-semibold text-slate-200">
                Prueba técnica
            </span>

            <span class="sidebar-label block">
                Construido como un proyecto de prueba para Abarrotes Base.
            </span>

            <span
                class="sidebar-footer-short hidden text-center text-[10px] font-bold text-slate-500">
                GLLA
            </span>

        </div>

    </div>

</aside>


{{-- BACKDROP MÓVIL --}}
<div
    id="app-sidebar-backdrop"
    class="fixed inset-0 z-30 hidden bg-slate-950/50 lg:hidden"
    data-sidebar-close>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {

    const sidebar = document.getElementById('app-sidebar');
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebarToggleIcon = document.getElementById('sidebar-toggle-icon');
    const backdrop = document.getElementById('app-sidebar-backdrop');

    if (!sidebar || !sidebarToggle) {
        return;
    }

    const labels = sidebar.querySelectorAll('.sidebar-label');
    const sectionLabels = sidebar.querySelectorAll('.sidebar-section-label');
    const navLinks = sidebar.querySelectorAll('.sidebar-nav-link');
    const footerShort = sidebar.querySelector('.sidebar-footer-short');

    const STORAGE_KEY = 'abarrotesbase.sidebar.collapsed';


    /*
    |--------------------------------------------------------------------------
    | Desktop: contraer / expandir
    |--------------------------------------------------------------------------
    */

    const setCollapsed = (collapsed, save = true) => {

        if (window.innerWidth < 1024) {
            return;
        }

        sidebar.classList.toggle('w-20', collapsed);
        sidebar.classList.toggle('w-72', !collapsed);

        labels.forEach(element => {
            element.classList.toggle('hidden', collapsed);
        });

        sectionLabels.forEach(element => {
            element.classList.toggle('hidden', collapsed);
        });

        navLinks.forEach(element => {

            element.classList.toggle('justify-center', collapsed);

            if (collapsed) {
                element.classList.remove('px-3');
                element.classList.add('px-2');
            } else {
                element.classList.remove('px-2');
                element.classList.add('px-3');
            }

        });

        footerShort?.classList.toggle('hidden', !collapsed);

        sidebarToggle.setAttribute(
            'aria-label',
            collapsed
                ? 'Expandir menú'
                : 'Contraer menú'
        );

        sidebarToggle.setAttribute(
            'title',
            collapsed
                ? 'Expandir menú'
                : 'Contraer menú'
        );

        /*
         * El icono permanece como ☰.
         * No necesitamos inventar flechas nuevas cuando
         * el usuario ya entiende perfectamente el menú.
         */

        if (save) {
            localStorage.setItem(
                STORAGE_KEY,
                collapsed ? '1' : '0'
            );
        }
    };


    /*
    |--------------------------------------------------------------------------
    | Desktop: restaurar estado
    |--------------------------------------------------------------------------
    */

    const savedState = localStorage.getItem(STORAGE_KEY);

    if (
        savedState === '1' &&
        window.innerWidth >= 1024
    ) {
        setCollapsed(true, false);
    } else if (window.innerWidth >= 1024) {
        setCollapsed(false, false);
    }


    /*
    |--------------------------------------------------------------------------
    | Botón principal
    |--------------------------------------------------------------------------
    */

    sidebarToggle.addEventListener('click', () => {

        /*
         * En móvil:
         * abre/cierra el panel.
         */
        if (window.innerWidth < 1024) {

            const isOpen =
                !sidebar.classList.contains('-translate-x-full');

            if (isOpen) {
                closeMobileSidebar();
            } else {
                openMobileSidebar();
            }

            return;
        }


        /*
         * En escritorio:
         * contrae/expande.
         */
        const collapsed =
            sidebar.classList.contains('w-20');

        setCollapsed(!collapsed);

    });


    /*
    |--------------------------------------------------------------------------
    | Móvil
    |--------------------------------------------------------------------------
    */

    const openMobileSidebar = () => {

        sidebar.classList.remove('-translate-x-full');

        backdrop?.classList.remove('hidden');

        document.body.classList.add('overflow-hidden');

    };


    const closeMobileSidebar = () => {

        sidebar.classList.add('-translate-x-full');

        backdrop?.classList.add('hidden');

        document.body.classList.remove('overflow-hidden');

    };


    document
        .querySelectorAll('[data-sidebar-close]')
        .forEach(button => {

            button.addEventListener(
                'click',
                closeMobileSidebar
            );

        });


    /*
    |--------------------------------------------------------------------------
    | Escape
    |--------------------------------------------------------------------------
    */

    document.addEventListener('keydown', event => {

        if (event.key !== 'Escape') {
            return;
        }

        if (window.innerWidth < 1024) {
            closeMobileSidebar();
        }

    });


    /*
    |--------------------------------------------------------------------------
    | Resize
    |--------------------------------------------------------------------------
    */

    window.addEventListener('resize', () => {

        if (window.innerWidth < 1024) {

            sidebar.classList.remove('w-20');
            sidebar.classList.add('w-72');

            labels.forEach(element => {
                element.classList.remove('hidden');
            });

            sectionLabels.forEach(element => {
                element.classList.remove('hidden');
            });

            navLinks.forEach(element => {
                element.classList.remove('justify-center');
                element.classList.remove('px-2');
                element.classList.add('px-3');
            });

        } else {

            const collapsed =
                localStorage.getItem(STORAGE_KEY) === '1';

            setCollapsed(collapsed, false);

        }

    });

});
</script>
