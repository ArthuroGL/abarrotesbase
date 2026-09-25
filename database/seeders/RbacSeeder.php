<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

final class RbacSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {

            /*
            |--------------------------------------------------------------------------
            | 1. Obtener organización
            |--------------------------------------------------------------------------
            */

            $organization = DB::table('organizations')
                ->where('display_name', 'ABARROTESBASE')
                ->first();

            if (!$organization) {
                throw new RuntimeException(
                    'No existe la organización ABARROTESBASE. Ejecuta primero DatabaseSeeder.'
                );
            }

            $organizationId = (string) $organization->id;

            /*
            |--------------------------------------------------------------------------
            | 2. Obtener usuario administrador
            |--------------------------------------------------------------------------
            */

            $adminUser = DB::table('users')
                ->where('email', 'admin@abarrotesbase.com')
                ->first();

            if (!$adminUser) {
                throw new RuntimeException(
                    'No existe el usuario admin@abarrotesbase.com.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 3. Obtener relación usuario-organización
            |--------------------------------------------------------------------------
            */

            $organizationUser = DB::table('organization_users')
                ->where('organization_id', $organizationId)
                ->where('user_id', $adminUser->id)
                ->where('is_active', true)
                ->first();

            if (!$organizationUser) {
                throw new RuntimeException(
                    'El usuario administrador no pertenece a la organización ABARROTESBASE.'
                );
            }

            $organizationUserId = (string) $organizationUser->id;

            /*
            |--------------------------------------------------------------------------
            | 4. Permisos
            |--------------------------------------------------------------------------
            */

            $permissions = [

                // Ventas
                [
                    'code' => 'sales.view',
                    'module' => 'sales',
                    'name' => 'Consultar ventas',
                    'description' => 'Permite consultar el historial de ventas.',
                ],
                [
                    'code' => 'sales.create',
                    'module' => 'sales',
                    'name' => 'Crear ventas',
                    'description' => 'Permite registrar nuevas ventas.',
                ],
                [
                    'code' => 'sales.cancel',
                    'module' => 'sales',
                    'name' => 'Cancelar ventas',
                    'description' => 'Permite cancelar ventas.',
                ],
                [
                    'code' => 'sales.return',
                    'module' => 'sales',
                    'name' => 'Realizar devoluciones',
                    'description' => 'Permite realizar devoluciones de ventas.',
                ],

                // Caja
                [
                    'code' => 'cash.view',
                    'module' => 'cash',
                    'name' => 'Consultar caja',
                    'description' => 'Permite consultar información de caja.',
                ],
                [
                    'code' => 'cash.open',
                    'module' => 'cash',
                    'name' => 'Abrir caja',
                    'description' => 'Permite realizar la apertura de caja.',
                ],
                [
                    'code' => 'cash.movement',
                    'module' => 'cash',
                    'name' => 'Registrar movimientos de caja',
                    'description' => 'Permite registrar entradas y salidas de efectivo.',
                ],
                [
                    'code' => 'cash.close',
                    'module' => 'cash',
                    'name' => 'Cerrar caja',
                    'description' => 'Permite realizar el cierre de caja.',
                ],
                [
                    'code' => 'cash.history',
                    'module' => 'cash',
                    'name' => 'Consultar historial de caja',
                    'description' => 'Permite consultar sesiones y movimientos de caja.',
                ],

                // Productos
                [
                    'code' => 'products.view',
                    'module' => 'products',
                    'name' => 'Consultar productos',
                    'description' => 'Permite consultar productos.',
                ],
                [
                    'code' => 'products.create',
                    'module' => 'products',
                    'name' => 'Crear productos',
                    'description' => 'Permite registrar productos.',
                ],
                [
                    'code' => 'products.update',
                    'module' => 'products',
                    'name' => 'Editar productos',
                    'description' => 'Permite modificar productos.',
                ],
                [
                    'code' => 'products.delete',
                    'module' => 'products',
                    'name' => 'Eliminar productos',
                    'description' => 'Permite eliminar o desactivar productos.',
                ],

                // Existencias
                [
                    'code' => 'stock.view',
                    'module' => 'stock',
                    'name' => 'Consultar existencias',
                    'description' => 'Permite consultar existencias de inventario.',
                ],
                [
                    'code' => 'stock.adjust',
                    'module' => 'stock',
                    'name' => 'Ajustar existencias',
                    'description' => 'Permite realizar ajustes de inventario.',
                ],

                // Proveedores
                [
                    'code' => 'suppliers.view',
                    'module' => 'suppliers',
                    'name' => 'Consultar proveedores',
                    'description' => 'Permite consultar proveedores.',
                ],
                [
                    'code' => 'suppliers.create',
                    'module' => 'suppliers',
                    'name' => 'Crear proveedores',
                    'description' => 'Permite registrar proveedores.',
                ],
                [
                    'code' => 'suppliers.update',
                    'module' => 'suppliers',
                    'name' => 'Editar proveedores',
                    'description' => 'Permite modificar proveedores.',
                ],
                [
                    'code' => 'suppliers.delete',
                    'module' => 'suppliers',
                    'name' => 'Eliminar proveedores',
                    'description' => 'Permite eliminar o desactivar proveedores.',
                ],

                // Compras
                [
                    'code' => 'purchases.view',
                    'module' => 'purchases',
                    'name' => 'Consultar compras',
                    'description' => 'Permite consultar compras.',
                ],
                [
                    'code' => 'purchases.create',
                    'module' => 'purchases',
                    'name' => 'Crear compras',
                    'description' => 'Permite registrar compras.',
                ],
                [
                    'code' => 'purchases.update',
                    'module' => 'purchases',
                    'name' => 'Editar compras',
                    'description' => 'Permite modificar compras.',
                ],
                [
                    'code' => 'purchases.receive',
                    'module' => 'purchases',
                    'name' => 'Recibir compras',
                    'description' => 'Permite recibir mercancía de compras.',
                ],
                [
                    'code' => 'purchases.cancel',
                    'module' => 'purchases',
                    'name' => 'Cancelar compras',
                    'description' => 'Permite cancelar compras.',
                ],

                // Gastos
                [
                    'code' => 'expenses.view',
                    'module' => 'expenses',
                    'name' => 'Consultar gastos',
                    'description' => 'Permite consultar gastos.',
                ],
                [
                    'code' => 'expenses.create',
                    'module' => 'expenses',
                    'name' => 'Crear gastos',
                    'description' => 'Permite registrar gastos.',
                ],
                [
                    'code' => 'expenses.update',
                    'module' => 'expenses',
                    'name' => 'Editar gastos',
                    'description' => 'Permite modificar gastos.',
                ],
                [
                    'code' => 'expenses.approve',
                    'module' => 'expenses',
                    'name' => 'Aprobar gastos',
                    'description' => 'Permite aprobar gastos.',
                ],
                [
                    'code' => 'expenses.reject',
                    'module' => 'expenses',
                    'name' => 'Rechazar gastos',
                    'description' => 'Permite rechazar gastos.',
                ],
                [
                    'code' => 'expenses.pay',
                    'module' => 'expenses',
                    'name' => 'Pagar gastos',
                    'description' => 'Permite registrar el pago de gastos.',
                ],
                [
                    'code' => 'expenses.cancel',
                    'module' => 'expenses',
                    'name' => 'Cancelar gastos',
                    'description' => 'Permite cancelar gastos.',
                ],

                // Usuarios
                [
                    'code' => 'users.view',
                    'module' => 'users',
                    'name' => 'Consultar usuarios',
                    'description' => 'Permite consultar usuarios.',
                ],
                [
                    'code' => 'users.create',
                    'module' => 'users',
                    'name' => 'Crear usuarios',
                    'description' => 'Permite registrar usuarios.',
                ],
                [
                    'code' => 'users.update',
                    'module' => 'users',
                    'name' => 'Editar usuarios',
                    'description' => 'Permite modificar usuarios.',
                ],
                [
                    'code' => 'users.delete',
                    'module' => 'users',
                    'name' => 'Eliminar usuarios',
                    'description' => 'Permite desactivar o eliminar usuarios.',
                ],

                // Roles
                [
                    'code' => 'roles.view',
                    'module' => 'roles',
                    'name' => 'Consultar roles',
                    'description' => 'Permite consultar roles y permisos.',
                ],
                [
                    'code' => 'roles.update',
                    'module' => 'roles',
                    'name' => 'Gestionar roles',
                    'description' => 'Permite modificar los permisos de los roles.',
                ],
            ];

            /*
            |--------------------------------------------------------------------------
            | 5. Crear / actualizar permisos
            |--------------------------------------------------------------------------
            */

            $permissionIds = [];

            foreach ($permissions as $permission) {

                $existingPermission = DB::table('permissions')
                    ->where('code', $permission['code'])
                    ->first();

                if ($existingPermission) {

                    DB::table('permissions')
                        ->where('id', $existingPermission->id)
                        ->update([
                            'module' => $permission['module'],
                            'name' => $permission['name'],
                            'description' => $permission['description'],
                            'updated_at' => now(),
                        ]);

                    $permissionIds[$permission['code']] = $existingPermission->id;
                } else {

                    $permissionId = (string) Str::uuid();

                    DB::table('permissions')->insert([
                        'id' => $permissionId,
                        'code' => $permission['code'],
                        'module' => $permission['module'],
                        'name' => $permission['name'],
                        'description' => $permission['description'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $permissionIds[$permission['code']] = $permissionId;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | 6. Definición de roles
            |--------------------------------------------------------------------------
            */

            $roles = [

                'admin' => [
                    'name' => 'Administrador',
                    'description' => 'Acceso completo al sistema.',
                    'permissions' => array_keys($permissionIds),
                ],

                'cashier' => [
                    'name' => 'Empleado Caja',
                    'description' => 'Operación de ventas y caja.',
                    'permissions' => [
                        'sales.view',
                        'sales.create',
                        'sales.cancel',
                        'sales.return',

                        'cash.view',
                        'cash.open',
                        'cash.movement',
                        'cash.close',
                        'cash.history',
                    ],
                ],

                'warehouse' => [
                    'name' => 'Empleado Almacén',
                    'description' => 'Gestión de productos y existencias.',
                    'permissions' => [
                        'products.view',
                        'products.create',
                        'products.update',

                        'stock.view',
                        'stock.adjust',
                    ],
                ],

                'purchases' => [
                    'name' => 'Empleado Proveedores',
                    'description' => 'Gestión de proveedores y compras.',
                    'permissions' => [
                        'suppliers.view',
                        'suppliers.create',
                        'suppliers.update',

                        'purchases.view',
                        'purchases.create',
                        'purchases.update',
                        'purchases.receive',
                        'purchases.cancel',
                    ],
                ],
            ];

            /*
            |--------------------------------------------------------------------------
            | 7. Crear / actualizar roles y permisos
            |--------------------------------------------------------------------------
            */

            foreach ($roles as $code => $roleData) {

                $role = DB::table('roles')
                    ->where('organization_id', $organizationId)
                    ->where('code', $code)
                    ->first();

                if ($role) {

                    $roleId = $role->id;

                    DB::table('roles')
                        ->where('id', $roleId)
                        ->update([
                            'name' => $roleData['name'],
                            'description' => $roleData['description'],
                            'is_system' => true,
                            'updated_at' => now(),
                        ]);
                } else {

                    $roleId = (string) Str::uuid();

                    DB::table('roles')->insert([
                        'id' => $roleId,
                        'organization_id' => $organizationId,
                        'code' => $code,
                        'name' => $roleData['name'],
                        'description' => $roleData['description'],
                        'is_system' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Reemplazar permisos del rol
                |--------------------------------------------------------------------------
                */

                DB::table('role_permissions')
                    ->where('role_id', $roleId)
                    ->delete();

                $rows = [];

                foreach ($roleData['permissions'] as $permissionCode) {

                    if (!isset($permissionIds[$permissionCode])) {
                        throw new RuntimeException(
                            "El permiso {$permissionCode} no existe."
                        );
                    }

                    $rows[] = [
                        'role_id' => $roleId,
                        'permission_id' => $permissionIds[$permissionCode],
                    ];
                }

                if ($rows) {
                    DB::table('role_permissions')->insert($rows);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | 8. Asignar Administrador al rol admin
            |--------------------------------------------------------------------------
            */

            $adminRole = DB::table('roles')
                ->where('organization_id', $organizationId)
                ->where('code', 'admin')
                ->first();

            if (!$adminRole) {
                throw new RuntimeException(
                    'No fue posible obtener el rol admin.'
                );
            }

            $adminRoleExists = DB::table('organization_user_roles')
                ->where('organization_user_id', $organizationUserId)
                ->where('role_id', $adminRole->id)
                ->exists();

            if (!$adminRoleExists) {
                DB::table('organization_user_roles')->insert([
                    'organization_user_id' => $organizationUserId,
                    'role_id' => $adminRole->id,
                ]);
            }
        });
    }
}
