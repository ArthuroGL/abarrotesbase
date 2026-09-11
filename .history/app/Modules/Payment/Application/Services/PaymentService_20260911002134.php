<?php

declare(strict_types=1);

namespace App\Modules\Payment\Application\Services;

use App\Modules\Identity\Application\Services\CurrentContext;
use App\Modules\Operation\Infrastructure\Persistence\Models\PaymentMethod;
use Illuminate\Validation\ValidationException;

final class PaymentService
{
    public function __construct(
        private readonly CurrentContext $context,
    ) {}

    public function getPaymentMethod(string $paymentMethodId): PaymentMethod
    {
        $method = PaymentMethod::query()
            ->where('organization_id', $this->context->organizationId())
            ->whereKey($paymentMethodId)
            ->where('is_active', true)
            ->first();

        if (!$method) {
            throw ValidationException::withMessages([
                'payment_method_id' => 'El método de pago seleccionado no es válido.',
            ]);
        }

        return $method;
    }

    public function getPaymentMethodByCode(string $code): PaymentMethod
    {
        $method = PaymentMethod::query()
            ->where('organization_id', $this->context->organizationId())
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

    public function requiresReference(PaymentMethod $method): bool
    {
        return $method->requires_reference;
    }

    public function affectsCash(PaymentMethod $method): bool
    {
        return $method->affects_cash;
    }

    public function isInternal(PaymentMethod $method): bool
    {
        return $method->provider === 'internal';
    }

    public function isMercadoPago(PaymentMethod $method): bool
    {
        return $method->provider === 'mercadopago';
    }

    public function isProvider(PaymentMethod $method, string $providerCode): bool
    {
        return $method->provider === 'mercadopago'
            && $method->provider_code === $providerCode;
    }
}