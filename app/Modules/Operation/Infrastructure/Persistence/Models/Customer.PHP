<?php

declare(strict_types=1);

namespace App\Modules\Operation\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Customer extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'code',
        'name',
        'phone',
        'email',
        'address',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
