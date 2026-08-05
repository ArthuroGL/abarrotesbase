<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('parent_id')->nullable();
            $table->string('code', 50);
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->softDeletesTz();
            $table->timestampsTz();
            $table->foreign('organization_id')->references('id')->on('organizations')->restrictOnDelete();
            $table->index(['organization_id', 'name']);
        });
        Schema::table('categories', function (Blueprint $table): void {
            $table->foreign('parent_id')->references('id')->on('categories')->restrictOnDelete();
        });
        DB::statement('CREATE UNIQUE INDEX categories_organization_code_active_unique ON categories (organization_id, code) WHERE deleted_at IS NULL');

        Schema::create('brands', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->softDeletesTz();
            $table->timestampsTz();
            $table->foreign('organization_id')->references('id')->on('organizations')->restrictOnDelete();
        });
        DB::statement('CREATE UNIQUE INDEX brands_organization_name_active_unique ON brands (organization_id, lower(name)) WHERE deleted_at IS NULL');

        Schema::create('units', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->nullable();
            $table->string('code', 20);
            $table->string('name');
            $table->string('dimension', 20);
            $table->unsignedTinyInteger('decimal_precision')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();
            $table->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
            $table->index(['organization_id', 'code']);
        });
        DB::statement("ALTER TABLE units ADD CONSTRAINT units_dimension_check CHECK (dimension IN ('count', 'mass', 'volume', 'length', 'other'))");

        Schema::create('tax_rates', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('code', 30);
            $table->string('name');
            $table->decimal('rate', 8, 6);
            $table->boolean('is_included')->default(false);
            $table->timestampTz('starts_at')->nullable();
            $table->timestampTz('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();
            $table->foreign('organization_id')->references('id')->on('organizations')->restrictOnDelete();
            $table->unique(['organization_id', 'code']);
        });
        DB::statement('ALTER TABLE tax_rates ADD CONSTRAINT tax_rates_rate_check CHECK (rate >= 0 AND rate <= 1)');

        Schema::create('products', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('category_id')->nullable();
            $table->uuid('brand_id')->nullable();
            $table->uuid('tax_rate_id')->nullable();
            $table->string('sku', 80)->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('product_type', 20);
            $table->boolean('track_inventory')->default(true);
            $table->boolean('allow_negative_stock')->default(false);
            $table->boolean('is_active')->default(true);
            $table->softDeletesTz();
            $table->timestampsTz();
            $table->foreign('organization_id')->references('id')->on('organizations')->restrictOnDelete();
            $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();
            $table->foreign('brand_id')->references('id')->on('brands')->nullOnDelete();
            $table->foreign('tax_rate_id')->references('id')->on('tax_rates')->nullOnDelete();
            $table->index(['organization_id', 'name']);
        });
        DB::statement("ALTER TABLE products ADD CONSTRAINT products_type_check CHECK (product_type IN ('simple', 'bulk', 'variant_parent'))");
        DB::statement('CREATE UNIQUE INDEX products_organization_sku_active_unique ON products (organization_id, sku) WHERE sku IS NOT NULL AND deleted_at IS NULL');

        Schema::create('product_variants', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('product_id');
            $table->string('sku', 80)->nullable();
            $table->string('name');
            $table->jsonb('attribute_summary')->nullable();
            $table->boolean('is_active')->default(true);
            $table->softDeletesTz();
            $table->timestampsTz();
            $table->foreign('organization_id')->references('id')->on('organizations')->restrictOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->restrictOnDelete();
        });
        DB::statement('CREATE UNIQUE INDEX product_variants_organization_sku_active_unique ON product_variants (organization_id, sku) WHERE sku IS NOT NULL AND deleted_at IS NULL');

        Schema::create('stock_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('product_id')->nullable();
            $table->uuid('product_variant_id')->nullable();
            $table->uuid('inventory_unit_id');
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();
            $table->foreign('organization_id')->references('id')->on('organizations')->restrictOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->restrictOnDelete();
            $table->foreign('product_variant_id')->references('id')->on('product_variants')->restrictOnDelete();
            $table->foreign('inventory_unit_id')->references('id')->on('units')->restrictOnDelete();
            $table->unique(['product_id']);
            $table->unique(['product_variant_id']);
        });
        DB::statement('ALTER TABLE stock_items ADD CONSTRAINT stock_items_subject_check CHECK ((product_id IS NOT NULL AND product_variant_id IS NULL) OR (product_id IS NULL AND product_variant_id IS NOT NULL))');

        Schema::create('product_units', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('stock_item_id');
            $table->uuid('unit_id');
            $table->decimal('conversion_factor', 18, 6);
            $table->boolean('is_inventory_unit')->default(false);
            $table->boolean('is_sale_unit')->default(true);
            $table->boolean('is_purchase_unit')->default(true);
            $table->boolean('allow_decimal')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();
            $table->foreign('organization_id')->references('id')->on('organizations')->restrictOnDelete();
            $table->foreign('stock_item_id')->references('id')->on('stock_items')->restrictOnDelete();
            $table->foreign('unit_id')->references('id')->on('units')->restrictOnDelete();
            $table->unique(['stock_item_id', 'unit_id']);
        });
        DB::statement('ALTER TABLE product_units ADD CONSTRAINT product_units_conversion_factor_check CHECK (conversion_factor > 0)');
        DB::statement('CREATE UNIQUE INDEX product_units_one_inventory_unit_unique ON product_units (stock_item_id) WHERE is_inventory_unit = true');

        Schema::create('product_barcodes', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('stock_item_id');
            $table->uuid('product_unit_id')->nullable();
            $table->string('barcode', 80);
            $table->string('symbology', 30)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();
            $table->foreign('organization_id')->references('id')->on('organizations')->restrictOnDelete();
            $table->foreign('stock_item_id')->references('id')->on('stock_items')->restrictOnDelete();
            $table->foreign('product_unit_id')->references('id')->on('product_units')->restrictOnDelete();
        });
        DB::statement('CREATE UNIQUE INDEX product_barcodes_organization_active_unique ON product_barcodes (organization_id, barcode) WHERE is_active = true');

        Schema::create('price_lists', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('code', 40);
            $table->string('name');
            $table->char('currency_code', 3)->default('MXN');
            $table->unsignedSmallInteger('priority')->default(0);
            $table->timestampTz('starts_at')->nullable();
            $table->timestampTz('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();
            $table->foreign('organization_id')->references('id')->on('organizations')->restrictOnDelete();
            $table->unique(['organization_id', 'code']);
        });

        Schema::create('product_prices', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('price_list_id');
            $table->uuid('stock_item_id');
            $table->uuid('product_unit_id');
            $table->decimal('amount', 18, 6);
            $table->decimal('min_quantity', 18, 6)->default(1);
            $table->timestampTz('starts_at')->nullable();
            $table->timestampTz('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();
            $table->foreign('organization_id')->references('id')->on('organizations')->restrictOnDelete();
            $table->foreign('price_list_id')->references('id')->on('price_lists')->restrictOnDelete();
            $table->foreign('stock_item_id')->references('id')->on('stock_items')->restrictOnDelete();
            $table->foreign('product_unit_id')->references('id')->on('product_units')->restrictOnDelete();
            $table->index(['stock_item_id', 'product_unit_id', 'is_active']);
        });
        DB::statement('ALTER TABLE product_prices ADD CONSTRAINT product_prices_amount_check CHECK (amount >= 0)');
        DB::statement('ALTER TABLE product_prices ADD CONSTRAINT product_prices_min_quantity_check CHECK (min_quantity > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('product_prices');
        Schema::dropIfExists('price_lists');
        Schema::dropIfExists('product_barcodes');
        Schema::dropIfExists('product_units');
        Schema::dropIfExists('stock_items');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('products');
        Schema::dropIfExists('tax_rates');
        Schema::dropIfExists('units');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('categories');
    }
};
