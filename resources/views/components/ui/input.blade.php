@props([
    'disabled' => false,
    'error' => false,
])

<input
    {{ $disabled ? 'disabled' : '' }}
    {{ $attributes->class([
        'app-input',
        'ring-rose-300 focus:ring-rose-500' => $error,
    ]) }}
>
