<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseReceiptLine extends Model
{
    use HasUuids;

    protected $fillable = [
        'purchase_receipt_id',
        'purchase_line_id',
        'stock_item_id',
        'product_unit_id',
        'received_quantity',
        'conversion_factor',
        'unit_cost',
        'line_total',
    ];

    protected $casts = [
        'received_quantity' => 'decimal:6',
        'conversion_factor' => 'decimal:6',
        'unit_cost' => 'decimal:6',
        'line_total' => 'decimal:6',
    ];

    public function receipt(): BelongsTo
    {
        return $this->belongsTo(PurchaseReceipt::class, 'purchase_receipt_id');
    }

    public function purchaseLine(): BelongsTo
    {
        return $this->belongsTo(PurchaseLine::class);
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
