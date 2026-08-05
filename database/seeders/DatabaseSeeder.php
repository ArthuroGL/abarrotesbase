<?php

declare(strict_types=1);

namespace Database\Seeders;

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
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            // 1. Organización Base
            $orgId = (string) Str::uuid();
            DB::table('organizations')->insert([
                'id' => $orgId,
                'legal_name' => 'ABARROTESBASE S.A. DE C.V.',
                'display_name' => 'ABARROTESBASE',
                'currency_code' => 'MXN',
                'timezone' => 'America/Mexico_City',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Sucursal Principal
            $branchId = (string) Str::uuid();
            DB::table('branches')->insert([
                'id' => $branchId,
                'organization_id' => $orgId,
                'code' => 'SUC-01',
                'name' => 'Sucursal Principal',
                'phone' => '5551234567',
                'address' => 'Av. Principal #123, Centro',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 3. Caja Registradora
            $registerId = (string) Str::uuid();
            DB::table('registers')->insert([
                'id' => $registerId,
                'organization_id' => $orgId,
                'branch_id' => $branchId,
                'code' => 'CAJA-01',
                'name' => 'Caja Principal',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 4. Usuario Administrador
            $userId = (string) Str::uuid();
            DB::table('users')->insert([
                'id' => $userId,
                'name' => 'Administrador Base',
                'email' => 'admin@abarrotesbase.com',
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $orgUserId = (string) Str::uuid();
            DB::table('organization_users')->insert([
                'id' => $orgUserId,
                'organization_id' => $orgId,
                'user_id' => $userId,
                'is_active' => true,
                'joined_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('user_branches')->insert([
                'organization_user_id' => $orgUserId,
                'branch_id' => $branchId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 5. Unidades de Medida
            $unitPza = Unit::create([
                'organization_id' => $orgId,
                'code' => 'PZA',
                'name' => 'Pieza',
                'dimension' => 'count',
                'decimal_precision' => 0,
                'is_active' => true,
            ]);

            $unitKg = Unit::create([
                'organization_id' => $orgId,
                'code' => 'KG',
                'name' => 'Kilogramo',
                'dimension' => 'mass',
                'decimal_precision' => 3,
                'is_active' => true,
            ]);

            // 6. Tasas de Impuesto
            $taxIva16 = TaxRate::create([
                'organization_id' => $orgId,
                'code' => 'IVA16',
                'name' => 'IVA 16%',
                'rate' => 0.160000,
                'is_included' => true,
                'is_active' => true,
            ]);

            // 7. Lista de Precios
            $priceList = PriceList::create([
                'organization_id' => $orgId,
                'code' => 'GENERAL',
                'name' => 'Público General',
                'currency_code' => 'MXN',
                'priority' => 1,
                'is_active' => true,
            ]);

            // 8. Categorías
            $catBebidas = Category::create([
                'organization_id' => $orgId,
                'code' => 'BEB',
                'name' => 'Bebidas',
                'is_active' => true,
            ]);

            $catBotanas = Category::create([
                'organization_id' => $orgId,
                'code' => 'BOT',
                'name' => 'Botanas y Frituras',
                'is_active' => true,
            ]);

            $catLacteos = Category::create([
                'organization_id' => $orgId,
                'code' => 'LAC',
                'name' => 'Lácteos y Derivados',
                'is_active' => true,
            ]);

            $catAbarrotes = Category::create([
                'organization_id' => $orgId,
                'code' => 'ABA',
                'name' => 'Abarrotes Secos',
                'is_active' => true,
            ]);

            // 9. Marcas
            $brandCoca = Brand::create(['organization_id' => $orgId, 'name' => 'Coca-Cola']);
            $brandSabritas = Brand::create(['organization_id' => $orgId, 'name' => 'Sabritas']);
            $brandAlpura = Brand::create(['organization_id' => $orgId, 'name' => 'Alpura']);
            $brandVerdeValle = Brand::create(['organization_id' => $orgId, 'name' => 'Verde Valle']);

            // 10. Catálogo de Productos de Muestra
            $productsData = [
                [
                    'sku' => 'BEB-001',
                    'name' => 'Coca-Cola Original 600ml',
                    'category_id' => $catBebidas->id,
                    'brand_id' => $brandCoca->id,
                    'unit' => $unitPza,
                    'price' => 18.00,
                    'barcode' => '7501055300078',
                    'type' => 'simple',
                ],
                [
                    'sku' => 'BOT-001',
                    'name' => 'Sabritas Saladas 45g',
                    'category_id' => $catBotanas->id,
                    'brand_id' => $brandSabritas->id,
                    'unit' => $unitPza,
                    'price' => 22.50,
                    'barcode' => '7501011115685',
                    'type' => 'simple',
                ],
                [
                    'sku' => 'LAC-001',
                    'name' => 'Leche Alpura Entera 1L',
                    'category_id' => $catLacteos->id,
                    'brand_id' => $brandAlpura->id,
                    'unit' => $unitPza,
                    'price' => 28.00,
                    'barcode' => '7501020512109',
                    'type' => 'simple',
                ],
                [
                    'sku' => 'ABA-001',
                    'name' => 'Frijol Negro Verde Valle (Granel)',
                    'category_id' => $catAbarrotes->id,
                    'brand_id' => $brandVerdeValle->id,
                    'unit' => $unitKg,
                    'price' => 38.50,
                    'barcode' => '7501000100010',
                    'type' => 'bulk',
                ],
            ];

            foreach ($productsData as $data) {
                // Producto Base
                $product = Product::create([
                    'organization_id' => $orgId,
                    'category_id' => $data['category_id'],
                    'brand_id' => $data['brand_id'],
                    'tax_rate_id' => $taxIva16->id,
                    'sku' => $data['sku'],
                    'name' => $data['name'],
                    'product_type' => $data['type'],
                    'track_inventory' => true,
                    'is_active' => true,
                ]);

                // Stock Item
                $stockItem = StockItem::create([
                    'organization_id' => $orgId,
                    'product_id' => $product->id,
                    'inventory_unit_id' => $data['unit']->id,
                    'is_active' => true,
                ]);

                // Product Unit
                $productUnit = ProductUnit::create([
                    'organization_id' => $orgId,
                    'stock_item_id' => $stockItem->id,
                    'unit_id' => $data['unit']->id,
                    'conversion_factor' => 1.000000,
                    'is_inventory_unit' => true,
                    'is_sale_unit' => true,
                    'is_purchase_unit' => true,
                    'allow_decimal' => $data['type'] === 'bulk',
                    'is_active' => true,
                ]);

                // Barcode
                ProductBarcode::create([
                    'organization_id' => $orgId,
                    'stock_item_id' => $stockItem->id,
                    'product_unit_id' => $productUnit->id,
                    'barcode' => $data['barcode'],
                    'symbology' => 'EAN-13',
                    'is_primary' => true,
                    'is_active' => true,
                ]);

                // Price
                ProductPrice::create([
                    'organization_id' => $orgId,
                    'price_list_id' => $priceList->id,
                    'stock_item_id' => $stockItem->id,
                    'product_unit_id' => $productUnit->id,
                    'amount' => $data['price'],
                    'min_quantity' => 1,
                    'is_active' => true,
                ]);
            }
        });
    }
}
