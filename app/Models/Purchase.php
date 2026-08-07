<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUids;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    use HasUuids;

    protected $fillable = [
        'organization_id',
        'branch_id',
        'supplier_id',
        'purchase_number',
        'status',
        'supplier_reference',
        'currency_code',
        'subtotal',
        'discount_total',
        'tax_total',
        'total',
        'ordered_at',
        'approved_at',
        'created_by',
        'approved_by',
        'notes',
    ];

    protected $casts = [
        'ordered_at' => 'datetime',
        'approved_at' => 'datetime',
        'subtotal' => 'decimal:6',
        'discount_total' => 'decimal:6',
        'tax_total' => 'decimal:6',
        'total' => 'decimal:6',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(PurchaseLine::class)->orderBy('line_number');
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(PurchaseReceipt::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
