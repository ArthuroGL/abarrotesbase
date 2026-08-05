@props(['name'])

@php
    $icons = [
        'chart' => '▥', 'store' => '▣', 'receipt' => '≡', 'cash' => '$', 'wallet' => '◫',
        'cube' => '◇', 'boxes' => '▦', 'cart' => '⊞', 'truck' => '▱', 'users' => '♙',
        'report' => '▤', 'settings' => '⚙',
    ];
@endphp
<span class="grid h-5 w-5 shrink-0 place-items-center text-sm" aria-hidden="true">{{ $icons[$name] ?? '•' }}</span>
