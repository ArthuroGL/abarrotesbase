<?php
namespace App\Modules\Operation\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Operation\Infrastructure\Persistence\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ExpenseCategoryController extends Controller
{
    public function index(): View {
        $categories=ExpenseCategory::where('organization_id',auth()->user()->organization_id)->withCount('expenses')->orderBy('name')->get();
        return view('modules.operation.expenses.categories',compact('categories'));
    }

    public function store(Request $request): RedirectResponse {
        $data=$request->validate([
            'code'=>['required','string','max:40',Rule::unique('expense_categories','code')->where(fn($q)=>$q->where('organization_id',auth()->user()->organization_id))],
            'name'=>['required','string','max:255'],'requires_approval'=>['nullable','boolean']
        ]);
        ExpenseCategory::create([
            'organization_id'=>auth()->user()->organization_id,'code'=>strtoupper(trim($data['code'])),
            'name'=>trim($data['name']),'requires_approval'=>(bool)($data['requires_approval']??false),'is_active'=>true
        ]);
        return back()->with('success','Categoría creada.');
    }
}
