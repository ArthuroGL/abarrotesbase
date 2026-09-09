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
        Schema::create('payment_transactions', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->uuid('organization_id');
            $table->uuid('branch_id');

            $table->uuid('sale_id')->nullable();
            $table->uuid('expense_id')->nullable();

            $table->uuid('payment_method_id');

            $table->string('provider', 40);
            $table->string('provider_order_id', 120)->nullable();
            $table->string('provider_payment_id', 120)->nullable();
            $table->string('external_reference', 64);

            $table->decimal('amount', 18, 6);
            $table->string('currency_code', 3)->default('MXN');

            $table->string('status', 30)->default('pending');
            $table->string('status_detail', 120)->nullable();

            $table->string('idempotency_key', 100)->nullable();

            $table->jsonb('request_payload')->nullable();
            $table->jsonb('response_payload')->nullable();

            $table->timestampTz('paid_at')->nullable();

            $table->timestampsTz();

            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations')
                ->restrictOnDelete();

            $table->foreign('branch_id')
                ->references('id')
                ->on('branches')
                ->restrictOnDelete();

            $table->foreign('sale_id')
                ->references('id')
                ->on('sales')
                ->restrictOnDelete();

            $table->foreign('expense_id')
                ->references('id')
                ->on('expenses')
                ->restrictOnDelete();

            $table->foreign('payment_method_id')
                ->references('id')
                ->on('payment_methods')
                ->restrictOnDelete();

            $table->unique([
                'organization_id',
                'provider',
                'external_reference',
            ]);

            $table->unique([
                'provider',
                'provider_order_id',
            ]);

            $table->index([
                'organization_id',
                'branch_id',
                'status',
            ]);

            $table->index([
                'provider',
                'provider_payment_id',
            ]);

            $table->index('sale_id');
            $table->index('expense_id');
        });

        DB::statement("
            ALTER TABLE payment_transactions
            ADD CONSTRAINT payment_transactions_status_check
            CHECK (
                status IN (
                    'pending',
                    'processing',
                    'approved',
                    'rejected',
                    'cancelled',
                    'refunded',
                    'expired'
                )
            )
        ");

        DB::statement("
            ALTER TABLE payment_transactions
            ADD CONSTRAINT payment_transactions_amount_check
            CHECK (amount > 0)
        ");

        DB::statement("
            ALTER TABLE payment_transactions
            ADD CONSTRAINT payment_transactions_source_check
            CHECK (
                (sale_id IS NOT NULL AND expense_id IS NULL)
                OR
                (sale_id IS NULL AND expense_id IS NOT NULL)
            )
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
