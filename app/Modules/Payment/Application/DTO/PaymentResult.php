<?php

declare(strict_types=1);

namespace App\Modules\Payment\Application\DTO;

final readonly class PaymentResult
{
    public function __construct(
        public bool $success,
        public string $status,
        public ?string $message = null,
        public ?string $transactionId = null,
        public ?string $providerOrderId = null,
        public ?string $providerPaymentId = null,
        public array $data = [],
    ) {}
}
