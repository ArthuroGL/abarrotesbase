<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class ProductUnit extends Model
{
    use HasUuids;

    protected $fillable = [
        'organization_id',
        'stock_item_id',
        'unit_id',
        'conversion_factor',
        'is_inventory_unit',
        'is_sale_unit',
        'is_purchase_unit',
        'allow_decimal',
        'is_active',
    ];

    protected $casts = [
        'conversion_factor' => 'float',
        'is_inventory_unit' => 'boolean',
        'is_sale_unit' => 'boolean',
        'is_purchase_unit' => 'boolean',
        'allow_decimal' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function stockItem(): BelongsTo
    {
        return $this->belongsTo(StockItem::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function barcodes(): HasMany
    {
        return $this->hasMany(ProductBarcode::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }
}
