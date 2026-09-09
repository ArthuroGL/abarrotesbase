<?php
namespace App\Modules\Operation\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseCategory extends Model
{
    use HasUuids, SoftDeletes;
    protected $table = 'expense_categories';
    protected $fillable = ['organization_id','code','name','requires_approval','is_active'];
    protected function casts(): array { return ['requires_approval'=>'boolean','is_active'=>'boolean']; }
    public function expenses(): HasMany { return $this->hasMany(Expense::class); }
}
