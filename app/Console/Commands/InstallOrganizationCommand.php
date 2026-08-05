<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use App\Modules\Identity\Infrastructure\Persistence\Models\Branch;
use App\Modules\Identity\Infrastructure\Persistence\Models\Organization;
use App\Modules\Identity\Infrastructure\Persistence\Models\Permission;
use App\Modules\Identity\Infrastructure\Persistence\Models\Register;
use App\Modules\Identity\Infrastructure\Persistence\Models\Role;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class InstallOrganizationCommand extends Command
{
    protected $signature = 'abarrotesbase:install
        {--name= : Nombre legal de la organización}
        {--display-name= : Nombre comercial}
        {--branch=Principal : Nombre de la sucursal inicial}
        {--register=CAJA-1 : Código de la caja inicial}
        {--admin-name= : Nombre de la persona administradora}
        {--admin-email= : Correo de la persona administradora}';

    protected $description = 'Crea la organización, sucursal, caja y administrador iniciales de ABARROTESBASE.';

    public function handle(): int
    {
        if (Organization::query()->exists()) {
            $this->error('Ya existe una organización. Este instalador solo puede ejecutarse una vez.');

            return self::FAILURE;
        }

        $legalName = $this->option('name') ?: $this->ask('Nombre legal de la organización');
        $displayName = $this->option('display-name') ?: $this->ask('Nombre comercial', $legalName);
        $branchName = $this->option('branch') ?: 'Principal';
        $registerCode = $this->option('register') ?: 'CAJA-1';
        $adminName = $this->option('admin-name') ?: $this->ask('Nombre de la persona administradora');
        $adminEmail = $this->option('admin-email') ?: $this->ask('Correo de administración');
        $password = $this->secret('Contraseña de administración (mínimo 12 caracteres)');

        if (! $legalName || ! $displayName || ! $adminName || ! filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            $this->error('Nombre legal, nombre comercial, administrador y correo válido son obligatorios.');

            return self::INVALID;
        }

        if (! is_string($password) || mb_strlen($password) < 12) {
            $this->error('La contraseña debe contener al menos 12 caracteres.');

            return self::INVALID;
        }

        DB::transaction(function () use ($legalName, $displayName, $branchName, $registerCode, $adminName, $adminEmail, $password): void {
            $organization = Organization::query()->create([
                'legal_name' => $legalName,
                'display_name' => $displayName,
                'currency_code' => config('abarrotesbase.currency'),
                'timezone' => config('abarrotesbase.timezone'),
            ]);

            $branch = Branch::query()->create([
                'organization_id' => $organization->id,
                'code' => 'PRINCIPAL',
                'name' => $branchName,
            ]);

            Register::query()->create([
                'organization_id' => $organization->id,
                'branch_id' => $branch->id,
                'code' => $registerCode,
                'name' => 'Caja principal',
            ]);

            $permissions = collect(self::permissions())->mapWithKeys(function (array $permission): array {
                $model = Permission::query()->firstOrCreate(['code' => $permission['code']], $permission);

                return [$permission['code'] => $model->id];
            });

            $role = Role::query()->create([
                'organization_id' => $organization->id,
                'code' => 'administrator',
                'name' => 'Administrador',
                'description' => 'Acceso administrativo inicial de la organización.',
                'is_system' => true,
            ]);
            $role->permissions()->sync($permissions->values());

            $user = User::query()->create([
                'name' => $adminName,
                'email' => mb_strtolower($adminEmail),
                'password' => Hash::make($password),
                'is_active' => true,
            ]);

            $organizationUserId = (string) str()->uuid();
            DB::table('organization_users')->insert([
                'id' => $organizationUserId,
                'organization_id' => $organization->id,
                'user_id' => $user->id,
                'is_active' => true,
                'joined_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('user_branches')->insert([
                'organization_user_id' => $organizationUserId,
                'branch_id' => $branch->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('organization_user_roles')->insert([
                'organization_user_id' => $organizationUserId,
                'role_id' => $role->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        $this->info('Organización inicial creada correctamente.');

        return self::SUCCESS;
    }

    /** @return list<array{code: string, module: string, name: string, description: string}> */
    private static function permissions(): array
    {
        return [
            ['code' => 'organization.manage', 'module' => 'identity', 'name' => 'Administrar organización', 'description' => 'Configura organización, sucursales y cajas.'],
            ['code' => 'user.manage', 'module' => 'identity', 'name' => 'Administrar usuarios', 'description' => 'Crea y desactiva usuarios.'],
            ['code' => 'role.manage', 'module' => 'identity', 'name' => 'Administrar roles', 'description' => 'Configura roles y permisos.'],
            ['code' => 'product.manage', 'module' => 'catalog', 'name' => 'Administrar productos', 'description' => 'Gestiona catálogo y precios.'],
            ['code' => 'inventory.manage', 'module' => 'inventory', 'name' => 'Administrar inventario', 'description' => 'Consulta y ajusta inventario.'],
            ['code' => 'purchase.manage', 'module' => 'purchasing', 'name' => 'Administrar compras', 'description' => 'Gestiona proveedores, compras y recepciones.'],
            ['code' => 'sale.manage', 'module' => 'sales', 'name' => 'Administrar ventas', 'description' => 'Opera y supervisa ventas.'],
            ['code' => 'cash.manage', 'module' => 'cash', 'name' => 'Administrar caja', 'description' => 'Opera y supervisa sesiones de caja.'],
            ['code' => 'report.manage', 'module' => 'reporting', 'name' => 'Consultar reportes', 'description' => 'Consulta reportes e indicadores.'],
            ['code' => 'audit.view', 'module' => 'audit', 'name' => 'Consultar auditoría', 'description' => 'Consulta bitácora operativa.'],
        ];
    }
}
