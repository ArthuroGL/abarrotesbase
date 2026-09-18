<?php

declare(strict_types=1);

namespace App\Modules\Operation\Infrastructure\Persistence\Models;

use App\Models\User;
use App\Modules\Identity\Infrastructure\Persistence\Models\Branch;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class SaleReturn extends Model
{
    use HasUuids;

    protected $table = 'sale_returns';

    protected $fillable = [
        'organization_id',
        'branch_id',
        'original_sale_id',
        'cash_session_id',
        'return_number',
        'status',
        'total',
        'reason_code',
        'created_by',
        'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'confirmed_at' => 'datetime',
        ];
    }

    public function originalSale(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'original_sale_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function cashSession(): BelongsTo
    {
        return $this->belongsTo(CashSession::class, 'cash_session_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(SaleReturnLine::class, 'sale_return_id');
    }
}
