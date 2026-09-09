@php
    $sections = [
        [
            'label' => 'Principal',
            'items' => [
                ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'chart'],
                ['label' => 'Punto de venta', 'route' => null, 'icon' => 'store', 'badge' => 'Próximamente'],
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

<aside id="app-sidebar" class="app-sidebar fixed inset-y-0 left-0 z-40 flex w-72 -translate-x-full flex-col border-r border-slate-800 bg-slate-950 text-slate-200 transition-transform duration-200 lg:sticky lg:top-0 lg:h-screen lg:translate-x-0" aria-label="Navegación principal">
    <div class="flex h-20 items-center justify-between border-b border-slate-800 px-6">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3" aria-label="Ir al dashboard">
            <span class="grid h-10 w-10 place-items-center rounded-xl bg-emerald-500 font-black text-slate-950">A</span>
            <span>
                <span class="block text-sm font-black tracking-[0.16em] text-white">ABARROTES</span>
                <span class="block text-xs font-semibold tracking-[0.2em] text-emerald-400">BASE</span>
            </span>
        </a>
        <button type="button" class="rounded-lg p-2 text-slate-400 hover:bg-slate-800 hover:text-white lg:hidden" data-sidebar-close aria-label="Cerrar menú">✕</button>
    </div>

    <nav class="min-h-0 flex-1 overflow-y-auto px-3 py-5">
        @foreach ($sections as $section)
            <div class="mb-6">
                <p class="mb-2 px-3 text-[11px] font-bold uppercase tracking-[0.14em] text-slate-500">{{ $section['label'] }}</p>
                <div class="space-y-1">
                    @foreach ($section['items'] as $item)
                        @php($isActive = $item['route'] && request()->routeIs($item['route']))
                        @if ($item['route'])
                            <a href="{{ route($item['route']) }}" @class(['nav-link', 'nav-link-active' => $isActive])>
                                <x-ui.nav-icon :name="$item['icon']" />
                                <span>{{ $item['label'] }}</span>
                            </a>
                        @else
                            <span class="nav-link cursor-not-allowed opacity-55" title="Módulo planificado">
                                <x-ui.nav-icon :name="$item['icon']" />
                                <span>{{ $item['label'] }}</span>
                                @isset($item['badge'])<span class="ml-auto rounded-full bg-slate-800 px-2 py-0.5 text-[10px] font-bold text-slate-400">{{ $item['badge'] }}</span>@endisset
                            </span>
                        @endif
                    @endforeach
                </div>
            </div>
        @endforeach
    </nav>

    <div class="border-t border-slate-800 p-4">
        <div class="rounded-xl bg-slate-900 p-3 text-xs leading-5 text-slate-400">
            <span class="font-semibold text-slate-200">Fundación técnica</span>
            <span class="block">Laravel 12 · PostgreSQL · MXN</span>
        </div>
    </div>
</aside>

<div id="app-sidebar-backdrop" class="fixed inset-0 z-30 hidden bg-slate-950/50 lg:hidden" data-sidebar-close></div>
