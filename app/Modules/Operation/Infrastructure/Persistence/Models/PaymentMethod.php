<?php

declare(strict_types=1);

namespace App\Modules\Operation\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class PaymentMethod extends Model
{
    use HasUuids;

    protected $fillable = [
        'organization_id',
        'code',
        'name',
        'type',
        'requires_reference',
        'affects_cash',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'requires_reference' => 'boolean',
            'affects_cash' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SalePayment::class);
    }
}
