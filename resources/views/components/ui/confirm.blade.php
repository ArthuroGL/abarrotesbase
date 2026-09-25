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
    $modalId = $attributes->get('id') ?: 'ui-confirm-modal';

    $confirmId = $confirmId ?? ($modalId . '-confirm');
    $cancelId = $cancelId ?? ($modalId . '-cancel');
    $closeId = $closeId ?? ($modalId . '-close');
    $formId = $modalId . '-form';
    $methodId = $modalId . '-method';

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
    id="{{ $modalId }}"
    size="{{ $size }}"
    :title="$title"
    :description="null"
    close-id="{{ $closeId }}"
>
    <div class="px-5 py-6 sm:px-6">
        <div class="flex items-start gap-4">

            <div
                data-confirm-icon
                class="grid h-11 w-11 shrink-0 place-items-center rounded-xl text-lg font-black {{ $currentVariant['iconClass'] }}"
                aria-hidden="true"
            >
                {{ $currentVariant['icon'] }}
            </div>

            <div class="min-w-0 flex-1">
                <p
                    data-confirm-description
                    class="text-sm leading-6 text-slate-600"
                >
                    @if ($description)
                        {{ $description }}
                    @elseif (isset($slot) && trim($slot) !== '')
                        {{ $slot }}
                    @else
                        ¿Deseas continuar con esta acción?
                    @endif
                </p>
            </div>

        </div>
    </div>

    <x-slot:footer>

        <form
            id="{{ $formId }}"
            method="POST"
            action="#"
            class="contents"
        >
            @csrf
            <input
                type="hidden"
                name="_method"
                id="{{ $methodId }}"
                disabled
            >
        </form>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

            <button
                type="button"
                id="{{ $cancelId }}"
                data-close-modal="{{ $modalId }}"
                class="inline-flex min-h-12 items-center justify-center rounded-xl border border-slate-300 bg-white px-5 text-sm font-bold text-slate-700 transition hover:bg-slate-50 hover:text-slate-950 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-500"
            >
                {{ $cancelText }}
            </button>

            <button
                type="submit"
                id="{{ $confirmId }}"
                form="{{ $formId }}"
                disabled
                class="inline-flex min-h-12 items-center justify-center rounded-xl px-5 text-sm font-bold shadow-sm transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 disabled:cursor-not-allowed disabled:opacity-60 {{ $currentVariant['buttonClass'] }}"
            >
                <span data-confirm-text>{{ $confirmText }}</span>
            </button>

        </div>

    </x-slot:footer>
</x-ui.modal>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modalId = @json($modalId);
        const formId = @json($formId);
        const methodId = @json($methodId);
        const confirmId = @json($confirmId);
        const closeId = @json($closeId);

        const modal = document.getElementById(modalId);
        const form = document.getElementById(formId);
        const methodInput = document.getElementById(methodId);
        const confirmButton = document.getElementById(confirmId);
        const description = modal?.querySelector('[data-confirm-description]');
        const icon = modal?.querySelector('[data-confirm-icon]');
        const confirmText = modal?.querySelector('[data-confirm-text]');
        const closeButton = document.getElementById(closeId);

        if (!modal || !form || !methodInput || !confirmButton) {
            return;
        }

        const baseButtonClasses =
            'inline-flex min-h-12 items-center justify-center rounded-xl px-5 text-sm font-bold shadow-sm transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 disabled:cursor-not-allowed disabled:opacity-60';

        const variants = {
            primary: {
                icon: '✓',
                iconClass: 'bg-emerald-100 text-emerald-700',
                buttonClass: 'bg-emerald-600 text-white hover:bg-emerald-700 focus-visible:outline-emerald-600',
            },
            danger: {
                icon: '!',
                iconClass: 'bg-rose-100 text-rose-700',
                buttonClass: 'bg-rose-600 text-white hover:bg-rose-700 focus-visible:outline-rose-600',
            },
            warning: {
                icon: '!',
                iconClass: 'bg-amber-100 text-amber-700',
                buttonClass: 'bg-amber-500 text-white hover:bg-amber-600 focus-visible:outline-amber-600',
            },
            info: {
                icon: 'i',
                iconClass: 'bg-sky-100 text-sky-700',
                buttonClass: 'bg-sky-600 text-white hover:bg-sky-700 focus-visible:outline-sky-600',
            },
        };

        function openModal() {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('overflow-hidden');
            confirmButton.focus();
        }

        function closeModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('overflow-hidden');

            form.action = '#';
            methodInput.value = '';
            methodInput.disabled = true;
            confirmButton.disabled = true;
        }

        document
            .querySelectorAll('[data-confirm-open]')
            .forEach((trigger) => {
                if (trigger.dataset.confirmOpen !== modalId) {
                    return;
                }

                trigger.addEventListener('click', () => {
                    const action = trigger.dataset.confirmAction;
                    const method = (
                        trigger.dataset.confirmMethod || 'POST'
                    ).toUpperCase();

                    const title = trigger.dataset.confirmTitle;
                    const message =
                        trigger.dataset.confirmDescription ||
                        trigger.dataset.confirmMessage;

                    const text =
                        trigger.dataset.confirmText ||
                        @json($confirmText);

                    const variant =
                        variants[trigger.dataset.confirmVariant || 'primary'] ||
                        variants.primary;

                    if (!action) {
                        return;
                    }

                    const modalTitle = modal.querySelector(
                        '[data-modal-title]'
                    );

                    if (modalTitle && title) {
                        modalTitle.textContent = title;
                    }

                    if (description && message) {
                        description.textContent = message;
                    }

                    if (confirmText) {
                        confirmText.textContent = text;
                    }

                    if (icon) {
                        icon.textContent = variant.icon;
                        icon.className =
                            'grid h-11 w-11 shrink-0 place-items-center rounded-xl text-lg font-black ' +
                            variant.iconClass;
                    }

                    confirmButton.className =
                        baseButtonClasses + ' ' + variant.buttonClass;

                    form.action = action;

                    if (method === 'POST') {
                        methodInput.value = '';
                        methodInput.disabled = true;
                    } else {
                        methodInput.value = method;
                        methodInput.disabled = false;
                    }

                    confirmButton.disabled = false;
                    openModal();
                });
            });

        modal
            .querySelectorAll('[data-close-modal]')
            .forEach((button) => {
                button.addEventListener('click', closeModal);
            });

        closeButton?.addEventListener('click', closeModal);

        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                closeModal();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (
                event.key === 'Escape' &&
                !modal.classList.contains('hidden')
            ) {
                closeModal();
            }
        });
    });
</script>
