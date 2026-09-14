@props([
    'size' => 'sm',
    'title' => 'Confirmar acción',
    'description' => null,
    'confirmText' => 'Confirmar',
    'cancelText' => 'Cancelar',
    'variant' => 'primary',
    'confirmId' => null,
    'cancelId' => null,
    'closeId' => null,
])

@php
    $confirmId = $confirmId ?? ($attributes->get('id') . '-confirm');
    $cancelId = $cancelId ?? ($attributes->get('id') . '-cancel');
    $closeId = $closeId ?? ($attributes->get('id') . '-close');

    $variants = [
        'primary' => [
            'icon' => '✓',
            'iconClass' => 'bg-emerald-100 text-emerald-700',
            'buttonClass' => 'bg-emerald-600 text-white hover:bg-emerald-700 focus-visible:outline-emerald-600',
        ],

        'danger' => [
            'icon' => '!',
            'iconClass' => 'bg-rose-100 text-rose-700',
            'buttonClass' => 'bg-rose-600 text-white hover:bg-rose-700 focus-visible:outline-rose-600',
        ],

        'warning' => [
            'icon' => '!',
            'iconClass' => 'bg-amber-100 text-amber-700',
            'buttonClass' => 'bg-amber-500 text-white hover:bg-amber-600 focus-visible:outline-amber-600',
        ],

        'info' => [
            'icon' => 'i',
            'iconClass' => 'bg-sky-100 text-sky-700',
            'buttonClass' => 'bg-sky-600 text-white hover:bg-sky-700 focus-visible:outline-sky-600',
        ],
    ];

    $currentVariant = $variants[$variant] ?? $variants['primary'];
@endphp

<x-ui.modal
    id="{{ $attributes->get('id') }}"
    size="{{ $size }}"
    :title="$title"
    :description="$description"
    close-id="{{ $closeId }}"
>

    {{-- =========================================================
         BODY
    ========================================================== --}}
    <div class="px-5 py-6 sm:px-6">

        <div class="flex items-start gap-4">

            {{-- Icono --}}
            <div
                class="grid h-11 w-11 shrink-0 place-items-center rounded-xl text-lg font-black {{ $currentVariant['iconClass'] }}"
                aria-hidden="true"
            >
                {{ $currentVariant['icon'] }}
            </div>


            {{-- Contenido --}}
            <div class="min-w-0 flex-1">

                @if (isset($slot) && trim($slot) !== '')
                    {{ $slot }}
                @else
                    <p class="text-sm leading-6 text-slate-600">
                        ¿Deseas continuar con esta acción?
                    </p>
                @endif

            </div>

        </div>

    </div>


    {{-- =========================================================
         FOOTER
    ========================================================== --}}
    <x-slot:footer>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

            <button
                type="button"
                id="{{ $cancelId }}"
                class="inline-flex min-h-12 items-center justify-center rounded-xl border border-slate-300 bg-white px-5 text-sm font-bold text-slate-700 transition hover:bg-slate-50 hover:text-slate-950 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-500"
            >
                {{ $cancelText }}
            </button>


            <button
                type="button"
                id="{{ $confirmId }}"
                class="inline-flex min-h-12 items-center justify-center rounded-xl px-5 text-sm font-bold shadow-sm transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 {{ $currentVariant['buttonClass'] }}"
            >
                {{ $confirmText }}
            </button>

        </div>

    </x-slot:footer>

</x-ui.modal>
