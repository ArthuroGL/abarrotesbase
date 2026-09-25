<?php

namespace App\Providers;

use App\Modules\Identity\Application\Services\CurrentContext;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(CurrentContext::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Blade::if('permission', function (string $permission): bool {
            return app(CurrentContext::class)
                ->hasPermission($permission);
        });
    }
}
