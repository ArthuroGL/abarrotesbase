<?php

declare(strict_types=1);

namespace App\Modules\Payment\Infrastructure\MercadoPago;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final class MercadoPagoClient
{
    public function createOrder(
        array $payload,
        string $idempotencyKey
    ): Response {
        $response = Http::baseUrl(
            config('services.mercadopago.base_url')
        )
            ->withToken(
                config('services.mercadopago.access_token')
            )
            ->acceptJson()
            ->asJson()
            ->withHeaders([
                'X-Idempotency-Key' => $idempotencyKey,
            ])
            ->post('/v1/orders', $payload);

        if ($response->failed()) {
            throw new RuntimeException(
                'Mercado Pago rechazó la creación de la orden: '
                . $response->body()
            );
        }

        return $response;
    }

    public function getOrder(string $orderId): Response
    {
        return Http::baseUrl(
            config('services.mercadopago.base_url')
        )
            ->withToken(
                config('services.mercadopago.access_token')
            )
            ->acceptJson()
            ->get("/v1/orders/{$orderId}");
    }

    public function cancelOrder(
        string $orderId,
        string $idempotencyKey
    ): Response {
        return Http::baseUrl(
            config('services.mercadopago.base_url')
        )
            ->withToken(
                config('services.mercadopago.access_token')
            )
            ->acceptJson()
            ->withHeaders([
                'X-Idempotency-Key' => $idempotencyKey,
            ])
            ->post("/v1/orders/{$orderId}/cancel");
    }

    public function refundOrder(
        string $orderId,
        string $idempotencyKey
    ): Response {
        return Http::baseUrl(
            config('services.mercadopago.base_url')
        )
            ->withToken(
                config('services.mercadopago.access_token')
            )
            ->acceptJson()
            ->withHeaders([
                'X-Idempotency-Key' => $idempotencyKey,
            ])
            ->post("/v1/orders/{$orderId}/refund");
    }
}
