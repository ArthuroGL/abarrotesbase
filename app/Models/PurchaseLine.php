<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUids;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseLine extends Model
{
    use HasUuids;

    protected $fillable = [
        'purchase_id',
        'line_number',
        'stock_item_id',
        'product_unit_id',
        'description',
        'ordered_quantity',
        'unit_cost',
        'discount_amount',
        'tax_amount',
        'line_total',
    ];

    protected $casts = [
        'ordered_quantity' => 'decimal:6',
        'unit_cost' => 'decimal:6',
        'discount_amount' => 'decimal:6',
        'tax_amount' => 'decimal:6',
        'line_total' => 'decimal:6',
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
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
