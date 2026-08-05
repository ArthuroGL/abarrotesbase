<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class BrandController extends Controller
{
    public function index(): View
    {
        $brands = Brand::query()
            ->withCount('products')
            ->orderBy('name')
            ->paginate(10);

        return view('modules.inventory.brands.index', compact('brands'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $orgId = DB::table('organizations')->value('id');

        Brand::create([
            'organization_id' => $orgId,
            'name' => $validated['name'],
            'is_active' => true,
        ]);

        return redirect()->route('brands.index')->with('status', 'Marca creada con éxito.');
    }

    public function quickStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $orgId = DB::table('organizations')->value('id');

        $brand = Brand::create([
            'organization_id' => $orgId,
            'name' => $validated['name'],
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'brand' => $brand,
        ]);
    }
}
