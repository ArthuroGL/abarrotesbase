<x-layouts.app title="Categorías - ABARROTESBASE">
    <div class="mx-auto max-w-5xl space-y-6">
        <div class="flex items-center justify-between border-b border-slate-200 pb-5">
            <div>
                <h1 class="text-2xl font-black text-slate-900">Categorías de Productos</h1>
                <p class="mt-1 text-xs font-semibold text-slate-500">Administra las clasificaciones para organizar tu catálogo.</p>
            </div>
        </div>

        <div class="grid gap-6 md:grid-cols-12">
            {{-- Formulario para crear --}}
            <div class="md:col-span-5">
                <x-ui.card padding="p-6" class="shadow-sm border-slate-200 bg-white space-y-4">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-emerald-700">Nueva Categoría</h2>

                    <form method="POST" action="{{ route('categories.store') }}" class="space-y-4">
                        @csrf
                        <div>
                            <label for="code" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Código / Clave *
                            </label>
                            <x-ui.input id="code" name="code" required placeholder="Ej. BEB" :error="$errors->has('code')" />
                        </div>

                        <div>
                            <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Nombre *
                            </label>
                            <x-ui.input id="name" name="name" required placeholder="Ej. Bebidas y Refrescos" :error="$errors->has('name')" />
                        </div>

                        <x-ui.button type="submit" variant="primary" class="w-full justify-center">
                            Guardar Categoría
                        </x-ui.button>
                    </form>
                </x-ui.card>
            </div>

            {{-- Tabla de Categorías --}}
            <div class="md:col-span-7">
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <table class="w-full text-left text-sm text-slate-700">
                        <thead class="border-b border-slate-200 bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-5 py-3.5">Código</th>
                                <th class="px-5 py-3.5">Nombre</th>
                                <th class="px-5 py-3.5 text-center">Productos</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($categories as $cat)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-5 py-3.5 font-mono text-xs font-bold text-slate-600">{{ $cat->code }}</td>
                                    <td class="px-5 py-3.5 font-bold text-slate-900">{{ $cat->name }}</td>
                                    <td class="px-5 py-3.5 text-center">
                                        <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-bold text-slate-600">
                                            {{ $cat->products_count }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-5 py-8 text-center text-slate-400">Sin categorías creadas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
