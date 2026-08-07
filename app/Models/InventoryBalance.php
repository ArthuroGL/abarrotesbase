<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class InventoryBalance extends Model
{
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = 'stock_item_id';

    protected $fillable = [
        'organization_id',
        'branch_id',
        'stock_item_id',
        'on_hand_quantity',
        'reserved_quantity',
        'weighted_average_cost',
        'version',
    ];

    protected $casts = [
        'on_hand_quantity' => 'decimal:6',
        'reserved_quantity' => 'decimal:6',
        'available_quantity' => 'decimal:6',
        'weighted_average_cost' => 'decimal:6',
        'version' => 'integer',
    ];

    public function stockItem(): BelongsTo
    {
        return $this->belongsTo(StockItem::class, 'stock_item_id');
    }
}
