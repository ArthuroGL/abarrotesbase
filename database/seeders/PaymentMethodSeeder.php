<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $organizationId = DB::table('organizations')->value('id');

        if (!$organizationId) {
            return;
        }

        $methods = [
            [
                'code' => 'CASH',
                'name' => 'Efectivo',
                'type' => 'cash',
                'requires_reference' => false,
                'affects_cash' => true,
            ],
            [
                'code' => 'CARD',
                'name' => 'Tarjeta',
                'type' => 'card',
                'requires_reference' => false,
                'affects_cash' => false,
            ],
            [
                'code' => 'TRANSFER',
                'name' => 'Transferencia',
                'type' => 'transfer',
                'requires_reference' => true,
                'affects_cash' => false,
            ],
        ];

        foreach ($methods as $method) {

            DB::table('payment_methods')->updateOrInsert(
                [
                    'organization_id' => $organizationId,
                    'code' => $method['code'],
                ],
                [
                    'id' => DB::table('payment_methods')
                        ->where('organization_id', $organizationId)
                        ->where('code', $method['code'])
                        ->value('id') ?? (string) Str::uuid(),

                    'name' => $method['name'],
                    'type' => $method['type'],
                    'requires_reference' => $method['requires_reference'],
                    'affects_cash' => $method['affects_cash'],
                    'is_active' => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
