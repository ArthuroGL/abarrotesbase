<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Product extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'category_id',
        'brand_id',
        'tax_rate_id',
        'sku',
        'name',
        'description',
        'product_type',
        'track_inventory',
        'allow_negative_stock',
        'is_active',
    ];

    protected $casts = [
        'track_inventory' => 'boolean',
        'allow_negative_stock' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function taxRate(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function stockItem(): HasOne
    {
        return $this->hasOne(StockItem::class);
    }
}
