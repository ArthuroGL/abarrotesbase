@props(['eyebrow' => null, 'title', 'description' => null])

<div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        @if ($eyebrow)
            <p class="mb-2 text-xs font-bold uppercase tracking-[0.14em] text-emerald-700">{{ $eyebrow }}</p>
        @endif
        <h1 class="text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">{{ $title }}</h1>
        @if ($description)
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">{{ $description }}</p>
        @endif
    </div>
    @isset($actions)<div class="flex shrink-0 items-center gap-3">{{ $actions }}</div>@endisset
</div>
