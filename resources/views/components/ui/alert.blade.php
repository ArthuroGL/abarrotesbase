@props([
    'type' => 'success',
    'title' => null,
])

@php
    $types = [
        'success' => [
            'container' => 'border-emerald-200 bg-emerald-50',
            'icon' => 'bg-emerald-100 text-emerald-700',
            'title' => 'text-emerald-900',
            'text' => 'text-emerald-700',
            'symbol' => '✓',
            'role' => 'status',
        ],
        'error' => [
            'container' => 'border-rose-200 bg-rose-50',
            'icon' => 'bg-rose-100 text-rose-700',
            'title' => 'text-rose-900',
            'text' => 'text-rose-700',
            'symbol' => '!',
            'role' => 'alert',
        ],
        'warning' => [
            'container' => 'border-amber-200 bg-amber-50',
            'icon' => 'bg-amber-100 text-amber-700',
            'title' => 'text-amber-900',
            'text' => 'text-amber-700',
            'symbol' => '!',
            'role' => 'alert',
        ],
        'info' => [
            'container' => 'border-sky-200 bg-sky-50',
            'icon' => 'bg-sky-100 text-sky-700',
            'title' => 'text-sky-900',
            'text' => 'text-sky-700',
            'symbol' => 'i',
            'role' => 'status',
        ],
    ];

    $currentType = $types[$type] ?? $types['success'];
@endphp

<div
    {{ $attributes->merge([
        'class' => 'rounded-2xl border px-5 py-4 ' . $currentType['container'],
    ]) }}
    role="{{ $currentType['role'] }}"
>
    <div class="flex items-start gap-3">
        <div
            class="grid h-9 w-9 shrink-0 place-items-center rounded-xl font-black {{ $currentType['icon'] }}"
            aria-hidden="true"
        >
            {{ $currentType['symbol'] }}
        </div>

        <div class="min-w-0 flex-1">
            @if ($title)
                <p class="text-sm font-black {{ $currentType['title'] }}">
                    {{ $title }}
                </p>
            @endif

            <div class="{{ $title ? 'mt-1 ' : '' }}text-sm leading-6 {{ $currentType['text'] }}">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
