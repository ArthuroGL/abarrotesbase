@props([
    'caption' => null,
    'dividers' => true,
])

<div class="w-full overflow-x-auto">
    <table
        {{ $attributes->class([
            'min-w-[900px] w-full text-left',
        ]) }}
    >
        @if ($caption)
            <caption class="sr-only">
                {{ $caption }}
            </caption>
        @endif

        @isset($head)
            <thead class="border-b border-slate-200 bg-slate-50">
                {{ $head }}
            </thead>
        @endisset

        <tbody @class([
            'bg-white',
            'divide-y divide-slate-100' => $dividers,
        ])>
            {{ $slot }}
        </tbody>
    </table>
</div>
