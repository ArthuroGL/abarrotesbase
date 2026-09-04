<?php

declare(strict_types=1);

namespace App\Modules\Operation\Infrastructure\Persistence\Models;

use App\Models\User;
use App\Modules\Identity\Infrastructure\Persistence\Models\Branch;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CashMovement extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'organization_id',
        'branch_id',
        'cash_session_id',
        'payment_method_id',
        'movement_type',
        'amount',
        'source_type',
        'source_id',
        'reason_code',
        'notes',
        'created_by',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'occurred_at' => 'datetime',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(CashSession::class, 'cash_session_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
