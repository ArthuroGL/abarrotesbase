<x-layouts.app title="Marcas - ABARROTESBASE">
    <div class="mx-auto max-w-5xl space-y-6">
        <div class="flex items-center justify-between border-b border-slate-200 pb-5">
            <div>
                <h1 class="text-2xl font-black text-slate-900">Marcas de Productos</h1>
                <p class="mt-1 text-xs font-semibold text-slate-500">Administra los fabricantes y firmas comerciales de tu inventario.</p>
            </div>
        </div>

        <div class="grid gap-6 md:grid-cols-12">
            {{-- Formulario para crear --}}
            <div class="md:col-span-5">
                <x-ui.card padding="p-6" class="shadow-sm border-slate-200 bg-white space-y-4">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-emerald-700">Nueva Marca</h2>

                    <form method="POST" action="{{ route('brands.store') }}" class="space-y-4">
                        @csrf
                        <div>
                            <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Nombre de la marca *
                            </label>
                            <x-ui.input id="name" name="name" required placeholder="Ej. La Moderna, Sabritas, Bimbo" :error="$errors->has('name')" />
                        </div>

                        <x-ui.button type="submit" variant="primary" class="w-full justify-center">
                            Guardar Marca
                        </x-ui.button>
                    </form>
                </x-ui.card>
            </div>

            {{-- Tabla de Marcas --}}
            <div class="md:col-span-7">
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <table class="w-full text-left text-sm text-slate-700">
                        <thead class="border-b border-slate-200 bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-5 py-3.5">Nombre</th>
                                <th class="px-5 py-3.5 text-center">Productos asociados</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($brands as $brand)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-5 py-3.5 font-bold text-slate-900">{{ $brand->name }}</td>
                                    <td class="px-5 py-3.5 text-center">
                                        <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-bold text-slate-600">
                                            {{ $brand->products_count }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="px-5 py-8 text-center text-slate-400">Sin marcas registradas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
