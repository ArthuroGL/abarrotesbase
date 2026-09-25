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

    public function hasPermission(string $permission): bool
    {
        $permissions = $this->permissionCodes();

        return in_array('*', $permissions, true)
            || in_array($permission, $permissions, true);
    }

    public function isAdmin(): bool
    {
        $userId = $this->user()->id;
        $organizationId = $this->organizationId();

        return DB::table('organization_user_roles as our')
            ->join(
                'organization_users as ou',
                'ou.id',
                '=',
                'our.organization_user_id'
            )
            ->join(
                'roles as r',
                'r.id',
                '=',
                'our.role_id'
            )
            ->where('ou.user_id', $userId)
            ->where('ou.organization_id', $organizationId)
            ->where('ou.is_active', true)
            ->where('r.code', 'admin')
            ->exists();
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

    public function permissionCodes(): array
    {
        $userId = $this->user()->id;
        $organizationId = $this->organizationId();

        $isAdmin = DB::table('organization_users as ou')
            ->join(
                'organization_user_roles as our',
                'our.organization_user_id',
                '=',
                'ou.id'
            )
            ->join(
                'roles as r',
                'r.id',
                '=',
                'our.role_id'
            )
            ->where('ou.user_id', $userId)
            ->where('ou.organization_id', $organizationId)
            ->where('ou.is_active', true)
            ->where('r.code', 'admin')
            ->exists();

        if ($isAdmin) {
            return ['*'];
        }

        return DB::table('organization_user_roles as our')
            ->join(
                'organization_users as ou',
                'ou.id',
                '=',
                'our.organization_user_id'
            )
            ->join(
                'roles as r',
                'r.id',
                '=',
                'our.role_id'
            )
            ->join(
                'role_permissions as rp',
                'rp.role_id',
                '=',
                'r.id'
            )
            ->join(
                'permissions as p',
                'p.id',
                '=',
                'rp.permission_id'
            )
            ->where('ou.user_id', $userId)
            ->where('ou.organization_id', $organizationId)
            ->where('ou.is_active', true)
            ->distinct()
            ->orderBy('p.code')
            ->pluck('p.code')
            ->values()
            ->all();
    }
}
