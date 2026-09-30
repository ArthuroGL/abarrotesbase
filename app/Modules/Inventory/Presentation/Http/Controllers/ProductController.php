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
use App\Modules\Identity\Application\Services\CurrentContext;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class ProductController extends Controller
{

    public function __construct(
        private readonly CurrentContext $context,
    ) {}
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $categoryId = $request->input('category_id');
        // Capturamos las nuevas variables de filtro para persistirlas en la vista
        $brandId = $request->input('brand_id');
        $stockFilter = $request->input('stock_filter');

        $perPage = (int) $request->input('per_page', 10);

        $perPage = in_array($perPage, [10, 25, 50, 100], true)
            ? $perPage
            : 10;

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
            ->when($brandId, function ($query, $brandId) {
                $query->where('brand_id', $brandId);
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
        $stockItemIds = $products->getCollection()
            ->pluck('stockItem.id')
            ->filter()
            ->values();

        $stockQuantities = DB::table('inventory_balances')
            ->where('organization_id', $this->context->organizationId())
            ->whereIn('stock_item_id', $stockItemIds)
            ->select(
                'stock_item_id',
                DB::raw('SUM(on_hand_quantity) as quantity')
            )
            ->groupBy('stock_item_id')
            ->pluck('quantity', 'stock_item_id');

        $categories = Category::query()->where('is_active', true)->orderBy('name')->get();
        // Cargamos las marcas para el filtro
        $brands = Brand::query()->where('is_active', true)->orderBy('name')->get();

        // IMPORTANTE: Asegúrate de incluir 'brandId' y 'stockFilter' en el compact
        return view(
            'modules.inventory.products.index',
            compact(
                'products',
                'categories',
                'brands',
                'search',
                'categoryId',
                'brandId',
                'stockFilter',
                'perPage',
                'stockQuantities'
            )
        );
    }

    public function create(Request $request): View
    {
        $units = Unit::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $taxRates = TaxRate::query()
            ->where('is_active', true)
            ->get();

        $initialBarcode = trim(
            (string) $request->query('barcode', '')
        );

        $initialCategory = null;

        if ($categoryId = old('category_id')) {
            $initialCategory = Category::query()
                ->where('organization_id', $this->context->organizationId())
                ->where('is_active', true)
                ->find($categoryId);
        }

        $initialBrand = null;

        if ($brandId = old('brand_id')) {
            $initialBrand = Brand::query()
                ->where('organization_id', $this->context->organizationId())
                ->where('is_active', true)
                ->find($brandId);
        }

        return view(
            'modules.inventory.products.create',
            compact(
                'units',
                'taxRates',
                'initialBarcode',
                'initialCategory',
                'initialBrand'
            )
        );
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:80'],
            /* 'barcode' => ['required', 'string', 'max:80'], */
            'barcode' => ['required', 'string', 'max:80', 'unique:product_barcodes,barcode',],

            'category_id' => [
                'nullable',
                'uuid',
                Rule::exists('categories', 'id')
                    ->where(
                        fn($query) =>
                        $query->where('organization_id', $this->context->organizationId())
                    ),
            ],

            'brand_id' => [
                'nullable',
                'uuid',
                Rule::exists('brands', 'id')
                    ->where(
                        fn($query) =>
                        $query->where('organization_id', $this->context->organizationId())
                    ),
            ],

            'unit_id' => [
                'required',
                'uuid',
                Rule::exists('units', 'id')
                    ->where(
                        fn($query) =>
                        $query->where('organization_id', $this->context->organizationId())
                    ),
            ],

            'tax_rate_id' => [
                'nullable',
                'uuid',
                Rule::exists('tax_rates', 'id')
                    ->where(
                        fn($query) =>
                        $query->where('organization_id', $this->context->organizationId())
                    ),
            ],

            'price' => ['required', 'numeric', 'min:0'],
            'product_type' => ['required', 'string', 'in:simple,bulk'],
        ]);

        // Obtenemos la organización del contexto (tomamos la primera activa disponible)
        $orgId = $this->context->organizationId();
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

            'category_id' => [
                'nullable',
                'uuid',
                Rule::exists('categories', 'id')
                    ->where(
                        fn($query) =>
                        $query->where(
                            'organization_id',
                            $this->context->organizationId()
                        )
                    ),
            ],

            'brand_id' => [
                'nullable',
                'uuid',
                Rule::exists('brands', 'id')
                    ->where(
                        fn($query) =>
                        $query->where(
                            'organization_id',
                            $this->context->organizationId()
                        )
                    ),
            ],

            'unit_id' => [
                'required',
                'uuid',
                Rule::exists('units', 'id')
                    ->where(
                        fn($query) =>
                        $query->where(
                            'organization_id',
                            $this->context->organizationId()
                        )
                    ),
            ],

            'tax_rate_id' => [
                'nullable',
                'uuid',
                Rule::exists('tax_rates', 'id')
                    ->where(
                        fn($query) =>
                        $query->where(
                            'organization_id',
                            $this->context->organizationId()
                        )
                    ),
            ],

            'price' => ['required', 'numeric', 'min:0'],
            'product_type' => ['required', 'string', 'in:simple,bulk'],
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

    public function deactivate(Product $product): RedirectResponse
    {
        if ($product->organization_id !== $this->context->organizationId()) {
            abort(403);
        }

        if (!$product->is_active) {
            return redirect()
                ->route('products.index')
                ->with('status', 'El producto ya está inactivo.');
        }

        $stockItem = $product->stockItem;

        if ($stockItem) {
            $stockQuantity = DB::table('inventory_balances')
                ->where('organization_id', $product->organization_id)
                ->where('stock_item_id', $stockItem->id)
                ->sum('on_hand_quantity');

            if ((float) $stockQuantity > 0) {
                return redirect()
                    ->route('products.index')
                    ->with(
                        'error',
                        "No se puede desactivar el producto porque tiene {$stockQuantity} unidades en existencia. Primero realiza un ajuste de salida."
                    );
            }
        }

        $product->update([
            'is_active' => false,
        ]);

        return redirect()
            ->route('products.index')
            ->with('status', 'Producto desactivado correctamente.');
    }

    public function reactivate(Product $product): RedirectResponse
    {
        if ($product->organization_id !== $this->context->organizationId()) {
            abort(403);
        }

        if ($product->is_active) {
            return redirect()
                ->route('products.index')
                ->with('status', 'El producto ya está activo.');
        }

        $product->update([
            'is_active' => true,
        ]);

        return redirect()
            ->route('products.index')
            ->with('status', 'Producto reactivado correctamente.');
    }

    public function search(Request $request): JsonResponse
    {
        $search = trim((string) $request->input('q', ''));

        if ($search === '') {
            return response()->json([
                'items' => [],
            ]);
        }

        $orgId = $this->context->organizationId();

        $items = StockItem::query()
            ->where('organization_id', $orgId)
            ->where('is_active', true)
            ->whereHas('product', function ($query) {
                $query->where('is_active', true);
            })
            ->with([
                'product',
                'inventoryUnit',
                'barcodes' => fn($query) => $query
                    ->where('is_active', true)
                    ->orderByDesc('is_primary'),
            ])
            ->where(function ($query) use ($search) {
                $query->whereHas('product', function ($productQuery) use ($search) {
                    $productQuery
                        ->where('name', 'ilike', "%{$search}%")
                        ->orWhere('sku', 'ilike', "%{$search}%");
                })
                    ->orWhereHas('barcodes', function ($barcodeQuery) use ($search) {
                        $barcodeQuery->where(
                            'barcode',
                            'ilike',
                            "%{$search}%"
                        );
                    });
            })
            ->limit(8)
            ->get();

        return response()->json([
            'items' => $items->map(function (StockItem $stockItem) {
                $barcode = $stockItem->barcodes->first();

                return [
                    'stock_item_id' => $stockItem->id,
                    'product_id' => $stockItem->product?->id,
                    'name' => $stockItem->product?->name
                        ?? 'Producto sin nombre',
                    'sku' => $stockItem->product?->sku,
                    'barcode' => $barcode?->barcode,
                    'unit' => $stockItem->inventoryUnit?->code
                        ?? 'PZA',
                ];
            })->values(),
        ]);
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

}
