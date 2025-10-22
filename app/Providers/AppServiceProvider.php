<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use App\Services\RateLimitingService;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(RateLimitingService::class, function ($app) {
            return new RateLimitingService();
        });
    }

    public function boot(): void
    {
        // Set default string length for MySQL
        Schema::defaultStringLength(191);
    }
}
