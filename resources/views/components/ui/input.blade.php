@props([
    'disabled' => false,
    'error' => false,
    'money' => false,
])

<input
    {{ $disabled ? 'disabled' : '' }}
    {{ $money ? 'data-money-input' : '' }}
    {{ $attributes->class([
        'app-input',
        'ring-rose-300 focus:ring-rose-500' => $error,
    ]) }}
>
