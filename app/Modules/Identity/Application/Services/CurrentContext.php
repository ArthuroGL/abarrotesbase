<?php

declare(strict_types=1);

namespace App\Modules\Identity\Application\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
final class CurrentContext
{
    public function user(): User
    {
        $user = auth()->user();

        abort_unless($user, 401);

        return $user;
    }

    public function organizationId(): string
    {
        $userId = $this->user()->id;

        $organizationId = session('organization_id');

        if ($organizationId) {
            $exists = DB::table('organization_users')
                ->where('user_id', $userId)
                ->where('organization_id', $organizationId)
                ->where('is_active', true)
                ->exists();

            abort_unless(
                $exists,
                403,
                'La organización seleccionada no pertenece al usuario actual.'
            );

            return (string) $organizationId;
        }

        $organizations = DB::table('organization_users')
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->pluck('organization_id');

        abort_if(
            $organizations->isEmpty(),
            403,
            'El usuario no pertenece a ninguna organización activa.'
        );

        abort_if(
            $organizations->count() > 1,
            409,
            'El usuario pertenece a varias organizaciones. Debe seleccionar una.'
        );

        $organizationId = (string) $organizations->first();

        session(['organization_id' => $organizationId]);

        return $organizationId;
    }

    public function branchId(): string
    {
        $userId = $this->user()->id;
        $organizationId = $this->organizationId();

        $branchId = session('branch_id');

        if ($branchId) {
            $allowed = DB::table('user_branches as ub')
                ->join(
                    'organization_users as ou',
                    'ou.id',
                    '=',
                    'ub.organization_user_id'
                )
                ->join(
                    'branches as b',
                    'b.id',
                    '=',
                    'ub.branch_id'
                )
                ->where('ou.user_id', $userId)
                ->where('ou.organization_id', $organizationId)
                ->where('ou.is_active', true)
                ->where('b.id', $branchId)
                ->where('b.is_active', true)
                ->exists();

            abort_unless(
                $allowed,
                403,
                'La sucursal seleccionada no está asignada al usuario.'
            );

            return (string) $branchId;
        }

        $branches = DB::table('user_branches as ub')
            ->join(
                'organization_users as ou',
                'ou.id',
                '=',
                'ub.organization_user_id'
            )
            ->join(
                'branches as b',
                'b.id',
                '=',
                'ub.branch_id'
            )
            ->where('ou.user_id', $userId)
            ->where('ou.organization_id', $organizationId)
            ->where('ou.is_active', true)
            ->where('b.is_active', true)
            ->pluck('b.id');

        abort_if(
            $branches->isEmpty(),
            403,
            'El usuario no tiene sucursales asignadas.'
        );

        abort_if(
            $branches->count() > 1,
            409,
            'El usuario tiene varias sucursales asignadas. Debe seleccionar una.'
        );

        $branchId = (string) $branches->first();

        session(['branch_id' => $branchId]);

        return $branchId;
    }
}
