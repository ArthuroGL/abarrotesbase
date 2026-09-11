@props([
'size' => 'md',
'title' => null,
'description' => null,
'closeId' => null,
])

@php
$sizes = [
'sm' => '32rem',
'md' => '42rem',
'lg' => '56rem',
'xl' => '72rem',
];

$maxWidth = $sizes[$size] ?? $sizes['md'];
$modalElementId = $attributes->get('id');
@endphp

<div
    {{ $attributes->class([
        'fixed inset-0 z-[9999] hidden h-screen w-screen items-center justify-center',
        'bg-slate-950/900 p-4 sm:p-6',
        'backdrop-blur-md',
        'animate-modal-backdrop',
    ]) }}
    role="dialog"
    aria-modal="true"
    @php
    $modalElementId=$attributes->get('id');
    $titleId = $modalElementId ? "{$modalElementId}-title" : null;
    @endphp
    @if ($title && $titleId)
    aria-labelledby="{{ $titleId }}"
    @endif

    >
    <div
        class="animate-modal-panel relative flex max-h-[calc(100vh-2rem)] w-full min-w-0 flex-col overflow-hidden rounded-2xl border border-slate-300 bg-white shadow-[0_25px_80px_rgba(15,23,42,0.35)] ring-1 ring-black/10"
        style="max-width: {{ $maxWidth }};">

        {{-- HEADER --}}
        @if ($title || $description || $closeId || isset($header))
        <div class="flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 bg-white px-5 py-5 sm:px-6">

            @if (isset($header))

            {{ $header }}

            @else

            <div class="min-w-0">

                @if ($title)
                <h2
                    id="{{ $modalElementId }}-title"
                    class="text-xl font-black tracking-tight text-slate-950 sm:text-2xl">
                    {{ $title }}
                </h2>
                @endif

                @if ($description)
                <p class="mt-1.5 text-sm leading-6 text-slate-500">
                    {{ $description }}
                </p>
                @endif

            </div>

            @if ($closeId)
            <button
                type="button"
                id="{{ $closeId }}"
                class="grid h-11 w-11 shrink-0 place-items-center rounded-xl border border-slate-200 bg-white text-xl font-bold text-slate-500 transition hover:border-slate-300 hover:bg-slate-100 hover:text-slate-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600"
                aria-label="Cerrar ventana">
                ×
            </button>
            @endif

            @endif

        </div>
        @endif


        {{-- BODY --}}
        <div class="min-h-0 flex-1 overflow-y-auto bg-white">
            {{ $slot }}
        </div>


        {{-- FOOTER --}}
        @isset($footer)
        <div class="shrink-0 border-t border-slate-200 bg-slate-50 px-5 py-5 sm:px-6">
            {{ $footer }}
        </div>
        @endisset

    </div>
</div>
