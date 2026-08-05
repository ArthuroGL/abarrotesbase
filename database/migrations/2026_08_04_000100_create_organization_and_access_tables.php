<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('legal_name');
            $table->string('display_name');
            $table->char('currency_code', 3)->default('MXN');
            $table->string('timezone')->default('America/Mexico_City');
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();
        });

        Schema::create('branches', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('code', 30);
            $table->string('name');
            $table->string('phone', 30)->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();
            $table->foreign('organization_id')->references('id')->on('organizations')->restrictOnDelete();
            $table->unique(['organization_id', 'code']);
            $table->index(['organization_id', 'is_active']);
        });

        Schema::create('registers', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('branch_id');
            $table->string('code', 30);
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();
            $table->foreign('organization_id')->references('id')->on('organizations')->restrictOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches')->restrictOnDelete();
            $table->unique(['branch_id', 'code']);
            $table->index(['organization_id', 'branch_id', 'is_active']);
        });

        Schema::create('organization_users', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('user_id');
            $table->boolean('is_active')->default(true);
            $table->timestampTz('joined_at')->useCurrent();
            $table->timestampsTz();
            $table->foreign('organization_id')->references('id')->on('organizations')->restrictOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
            $table->unique(['organization_id', 'user_id']);
        });

        Schema::create('user_branches', function (Blueprint $table): void {
            $table->uuid('organization_user_id');
            $table->uuid('branch_id');
            $table->timestampsTz();
            $table->primary(['organization_user_id', 'branch_id']);
            $table->foreign('organization_user_id')->references('id')->on('organization_users')->cascadeOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches')->restrictOnDelete();
        });

        Schema::create('roles', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->nullable();
            $table->string('code', 100);
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestampsTz();
            $table->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
            $table->unique(['organization_id', 'code']);
        });

        Schema::create('permissions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('code', 150)->unique();
            $table->string('module', 80);
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestampsTz();
        });

        Schema::create('role_permissions', function (Blueprint $table): void {
            $table->uuid('role_id');
            $table->uuid('permission_id');
            $table->timestampsTz();
            $table->primary(['role_id', 'permission_id']);
            $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
            $table->foreign('permission_id')->references('id')->on('permissions')->cascadeOnDelete();
        });

        Schema::create('organization_user_roles', function (Blueprint $table): void {
            $table->uuid('organization_user_id');
            $table->uuid('role_id');
            $table->timestampsTz();
            $table->primary(['organization_user_id', 'role_id']);
            $table->foreign('organization_user_id')->references('id')->on('organization_users')->cascadeOnDelete();
            $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
        });

        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('branch_id')->nullable();
            $table->uuid('actor_id')->nullable();
            $table->string('action', 150);
            $table->string('auditable_type', 150);
            $table->uuid('auditable_id')->nullable();
            $table->jsonb('before_values')->nullable();
            $table->jsonb('after_values')->nullable();
            $table->string('request_id', 100)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestampTz('occurred_at')->useCurrent();
            $table->foreign('organization_id')->references('id')->on('organizations')->restrictOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
            $table->foreign('actor_id')->references('id')->on('users')->nullOnDelete();
            $table->index(['organization_id', 'occurred_at']);
            $table->index(['auditable_type', 'auditable_id']);
        });

        Schema::create('outbox_events', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('event_type', 150);
            $table->jsonb('payload');
            $table->timestampTz('occurred_at')->useCurrent();
            $table->timestampTz('available_at')->useCurrent();
            $table->timestampTz('published_at')->nullable();
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->text('last_error')->nullable();
            $table->foreign('organization_id')->references('id')->on('organizations')->restrictOnDelete();
            $table->index(['published_at', 'available_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outbox_events');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('organization_user_roles');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('user_branches');
        Schema::dropIfExists('organization_users');
        Schema::dropIfExists('registers');
        Schema::dropIfExists('branches');
        Schema::dropIfExists('organizations');
    }
};
