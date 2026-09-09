<x-layouts.app title="Detalle de gasto">
    <x-layout.page-header eyebrow="Gasto {{ $expense->expense_number }}" title="{{ $expense->category?->name }}" description="{{ $expense->description }}"><x-slot:actions><a href="{{ route('expenses.index') }}" class="rounded-xl border bg-white px-4 py-2.5 font-bold">Volver</a></x-slot:actions></x-layout.page-header>
    @if(session('success'))<div class="mt-5 rounded-xl bg-emerald-50 p-4 text-sm font-bold text-emerald-800">{{ session('success') }}</div>@endif
    @if ($errors->any())
    <div class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 p-5">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="font-black text-amber-900">
                    No se pudo realizar el pago
                </p>

                <p class="mt-1 text-sm text-amber-800">
                    No hay una caja abierta para registrar este pago.
                    Abre Caja antes de realizar un pago en efectivo.
                </p>
            </div>

            <a
                href="{{ route('cash.index') }}"
                class="shrink-0 rounded-xl bg-amber-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-amber-700">
                Ir a Caja
            </a>
        </div>
    </div>
    @endif
    <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_360px]">
        <div class="rounded-2xl border bg-white p-6 shadow-sm">
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <p class="text-xs font-bold uppercase text-slate-500">Importe</p>
                    <p class="text-3xl font-black">${{ number_format($expense->amount,2) }}</p>
                </div>
                <div>
                    <p class="text-xs font-bold uppercase text-slate-500">Estado</p>
                    <p class="font-bold">{{ ucfirst(str_replace('_',' ',$expense->status)) }}</p>
                </div>
                <div>
                    <p class="text-xs font-bold uppercase text-slate-500">Tipo</p>
                    <p class="font-semibold">{{ \App\Modules\Operation\Presentation\Http\Controllers\ExpenseController::TYPES[$expense->expense_type] }}</p>
                </div>
                <div>
                    <p class="text-xs font-bold uppercase text-slate-500">Fecha</p>
                    <p class="font-semibold">{{ $expense->expense_date->format('d/m/Y') }}</p>
                </div>
                <div>
                    <p class="text-xs font-bold uppercase text-slate-500">Proveedor</p>
                    <p>{{ $expense->supplier?->business_name ?? 'Sin proveedor' }}</p>
                </div>
                <div>
                    <p class="text-xs font-bold uppercase text-slate-500">Origen</p>
                    <p>{{ $expense->source_name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-bold uppercase text-slate-500">Beneficiario</p>
                    <p>{{ $expense->beneficiary ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-bold uppercase text-slate-500">Referencia</p>
                    <p>{{ $expense->reference ?? '—' }}</p>
                </div>
            </div>
            @if($expense->rejection_reason)<div class="mt-6 rounded-xl bg-red-50 p-4"><b>Motivo de rechazo</b>
                <p>{{ $expense->rejection_reason }}</p>
            </div>@endif
        </div>
        <div class="space-y-4">
            @if($expense->status==='pending_approval')<div class="rounded-2xl border border-amber-200 bg-amber-50 p-5"><b>Requiere aprobación</b>
                <div class="mt-4 flex gap-2">
                    <form method="POST" action="{{ route('expenses.approve',$expense) }}">@csrf<button class="rounded-xl bg-emerald-600 px-4 py-2.5 font-bold text-white">Aprobar</button></form>
                    <form method="POST" action="{{ route('expenses.reject',$expense) }}">@csrf<input name="rejection_reason" required placeholder="Motivo" class="rounded-xl border-slate-200"><button class="rounded-xl bg-red-600 px-4 py-2.5 font-bold text-white">Rechazar</button></form>
                </div>
            </div>@endif
            @if ($expense->status === 'approved')
            <div class="rounded-2xl border border-blue-200 bg-blue-50 p-5">
                <b>Registrar pago</b>

                <form
                    method="POST"
                    action="{{ route('expenses.pay', $expense) }}"
                    class="mt-4 space-y-3">
                    @csrf

                    <select
                        name="payment_method_id"
                        required
                        class="w-full rounded-xl border-slate-200">
                        <option value="">Método de pago</option>

                        @foreach ($paymentMethods as $method)
                        <option value="{{ $method->id }}">
                            {{ $method->name }}
                            {{ $method->affects_cash ? ' · afecta caja' : '' }}
                        </option>
                        @endforeach
                    </select>

                    <input
                        name="reference"
                        value="{{ $expense->reference }}"
                        placeholder="Referencia / ticket"
                        class="w-full rounded-xl border-slate-200">

                    <button
                        type="submit"
                        class="w-full rounded-xl bg-emerald-600 px-4 py-2.5 font-bold text-white">
                        Marcar como pagado
                    </button>
                </form>
            </div>
            @endif
            @if(in_array($expense->status,['draft','pending_approval','approved']))<form method="POST" action="{{ route('expenses.cancel',$expense) }}">@csrf<button class="w-full rounded-xl border border-red-200 bg-white px-4 py-2.5 font-bold text-red-700">Cancelar gasto</button></form>@endif
        </div>
    </div>
</x-layouts.app>
