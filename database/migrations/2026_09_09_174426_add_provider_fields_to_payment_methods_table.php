<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_methods', function (Blueprint $table): void {
            $table->string('provider', 40)
                ->default('internal')
                ->after('type');

            $table->string('provider_code', 60)
                ->nullable()
                ->after('provider');

            $table->index([
                'organization_id',
                'provider',
                'provider_code',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('payment_methods', function (Blueprint $table): void {
            $table->dropIndex([
                'payment_methods_organization_id_provider_provider_code_index',
            ]);

            $table->dropColumn([
                'provider',
                'provider_code',
            ]);
        });
    }
};
