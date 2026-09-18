@props([
    'caption' => null,
    'maxHeight' => '420px',
])

<div
    {{ $attributes->class([
        'w-full min-w-0',
    ]) }}
    data-table-wrapper
>
    <div
        class="w-full overflow-x-auto overflow-y-auto overscroll-contain rounded-xl"
        style="max-height: {{ $maxHeight }};"
        data-table-scroll
    >
        <table class="w-full border-separate border-spacing-0">

            @if ($caption)
                <caption class="sr-only">
                    {{ $caption }}
                </caption>
            @endif

            @isset($head)
                <thead class="sticky top-0 z-20">
                    {{ $head }}
                </thead>
            @endisset

            <tbody class="divide-y divide-slate-200 bg-white">
                {{ $slot }}
            </tbody>

        </table>
    </div>
</div>
