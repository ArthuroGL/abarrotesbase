<?php

declare(strict_types=1);

namespace App\Modules\Payment\Application\Services;

use App\Modules\Identity\Application\Services\CurrentContext;
use App\Modules\Operation\Infrastructure\Persistence\Models\PaymentMethod;
use App\Modules\Payment\Infrastructure\Persistence\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

final class PaymentPointService
{
    public function __construct(
        private readonly CurrentContext $context,
        private readonly PaymentService $paymentService,
    ) {}

    public function createPendingPayment(
        string $saleId,
        float $amount,
        string $externalReference,
    ): PaymentTransaction {
        if ($amount <= 0) {
            throw new RuntimeException(
                'El importe debe ser mayor a cero.'
            );
        }

        $paymentMethod = $this->paymentService->getPaymentMethodByCode(
            'MP_POINT'
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
            $saleId,
            $amount,
            $externalReference,
            $idempotencyKey,
        ) {
            return PaymentTransaction::query()->create([
                'organization_id' => $organizationId,
                'branch_id' => $branchId,
                'sale_id' => $saleId,
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


    public function getPaymentMethodByCode(string $code): PaymentMethod
    {
        $method = PaymentMethod::query()
            ->where(
                'organization_id',
                $this->context->organizationId()
            )
            ->where('code', $code)
            ->where('is_active', true)
            ->first();

        if (!$method) {
            throw ValidationException::withMessages([
                'payment_method_id' => "El método de pago {$code} no está disponible.",
            ]);
        }

        return $method;
    }
}
