<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Đăng ký Middleware Group cho web
        $middleware->web(append: [
            \App\Http\Middleware\SetAuditContext::class,
            \App\Http\Middleware\AuditTrail::class,
	    \App\Http\Middleware\SetLocale::class,
        ]);
        
        $middleware->alias([
            'admin' => \App\Http\Middleware\Admin::class,
            'admin.or.manager' => \App\Http\Middleware\AdminOrManagerMiddleware::class,
            'check.operator' => \App\Http\Middleware\CheckOperatorAccess::class,
            'check.organization' => \App\Http\Middleware\CheckOrganizationAccess::class,
            'package' => \App\Http\Middleware\CheckPackage::class,
            'package.limit' => \App\Http\Middleware\CheckPackageLimit::class,
            'package.feature' => \App\Http\Middleware\CheckPackageFeature::class,
            'ensure.organization' => \App\Http\Middleware\EnsureOrganizationAccess::class,
            'ensure.manager' => \App\Http\Middleware\EnsureManagerAccess::class,
            'ensure.admin' => \App\Http\Middleware\EnsureAdminAccess::class,
            'rate.limit.signatures' => \App\Http\Middleware\RateLimitSignatures::class,
            'rate.limit.trace' => \App\Http\Middleware\RateLimitTraceability::class,
        ]);
    })
    
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
