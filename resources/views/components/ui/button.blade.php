@props([
    'variant' => 'primary',
    'type' => 'button',
    'size' => 'md',
])

@php
    $variants = [
        'primary' => 'bg-emerald-600 text-white hover:bg-emerald-700 focus-visible:outline-emerald-600',
        'secondary' => 'bg-white text-slate-700 ring-1 ring-inset ring-slate-300 hover:bg-slate-50 focus-visible:outline-slate-500',
        'danger' => 'bg-rose-600 text-white hover:bg-rose-700 focus-visible:outline-rose-600',
    ];

    $sizes = [
        'sm' => 'min-h-10 px-3 text-sm',
        'md' => 'min-h-12 px-4 text-base',
        'lg' => 'min-h-14 px-5 text-lg',
    ];
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->class([
        'inline-flex items-center justify-center gap-2 rounded-xl font-bold transition',
        'focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2',
        'disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50',
        $sizes[$size] ?? $sizes['md'],
        $variants[$variant] ?? $variants['primary'],
    ]) }}
>
    {{ $slot }}
</button>
