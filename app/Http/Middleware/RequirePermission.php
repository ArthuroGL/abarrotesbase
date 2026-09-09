<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

final class RequirePermission
{
    public function handle(
        Request $request,
        Closure $next,
        string ...$permissions
    ): Response {
        $user = $request->user();

        abort_unless($user, 401);

        $isAdmin = DB::table('organization_user_roles as our')
            ->join(
                'roles as r',
                'r.id',
                '=',
                'our.role_id'
            )
            ->join(
                'organization_users as ou',
                'ou.id',
                '=',
                'our.organization_user_id'
            )
            ->where('ou.user_id', $user->id)
            ->where('ou.is_active', true)
            ->whereIn('r.code', ['admin'])
            ->exists();

        if ($isAdmin) {
            return $next($request);
        }

        $hasPermission = DB::table('organization_user_roles as our')
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
            ->where('ou.user_id', $user->id)
            ->where('ou.is_active', true)
            ->whereIn('p.code', $permissions)
            ->exists();

        abort_unless($hasPermission, 403);

        return $next($request);
    }
}
