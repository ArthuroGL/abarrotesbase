<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'code',
        'business_name',
        'rfc',
        'contact_name',
        'email',
        'phone',
        'address',
        'payment_terms_days',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'payment_terms_days' => 'integer',
    ];

    /**
     * Relación con las órdenes de compra.
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }
}
