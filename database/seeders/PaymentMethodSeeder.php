<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Operation\Infrastructure\Persistence\Models\PaymentMethod;
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
                'provider' => 'internal',
                'provider_code' => 'cash',
                'requires_reference' => false,
                'affects_cash' => true,
            ],
            [
                'code' => 'CARD',
                'name' => 'Tarjeta',
                'type' => 'card',
                'provider' => 'internal',
                'provider_code' => 'card',
                'requires_reference' => false,
                'affects_cash' => false,
            ],
            [
                'code' => 'TRANSFER',
                'name' => 'Transferencia',
                'type' => 'transfer',
                'provider' => 'internal',
                'provider_code' => 'transfer',
                'requires_reference' => true,
                'affects_cash' => false,
            ],
            [
                'code' => 'MP_POINT',
                'name' => 'Mercado Pago Point',
                'type' => 'card',
                'provider' => 'mercadopago',
                'provider_code' => 'point',
                'requires_reference' => false,
                'affects_cash' => false,
            ],
        ];

        foreach ($methods as $method) {
            PaymentMethod::updateOrCreate(
                [
                    'organization_id' => $organizationId,
                    'code' => $method['code'],
                ],
                [
                    'name' => $method['name'],
                    'type' => $method['type'],
                    'provider' => $method['provider'],
                    'provider_code' => $method['provider_code'],
                    'requires_reference' => $method['requires_reference'],
                    'affects_cash' => $method['affects_cash'],
                    'is_active' => true,
                ]
            );
        }
    }
}
