<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class InventoryMovement extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'organization_id',
        'branch_id',
        'stock_item_id',
        'movement_type',
        'quantity_delta',
        'unit_cost',
        'total_cost',
        'source_type',
        'source_id',
        'reason_code',
        'notes',
        'created_by',
        'occurred_at',
    ];

    protected $casts = [
        'quantity_delta' => 'decimal:6',
        'unit_cost' => 'decimal:6',
        'total_cost' => 'decimal:6',
        'occurred_at' => 'datetime',
    ];

    public function stockItem(): BelongsTo
    {
        return $this->belongsTo(StockItem::class, 'stock_item_id');
    }
}
