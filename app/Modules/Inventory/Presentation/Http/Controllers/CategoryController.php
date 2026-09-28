<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Modules\Identity\Application\Services\CurrentContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class CategoryController extends Controller
{
    public function __construct(
        private readonly CurrentContext $context,
    ) {}
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

    public function search(Request $request): JsonResponse
    {
        $search = trim((string) $request->input('q', ''));

        if ($search === '') {
            return response()->json([
                'items' => [],
            ]);
        }

        $items = Category::query()
            ->where(
                'organization_id',
                $this->context->organizationId()
            )
            ->where('is_active', true)
            ->where('name', 'ilike', "%{$search}%")
            ->orderBy('name')
            ->limit(8)
            ->get();

        return response()->json([
            'items' => $items->map(
                fn(Category $category) => [
                    'id' => $category->id,
                    'name' => $category->name,
                ]
            )->values(),
        ]);
    }
}
