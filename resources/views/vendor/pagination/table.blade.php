@if ($paginator->hasPages())

    <nav
        role="navigation"
        aria-label="Paginación"
        class="flex items-center gap-1"
    >

        {{-- ANTERIOR --}}
        @if ($paginator->onFirstPage())

            <span
                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-slate-100 text-slate-300"
                aria-disabled="true"
            >
                ‹
            </span>

        @else

            <a
                href="{{ $paginator->previousPageUrl() }}"
                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-300 bg-white text-sm font-black text-slate-700 transition hover:bg-slate-50"
                rel="prev"
                aria-label="Página anterior"
            >
                ‹
            </a>

        @endif


        {{-- NÚMEROS --}}
        @foreach ($elements as $element)

            {{-- Separador ... --}}
            @if (is_string($element))

                <span
                    class="inline-flex h-9 min-w-9 items-center justify-center px-2 text-sm font-bold text-slate-400"
                >
                    {{ $element }}
                </span>

            @endif


            {{-- Enlaces --}}
            @if (is_array($element))

                @foreach ($element as $page => $url)

                    @if ($page == $paginator->currentPage())

                        <span
                            class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg bg-slate-950 px-3 text-sm font-black text-white"
                            aria-current="page"
                        >
                            {{ $page }}
                        </span>

                    @else

                        <a
                            href="{{ $url }}"
                            class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg border border-slate-300 bg-white px-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50"
                        >
                            {{ $page }}
                        </a>

                    @endif

                @endforeach

            @endif

        @endforeach


        {{-- SIGUIENTE --}}
        @if ($paginator->hasMorePages())

            <a
                href="{{ $paginator->nextPageUrl() }}"
                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-300 bg-white text-sm font-black text-slate-700 transition hover:bg-slate-50"
                rel="next"
                aria-label="Página siguiente"
            >
                ›
            </a>

        @else

            <span
                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-slate-100 text-slate-300"
                aria-disabled="true"
            >
                ›
            </span>

        @endif

    </nav>

@endif
