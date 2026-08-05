<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class PriceList extends Model
{
    use HasUuids;

    protected $fillable = [
        'organization_id',
        'code',
        'name',
        'currency_code',
        'priority',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected $casts = [
        'priority' => 'integer',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }
}
