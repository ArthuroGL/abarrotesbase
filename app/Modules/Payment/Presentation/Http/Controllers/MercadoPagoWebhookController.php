<?php

declare(strict_types=1);

namespace App\Modules\Payment\Presentation\Http\Controllers;

use App\Modules\Payment\Application\Services\PointPaymentFinalizer;
use App\Modules\Payment\Application\Services\MercadoPagoService;
use App\Modules\Payment\Infrastructure\Persistence\Models\PaymentTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

final class MercadoPagoWebhookController
{
    public function __construct(
        private readonly MercadoPagoService $mercadoPago,
        private readonly PointPaymentFinalizer $finalizer,
    ) {}

    public function handle(Request $request): JsonResponse
    {
        $action = (string) $request->input('action');
        $type = (string) $request->input('type');

        /*
         * Mercado Pago actualmente envía:
         *
         * type = order
         * action = order.processed
         *
         * para una orden Point procesada.
         */
        if ($type !== 'order') {
            return response()->json([
                'received' => true,
            ]);
        }

        $orderId = (string) (
            $request->input('data.id')
            ?? $request->query('data.id')
            ?? ''
        );

        if ($orderId === '') {
            Log::warning(
                'Mercado Pago Webhook sin order ID.',
                [
                    'payload' => $request->all(),
                ]
            );

            return response()->json([
                'received' => true,
            ]);
        }

        /*
         * Primero confirmamos que la orden realmente existe
         * y obtenemos su estado directamente desde Mercado Pago.
         *
         * NO confiamos ciegamente en el payload del webhook.
         */
        try {
            $order = $this->mercadoPago->getOrder($orderId);
        } catch (\Throwable $e) {
            Log::error(
                'No fue posible consultar la orden de Mercado Pago.',
                [
                    'order_id' => $orderId,
                    'error' => $e->getMessage(),
                ]
            );

            /*
             * Devolvemos error para que Mercado Pago pueda reintentar.
             */
            return response()->json([
                'received' => false,
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        /*
         * Buscamos nuestra transacción local.
         */
        $transaction = PaymentTransaction::query()
            ->where('provider', 'mercadopago')
            ->where('provider_order_id', $orderId)
            ->first();

        /*
         * Puede llegar una notificación de una orden que todavía
         * no tengamos registrada localmente.
         */
        if (!$transaction) {
            Log::warning(
                'Mercado Pago Webhook: orden no encontrada localmente.',
                [
                    'order_id' => $orderId,
                    'action' => $action,
                ]
            );

            /*
             * La notificación sí fue recibida correctamente.
             * No tiene sentido pedir reintentos eternos por una
             * orden que nuestra aplicación no conoce.
             */
            return response()->json([
                'received' => true,
            ]);
        }

        /*
         * Solamente finalizamos cuando Mercado Pago confirma
         * el procesamiento exitoso.
         */
        if (($order['status'] ?? null) === 'processed') {
            try {
                $this->finalizer->finalize(
                    $transaction,
                    null,
                );
            } catch (\Throwable $e) {
                Log::error(
                    'Error finalizando pago Mercado Pago Point.',
                    [
                        'transaction_id' => $transaction->id,
                        'sale_id' => $transaction->sale_id,
                        'order_id' => $orderId,
                        'error' => $e->getMessage(),
                    ]
                );

                /*
                 * Esto sí debe generar retry.
                 */
                return response()->json([
                    'received' => false,
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        /*
         * Para canceled, failed, expired, etc. todavía podemos
         * sincronizar el estado posteriormente.
         */
        return response()->json([
            'received' => true,
        ]);
    }
}
