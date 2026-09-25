<?php

declare(strict_types=1);

namespace App\Modules\Identity\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Application\Services\AuditService;
use App\Modules\Identity\Application\Services\CurrentContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

final class UserController extends Controller
{
    public function __construct(
        private readonly CurrentContext $context,
        private readonly AuditService $audit,
    ) {}

    /*
    |--------------------------------------------------------------------------
    | Listado de usuarios
    |--------------------------------------------------------------------------
    */

    public function index(): View
    {
        $organizationId = $this->context->organizationId();

        $users = DB::table('users as u')
            ->join('organization_users as ou', 'ou.user_id', '=', 'u.id')
            ->where('ou.organization_id', $organizationId)
            ->select([
                'u.id',
                'u.name',
                'u.email',
                'u.is_active',
                'u.last_login_at',
                'u.created_at',
                'ou.id as organization_user_id',
                'ou.is_active as organization_user_is_active',
            ])
            ->orderBy('u.name')
            ->get();

        foreach ($users as $user) {
            $user->roles = DB::table('organization_user_roles as our')
                ->join('roles as r', 'r.id', '=', 'our.role_id')
                ->where('our.organization_user_id', $user->organization_user_id)
                ->orderBy('r.name')
                ->pluck('r.name')
                ->values();
        }

        return view('modules.identity.users.index', [
            'users' => $users,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Formulario de creación
    |--------------------------------------------------------------------------
    */

    public function create(): View
    {
        $organizationId = $this->context->organizationId();

        $roles = DB::table('roles')
            ->where('organization_id', $organizationId)
            ->orderBy('name')
            ->get([
                'id',
                'code',
                'name',
                'description',
            ]);

        $branches = DB::table('branches')
            ->where('organization_id', $organizationId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get([
                'id',
                'code',
                'name',
            ]);

        return view('modules.identity.users.create', [
            'roles' => $roles,
            'branches' => $branches,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Crear usuario
    |--------------------------------------------------------------------------
    */

    public function store(Request $request): RedirectResponse
    {
        $organizationId = $this->context->organizationId();

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],

            'role_id' => [
                'required',
                'uuid',
                Rule::exists('roles', 'id')
                    ->where('organization_id', $organizationId),
            ],

            'branch_id' => [
                'required',
                'uuid',
                Rule::exists('branches', 'id')
                    ->where('organization_id', $organizationId)
                    ->where('is_active', true),
            ],
        ]);

        DB::transaction(function () use (
            $validated,
            $organizationId
        ): void {

            /*
            |--------------------------------------------------------------------------
            | Usuario
            |--------------------------------------------------------------------------
            */

            $userId = (string) Str::uuid();

            DB::table('users')->insert([
                'id' => $userId,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | Relación usuario-organización
            |--------------------------------------------------------------------------
            */

            $organizationUserId = (string) Str::uuid();

            DB::table('organization_users')->insert([
                'id' => $organizationUserId,
                'organization_id' => $organizationId,
                'user_id' => $userId,
                'is_active' => true,
                'joined_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | Sucursal
            |--------------------------------------------------------------------------
            */

            DB::table('user_branches')->insert([
                'organization_user_id' => $organizationUserId,
                'branch_id' => $validated['branch_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | Rol
            |--------------------------------------------------------------------------
            */

            DB::table('organization_user_roles')->insert([
                'organization_user_id' => $organizationUserId,
                'role_id' => $validated['role_id'],
            ]);
            /*
            auditoria
            */
            $this->audit->record(
                action: 'user.created',
                auditableType: 'User',
                auditableId: $userId,
                before: null,
                after: [
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'is_active' => true,
                    'role_id' => $validated['role_id'],
                    'branch_id' => $validated['branch_id'],
                ],
                branchId: $validated['branch_id'],
            );
        });

        return redirect()
            ->route('users.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    /*
    |--------------------------------------------------------------------------
    | Formulario de edición
    |--------------------------------------------------------------------------
    */

    public function edit(string $user): View
    {
        $organizationId = $this->context->organizationId();

        $userData = DB::table('users as u')
            ->join(
                'organization_users as ou',
                'ou.user_id',
                '=',
                'u.id'
            )
            ->where('u.id', $user)
            ->where('ou.organization_id', $organizationId)
            ->select([
                'u.id',
                'u.name',
                'u.email',
                'u.is_active',
                'ou.id as organization_user_id',
            ])
            ->first();

        abort_unless($userData, 404);

        $roles = DB::table('roles')
            ->where('organization_id', $organizationId)
            ->orderBy('name')
            ->get([
                'id',
                'code',
                'name',
                'description',
            ]);

        $branches = DB::table('branches')
            ->where('organization_id', $organizationId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get([
                'id',
                'code',
                'name',
            ]);

        $currentRole = DB::table('organization_user_roles')
            ->where('organization_user_id', $userData->organization_user_id)
            ->value('role_id');

        $currentBranch = DB::table('user_branches')
            ->where('organization_user_id', $userData->organization_user_id)
            ->value('branch_id');

        return view('modules.identity.users.edit', [
            'user' => $userData,
            'roles' => $roles,
            'branches' => $branches,
            'currentRole' => $currentRole,
            'currentBranch' => $currentBranch,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Actualizar usuario
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, string $user): RedirectResponse
    {
        $organizationId = $this->context->organizationId();

        $userData = DB::table('users as u')
            ->join(
                'organization_users as ou',
                'ou.user_id',
                '=',
                'u.id'
            )
            ->where('u.id', $user)
            ->where('ou.organization_id', $organizationId)
            ->select([
                'u.id',
                'u.name',
                'u.email',
                'u.is_active',
                'ou.id as organization_user_id',
            ])
            ->first();

        abort_unless($userData, 404);

        $currentRole = DB::table('organization_user_roles as our')
            ->join('roles as r', 'r.id', '=', 'our.role_id')
            ->where('our.organization_user_id', $userData->organization_user_id)
            ->first([
                'r.id',
                'r.code',
                'r.name',
            ]);

        $currentBranch = DB::table('user_branches as ub')
            ->join('branches as b', 'b.id', '=', 'ub.branch_id')
            ->where('ub.organization_user_id', $userData->organization_user_id)
            ->first([
                'b.id',
                'b.code',
                'b.name',
            ]);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userData->id),
            ],

            'password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
            ],

            'role_id' => [
                'required',
                'uuid',
                Rule::exists('roles', 'id')
                    ->where('organization_id', $organizationId),
            ],

            'branch_id' => [
                'required',
                'uuid',
                Rule::exists('branches', 'id')
                    ->where('organization_id', $organizationId)
                    ->where('is_active', true),
            ],

            'is_active' => [
                'required',
                'boolean',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Evitar que el administrador se desactive a sí mismo
        |--------------------------------------------------------------------------
        */

        if (
            $userData->id === $request->user()->id
            && ! $validated['is_active']
        ) {
            return back()
                ->withErrors([
                    'is_active' => 'No puedes desactivar tu propia cuenta.',
                ])
                ->withInput();
        }

        $before = [
            'name' => $userData->name,
            'email' => $userData->email,
            'is_active' => (bool) $userData->is_active,
            'role_id' => $currentRole?->id,
            'role_code' => $currentRole?->code,
            'branch_id' => $currentBranch?->id,
            'branch_code' => $currentBranch?->code,
        ];

        DB::transaction(function () use (
            $validated,
            $userData,
            $before
        ): void {

            /*
    |--------------------------------------------------------------------------
    | Usuario
    |--------------------------------------------------------------------------
    */

            $userUpdate = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'is_active' => $validated['is_active'],
                'updated_at' => now(),
            ];

            if (
                isset($validated['password'])
                && $validated['password'] !== ''
            ) {
                $userUpdate['password'] = Hash::make(
                    $validated['password']
                );
            }

            DB::table('users')
                ->where('id', $userData->id)
                ->update($userUpdate);


            /*
    |--------------------------------------------------------------------------
    | Organización
    |--------------------------------------------------------------------------
    */

            DB::table('organization_users')
                ->where('id', $userData->organization_user_id)
                ->update([
                    'is_active' => $validated['is_active'],
                    'updated_at' => now(),
                ]);


            /*
    |--------------------------------------------------------------------------
    | Sucursal
    |--------------------------------------------------------------------------
    */

            DB::table('user_branches')
                ->where(
                    'organization_user_id',
                    $userData->organization_user_id
                )
                ->delete();

            DB::table('user_branches')->insert([
                'organization_user_id' => $userData->organization_user_id,
                'branch_id' => $validated['branch_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);


            /*
    |--------------------------------------------------------------------------
    | Rol
    |--------------------------------------------------------------------------
    */

            DB::table('organization_user_roles')
                ->where(
                    'organization_user_id',
                    $userData->organization_user_id
                )
                ->delete();

            DB::table('organization_user_roles')->insert([
                'organization_user_id' => $userData->organization_user_id,
                'role_id' => $validated['role_id'],
            ]);


            /*
    |--------------------------------------------------------------------------
    | Estado final
    |--------------------------------------------------------------------------
    */

            $roleAfter = DB::table('roles')
                ->where('id', $validated['role_id'])
                ->first([
                    'id',
                    'code',
                    'name',
                ]);

            $branchAfter = DB::table('branches')
                ->where('id', $validated['branch_id'])
                ->first([
                    'id',
                    'code',
                    'name',
                ]);


            /*
    |--------------------------------------------------------------------------
    | Auditoría
    |--------------------------------------------------------------------------
    */

            $this->audit->record(
                action: 'user.updated',
                auditableType: 'User',
                auditableId: $userData->id,
                before: $before,
                after: [
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'is_active' => (bool) $validated['is_active'],
                    'role_id' => $roleAfter?->id,
                    'role_code' => $roleAfter?->code,
                    'branch_id' => $branchAfter?->id,
                    'branch_code' => $branchAfter?->code,
                ],
                branchId: $validated['branch_id'],
            );
        });

        return redirect()
            ->route('users.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    /*
    |--------------------------------------------------------------------------
    | Desactivar usuario
    |--------------------------------------------------------------------------
    */

    public function destroy(string $user): RedirectResponse
    {
        $organizationId = $this->context->organizationId();

        $userData = DB::table('users as u')
            ->join(
                'organization_users as ou',
                'ou.user_id',
                '=',
                'u.id'
            )
            ->where('u.id', $user)
            ->where('ou.organization_id', $organizationId)
            ->select([
                'u.id',
                'ou.id as organization_user_id',
            ])
            ->first();

        abort_unless($userData, 404);


        /*
    |--------------------------------------------------------------------------
    | Evitar auto-desactivación
    |--------------------------------------------------------------------------
    */

        if ($userData->id === request()->user()->id) {
            return back()->withErrors([
                'user' => 'No puedes desactivar tu propia cuenta.',
            ]);
        }


        /*
    |--------------------------------------------------------------------------
    | Ejecutar desactivación + auditoría en una sola transacción
    |--------------------------------------------------------------------------
    */

        DB::transaction(function () use ($userData): void {

            $targetUser = DB::table('users')
                ->where('id', $userData->id)
                ->first([
                    'id',
                    'name',
                    'email',
                    'is_active',
                ]);

            abort_unless($targetUser, 404);


            /*
        |--------------------------------------------------------------------------
        | Desactivar usuario
        |--------------------------------------------------------------------------
        */

            DB::table('users')
                ->where('id', $userData->id)
                ->update([
                    'is_active' => false,
                    'updated_at' => now(),
                ]);


            /*
        |--------------------------------------------------------------------------
        | Desactivar relación con la organización
        |--------------------------------------------------------------------------
        */

            DB::table('organization_users')
                ->where(
                    'id',
                    $userData->organization_user_id
                )
                ->update([
                    'is_active' => false,
                    'updated_at' => now(),
                ]);


            /*
        |--------------------------------------------------------------------------
        | Auditoría
        |--------------------------------------------------------------------------
        */

            $this->audit->record(
                action: 'user.deactivated',
                auditableType: 'User',
                auditableId: $userData->id,
                before: [
                    'name' => $targetUser->name,
                    'email' => $targetUser->email,
                    'is_active' => (bool) $targetUser->is_active,
                ],
                after: [
                    'name' => $targetUser->name,
                    'email' => $targetUser->email,
                    'is_active' => false,
                ],
            );
        });


        return redirect()
            ->route('users.index')
            ->with('success', 'Usuario desactivado correctamente.');
    }

    public function reactivate(string $user): RedirectResponse
    {
        $organizationId = $this->context->organizationId();

        DB::transaction(function () use ($user, $organizationId): void {
            $target = DB::table('users as u')
                ->join('organization_users as ou', 'ou.user_id', '=', 'u.id')
                ->where('u.id', $user)
                ->where('ou.organization_id', $organizationId)
                ->select([
                    'u.id',
                    'u.name',
                    'u.email',
                    'u.is_active',
                    'ou.id as organization_user_id',
                    'ou.is_active as organization_user_is_active',
                ])
                ->first();

            abort_unless($target, 404, 'Usuario no encontrado.');

            if ($target->is_active && $target->organization_user_is_active) {
                return;
            }

            $before = [
                'user' => [
                    'id' => $target->id,
                    'name' => $target->name,
                    'email' => $target->email,
                    'is_active' => (bool) $target->is_active,
                ],
                'organization_user' => [
                    'id' => $target->organization_user_id,
                    'is_active' => (bool) $target->organization_user_is_active,
                ],
            ];

            DB::table('users')
                ->where('id', $target->id)
                ->update([
                    'is_active' => true,
                    'updated_at' => now(),
                ]);

            DB::table('organization_users')
                ->where('id', $target->organization_user_id)
                ->update([
                    'is_active' => true,
                    'updated_at' => now(),
                ]);

            $after = [
                'user' => [
                    'id' => $target->id,
                    'name' => $target->name,
                    'email' => $target->email,
                    'is_active' => true,
                ],
                'organization_user' => [
                    'id' => $target->organization_user_id,
                    'is_active' => true,
                ],
            ];

            $this->audit->record(
                action: 'user.reactivated',
                auditableType: 'User',
                auditableId: $target->id,
                before: $before,
                after: $after,
            );
        });

        return redirect()
            ->route('users.index')
            ->with('success', 'Usuario reactivado correctamente.');
    }
}
