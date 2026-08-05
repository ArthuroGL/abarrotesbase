<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ProductPrice extends Model
{
    use HasUuids;

    protected $fillable = [
        'organization_id',
        'price_list_id',
        'stock_item_id',
        'product_unit_id',
        'amount',
        'min_quantity',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected $casts = [
        'amount' => 'float',
        'min_quantity' => 'float',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function priceList(): BelongsTo
    {
        return $this->belongsTo(PriceList::class);
    }

    public function stockItem(): BelongsTo
    {
        return $this->belongsTo(StockItem::class);
    }

    public function productUnit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class);
    }
}
