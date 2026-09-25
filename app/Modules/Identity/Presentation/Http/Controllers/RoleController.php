<?php

declare(strict_types=1);

namespace App\Modules\Identity\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Application\Services\AuditService;
use App\Modules\Identity\Application\Services\CurrentContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class RoleController extends Controller
{
    public function __construct(
        private readonly CurrentContext $context,
        private readonly AuditService $audit,
    ) {}

    /*
    |--------------------------------------------------------------------------
    | Listado de roles
    |--------------------------------------------------------------------------
    */

    public function index(): View
    {
        $organizationId = $this->context->organizationId();

        $roles = DB::table('roles as r')
            ->leftJoin(
                'role_permissions as rp',
                'rp.role_id',
                '=',
                'r.id'
            )
            ->where('r.organization_id', $organizationId)
            ->select([
                'r.id',
                'r.code',
                'r.name',
                'r.description',
                'r.is_system',
                DB::raw('COUNT(rp.permission_id) as permissions_count'),
            ])
            ->groupBy([
                'r.id',
                'r.code',
                'r.name',
                'r.description',
                'r.is_system',
            ])
            ->orderByRaw("
                CASE
                    WHEN r.code = 'admin' THEN 0
                    ELSE 1
                END
            ")
            ->orderBy('r.name')
            ->get();

        return view('modules.identity.roles.index', [
            'roles' => $roles,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Editar permisos de un rol
    |--------------------------------------------------------------------------
    */

    public function edit(string $role): View
    {
        $organizationId = $this->context->organizationId();

        $roleData = DB::table('roles')
            ->where('id', $role)
            ->where('organization_id', $organizationId)
            ->first();

        abort_unless($roleData, 404);

        /*
        |--------------------------------------------------------------------------
        | Todos los permisos disponibles
        |--------------------------------------------------------------------------
        */

        $permissions = DB::table('permissions')
            ->orderBy('module')
            ->orderBy('code')
            ->get([
                'id',
                'code',
                'module',
                'name',
                'description',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Permisos actualmente asignados
        |--------------------------------------------------------------------------
        */

        $selectedPermissionIds = DB::table('role_permissions')
            ->where('role_id', $roleData->id)
            ->pluck('permission_id')
            ->map(fn($id) => (string) $id)
            ->all();

        /*
        |--------------------------------------------------------------------------
        | Agrupar permisos por módulo
        |--------------------------------------------------------------------------
        */

        $permissionGroups = $permissions->groupBy('module');

        return view('modules.identity.roles.edit', [
            'role' => $roleData,
            'permissionGroups' => $permissionGroups,
            'selectedPermissionIds' => $selectedPermissionIds,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Actualizar permisos
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, string $role): RedirectResponse
    {
        $organizationId = $this->context->organizationId();

        $roleData = DB::table('roles')
            ->where('id', $role)
            ->where('organization_id', $organizationId)
            ->first();

        abort_unless($roleData, 404);


        /*
    |--------------------------------------------------------------------------
    | El rol admin conserva acceso total
    |--------------------------------------------------------------------------
    */

        if ($roleData->code === 'admin') {
            return redirect()
                ->route('roles.edit', $roleData->id)
                ->with(
                    'info',
                    'El rol Administrador conserva acceso total al sistema.'
                );
        }


        /*
    |--------------------------------------------------------------------------
    | Validación
    |--------------------------------------------------------------------------
    */

        $validated = $request->validate([
            'permissions' => [
                'nullable',
                'array',
            ],

            'permissions.*' => [
                'uuid',
                'exists:permissions,id',
            ],
        ]);


        /*
    |--------------------------------------------------------------------------
    | Permisos recibidos
    |--------------------------------------------------------------------------
    */

        $permissionIds = collect(
            $validated['permissions'] ?? []
        )
            ->map(fn($id) => (string) $id)
            ->unique()
            ->values()
            ->all();


        /*
    |--------------------------------------------------------------------------
    | Verificar que existan
    |--------------------------------------------------------------------------
    */

        $validPermissionIds = DB::table('permissions')
            ->whereIn('id', $permissionIds)
            ->pluck('id')
            ->map(fn($id) => (string) $id)
            ->all();

        if (count($validPermissionIds) !== count($permissionIds)) {
            return back()
                ->withErrors([
                    'permissions' => 'Uno o más permisos seleccionados no son válidos.',
                ])
                ->withInput();
        }


        /*
    |--------------------------------------------------------------------------
    | Antes + modificación + después + auditoría
    |--------------------------------------------------------------------------
    */

        DB::transaction(function () use (
            $roleData,
            $validPermissionIds
        ): void {

            /*
        |--------------------------------------------------------------------------
        | BEFORE
        |--------------------------------------------------------------------------
        */

            $beforePermissions = DB::table('role_permissions as rp')
                ->join(
                    'permissions as p',
                    'p.id',
                    '=',
                    'rp.permission_id'
                )
                ->where('rp.role_id', $roleData->id)
                ->orderBy('p.code')
                ->pluck('p.code')
                ->values()
                ->all();


            /*
        |--------------------------------------------------------------------------
        | Eliminar permisos actuales
        |--------------------------------------------------------------------------
        */

            DB::table('role_permissions')
                ->where('role_id', $roleData->id)
                ->delete();


            /*
        |--------------------------------------------------------------------------
        | Insertar permisos nuevos
        |--------------------------------------------------------------------------
        */

            if ($validPermissionIds !== []) {

                $rows = [];

                foreach ($validPermissionIds as $permissionId) {
                    $rows[] = [
                        'role_id' => $roleData->id,
                        'permission_id' => $permissionId,
                    ];
                }

                DB::table('role_permissions')->insert($rows);
            }


            /*
        |--------------------------------------------------------------------------
        | AFTER
        |--------------------------------------------------------------------------
        */

            $afterPermissions = DB::table('role_permissions as rp')
                ->join(
                    'permissions as p',
                    'p.id',
                    '=',
                    'rp.permission_id'
                )
                ->where('rp.role_id', $roleData->id)
                ->orderBy('p.code')
                ->pluck('p.code')
                ->values()
                ->all();


            /*
        |--------------------------------------------------------------------------
        | Auditoría
        |--------------------------------------------------------------------------
        */

            $this->audit->record(
                action: 'role.permissions.updated',
                auditableType: 'Role',
                auditableId: $roleData->id,
                before: [
                    'role_code' => $roleData->code,
                    'role_name' => $roleData->name,
                    'permissions' => $beforePermissions,
                ],
                after: [
                    'role_code' => $roleData->code,
                    'role_name' => $roleData->name,
                    'permissions' => $afterPermissions,
                ],
            );
        });


        return redirect()
            ->route('roles.edit', $roleData->id)
            ->with(
                'success',
                'Permisos actualizados correctamente.'
            );
    }
}
