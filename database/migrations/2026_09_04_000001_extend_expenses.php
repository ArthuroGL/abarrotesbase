<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('expenses', function (Blueprint $table): void {
            $table->string('expense_type', 30)->default('operational')->after('status');
            $table->string('source_name', 150)->nullable()->after('supplier_id');
            $table->uuid('payment_method_id')->nullable()->after('cash_session_id');
            $table->text('rejection_reason')->nullable()->after('description');
            $table->timestampTz('cancelled_at')->nullable()->after('paid_at');
            $table->uuid('cancelled_by')->nullable()->after('cancelled_at');
            $table->foreign('payment_method_id')->references('id')->on('payment_methods')->nullOnDelete();
            $table->foreign('cancelled_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['organization_id','expense_type']);
            $table->index(['organization_id','expense_date']);
            $table->index(['organization_id','status']);
        });
        DB::statement("ALTER TABLE expenses ADD CONSTRAINT expenses_type_check CHECK (expense_type IN ('operational','stock_replenishment','investment','other'))");
    }
    public function down(): void {
        DB::statement('ALTER TABLE expenses DROP CONSTRAINT IF EXISTS expenses_type_check');
        Schema::table('expenses', function (Blueprint $table): void {
            $table->dropForeign(['payment_method_id']);
            $table->dropForeign(['cancelled_by']);
            $table->dropIndex(['organization_id','expense_type']);
            $table->dropIndex(['organization_id','expense_date']);
            $table->dropIndex(['organization_id','status']);
            $table->dropColumn(['expense_type','source_name','payment_method_id','rejection_reason','cancelled_at','cancelled_by']);
        });
    }
};
