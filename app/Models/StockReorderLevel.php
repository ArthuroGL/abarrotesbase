<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class StockReorderLevel extends Model
{
    use HasUuids;

    protected $fillable = [
        'organization_id',
        'branch_id',
        'stock_item_id',
        'minimum_quantity',
        'maximum_quantity',
        'reorder_quantity',
    ];

    protected $casts = [
        'minimum_quantity' => 'decimal:6',
        'maximum_quantity' => 'decimal:6',
        'reorder_quantity' => 'decimal:6',
    ];

    public function stockItem(): BelongsTo
    {
        return $this->belongsTo(StockItem::class, 'stock_item_id');
    }
}
