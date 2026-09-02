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

final class CashController
{
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

        $cashIn = 0;
        $cashOut = 0;

        foreach ($movements as $movement) {
            $amount = (float) $movement->amount;

            if (in_array($movement->movement_type, [
                'opening_float',
                'sale_payment',
                'income',
                'deposit',
            ], true)) {
                $cashIn += $amount;
            }

            if (in_array($movement->movement_type, [
                'sale_change',
                'return_payment',
                'expense',
                'withdrawal',
            ], true)) {
                $cashOut += $amount;
            }
        }

        $theoreticalCash = $cashIn - $cashOut;

        return view('modules.operation.cash.index', [
            'branches' => $branches,
            'registers' => $registers,
            'activeSession' => $activeSession,
            'movements' => $movements,
            'cashIn' => $cashIn,
            'cashOut' => $cashOut,
            'theoreticalCash' => $theoreticalCash,
        ]);
    }

    public function open(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'uuid'],
            'register_id' => ['required', 'uuid'],
            'opening_float' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $register = Register::query()
            ->where('id', $validated['register_id'])
            ->where('branch_id', $validated['branch_id'])
            ->where('is_active', true)
            ->firstOrFail();

        $existingSession = CashSession::query()
            ->where('register_id', $register->id)
            ->whereIn('status', ['open', 'counting'])
            ->exists();

        if ($existingSession) {
            return back()
                ->withErrors([
                    'register_id' => 'La caja seleccionada ya tiene una sesión abierta.',
                ])
                ->withInput();
        }

        DB::transaction(function () use ($request, $validated, $register): void {
            $session = CashSession::create([
                'organization_id' => $register->organization_id,
                'branch_id' => $register->branch_id,
                'register_id' => $register->id,
                'responsible_user_id' => $request->user()->id,
                'status' => 'open',
                'opening_float' => $validated['opening_float'],
                'opened_at' => now(),
                'notes' => $validated['notes'] ?? null,
            ]);

            if ((float) $validated['opening_float'] > 0) {
                CashMovement::create([
                    'organization_id' => $register->organization_id,
                    'branch_id' => $register->branch_id,
                    'cash_session_id' => $session->id,
                    'movement_type' => 'opening_float',
                    'amount' => $validated['opening_float'],
                    'source_type' => 'cash_session',
                    'source_id' => $session->id,
                    'created_by' => $request->user()->id,
                    'occurred_at' => now(),
                ]);
            }
        });

        return redirect()
            ->route('cash.index')
            ->with('success', 'La sesión de caja se abrió correctamente.');
    }
}
