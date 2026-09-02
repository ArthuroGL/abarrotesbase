<?php

declare(strict_types=1);

namespace App\Modules\Operation\Infrastructure\Persistence\Models;

use App\Models\User;
use App\Modules\Identity\Infrastructure\Persistence\Models\Branch;
use App\Modules\Identity\Infrastructure\Persistence\Models\Register;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Sale extends Model
{
    use HasUuids;

    protected $fillable = [
        'organization_id',
        'branch_id',
        'register_id',
        'cash_session_id',
        'customer_id',
        'sale_number',
        'status',
        'currency_code',
        'subtotal',
        'discount_total',
        'tax_total',
        'total',
        'change_total',
        'confirmed_at',
        'cancelled_at',
        'created_by',
        'confirmed_by',
        'cancelled_by',
        'cancellation_reason',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:6',
            'discount_total' => 'decimal:6',
            'tax_total' => 'decimal:6',
            'total' => 'decimal:6',
            'change_total' => 'decimal:6',
            'confirmed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function register(): BelongsTo
    {
        return $this->belongsTo(Register::class);
    }

    public function cashSession(): BelongsTo
    {
        return $this->belongsTo(CashSession::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(SaleLine::class)->orderBy('line_number');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SalePayment::class)->orderBy('line_number');
    }
}
