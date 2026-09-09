<?php

namespace App\Modules\Operation\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Modules\Identity\Application\Services\CurrentContext;
use App\Modules\Operation\Infrastructure\Persistence\Models\CashMovement;
use App\Modules\Operation\Infrastructure\Persistence\Models\CashSession;
use App\Modules\Operation\Infrastructure\Persistence\Models\Expense;
use App\Modules\Operation\Infrastructure\Persistence\Models\ExpenseCategory;
use App\Modules\Operation\Infrastructure\Persistence\Models\PaymentMethod as ModelsPaymentMethod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function __construct(
        private readonly CurrentContext $context
    ) {}
    public const TYPES = [
        'operational' => 'Operativo',
        'stock_replenishment' => 'Surtido de mercancía',
        'investment' => 'Inversión / equipo',
        'other' => 'Otro',
    ];

    public function index(Request $request): View
    {

        $base = Expense::where('organization_id', $this->org())->where('branch_id', $this->branch());
        $query = (clone $base)->with(['category', 'supplier', 'paymentMethod']);

        foreach (['status', 'expense_type', 'expense_category_id'] as $field) {
            if ($request->filled($field)) $query->where($field, $request->input($field));
        }
        if ($request->filled('date_from')) $query->whereDate('expense_date', '>=', $request->input('date_from'));
        if ($request->filled('date_to')) $query->whereDate('expense_date', '<=', $request->input('date_to'));
        if ($request->filled('q')) {
            $term = trim($request->input('q'));
            $query->where(fn($q) => $q->where('expense_number', 'ilike', "%$term%")
                ->orWhere('beneficiary', 'ilike', "%$term%")->orWhere('source_name', 'ilike', "%$term%")
                ->orWhere('reference', 'ilike', "%$term%"));
        }

        $expenses = $query->latest('expense_date')->latest('created_at')->paginate(20)->withQueryString();
        $month = [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()];

        return view('modules.operation.expenses.index', [
            'expenses' => $expenses,
            'categories' => ExpenseCategory::where('organization_id', $this->org())->where('is_active', true)->orderBy('name')->get(),
            'types' => self::TYPES,
            'monthTotal' => (clone $base)->whereBetween('expense_date', $month)->whereIn('status', ['approved', 'paid'])->sum('amount'),
            'monthPaid' => (clone $base)->whereBetween('expense_date', $month)->where('status', 'paid')->sum('amount'),
            'pending' => (clone $base)->where('status', 'pending_approval')->sum('amount'),
        ]);
    }

    public function create(): View
    {
        return view('modules.operation.expenses.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $cat = ExpenseCategory::where('organization_id', $this->org())->whereKey($data['expense_category_id'])->firstOrFail();

        $expense = DB::transaction(function () use ($data, $cat) {
            return Expense::create([
                ...$data,
                'organization_id' => $this->org(),
                'branch_id' => $this->branch(),
                'expense_number' => $this->nextNumber(),
                'status' => $cat->requires_approval ? 'pending_approval' : 'approved',
                'requested_by' => auth()->id()
            ]);
        });

        return redirect()->route('expenses.show', $expense)->with('success', 'Gasto registrado correctamente.');
    }

    public function show(Expense $expense): View
    {

        $paymentMethods =   ModelsPaymentMethod::query()
            ->where('organization_id', $this->context->organizationId())
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $this->owner($expense);
        $expense->load(['category', 'supplier', 'paymentMethod', 'cashSession', 'requestedBy', 'approvedBy', 'cancelledBy']);
        return view('modules.operation.expenses.show', compact('expense', 'paymentMethods'));
    }

    public function edit(Expense $expense): View
    {
        $this->owner($expense);
        abort_unless(in_array($expense->status, ['draft', 'rejected']), 422);
        return view('modules.operation.expenses.edit', [...$this->formData(), 'expense' => $expense]);
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        $this->owner($expense);
        abort_unless(in_array($expense->status, ['draft', 'rejected']), 422);
        $data = $this->validated($request);
        $cat = ExpenseCategory::where('organization_id', $this->org())->whereKey($data['expense_category_id'])->firstOrFail();
        $expense->update([...$data, 'status' => $cat->requires_approval ? 'pending_approval' : 'approved', 'rejection_reason' => null]);
        return redirect()->route('expenses.show', $expense)->with('success', 'Gasto actualizado.');
    }

    public function approve(Expense $expense): RedirectResponse
    {
        $this->owner($expense);
        abort_unless($expense->status === 'pending_approval', 422);
        $expense->update(['status' => 'approved', 'approved_by' => auth()->id(), 'approved_at' => now(), 'rejection_reason' => null]);
        return back()->with('success', 'Gasto aprobado.');
    }

    public function reject(Request $request, Expense $expense): RedirectResponse
    {
        $this->owner($expense);
        abort_unless($expense->status === 'pending_approval', 422);
        $reason = $request->validate(['rejection_reason' => ['required', 'string', 'min:5', 'max:2000']])['rejection_reason'];
        $expense->update(['status' => 'rejected', 'rejection_reason' => $reason]);
        return back()->with('success', 'Gasto rechazado.');
    }

    public function pay(Request $request, Expense $expense): RedirectResponse
    {
        $this->owner($expense);
        $data = $request->validate([
            'payment_method_id' => ['required', 'uuid', Rule::exists('payment_methods', 'id')->where(fn($q) => $q->where('organization_id', $this->org())->where('is_active', true))],
            'reference' => ['nullable', 'string', 'max:120']
        ]);

        DB::transaction(function () use ($expense, $data) {
            $e = Expense::whereKey($expense->id)->lockForUpdate()->firstOrFail();
            abort_unless($e->status === 'approved', 422, 'El gasto debe estar aprobado.');

            $method = ModelsPaymentMethod::whereKey($data['payment_method_id'])->where('organization_id', $this->org())->where('is_active', true)->lockForUpdate()->firstOrFail();
            $session = null;

            if ($method->affects_cash) {
                $session = CashSession::where('organization_id', $this->org())
                    ->where('branch_id', $this->branch())
                    ->where('responsible_user_id', auth()->id())
                    ->where('status', 'open')
                    ->lockForUpdate()
                    ->first();

                if (!$session) {
                    throw ValidationException::withMessages([
                        'payment_method_id' => 'No hay una caja abierta para registrar este pago. Abre Caja antes de realizar un pago en efectivo.',
                    ]);
                }
            }

            $e->update(['status' => 'paid', 'payment_method_id' => $method->id, 'cash_session_id' => $session?->id, 'paid_at' => now(), 'reference' => $data['reference'] ?: $e->reference]);

            if ($method->affects_cash) {
                CashMovement::create([
                    'organization_id' => $e->organization_id,
                    'branch_id' => $e->branch_id,
                    'cash_session_id' => $session->id,
                    'payment_method_id' => $method->id,
                    'movement_type' => 'expense',
                    'amount' => $e->amount,
                    'source_type' => Expense::class,
                    'source_id' => $e->id,
                    'reason_code' => $e->expense_type,
                    'notes' => $e->expense_number . ' · ' . $e->description,
                    'created_by' => auth()->id(),
                    'occurred_at' => now()
                ]);
            }
        });

        return back()->with('success', 'Gasto pagado y movimiento de caja registrado.');
    }

    public function cancel(Expense $expense): RedirectResponse
    {
        $this->owner($expense);
        abort_unless(in_array($expense->status, ['draft', 'pending_approval', 'approved']), 422);
        $expense->update(['status' => 'cancelled', 'cancelled_at' => now(), 'cancelled_by' => auth()->id()]);
        return back()->with('success', 'Gasto cancelado.');
    }

    private function validated(Request $r): array
    {
        return $r->validate([
            'expense_category_id' => ['required', 'uuid', Rule::exists('expense_categories', 'id')->where(fn($q) => $q->where('organization_id', $this->org())->where('is_active', true))],
            'supplier_id' => [
                'nullable',
                'uuid',
                Rule::exists('suppliers', 'id')->where(fn($q) => $q->where('organization_id', $this->org())->where('is_active', true)),
            ],
            /* 'supplier_id' => ['nullable', 'uuid', Rule::exists('suppliers', 'id')], */
            'expense_type' => ['required', Rule::in(array_keys(self::TYPES))],
            'amount' => ['required', 'numeric', 'gt:0', 'max:999999999'],
            'beneficiary' => ['nullable', 'string', 'max:255'],
            'source_name' => ['nullable', 'string', 'max:150'],
            'reference' => ['nullable', 'string', 'max:120'],
            'expense_date' => ['required', 'date'],
            'description' => ['required', 'string', 'min:3', 'max:5000']
        ]);
    }

    private function formData(): array
    {
        return [
            'categories' => ExpenseCategory::where('organization_id', $this->org())->where('is_active', true)->orderBy('name')->get(),
            'suppliers' => Supplier::where('organization_id', $this->org())->where('is_active', true)->orderBy('business_name')->get(),
            /* 'suppliers' => Supplier::where('organization_id', $this->org())->orderBy('name')->get(), */
            'paymentMethods' => ModelsPaymentMethod::where('organization_id', $this->org())->where('is_active', true)->orderBy('name')->get(),
            'types' => self::TYPES
        ];
    }

    private function nextNumber(): string
    {
        $prefix = 'GAS-' . now()->format('Ym') . '-';
        $last = Expense::where('organization_id', $this->org())->where('expense_number', 'like', $prefix . '%')->orderByDesc('expense_number')->lockForUpdate()->value('expense_number');
        return $prefix . str_pad((string)($last ? ((int)substr($last, -4)) + 1 : 1), 4, '0', STR_PAD_LEFT);
    }

    private function org(): string
    {
        return $this->context->organizationId();
    }

    private function branch(): string
    {
        return $this->context->branchId();
    }
    private function owner(Expense $e): void
    {
        abort_unless((string)$e->organization_id === $this->org() && (string)$e->branch_id === $this->branch(), 404);
    }
}
