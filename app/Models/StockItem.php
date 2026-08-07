<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class StockItem extends Model
{
    use HasUuids;

    protected $fillable = [
        'organization_id',
        'product_id',
        'product_variant_id',
        'inventory_unit_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function inventoryUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'inventory_unit_id');
    }

    public function productUnits(): HasMany
    {
        return $this->hasMany(ProductUnit::class);
    }

    public function barcodes(): HasMany
    {
        return $this->hasMany(ProductBarcode::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }

    public function inventoryBalance(): HasOne
    {
        return $this->hasOne(InventoryBalance::class, 'stock_item_id');
    }

    public function reorderLevel(): HasOne
    {
        return $this->hasOne(StockReorderLevel::class, 'stock_item_id');
    }
    public function units(): HasMany
{
    return $this->hasMany(ProductUnit::class);
}
}
