<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

final class Unit extends Model
{
    use HasUuids;

    protected $fillable = [
        'organization_id',
        'code',
        'name',
        'dimension',
        'decimal_precision',
        'is_active',
    ];

    protected $casts = [
        'decimal_precision' => 'integer',
        'is_active' => 'boolean',
    ];
}
