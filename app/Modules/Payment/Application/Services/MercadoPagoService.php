<?php

declare(strict_types=1);

namespace App\Modules\Payment\Application\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

final class MercadoPagoService
{
    private string $baseUrl;
    private string $accessToken;
    private string $terminalId;
    private string $currency;

    public function __construct()
    {
        $this->baseUrl = rtrim(
            (string) config('services.mercadopago.base_url'),
            '/'
        );

        $this->accessToken = (string) config(
            'services.mercadopago.access_token'
        );

        $this->terminalId = (string) config(
            'services.mercadopago.point_terminal_id'
        );

        $this->currency = (string) config(
            'services.mercadopago.currency',
            'MXN'
        );

        if ($this->accessToken === '') {
            throw new RuntimeException(
                'MERCADOPAGO_ACCESS_TOKEN no está configurado.'
            );
        }

        if ($this->terminalId === '') {
            throw new RuntimeException(
                'MERCADOPAGO_POINT_TERMINAL_ID no está configurado.'
            );
        }
    }

    public function getTerminals(): array
    {
        $response = $this->request()->get('/terminals/v1/list', [
            'limit' => 50,
            'offset' => 0,
        ]);

        $response->throw();

        return $response->json();
    }

    public function createPointOrder(
        float $amount,
        string $externalReference,
        string $idempotencyKey,
    ): array {
        if ($amount <= 0) {
            throw new RuntimeException(
                'El importe de la orden debe ser mayor a cero.'
            );
        }

        if ($idempotencyKey === '') {
            throw new RuntimeException(
                'La idempotency key es obligatoria.'
            );
        }

        $payload = [
            'type' => 'point',
            'external_reference' => $externalReference,
            'transactions' => [
                'payments' => [
                    [
                        'amount' => number_format(
                            $amount,
                            2,
                            '.',
                            ''
                        ),
                    ],
                ],
            ],
            'config' => [
                'point' => [
                    'terminal_id' => $this->terminalId,
                ],
            ],
        ];

        $response = $this->request()
            ->withHeaders([
                'X-Idempotency-Key' => $idempotencyKey,
            ])
            ->post('/v1/orders', $payload);

        $response->throw();

        return $response->json();
    }

    public function getOrder(string $orderId): array
    {
        $response = $this->request()
            ->get("/v1/orders/{$orderId}");

        $response->throw();

        return $response->json();
    }

    private function request()
    {
        return Http::withToken($this->accessToken)
            ->acceptJson()
            ->asJson()
            ->baseUrl($this->baseUrl);
    }
}
