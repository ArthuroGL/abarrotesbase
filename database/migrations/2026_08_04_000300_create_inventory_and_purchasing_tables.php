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
        Schema::create('suppliers', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('code', 40);
            $table->string('business_name');
            $table->string('rfc', 20)->nullable();
            $table->string('contact_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 30)->nullable();
            $table->text('address')->nullable();
            $table->unsignedSmallInteger('payment_terms_days')->default(0);
            $table->boolean('is_active')->default(true);
            $table->softDeletesTz();
            $table->timestampsTz();
            $table->foreign('organization_id')->references('id')->on('organizations')->restrictOnDelete();
            $table->index(['organization_id', 'business_name']);
        });
        DB::statement('CREATE UNIQUE INDEX suppliers_organization_code_active_unique ON suppliers (organization_id, code) WHERE deleted_at IS NULL');
        DB::statement('CREATE UNIQUE INDEX suppliers_organization_rfc_active_unique ON suppliers (organization_id, rfc) WHERE rfc IS NOT NULL AND deleted_at IS NULL');

        Schema::create('inventory_balances', function (Blueprint $table): void {
            $table->uuid('organization_id');
            $table->uuid('branch_id');
            $table->uuid('stock_item_id');
            $table->decimal('on_hand_quantity', 18, 6)->default(0);
            $table->decimal('reserved_quantity', 18, 6)->default(0);
            $table->decimal('available_quantity', 18, 6)->storedAs('on_hand_quantity - reserved_quantity');
            $table->decimal('weighted_average_cost', 18, 6)->default(0);
            $table->unsignedBigInteger('version')->default(1);
            $table->timestampTz('updated_at')->useCurrent();
            $table->primary(['organization_id', 'branch_id', 'stock_item_id']);
            $table->foreign('organization_id')->references('id')->on('organizations')->restrictOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches')->restrictOnDelete();
            $table->foreign('stock_item_id')->references('id')->on('stock_items')->restrictOnDelete();
        });
        DB::statement('ALTER TABLE inventory_balances ADD CONSTRAINT inventory_balances_reserved_check CHECK (reserved_quantity >= 0 AND reserved_quantity <= on_hand_quantity)');

        Schema::create('stock_reorder_levels', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('branch_id');
            $table->uuid('stock_item_id');
            $table->decimal('minimum_quantity', 18, 6);
            $table->decimal('maximum_quantity', 18, 6)->nullable();
            $table->decimal('reorder_quantity', 18, 6)->nullable();
            $table->timestampsTz();
            $table->foreign('organization_id')->references('id')->on('organizations')->restrictOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches')->restrictOnDelete();
            $table->foreign('stock_item_id')->references('id')->on('stock_items')->restrictOnDelete();
            $table->unique(['branch_id', 'stock_item_id']);
        });
        DB::statement('ALTER TABLE stock_reorder_levels ADD CONSTRAINT stock_reorder_levels_minimum_check CHECK (minimum_quantity >= 0)');

        Schema::create('purchases', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('branch_id');
            $table->uuid('supplier_id');
            $table->string('purchase_number', 40);
            $table->string('status', 20)->default('draft');
            $table->string('supplier_reference', 100)->nullable();
            $table->char('currency_code', 3)->default('MXN');
            $table->decimal('subtotal', 18, 6)->default(0);
            $table->decimal('discount_total', 18, 6)->default(0);
            $table->decimal('tax_total', 18, 6)->default(0);
            $table->decimal('total', 18, 6)->default(0);
            $table->timestampTz('ordered_at')->nullable();
            $table->timestampTz('approved_at')->nullable();
            $table->uuid('created_by');
            $table->uuid('approved_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestampsTz();
            $table->foreign('organization_id')->references('id')->on('organizations')->restrictOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches')->restrictOnDelete();
            $table->foreign('supplier_id')->references('id')->on('suppliers')->restrictOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
            $table->unique(['organization_id', 'purchase_number']);
            $table->index(['organization_id', 'branch_id', 'status', 'created_at']);
        });
        DB::statement("ALTER TABLE purchases ADD CONSTRAINT purchases_status_check CHECK (status IN ('draft', 'approved', 'partial', 'received', 'cancelled'))");

        Schema::create('purchase_lines', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('purchase_id');
            $table->unsignedSmallInteger('line_number');
            $table->uuid('stock_item_id');
            $table->uuid('product_unit_id');
            $table->string('description');
            $table->decimal('ordered_quantity', 18, 6);
            $table->decimal('unit_cost', 18, 6);
            $table->decimal('discount_amount', 18, 6)->default(0);
            $table->decimal('tax_amount', 18, 6)->default(0);
            $table->decimal('line_total', 18, 6);
            $table->timestampsTz();
            $table->foreign('purchase_id')->references('id')->on('purchases')->cascadeOnDelete();
            $table->foreign('stock_item_id')->references('id')->on('stock_items')->restrictOnDelete();
            $table->foreign('product_unit_id')->references('id')->on('product_units')->restrictOnDelete();
            $table->unique(['purchase_id', 'line_number']);
        });
        DB::statement('ALTER TABLE purchase_lines ADD CONSTRAINT purchase_lines_quantity_check CHECK (ordered_quantity > 0)');
        DB::statement('ALTER TABLE purchase_lines ADD CONSTRAINT purchase_lines_cost_check CHECK (unit_cost >= 0 AND line_total >= 0)');

        Schema::create('purchase_receipts', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('branch_id');
            $table->uuid('supplier_id');
            $table->uuid('purchase_id')->nullable();
            $table->string('receipt_number', 40);
            $table->string('status', 20)->default('draft');
            $table->timestampTz('received_at')->nullable();
            $table->uuid('received_by');
            $table->text('notes')->nullable();
            $table->timestampsTz();
            $table->foreign('organization_id')->references('id')->on('organizations')->restrictOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches')->restrictOnDelete();
            $table->foreign('supplier_id')->references('id')->on('suppliers')->restrictOnDelete();
            $table->foreign('purchase_id')->references('id')->on('purchases')->restrictOnDelete();
            $table->foreign('received_by')->references('id')->on('users')->restrictOnDelete();
            $table->unique(['organization_id', 'receipt_number']);
        });
        DB::statement("ALTER TABLE purchase_receipts ADD CONSTRAINT purchase_receipts_status_check CHECK (status IN ('draft', 'confirmed', 'cancelled'))");

        Schema::create('purchase_receipt_lines', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('purchase_receipt_id');
            $table->uuid('purchase_line_id')->nullable();
            $table->uuid('stock_item_id');
            $table->uuid('product_unit_id');
            $table->decimal('received_quantity', 18, 6);
            $table->decimal('conversion_factor', 18, 6);
            $table->decimal('unit_cost', 18, 6);
            $table->decimal('line_total', 18, 6);
            $table->string('lot_number', 100)->nullable();
            $table->date('expires_on')->nullable();
            $table->timestampsTz();
            $table->foreign('purchase_receipt_id')->references('id')->on('purchase_receipts')->cascadeOnDelete();
            $table->foreign('purchase_line_id')->references('id')->on('purchase_lines')->nullOnDelete();
            $table->foreign('stock_item_id')->references('id')->on('stock_items')->restrictOnDelete();
            $table->foreign('product_unit_id')->references('id')->on('product_units')->restrictOnDelete();
        });
        DB::statement('ALTER TABLE purchase_receipt_lines ADD CONSTRAINT purchase_receipt_lines_quantity_check CHECK (received_quantity > 0 AND conversion_factor > 0)');

        Schema::create('inventory_movements', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('branch_id');
            $table->uuid('stock_item_id');
            $table->string('movement_type', 30);
            $table->decimal('quantity_delta', 18, 6);
            $table->decimal('unit_cost', 18, 6)->default(0);
            $table->decimal('total_cost', 18, 6)->default(0);
            $table->string('source_type', 100);
            $table->uuid('source_id')->nullable();
            $table->string('reason_code', 50)->nullable();
            $table->text('notes')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestampTz('occurred_at')->useCurrent();
            $table->foreign('organization_id')->references('id')->on('organizations')->restrictOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches')->restrictOnDelete();
            $table->foreign('stock_item_id')->references('id')->on('stock_items')->restrictOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['organization_id', 'branch_id', 'stock_item_id', 'occurred_at']);
            $table->index(['source_type', 'source_id']);
        });
        DB::statement("ALTER TABLE inventory_movements ADD CONSTRAINT inventory_movements_type_check CHECK (movement_type IN ('initial_load', 'purchase_receipt', 'sale', 'sale_return', 'adjustment_in', 'adjustment_out', 'count_adjustment', 'transfer_in', 'transfer_out', 'void_reversal'))");
        DB::statement('ALTER TABLE inventory_movements ADD CONSTRAINT inventory_movements_quantity_check CHECK (quantity_delta <> 0)');

        Schema::create('stock_counts', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('branch_id');
            $table->string('status', 20)->default('draft');
            $table->timestampTz('started_at')->nullable();
            $table->timestampTz('counted_at')->nullable();
            $table->timestampTz('applied_at')->nullable();
            $table->uuid('created_by');
            $table->uuid('approved_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestampsTz();
            $table->foreign('organization_id')->references('id')->on('organizations')->restrictOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches')->restrictOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
        });
        DB::statement("ALTER TABLE stock_counts ADD CONSTRAINT stock_counts_status_check CHECK (status IN ('draft', 'in_progress', 'reconciled', 'applied', 'cancelled'))");

        Schema::create('stock_count_lines', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('stock_count_id');
            $table->uuid('stock_item_id');
            $table->decimal('system_quantity', 18, 6);
            $table->decimal('counted_quantity', 18, 6)->nullable();
            $table->decimal('difference_quantity', 18, 6)->nullable();
            $table->string('reason_code', 50)->nullable();
            $table->text('notes')->nullable();
            $table->timestampsTz();
            $table->foreign('stock_count_id')->references('id')->on('stock_counts')->cascadeOnDelete();
            $table->foreign('stock_item_id')->references('id')->on('stock_items')->restrictOnDelete();
            $table->unique(['stock_count_id', 'stock_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_count_lines');
        Schema::dropIfExists('stock_counts');
        Schema::dropIfExists('inventory_movements');
        Schema::dropIfExists('purchase_receipt_lines');
        Schema::dropIfExists('purchase_receipts');
        Schema::dropIfExists('purchase_lines');
        Schema::dropIfExists('purchases');
        Schema::dropIfExists('stock_reorder_levels');
        Schema::dropIfExists('inventory_balances');
        Schema::dropIfExists('suppliers');
    }
};
