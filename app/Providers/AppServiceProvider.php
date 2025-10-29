<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Route;
use App\Services\RateLimitingService;
use App\Models\Package;
use App\Traits\OrganizationScope;

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
        
        Route::bind('package', function ($value) {
            $package = Package::withoutGlobalScope(OrganizationScope::class)
                ->where('id', $value)
                ->first();
            
            if (!$package) {
                abort(404, 'Package not found');
            }
            
            return $package;
        });
    }
}
