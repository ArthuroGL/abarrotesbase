<x-layouts.app title="Detalle de gasto">

    @php
        $statusLabel = match ($expense->status) {
            'pending_approval' => 'Pendiente de aprobación',
            'approved' => 'Aprobado',
            'paid' => 'Pagado',
            'rejected' => 'Rechazado',
            'cancelled' => 'Cancelado',
            'draft' => 'Borrador',
            default => ucfirst(str_replace('_', ' ', $expense->status)),
        };

        $statusClass = match ($expense->status) {
            'paid' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            'approved' => 'bg-sky-50 text-sky-700 ring-sky-200',
            'pending_approval' => 'bg-amber-50 text-amber-700 ring-amber-200',
            'rejected', 'cancelled' => 'bg-rose-50 text-rose-700 ring-rose-200',
            default => 'bg-slate-100 text-slate-600 ring-slate-200',
        };

        $typeLabel = \App\Modules\Operation\Presentation\Http\Controllers\ExpenseController::TYPES[$expense->expense_type]
            ?? $expense->expense_type;
    @endphp


    <x-layout.page-header
        eyebrow="Gasto {{ $expense->expense_number }}"
        title="{{ $expense->category?->name ?? 'Sin categoría' }}"
        description="{{ $expense->description }}"
    >

        <x-slot:actions>

            <x-ui.button
                variant="secondary"
                type="button"
                onclick="window.location.href='{{ route('expenses.index') }}'"
            >
                Volver
            </x-ui.button>

            @if (in_array($expense->status, ['draft', 'rejected']))
                <x-ui.button
                    variant="primary"
                    type="button"
                    onclick="window.location.href='{{ route('expenses.edit', $expense) }}'"
                >
                    Editar
                </x-ui.button>
            @endif

        </x-slot:actions>

    </x-layout.page-header>


    {{-- ========================================================= --}}
    {{-- ÉXITO --}}
    {{-- ========================================================= --}}

    @if (session('success'))

        <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4">

            <div class="flex items-start gap-3">

                <div class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-emerald-100 font-black text-emerald-700">
                    ✓
                </div>

                <div>
                    <p class="text-sm font-black text-emerald-900">
                        Operación completada
                    </p>

                    <p class="mt-1 text-sm text-emerald-700">
                        {{ session('success') }}
                    </p>
                </div>

            </div>

        </div>

    @endif


    {{-- ========================================================= --}}
    {{-- ERROR DE PAGO EN EFECTIVO --}}
    {{-- ========================================================= --}}

    @if ($errors->has('payment_method_id'))

        <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4">

            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                <div>

                    <p class="font-black text-amber-900">
                        No se pudo realizar el pago
                    </p>

                    <p class="mt-1 text-sm text-amber-800">
                        {{ $errors->first('payment_method_id') }}
                    </p>

                </div>

                <x-ui.button
                    variant="secondary"
                    type="button"
                    onclick="window.location.href='{{ route('cash.index') }}'"
                >
                    Ir a Caja
                </x-ui.button>

            </div>

        </div>

    @endif


    <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_360px]">


        {{-- ===================================================== --}}
        {{-- INFORMACIÓN --}}
        {{-- ===================================================== --}}

        <x-ui.card>

            <div class="flex flex-col gap-4 border-b border-slate-200 pb-5 sm:flex-row sm:items-center sm:justify-between">

                <div>

                    <p class="text-xs font-black uppercase tracking-wider text-slate-500">
                        Estado actual
                    </p>

                    <span class="mt-2 inline-flex rounded-lg px-3 py-1.5 text-xs font-black ring-1 ring-inset {{ $statusClass }}">
                        {{ $statusLabel }}
                    </span>

                </div>

                <div class="text-left sm:text-right">

                    <p class="text-xs font-black uppercase tracking-wider text-slate-500">
                        Importe
                    </p>

                    <p class="mt-1 text-3xl font-black tabular-nums text-slate-900">
                        ${{ number_format((float) $expense->amount, 2) }}
                    </p>

                </div>

            </div>


            <dl class="mt-6 grid gap-x-6 gap-y-6 sm:grid-cols-2">

                <div>
                    <dt class="text-xs font-black uppercase tracking-wider text-slate-400">
                        Tipo
                    </dt>
                    <dd class="mt-1 font-semibold text-slate-800">
                        {{ $typeLabel }}
                    </dd>
                </div>


                <div>
                    <dt class="text-xs font-black uppercase tracking-wider text-slate-400">
                        Fecha
                    </dt>
                    <dd class="mt-1 font-semibold text-slate-800">
                        {{ $expense->expense_date->format('d/m/Y') }}
                    </dd>
                </div>


                <div>
                    <dt class="text-xs font-black uppercase tracking-wider text-slate-400">
                        Proveedor
                    </dt>
                    <dd class="mt-1 text-slate-700">
                        {{ $expense->supplier?->business_name ?? 'Sin proveedor' }}
                    </dd>
                </div>


                <div>
                    <dt class="text-xs font-black uppercase tracking-wider text-slate-400">
                        Origen
                    </dt>
                    <dd class="mt-1 text-slate-700">
                        {{ $expense->source_name ?? '—' }}
                    </dd>
                </div>


                <div>
                    <dt class="text-xs font-black uppercase tracking-wider text-slate-400">
                        Beneficiario
                    </dt>
                    <dd class="mt-1 text-slate-700">
                        {{ $expense->beneficiary ?? '—' }}
                    </dd>
                </div>


                <div>
                    <dt class="text-xs font-black uppercase tracking-wider text-slate-400">
                        Referencia
                    </dt>
                    <dd class="mt-1 text-slate-700">
                        {{ $expense->reference ?? '—' }}
                    </dd>
                </div>

            </dl>


            <div class="mt-6 border-t border-slate-200 pt-6">

                <p class="text-xs font-black uppercase tracking-wider text-slate-400">
                    Descripción
                </p>

                <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-700">
                    {{ $expense->description }}
                </p>

            </div>


            @if ($expense->rejection_reason)

                <div class="mt-6 rounded-2xl border border-rose-200 bg-rose-50 p-5">

                    <p class="text-xs font-black uppercase tracking-wider text-rose-700">
                        Motivo de rechazo
                    </p>

                    <p class="mt-2 whitespace-pre-line text-sm leading-6 text-rose-800">
                        {{ $expense->rejection_reason }}
                    </p>

                </div>

            @endif

        </x-ui.card>


        {{-- ===================================================== --}}
        {{-- ACCIONES --}}
        {{-- ===================================================== --}}

        <div class="space-y-4">


            {{-- Aprobación --}}
            @if ($expense->status === 'pending_approval')

                <x-ui.card class="border-amber-200 bg-amber-50">

                    <p class="text-sm font-black text-amber-900">
                        Requiere aprobación
                    </p>

                    <p class="mt-1 text-sm leading-6 text-amber-800">
                        El gasto debe aprobarse antes de poder registrarse como pagado.
                    </p>


                    <div class="mt-5 grid gap-3">

                        <form
                            method="POST"
                            action="{{ route('expenses.approve', $expense) }}"
                        >
                            @csrf

                            <x-ui.button
                                type="submit"
                                variant="primary"
                                class="w-full"
                            >
                                Aprobar gasto
                            </x-ui.button>

                        </form>


                        <form
                            method="POST"
                            action="{{ route('expenses.reject', $expense) }}"
                            class="space-y-3"
                        >

                            @csrf

                            <x-ui.input
                                name="rejection_reason"
                                maxlength="2000"
                                placeholder="Motivo del rechazo..."
                                required
                            />

                            <x-ui.button
                                type="submit"
                                variant="danger"
                                class="w-full"
                            >
                                Rechazar gasto
                            </x-ui.button>

                        </form>

                    </div>

                </x-ui.card>

            @endif


            {{-- Pago --}}
            @if ($expense->status === 'approved')

                <x-ui.card class="border-sky-200 bg-sky-50">

                    <p class="text-sm font-black text-sky-900">
                        Registrar pago
                    </p>

                    <p class="mt-1 text-sm leading-6 text-sky-800">
                        Selecciona cómo se liquidó el gasto.
                    </p>


                    <form
                        method="POST"
                        action="{{ route('expenses.pay', $expense) }}"
                        class="mt-5 space-y-4"
                    >

                        @csrf


                        <div>

                            <label
                                for="payment_method_id"
                                class="mb-1.5 block text-xs font-black uppercase tracking-wider text-slate-600"
                            >
                                Método de pago
                            </label>

                            <select
                                id="payment_method_id"
                                name="payment_method_id"
                                required
                                class="app-input"
                            >

                                <option value="">
                                    Selecciona un método
                                </option>

                                @foreach ($paymentMethods as $method)

                                    <option value="{{ $method->id }}">
                                        {{ $method->name }}

                                        @if ($method->affects_cash)
                                            · afecta caja
                                        @endif
                                    </option>

                                @endforeach

                            </select>

                        </div>


                        <div>

                            <label
                                for="payment-reference"
                                class="mb-1.5 block text-xs font-black uppercase tracking-wider text-slate-600"
                            >
                                Referencia
                            </label>

                            <x-ui.input
                                id="payment-reference"
                                name="reference"
                                maxlength="120"
                                value="{{ $expense->reference }}"
                                placeholder="Referencia, ticket o comprobante..."
                            />

                        </div>


                        <x-ui.button
                            type="submit"
                            variant="primary"
                            class="w-full"
                        >
                            Marcar como pagado
                        </x-ui.button>

                    </form>

                </x-ui.card>

            @endif


            {{-- Cancelar --}}
            @if (in_array($expense->status, ['draft', 'pending_approval', 'approved']))

                <form
                    method="POST"
                    action="{{ route('expenses.cancel', $expense) }}"
                >

                    @csrf

                    <x-ui.button
                        type="submit"
                        variant="danger"
                        class="w-full"
                    >
                        Cancelar gasto
                    </x-ui.button>

                </form>

            @endif

        </div>

    </div>

</x-layouts.app>
