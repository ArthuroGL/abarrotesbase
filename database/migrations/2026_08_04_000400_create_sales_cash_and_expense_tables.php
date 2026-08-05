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
        Schema::create('customers', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('code', 40);
            $table->string('name');
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->softDeletesTz();
            $table->timestampsTz();
            $table->foreign('organization_id')->references('id')->on('organizations')->restrictOnDelete();
            $table->index(['organization_id', 'name']);
        });
        DB::statement('CREATE UNIQUE INDEX customers_organization_code_active_unique ON customers (organization_id, code) WHERE deleted_at IS NULL');

        Schema::create('payment_methods', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('code', 40);
            $table->string('name');
            $table->string('type', 30);
            $table->boolean('requires_reference')->default(false);
            $table->boolean('affects_cash')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();
            $table->foreign('organization_id')->references('id')->on('organizations')->restrictOnDelete();
            $table->unique(['organization_id', 'code']);
        });
        DB::statement("ALTER TABLE payment_methods ADD CONSTRAINT payment_methods_type_check CHECK (type IN ('cash', 'card', 'transfer', 'digital_wallet', 'other'))");

        Schema::create('cash_sessions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('branch_id');
            $table->uuid('register_id');
            $table->uuid('responsible_user_id');
            $table->string('status', 20)->default('open');
            $table->decimal('opening_float', 18, 6)->default(0);
            $table->decimal('theoretical_total', 18, 6)->nullable();
            $table->decimal('counted_total', 18, 6)->nullable();
            $table->decimal('difference_total', 18, 6)->nullable();
            $table->timestampTz('opened_at')->useCurrent();
            $table->timestampTz('closed_at')->nullable();
            $table->uuid('closed_by')->nullable();
            $table->uuid('approved_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestampsTz();
            $table->foreign('organization_id')->references('id')->on('organizations')->restrictOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches')->restrictOnDelete();
            $table->foreign('register_id')->references('id')->on('registers')->restrictOnDelete();
            $table->foreign('responsible_user_id')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('closed_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['organization_id', 'branch_id', 'status']);
        });
        DB::statement("ALTER TABLE cash_sessions ADD CONSTRAINT cash_sessions_status_check CHECK (status IN ('open', 'counting', 'closed'))");
        DB::statement("CREATE UNIQUE INDEX cash_sessions_one_active_register_unique ON cash_sessions (register_id) WHERE status IN ('open', 'counting')");

        Schema::create('sales', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('branch_id');
            $table->uuid('register_id')->nullable();
            $table->uuid('cash_session_id')->nullable();
            $table->uuid('customer_id')->nullable();
            $table->string('sale_number', 40);
            $table->string('status', 30)->default('draft');
            $table->char('currency_code', 3)->default('MXN');
            $table->decimal('subtotal', 18, 6)->default(0);
            $table->decimal('discount_total', 18, 6)->default(0);
            $table->decimal('tax_total', 18, 6)->default(0);
            $table->decimal('total', 18, 6)->default(0);
            $table->decimal('change_total', 18, 6)->default(0);
            $table->timestampTz('confirmed_at')->nullable();
            $table->timestampTz('cancelled_at')->nullable();
            $table->uuid('created_by');
            $table->uuid('confirmed_by')->nullable();
            $table->uuid('cancelled_by')->nullable();
            $table->string('cancellation_reason', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestampsTz();
            $table->foreign('organization_id')->references('id')->on('organizations')->restrictOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches')->restrictOnDelete();
            $table->foreign('register_id')->references('id')->on('registers')->nullOnDelete();
            $table->foreign('cash_session_id')->references('id')->on('cash_sessions')->nullOnDelete();
            $table->foreign('customer_id')->references('id')->on('customers')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('confirmed_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('cancelled_by')->references('id')->on('users')->nullOnDelete();
            $table->unique(['organization_id', 'sale_number']);
            $table->index(['organization_id', 'branch_id', 'confirmed_at']);
            $table->index(['cash_session_id', 'status']);
        });
        DB::statement("ALTER TABLE sales ADD CONSTRAINT sales_status_check CHECK (status IN ('draft', 'confirmed', 'cancelled', 'partially_returned', 'returned'))");

        Schema::create('sale_lines', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('sale_id');
            $table->unsignedSmallInteger('line_number');
            $table->uuid('stock_item_id');
            $table->uuid('product_unit_id');
            $table->string('sku', 80)->nullable();
            $table->string('description');
            $table->decimal('quantity', 18, 6);
            $table->decimal('conversion_factor', 18, 6);
            $table->decimal('unit_price', 18, 6);
            $table->decimal('discount_amount', 18, 6)->default(0);
            $table->decimal('tax_amount', 18, 6)->default(0);
            $table->decimal('unit_cost', 18, 6)->default(0);
            $table->decimal('line_total', 18, 6);
            $table->uuid('inventory_movement_id')->nullable();
            $table->timestampsTz();
            $table->foreign('sale_id')->references('id')->on('sales')->cascadeOnDelete();
            $table->foreign('stock_item_id')->references('id')->on('stock_items')->restrictOnDelete();
            $table->foreign('product_unit_id')->references('id')->on('product_units')->restrictOnDelete();
            $table->foreign('inventory_movement_id')->references('id')->on('inventory_movements')->nullOnDelete();
            $table->unique(['sale_id', 'line_number']);
            $table->index('stock_item_id');
        });
        DB::statement('ALTER TABLE sale_lines ADD CONSTRAINT sale_lines_quantity_check CHECK (quantity > 0 AND conversion_factor > 0)');

        Schema::create('sale_payments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('sale_id');
            $table->unsignedSmallInteger('line_number');
            $table->uuid('payment_method_id');
            $table->decimal('amount_received', 18, 6);
            $table->decimal('amount_applied', 18, 6);
            $table->string('reference', 120)->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestampTz('paid_at')->useCurrent();
            $table->timestampsTz();
            $table->foreign('sale_id')->references('id')->on('sales')->cascadeOnDelete();
            $table->foreign('payment_method_id')->references('id')->on('payment_methods')->restrictOnDelete();
            $table->unique(['sale_id', 'line_number']);
        });
        DB::statement('ALTER TABLE sale_payments ADD CONSTRAINT sale_payments_amount_check CHECK (amount_received > 0 AND amount_applied > 0)');

        Schema::create('sale_returns', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('branch_id');
            $table->uuid('original_sale_id');
            $table->uuid('cash_session_id')->nullable();
            $table->string('return_number', 40);
            $table->string('status', 20)->default('draft');
            $table->decimal('total', 18, 6)->default(0);
            $table->string('reason_code', 50);
            $table->uuid('created_by');
            $table->timestampTz('confirmed_at')->nullable();
            $table->timestampsTz();
            $table->foreign('organization_id')->references('id')->on('organizations')->restrictOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches')->restrictOnDelete();
            $table->foreign('original_sale_id')->references('id')->on('sales')->restrictOnDelete();
            $table->foreign('cash_session_id')->references('id')->on('cash_sessions')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->restrictOnDelete();
            $table->unique(['organization_id', 'return_number']);
        });
        DB::statement("ALTER TABLE sale_returns ADD CONSTRAINT sale_returns_status_check CHECK (status IN ('draft', 'confirmed', 'cancelled'))");

        Schema::create('sale_return_lines', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('sale_return_id');
            $table->uuid('original_sale_line_id');
            $table->uuid('stock_item_id');
            $table->decimal('quantity', 18, 6);
            $table->decimal('line_total', 18, 6);
            $table->string('inventory_condition', 30)->default('resellable');
            $table->timestampsTz();
            $table->foreign('sale_return_id')->references('id')->on('sale_returns')->cascadeOnDelete();
            $table->foreign('original_sale_line_id')->references('id')->on('sale_lines')->restrictOnDelete();
            $table->foreign('stock_item_id')->references('id')->on('stock_items')->restrictOnDelete();
        });
        DB::statement("ALTER TABLE sale_return_lines ADD CONSTRAINT sale_return_lines_condition_check CHECK (inventory_condition IN ('resellable', 'damaged', 'discarded'))");

        Schema::create('expense_categories', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('code', 40);
            $table->string('name');
            $table->boolean('requires_approval')->default(false);
            $table->boolean('is_active')->default(true);
            $table->softDeletesTz();
            $table->timestampsTz();
            $table->foreign('organization_id')->references('id')->on('organizations')->restrictOnDelete();
        });
        DB::statement('CREATE UNIQUE INDEX expense_categories_organization_code_active_unique ON expense_categories (organization_id, code) WHERE deleted_at IS NULL');

        Schema::create('expenses', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('branch_id');
            $table->uuid('cash_session_id')->nullable();
            $table->uuid('expense_category_id');
            $table->uuid('supplier_id')->nullable();
            $table->string('expense_number', 40);
            $table->string('status', 20)->default('draft');
            $table->char('currency_code', 3)->default('MXN');
            $table->decimal('amount', 18, 6);
            $table->string('beneficiary')->nullable();
            $table->string('reference', 120)->nullable();
            $table->date('expense_date');
            $table->text('description');
            $table->uuid('requested_by');
            $table->uuid('approved_by')->nullable();
            $table->timestampTz('approved_at')->nullable();
            $table->timestampTz('paid_at')->nullable();
            $table->timestampsTz();
            $table->foreign('organization_id')->references('id')->on('organizations')->restrictOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches')->restrictOnDelete();
            $table->foreign('cash_session_id')->references('id')->on('cash_sessions')->nullOnDelete();
            $table->foreign('expense_category_id')->references('id')->on('expense_categories')->restrictOnDelete();
            $table->foreign('supplier_id')->references('id')->on('suppliers')->nullOnDelete();
            $table->foreign('requested_by')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
            $table->unique(['organization_id', 'expense_number']);
        });
        DB::statement("ALTER TABLE expenses ADD CONSTRAINT expenses_status_check CHECK (status IN ('draft', 'pending_approval', 'approved', 'rejected', 'paid', 'cancelled'))");
        DB::statement('ALTER TABLE expenses ADD CONSTRAINT expenses_amount_check CHECK (amount > 0)');

        Schema::create('cash_movements', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('branch_id');
            $table->uuid('cash_session_id');
            $table->uuid('payment_method_id')->nullable();
            $table->string('movement_type', 30);
            $table->decimal('amount', 18, 6);
            $table->string('source_type', 100);
            $table->uuid('source_id')->nullable();
            $table->string('reason_code', 50)->nullable();
            $table->text('notes')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestampTz('occurred_at')->useCurrent();
            $table->foreign('organization_id')->references('id')->on('organizations')->restrictOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches')->restrictOnDelete();
            $table->foreign('cash_session_id')->references('id')->on('cash_sessions')->restrictOnDelete();
            $table->foreign('payment_method_id')->references('id')->on('payment_methods')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['cash_session_id', 'occurred_at']);
            $table->index(['source_type', 'source_id']);
        });
        DB::statement("ALTER TABLE cash_movements ADD CONSTRAINT cash_movements_type_check CHECK (movement_type IN ('opening_float', 'sale_payment', 'sale_change', 'return_payment', 'expense', 'withdrawal', 'income', 'deposit', 'closing_adjustment'))");
        DB::statement('ALTER TABLE cash_movements ADD CONSTRAINT cash_movements_amount_check CHECK (amount > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_movements');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expense_categories');
        Schema::dropIfExists('sale_return_lines');
        Schema::dropIfExists('sale_returns');
        Schema::dropIfExists('sale_payments');
        Schema::dropIfExists('sale_lines');
        Schema::dropIfExists('sales');
        Schema::dropIfExists('cash_sessions');
        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('customers');
    }
};
