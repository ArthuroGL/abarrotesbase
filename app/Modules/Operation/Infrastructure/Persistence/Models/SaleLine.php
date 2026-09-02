<?php

declare(strict_types=1);

namespace App\Modules\Operation\Infrastructure\Persistence\Models;

use App\Models\InventoryMovement;
use App\Models\ProductUnit;
use App\Models\StockItem;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class SaleLine extends Model
{
    use HasUuids;

    protected $fillable = [
        'sale_id',
        'line_number',
        'stock_item_id',
        'product_unit_id',
        'sku',
        'description',
        'quantity',
        'conversion_factor',
        'unit_price',
        'discount_amount',
        'tax_amount',
        'unit_cost',
        'line_total',
        'inventory_movement_id',
    ];

    protected function casts(): array
    {
        return [
            'line_number' => 'integer',
            'quantity' => 'decimal:6',
            'conversion_factor' => 'decimal:6',
            'unit_price' => 'decimal:6',
            'discount_amount' => 'decimal:6',
            'tax_amount' => 'decimal:6',
            'unit_cost' => 'decimal:6',
            'line_total' => 'decimal:6',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function stockItem(): BelongsTo
    {
        return $this->belongsTo(StockItem::class);
    }

    public function productUnit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class);
    }

    public function inventoryMovement(): BelongsTo
    {
        return $this->belongsTo(
            InventoryMovement::class,
            'inventory_movement_id'
        );
    }
}
