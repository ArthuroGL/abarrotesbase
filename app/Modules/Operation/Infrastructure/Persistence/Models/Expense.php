<?php
namespace App\Modules\Operation\Infrastructure\Persistence\Models;

use App\Models\Supplier;
use App\Models\User;
use App\Modules\Identity\Infrastructure\Persistence\Models\Branch as ModelsBranch;
use App\Modules\Operation\Infrastructure\Persistence\Models\PaymentMethod as ModelsPaymentMethod;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasUuids;
    protected $table = 'expenses';
    protected $fillable = [
        'organization_id','branch_id','cash_session_id','payment_method_id','expense_category_id',
        'supplier_id','expense_number','status','expense_type','currency_code','amount',
        'beneficiary','source_name','reference','expense_date','description','rejection_reason',
        'requested_by','approved_by','approved_at','paid_at','cancelled_at','cancelled_by'
    ];
    protected function casts(): array {
        return ['amount'=>'decimal:2','expense_date'=>'date','approved_at'=>'datetime','paid_at'=>'datetime','cancelled_at'=>'datetime'];
    }
    public function category(): BelongsTo { return $this->belongsTo(ExpenseCategory::class,'expense_category_id'); }
    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class); }
    public function branch(): BelongsTo { return $this->belongsTo(ModelsBranch::class); }
    public function paymentMethod(): BelongsTo { return $this->belongsTo(ModelsPaymentMethod::class); }
    public function cashSession(): BelongsTo { return $this->belongsTo(CashSession::class); }
    public function requestedBy(): BelongsTo { return $this->belongsTo(User::class,'requested_by'); }
    public function approvedBy(): BelongsTo { return $this->belongsTo(User::class,'approved_by'); }
    public function cancelledBy(): BelongsTo { return $this->belongsTo(User::class,'cancelled_by'); }
}
