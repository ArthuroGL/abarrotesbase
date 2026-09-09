<?php

declare(strict_types=1);

namespace App\Modules\Payment\Infrastructure\Persistence\Models;

use App\Models\User;
use App\Modules\Identity\Infrastructure\Persistence\Models\Branch;
use App\Modules\Operation\Infrastructure\Persistence\Models\PaymentMethod;
use App\Modules\Operation\Infrastructure\Persistence\Models\Sale;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PaymentTransaction extends Model
{
    use HasUuids;

    protected $table = 'payment_transactions';

    protected $fillable = [
        'organization_id',
        'branch_id',
        'sale_id',
        'expense_id',
        'payment_method_id',
        'provider',
        'provider_order_id',
        'provider_payment_id',
        'external_reference',
        'amount',
        'currency_code',
        'status',
        'status_detail',
        'idempotency_key',
        'request_payload',
        'response_payload',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'request_payload' => 'array',
            'response_payload' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }
}
