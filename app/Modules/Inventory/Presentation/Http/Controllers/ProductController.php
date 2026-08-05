<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\PriceList;
use App\Models\Product;
use App\Models\ProductBarcode;
use App\Models\ProductPrice;
use App\Models\ProductUnit;
use App\Models\StockItem;
use App\Models\TaxRate;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $categoryId = $request->input('category_id');

        $products = Product::query()
            ->with([
                'category',
                'brand',
                'stockItem.barcodes',
                'stockItem.prices' => function ($query) {
                    $query->where('is_active', true);
                },
                'stockItem.inventoryUnit',
            ])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'ilike', "%{$search}%")
                        ->orWhere('sku', 'ilike', "%{$search}%")
                        ->orWhereHas('stockItem.barcodes', function ($b) use ($search) {
                            $b->where('barcode', 'ilike', "%{$search}%");
                        });
                });
            })
            ->when($categoryId, function ($query, $categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $categories = Category::query()->where('is_active', true)->orderBy('name')->get();

        return view('modules.inventory.products.index', compact('products', 'categories', 'search', 'categoryId'));
    }

    public function create(): View
    {
        $categories = Category::query()->where('is_active', true)->orderBy('name')->get();
        $brands = Brand::query()->where('is_active', true)->orderBy('name')->get();
        $units = Unit::query()->where('is_active', true)->orderBy('name')->get();
        $taxRates = TaxRate::query()->where('is_active', true)->get();

        return view('modules.inventory.products.create', compact('categories', 'brands', 'units', 'taxRates'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:80'],
            'barcode' => ['required', 'string', 'max:80'],
            'category_id' => ['nullable', 'uuid', 'exists:categories,id'],
            'brand_id' => ['nullable', 'uuid', 'exists:brands,id'],
            'unit_id' => ['required', 'uuid', 'exists:units,id'],
            'tax_rate_id' => ['nullable', 'uuid', 'exists:tax_rates,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'product_type' => ['required', 'string', 'in:simple,bulk'],
        ]);

        // Obtenemos la organización del contexto (tomamos la primera activa disponible)
        $orgId = DB::table('organizations')->value('id');
        $priceListId = PriceList::query()->where('organization_id', $orgId)->value('id');

        DB::transaction(function () use ($validated, $orgId, $priceListId) {
            // 1. Crear Producto
            $product = Product::create([
                'organization_id' => $orgId,
                'category_id' => $validated['category_id'] ?? null,
                'brand_id' => $validated['brand_id'] ?? null,
                'tax_rate_id' => $validated['tax_rate_id'] ?? null,
                'sku' => $validated['sku'] ?? null,
                'name' => $validated['name'],
                'product_type' => $validated['product_type'],
                'track_inventory' => true,
                'is_active' => true,
            ]);

            // 2. Stock Item
            $stockItem = StockItem::create([
                'organization_id' => $orgId,
                'product_id' => $product->id,
                'inventory_unit_id' => $validated['unit_id'],
                'is_active' => true,
            ]);

            // 3. Product Unit
            $productUnit = ProductUnit::create([
                'organization_id' => $orgId,
                'stock_item_id' => $stockItem->id,
                'unit_id' => $validated['unit_id'],
                'conversion_factor' => 1.000000,
                'is_inventory_unit' => true,
                'is_sale_unit' => true,
                'is_purchase_unit' => true,
                'allow_decimal' => $validated['product_type'] === 'bulk',
                'is_active' => true,
            ]);

            // 4. Barcode
            ProductBarcode::create([
                'organization_id' => $orgId,
                'stock_item_id' => $stockItem->id,
                'product_unit_id' => $productUnit->id,
                'barcode' => $validated['barcode'],
                'symbology' => 'EAN-13',
                'is_primary' => true,
                'is_active' => true,
            ]);

            // 5. Price
            if ($priceListId) {
                ProductPrice::create([
                    'organization_id' => $orgId,
                    'price_list_id' => $priceListId,
                    'stock_item_id' => $stockItem->id,
                    'product_unit_id' => $productUnit->id,
                    'amount' => $validated['price'],
                    'min_quantity' => 1,
                    'is_active' => true,
                ]);
            }
        });

        return redirect()->route('products.index')->with('status', 'Producto creado exitosamente.');
    }
    public function edit(Product $product): View
    {
        // Cargar las relaciones necesarias del producto
        $product->load([
            'category',
            'brand',
            'stockItem.inventoryUnit',
            'stockItem.barcodes',
            'stockItem.prices' => function ($query) {
                $query->where('is_active', true);
            },
        ]);

        $categories = Category::query()->where('is_active', true)->orderBy('name')->get();
        $brands = Brand::query()->where('is_active', true)->orderBy('name')->get();
        $units = Unit::query()->where('is_active', true)->orderBy('name')->get();
        $taxRates = TaxRate::query()->where('is_active', true)->get();

        return view('modules.inventory.products.edit', compact('product', 'categories', 'brands', 'units', 'taxRates'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:80'],
            'barcode' => ['required', 'string', 'max:80'],
            'category_id' => ['nullable', 'uuid', 'exists:categories,id'],
            'brand_id' => ['nullable', 'uuid', 'exists:brands,id'],
            'unit_id' => ['required', 'uuid', 'exists:units,id'],
            'tax_rate_id' => ['nullable', 'uuid', 'exists:tax_rates,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'product_type' => ['required', 'string', 'in:simple,bulk'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($validated, $product) {
            // 1. Actualizar Datos Generales del Producto
            $product->update([
                'category_id' => $validated['category_id'] ?? null,
                'brand_id' => $validated['brand_id'] ?? null,
                'tax_rate_id' => $validated['tax_rate_id'] ?? null,
                'sku' => $validated['sku'] ?? null,
                'name' => $validated['name'],
                'product_type' => $validated['product_type'],
                'is_active' => $request->has('is_active'),
            ]);

            // 2. Obtener / Actualizar Stock Item
            $stockItem = StockItem::firstOrCreate(
                ['product_id' => $product->id],
                [
                    'organization_id' => $product->organization_id,
                    'inventory_unit_id' => $validated['unit_id'],
                    'is_active' => true,
                ]
            );

            if ($stockItem->inventory_unit_id !== $validated['unit_id']) {
                $stockItem->update(['inventory_unit_id' => $validated['unit_id']]);
            }

            // 3. Product Unit
            $productUnit = ProductUnit::firstOrCreate(
                [
                    'stock_item_id' => $stockItem->id,
                    'unit_id' => $validated['unit_id'],
                ],
                [
                    'organization_id' => $product->organization_id,
                    'conversion_factor' => 1.000000,
                    'is_inventory_unit' => true,
                    'is_sale_unit' => true,
                    'is_purchase_unit' => true,
                    'allow_decimal' => $validated['product_type'] === 'bulk',
                    'is_active' => true,
                ]
            );

            $productUnit->update(['allow_decimal' => $validated['product_type'] === 'bulk']);

            // 4. Barcode (Actualizar el principal o registrar uno nuevo si no existe)
            $barcodeRecord = ProductBarcode::where('stock_item_id', $stockItem->id)
                ->where('is_primary', true)
                ->first();

            if ($barcodeRecord) {
                $barcodeRecord->update(['barcode' => $validated['barcode']]);
            } else {
                ProductBarcode::create([
                    'organization_id' => $product->organization_id,
                    'stock_item_id' => $stockItem->id,
                    'product_unit_id' => $productUnit->id,
                    'barcode' => $validated['barcode'],
                    'symbology' => 'EAN-13',
                    'is_primary' => true,
                    'is_active' => true,
                ]);
            }

            // 5. Precio
            $priceRecord = ProductPrice::where('stock_item_id', $stockItem->id)
                ->where('is_active', true)
                ->first();

            if ($priceRecord) {
                $priceRecord->update([
                    'amount' => $validated['price'],
                    'product_unit_id' => $productUnit->id,
                ]);
            } else {
                $priceListId = PriceList::where('organization_id', $product->organization_id)->value('id');
                if ($priceListId) {
                    ProductPrice::create([
                        'organization_id' => $product->organization_id,
                        'price_list_id' => $priceListId,
                        'stock_item_id' => $stockItem->id,
                        'product_unit_id' => $productUnit->id,
                        'amount' => $validated['price'],
                        'min_quantity' => 1,
                        'is_active' => true,
                    ]);
                }
            }
        });

        return redirect()->route('products.index')->with('status', 'Producto actualizado correctamente.');
    }
    public function lookup(string $barcode): JsonResponse
    {
        try {
            $response = Http::timeout(4)
                ->get("https://world.openfoodfacts.org/api/v0/product/{$barcode}.json");

            if ($response->successful() && $response->json('status') === 1) {
                $p = $response->json('product');

                // 1. Nombre
                $name = $p['product_name_es'] ?? $p['product_name'] ?? null;

                // 2. Marca (Open Food Facts a veces las trae separadas por coma)
                $rawBrand = $p['brands'] ?? null;
                $brand = $rawBrand ? trim(explode(',', $rawBrand)[0]) : null;

                // 3. Imagen
                $image = $p['image_front_small_url'] ?? $p['image_url'] ?? null;

                // 4. Contenido / Cantidad (ej. "1.5 L" o "600 ml")
                $quantity = $p['quantity'] ?? null;

                // 5. Categoría principal
                $categories = $p['categories_tags'] ?? [];
                $categoryName = !empty($categories) ? Str::headline(end($categories)) : null;

                return response()->json([
                    'found' => true,
                    'name' => $name,
                    'brand' => $brand,
                    'image' => $image,
                    'quantity' => $quantity,
                    'category_suggestion' => $categoryName,
                ]);
            }
        } catch (\Throwable $e) {
            // Log::error($e->getMessage());
        }

        return response()->json(['found' => false]);
    }
    /* public function lookup(string $barcode): JsonResponse
    {
        try {
            $response = Http::timeout(3)
                ->get("https://world.openfoodfacts.org/api/v0/product/{$barcode}.json");

            if ($response->successful() && $response->json('status') === 1) {
                $productData = $response->json('product');

                // Intentamos obtener el nombre en español o en su defecto el general
                $name = $productData['product_name_es']
                    ?? $productData['product_name']
                    ?? null;

                return response()->json([
                    'found' => true,
                    'name' => $name,
                ]);
            }
        } catch (\Throwable $e) {
            // Si falla la API externa, no interrumpimos el flujo
        }

        return response()->json(['found' => false]);
    } */
}
