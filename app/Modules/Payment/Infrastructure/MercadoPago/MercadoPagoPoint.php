<?php

declare(strict_types=1);

namespace App\Modules\Payment\Infrastructure\MercadoPago;

use App\Modules\Operation\Infrastructure\Persistence\Models\Sale;
use Illuminate\Support\Str;

final class MercadoPagoPoint
{
    public function __construct(
        private readonly MercadoPagoClient $client,
    ) {}

    public function createOrder(
        Sale $sale,
        string $terminalId,
        string $idempotencyKey
    ): array {
        $externalReference = 'SALE_' . $sale->sale_number;

        $payload = [
            'type' => 'point',

            'external_reference' => $externalReference,

            'expiration_time' => 'PT15M',

            'transactions' => [
                'payments' => [
                    [
                        'amount' => number_format(
                            (float) $sale->total,
                            2,
                            '.',
                            ''
                        ),
                    ],
                ],
            ],

            'config' => [
                'point' => [
                    'terminal_id' => $terminalId,
                    'print_on_terminal' => 'no_ticket',
                ],
            ],

            'description' => 'Venta ' . $sale->sale_number,
        ];

        return $this->client
            ->createOrder($payload, $idempotencyKey)
            ->json();
    }
}
