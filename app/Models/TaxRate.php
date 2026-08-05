<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class TaxRate extends Model
{
    use HasUuids;

    protected $fillable = [
        'organization_id',
        'code',
        'name',
        'rate',
        'is_included',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected $casts = [
        'rate' => 'float',
        'is_included' => 'boolean',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
