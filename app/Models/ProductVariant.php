<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

final class ProductVariant extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'product_id',
        'sku',
        'name',
        'attribute_summary',
        'is_active',
    ];

    protected $casts = [
        'attribute_summary' => 'array',
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function stockItem(): HasOne
    {
        return $this->hasOne(StockItem::class);
    }
}
