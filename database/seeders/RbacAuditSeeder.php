<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

final class RbacAuditSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {

            $organization = DB::table('organizations')
                ->where('display_name', 'ABARROTESBASE')
                ->first();

            if (!$organization) {
                throw new RuntimeException(
                    'No existe la organización ABARROTESBASE.'
                );
            }

            $adminUser = DB::table('users')
                ->where('email', 'admin@abarrotesbase.com')
                ->first();

            if (!$adminUser) {
                throw new RuntimeException(
                    'No existe el usuario administrador.'
                );
            }

            $permission = DB::table('permissions')
                ->where('code', 'audit.view')
                ->first();

            if (!$permission) {

                $permissionId = (string) Str::uuid();

                DB::table('permissions')->insert([
                    'id' => $permissionId,
                    'code' => 'audit.view',
                    'module' => 'audit',
                    'name' => 'Consultar auditoría',
                    'description' => 'Permite consultar el historial de auditoría del sistema.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $permissionId = (string) $permissionId;

            } else {

                $permissionId = (string) $permission->id;
            }

            $adminRole = DB::table('roles')
                ->where('organization_id', $organization->id)
                ->where('code', 'admin')
                ->first();

            if (!$adminRole) {
                throw new RuntimeException(
                    'No existe el rol admin.'
                );
            }

            $exists = DB::table('role_permissions')
                ->where('role_id', $adminRole->id)
                ->where('permission_id', $permissionId)
                ->exists();

            if (!$exists) {

                DB::table('role_permissions')->insert([
                    'role_id' => $adminRole->id,
                    'permission_id' => $permissionId,
                ]);
            }
        });
    }
}
