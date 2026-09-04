<?php

declare(strict_types=1);

namespace App\Modules\Operation\Presentation\Http\Controllers;

use App\Modules\Identity\Infrastructure\Persistence\Models\Branch;
use App\Modules\Identity\Infrastructure\Persistence\Models\Register;
use App\Modules\Operation\Infrastructure\Persistence\Models\CashMovement;
use App\Modules\Operation\Infrastructure\Persistence\Models\CashSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

final class CashController
{
    /**
     * Mostrar caja actual.
     */
    public function index(Request $request): View
    {
        $branches = Branch::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $registers = Register::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $activeSession = CashSession::query()
            ->with([
                'branch',
                'register',
                'responsibleUser',
            ])
            ->where('responsible_user_id', $request->user()->id)
            ->whereIn('status', ['open', 'counting'])
            ->latest('opened_at')
            ->first();

        $movements = $activeSession
            ? $activeSession->movements()
                ->with('createdBy')
                ->latest('occurred_at')
                ->get()
            : collect();

        $cashSummary = $this->calculateCashSummary($movements);

        return view('modules.operation.cash.index', [
            'branches' => $branches,
            'registers' => $registers,
            'activeSession' => $activeSession,
            'movements' => $movements,
            'cashIn' => $cashSummary['cash_in'],
            'cashOut' => $cashSummary['cash_out'],
            'theoreticalCash' => $cashSummary['theoretical_cash'],
        ]);
    }

    /**
     * Abrir una nueva sesión de caja.
     */
    public function open(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'uuid'],
            'register_id' => ['required', 'uuid'],
            'opening_float' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            DB::transaction(function () use ($request, $validated): void {
                $register = Register::query()
                    ->where('id', $validated['register_id'])
                    ->where('branch_id', $validated['branch_id'])
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->firstOrFail();

                $existingSession = CashSession::query()
                    ->where('register_id', $register->id)
                    ->whereIn('status', ['open', 'counting'])
                    ->lockForUpdate()
                    ->first();

                if ($existingSession) {
                    throw new \RuntimeException(
                        'La caja seleccionada ya tiene una sesión abierta.'
                    );
                }

                $now = now();

                $session = CashSession::create([
                    'organization_id' => $register->organization_id,
                    'branch_id' => $register->branch_id,
                    'register_id' => $register->id,
                    'responsible_user_id' => $request->user()->id,
                    'status' => 'open',
                    'opening_float' => $validated['opening_float'],
                    'opened_at' => $now,
                    'notes' => $validated['notes'] ?? null,
                ]);

                if ((float) $validated['opening_float'] > 0) {
                    CashMovement::create([
                        'organization_id' => $register->organization_id,
                        'branch_id' => $register->branch_id,
                        'cash_session_id' => $session->id,
                        'payment_method_id' => null,
                        'movement_type' => 'opening_float',
                        'amount' => $validated['opening_float'],
                        'source_type' => 'cash_session',
                        'source_id' => $session->id,
                        'reason_code' => null,
                        'notes' => 'Fondo inicial de apertura.',
                        'created_by' => $request->user()->id,
                        'occurred_at' => $now,
                    ]);
                }
            });
        } catch (Throwable $e) {
            return back()
                ->withErrors([
                    'register_id' => $e->getMessage(),
                ])
                ->withInput();
        }

        return redirect()
            ->route('cash.index')
            ->with('success', 'La sesión de caja se abrió correctamente.');
    }

    /**
     * Registrar un movimiento manual de caja.
     *
     * Tipos permitidos:
     * income     = entrada de efectivo
     * withdrawal = retiro de efectivo
     * deposit    = depósito de efectivo, sale de la caja
     * expense    = gasto pagado desde caja
     */
    public function storeMovement(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'movement_type' => [
                'required',
                'string',
                'in:income,withdrawal,deposit,expense',
            ],
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
            ],
            'reason_code' => [
                'nullable',
                'string',
                'max:50',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        DB::transaction(function () use ($request, $validated): void {
            $session = CashSession::query()
                ->where('responsible_user_id', $request->user()->id)
                ->where('status', 'open')
                ->latest('opened_at')
                ->lockForUpdate()
                ->first();

            if (!$session) {
                throw new \RuntimeException(
                    'No tienes una sesión de caja abierta.'
                );
            }

            CashMovement::create([
                'organization_id' => $session->organization_id,
                'branch_id' => $session->branch_id,
                'cash_session_id' => $session->id,
                'payment_method_id' => null,
                'movement_type' => $validated['movement_type'],
                'amount' => $validated['amount'],
                'source_type' => 'manual',
                'source_id' => null,
                'reason_code' => $validated['reason_code'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => $request->user()->id,
                'occurred_at' => now(),
            ]);
        });

        return redirect()
            ->route('cash.index')
            ->with('success', 'El movimiento se registró correctamente.');
    }

    /**
     * Pasar la sesión de OPEN a COUNTING.
     *
     * En este momento se congela la operación y se captura
     * el efectivo teórico esperado.
     */
    public function startCounting(
        Request $request,
        CashSession $cashSession
    ): RedirectResponse {
        DB::transaction(function () use ($request, $cashSession): void {
            $session = CashSession::query()
                ->where('id', $cashSession->id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->ensureSessionOwner($session, $request);

            if ($session->status !== 'open') {
                throw new \RuntimeException(
                    'La sesión no se encuentra abierta.'
                );
            }

            $movements = $session->movements()
                ->lockForUpdate()
                ->get();

            $summary = $this->calculateCashSummary($movements);

            $session->update([
                'status' => 'counting',
                'theoretical_total' => $summary['theoretical_cash'],
            ]);
        });

        return redirect()
            ->route('cash.index')
            ->with('success', 'La caja está lista para realizar el arqueo.');
    }

    /**
     * Cerrar la sesión después del arqueo.
     */
    public function close(
        Request $request,
        CashSession $cashSession
    ): RedirectResponse {
        $validated = $request->validate([
            'counted_total' => [
                'required',
                'numeric',
                'min:0',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        DB::transaction(function () use (
            $request,
            $cashSession,
            $validated
        ): void {
            $session = CashSession::query()
                ->where('id', $cashSession->id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->ensureSessionOwner($session, $request);

            if ($session->status !== 'counting') {
                throw new \RuntimeException(
                    'La sesión debe estar en estado de arqueo para poder cerrarse.'
                );
            }

            $theoreticalTotal = (float) $session->theoretical_total;
            $countedTotal = (float) $validated['counted_total'];

            $difference = round(
                $countedTotal - $theoreticalTotal,
                2
            );

            $session->update([
                'status' => 'closed',
                'theoretical_total' => $theoreticalTotal,
                'counted_total' => $countedTotal,
                'difference_total' => $difference,
                'closed_at' => now(),
                'closed_by' => $request->user()->id,
                'notes' => $validated['notes'] ?? $session->notes,
            ]);
        });

        return redirect()
            ->route('cash.history')
            ->with('success', 'La caja se cerró correctamente.');
    }

    /**
     * Historial de sesiones cerradas.
     */
    public function history(Request $request): View
    {
        $sessions = CashSession::query()
            ->with([
                'branch',
                'register',
                'responsibleUser',
                'closedBy',
            ])
            ->where('status', 'closed')
            ->latest('closed_at')
            ->paginate(20);

        return view('modules.operation.cash.history', [
            'sessions' => $sessions,
        ]);
    }

    /**
     * Detalle de una sesión.
     */
    public function show(
        Request $request,
        CashSession $cashSession
    ): View {
        $session = CashSession::query()
            ->with([
                'branch',
                'register',
                'responsibleUser',
                'closedBy',
                'approvedBy',
            ])
            ->findOrFail($cashSession->id);

        $movements = $session->movements()
            ->with('createdBy')
            ->orderBy('occurred_at')
            ->get();

        $cashSummary = $this->calculateCashSummary($movements);

        return view('modules.operation.cash.show', [
            'session' => $session,
            'movements' => $movements,
            'cashIn' => $cashSummary['cash_in'],
            'cashOut' => $cashSummary['cash_out'],
            'theoreticalCash' => $cashSummary['theoretical_cash'],
        ]);
    }

    /**
     * Calcular el efectivo real que debería existir físicamente
     * en la caja según los movimientos.
     */
    private function calculateCashSummary($movements): array
    {
        $cashIn = 0.0;
        $cashOut = 0.0;

        foreach ($movements as $movement) {
            $amount = (float) $movement->amount;

            switch ($movement->movement_type) {
                case 'opening_float':
                case 'sale_payment':
                case 'income':
                    $cashIn += $amount;
                    break;

                case 'sale_change':
                case 'return_payment':
                case 'expense':
                case 'withdrawal':
                case 'deposit':
                    $cashOut += $amount;
                    break;

                case 'closing_adjustment':
                    /*
                     * Un ajuste de cierre positivo entra.
                     * Uno negativo debería manejarse como
                     * movimiento independiente y justificado.
                     *
                     * Por seguridad no se incluye automáticamente
                     * en el efectivo teórico.
                     */
                    break;
            }
        }

        return [
            'cash_in' => round($cashIn, 2),
            'cash_out' => round($cashOut, 2),
            'theoretical_cash' => round($cashIn - $cashOut, 2),
        ];
    }

    /**
     * Verificar que la sesión pertenece al usuario actual.
     */
    private function ensureSessionOwner(
        CashSession $session,
        Request $request
    ): void {
        if ((string) $session->responsible_user_id !== (string) $request->user()->id) {
            abort(403, 'No tienes permiso para operar esta sesión de caja.');
        }
    }
}
