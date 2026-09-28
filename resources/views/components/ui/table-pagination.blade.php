@props([
'paginator',
'perPageOptions' => [10, 25, 50, 100],
])

@if ($paginator->total() > 0)

<div
    class="border-t border-slate-200 bg-slate-50/70 px-5 py-4 sm:px-6"
    data-table-pagination>

    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

        {{-- INFORMACIÓN --}}
        <div class="text-sm text-slate-600">

            Mostrando

            <span class="font-black text-slate-900">
                {{ $paginator->firstItem() ?? 0 }}
            </span>

            a

            <span class="font-black text-slate-900">
                {{ $paginator->lastItem() ?? 0 }}
            </span>

            de

            <span class="font-black text-slate-900">
                {{ $paginator->total() }}
            </span>

            registros

        </div>


        {{-- CONTROLES --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">

            {{-- REGISTROS POR PÁGINA --}}
            <form
                method="GET"
                action="{{ url()->current() }}"
                class="flex items-center justify-between gap-2 sm:justify-end"
                data-table-per-page-form>

                {{-- CONSERVAR FILTROS --}}
                @foreach (request()->except(['page', 'per_page']) as $key => $value)

                @if (is_array($value))

                @foreach ($value as $arrayValue)

                <input
                    type="hidden"
                    name="{{ $key }}[]"
                    value="{{ $arrayValue }}">

                @endforeach

                @else

                <input
                    type="hidden"
                    name="{{ $key }}"
                    value="{{ $value }}">

                @endif

                @endforeach


                <label
                    for="table-per-page"
                    class="whitespace-nowrap text-sm font-semibold text-slate-600">
                    Mostrar
                </label>


                <select
                    id="table-per-page"
                    name="per_page"
                    class="min-h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm font-bold text-slate-700 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                    onchange="this.form.submit()">

                    @foreach ($perPageOptions as $option)

                    <option
                        value="{{ $option }}"
                        @selected(
                        (int) request('per_page', $paginator->perPage()) === $option
                        )
                        >
                        {{ $option }}
                    </option>

                    @endforeach

                </select>


                <span class="whitespace-nowrap text-sm text-slate-500">
                    por página
                </span>

            </form>


            {{-- PAGINACIÓN --}}
            <div class="shrink-0">
                {{ $paginator->onEachSide(1)->links('pagination::table') }}
            </div>

        </div>

    </div>

</div>

@endif
