<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Category::query()
            ->withCount('products')
            ->orderBy('name')
            ->paginate(10);

        return view('modules.inventory.categories.index', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        $orgId = DB::table('organizations')->value('id');

        Category::create([
            'organization_id' => $orgId,
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'is_active' => true,
        ]);

        return redirect()->route('categories.index')->with('status', 'Categoría creada con éxito.');
    }

    public function quickStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
        ]);

        $orgId = DB::table('organizations')->value('id');
        $code = $validated['code'] ?? strtoupper(substr($validated['name'], 0, 3));

        $category = Category::create([
            'organization_id' => $orgId,
            'code' => $code,
            'name' => $validated['name'],
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'category' => $category,
        ]);
    }
}
