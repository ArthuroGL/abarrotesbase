<?php

declare(strict_types=1);

namespace App\Modules\Operation\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class SalePayment extends Model
{
    use HasUuids;

    protected $fillable = [
        'sale_id',
        'line_number',
        'payment_method_id',
        'amount_received',
        'amount_applied',
        'reference',
        'metadata',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'line_number' => 'integer',
            'amount_received' => 'decimal:6',
            'amount_applied' => 'decimal:6',
            'metadata' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }
}
