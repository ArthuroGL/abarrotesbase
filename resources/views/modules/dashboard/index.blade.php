<x-layouts.app title="Dashboard | ABARROTESBASE">
    <x-layout.page-header eyebrow="Operación" title="Dashboard" description="Resumen operativo de la sucursal principal. Los indicadores se conectarán a ventas, inventario y caja conforme se habiliten los módulos.">
        <x-slot:actions>
            <x-ui.button variant="secondary" disabled>Hoy · Sin filtros</x-ui.button>
            <x-ui.button disabled>Abrir punto de venta</x-ui.button>
        </x-slot:actions>
    </x-layout.page-header>

    <div class="mt-7 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($metrics as $metric)
            <x-ui.card class="metric-card metric-card-{{ $metric['tone'] }}">
                <p class="text-sm font-semibold text-slate-500">{{ $metric['label'] }}</p>
                <p class="mt-3 text-2xl font-black tracking-tight text-slate-900">{{ $metric['value'] }}</p>
                <p class="mt-2 text-xs font-medium text-slate-500">{{ $metric['detail'] }}</p>
            </x-ui.card>
        @endforeach
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        <x-ui.card class="xl:col-span-2" padding="p-0">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <div>
                    <h2 class="font-bold text-slate-900">Ventas de hoy</h2>
                    <p class="mt-1 text-sm text-slate-500">La tendencia se habilitará al registrar ventas.</p>
                </div>
                <span class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600">En preparación</span>
            </div>
            <div class="grid min-h-72 place-items-center px-5 text-center">
                <div>
                    <div class="mx-auto grid h-12 w-12 place-items-center rounded-2xl bg-emerald-50 text-xl text-emerald-700">↗</div>
                    <p class="mt-4 font-bold text-slate-800">Aún no hay actividad</p>
                    <p class="mt-1 max-w-sm text-sm leading-6 text-slate-500">Cuando el POS esté disponible, aquí podrás consultar ventas por hora y comparar periodos.</p>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card padding="p-0">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="font-bold text-slate-900">Pendientes operativos</h2>
                <p class="mt-1 text-sm text-slate-500">Alertas que requerirán atención.</p>
            </div>
            <ul class="divide-y divide-slate-100">
                <li class="flex items-start gap-3 px-5 py-4">
                    <span class="mt-0.5 grid h-7 w-7 place-items-center rounded-lg bg-amber-50 text-amber-700">!</span>
                    <span><span class="block text-sm font-semibold text-slate-800">Inventario bajo mínimo</span><span class="text-xs text-slate-500">Disponible al configurar productos.</span></span>
                </li>
                <li class="flex items-start gap-3 px-5 py-4">
                    <span class="mt-0.5 grid h-7 w-7 place-items-center rounded-lg bg-violet-50 text-violet-700">$</span>
                    <span><span class="block text-sm font-semibold text-slate-800">Sesión de caja</span><span class="text-xs text-slate-500">Aún no hay una caja configurada.</span></span>
                </li>
            </ul>
        </x-ui.card>
    </div>

    <x-ui.card class="mt-6" padding="p-0">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <div>
                <h2 class="font-bold text-slate-900">Base de implementación</h2>
                <p class="mt-1 text-sm text-slate-500">Módulos documentados que se incorporarán de manera incremental.</p>
            </div>
            <span class="rounded-lg bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">Fundación activa</span>
        </div>
        <div class="grid gap-px bg-slate-100 sm:grid-cols-2 lg:grid-cols-4">
            @foreach (['Acceso y sucursales', 'Productos y precios', 'Inventario y compras', 'POS y caja'] as $module)
                <div class="bg-white px-5 py-4">
                    <p class="text-sm font-bold text-slate-800">{{ $module }}</p>
                    <p class="mt-1 text-xs text-slate-500">Planeado en el roadmap.</p>
                </div>
            @endforeach
        </div>
    </x-ui.card>
</x-layouts.app>
