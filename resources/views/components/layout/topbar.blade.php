<header class="sticky top-0 z-20 flex h-20 items-center gap-4 border-b border-slate-200 bg-white/90 px-4 backdrop-blur sm:px-6 lg:px-8">
    <button
        type="button"
        id="sidebar-toggle"
        class="grid h-11 w-11 shrink-0 place-items-center rounded-xl text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600"
        aria-label="Contraer menú"
        title="Contraer menú">

        <span
            id="sidebar-toggle-icon"
            class="text-xl leading-none"
            aria-hidden="true">
            ☰
        </span>

    </button>

    <div class="min-w-0 flex-1">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Sucursal activa</p>
        <button type="button" class="mt-0.5 inline-flex items-center gap-2 text-sm font-bold text-slate-800" title="La selección de sucursal se habilitará con el módulo de acceso">
            <span class="inline-block h-2 w-2 rounded-full bg-emerald-500"></span>
            Sucursal principal
            <span class="text-slate-400">⌄</span>
        </button>
    </div>

    <div class="hidden max-w-sm flex-1 lg:block">
        <label class="relative block">
            <span class="sr-only">Buscar</span>
            <input class="app-input pl-10" type="search" placeholder="Buscar productos, ventas o clientes" disabled>
            <span class="pointer-events-none absolute inset-y-0 left-3 grid place-items-center text-slate-400">⌕</span>
        </label>
    </div>

    <button type="button" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100" title="Notificaciones próximamente" aria-label="Notificaciones">◌</button>
    <div class="flex items-center gap-3 border-l border-slate-200 pl-4">
        <span class="grid h-9 w-9 place-items-center rounded-full bg-emerald-100 text-sm font-bold text-emerald-800">AD</span>
        <div class="hidden sm:block">
            <p class="text-sm font-bold text-slate-800">Administrador</p>
            <p class="text-xs text-slate-500">Acceso pendiente</p>
        </div>
    </div>
</header>
