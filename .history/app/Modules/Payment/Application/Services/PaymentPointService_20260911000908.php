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
        private readonly MercadoPagoService $mercadoPago,
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

        $organizationId = $this->context->organizationId();
        $branchId = $this->context->branchId();

        $idempotencyKey = (string) Str::uuid();

        return DB::transaction(function () use (
            $organizationId,
            $branchId,
            $amount,
            $externalReference,
            $idempotencyKey,
        ) {
            return PaymentTransaction::query()->create([
                'organization_id' => $organizationId,
                'branch_id' => $branchId,
                'sale_id' => null,
                'expense_id' => null,
                'payment_method_id' => null,
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
}