<?php

declare(strict_types=1);

namespace App\Modules\Payment\Application\Services;

use App\Modules\Identity\Application\Services\CurrentContext;
use App\Modules\Payment\Infrastructure\Persistence\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

final class PaymentPointService
{
    public function __construct(
        private readonly CurrentContext $context,
        private readonly PaymentService $paymentService,
    ) {}

    public function createPendingPayment(
        float $amount,
        string $externalReference,
    ): PaymentTransaction {
        if ($amount <= 0) {
            throw new RuntimeException(
                'El importe debe ser mayor a cero.'
            );
        }

        $paymentMethod = $this->paymentService->getPaymentMethod(
            $this->getPointPaymentMethodId()
        );

        if (!$this->paymentService->isProvider($paymentMethod, 'point')) {
            throw new RuntimeException(
                'El método de pago seleccionado no corresponde a Mercado Pago Point.'
            );
        }

        $organizationId = $this->context->organizationId();
        $branchId = $this->context->branchId();

        $idempotencyKey = (string) Str::uuid();

        return DB::transaction(function () use (
            $organizationId,
            $branchId,
            $paymentMethod,
            $amount,
            $externalReference,
            $idempotencyKey,
        ) {
            return PaymentTransaction::query()->create([
                'organization_id' => $organizationId,
                'branch_id' => $branchId,
                'sale_id' => null,
                'expense_id' => null,
                'payment_method_id' => $paymentMethod->id,
                'provider' => 'mercadopago',
                'provider_order_id' => null,
                'provider_payment_id' => null,
                'external_reference' => $externalReference,
                'amount' => $amount,
                'currency_code' => config(
                    'services.mercadopago.currency',
                    'MXN'
                ),
                'status' => 'pending',
                'status_detail' => null,
                'idempotency_key' => $idempotencyKey,
                'request_payload' => null,
                'response_payload' => null,
                'paid_at' => null,
            ]);
        });
    }

    private function getPointPaymentMethodId(): string
    {
        $method = \App\Modules\Operation\Infrastructure\Persistence\Models\PaymentMethod::query()
            ->where(
                'organization_id',
                $this->context->organizationId()
            )
            ->where('code', 'MP_POINT')
            ->where('is_active', true)
            ->first();

        if (!$method) {
            throw new RuntimeException(
                'El método de pago MP_POINT no está configurado para la organización actual.'
            );
        }

        return (string) $method->id;
    }
}