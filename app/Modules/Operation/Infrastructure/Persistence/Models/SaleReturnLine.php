<?php

declare(strict_types=1);

namespace App\Modules\Operation\Infrastructure\Persistence\Models;

use App\Models\StockItem;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class SaleReturnLine extends Model
{
    use HasUuids;

    protected $table = 'sale_return_lines';

    protected $fillable = [
        'sale_return_id',
        'original_sale_line_id',
        'stock_item_id',
        'quantity',
        'line_total',
        'inventory_condition',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'line_total' => 'decimal:2',
        ];
    }

    public function saleReturn(): BelongsTo
    {
        return $this->belongsTo(SaleReturn::class, 'sale_return_id');
    }

    public function originalSaleLine(): BelongsTo
    {
        return $this->belongsTo(SaleLine::class, 'original_sale_line_id');
    }

    public function stockItem(): BelongsTo
    {
        return $this->belongsTo(StockItem::class, 'stock_item_id');
    }
}
