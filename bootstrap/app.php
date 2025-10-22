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
        $middleware->web(append: [
            \App\Http\Middleware\SetAuditContext::class,
            \App\Http\Middleware\AuditTrail::class,
        ]);
        
        $middleware->alias([
            'admin' => \App\Http\Middleware\Admin::class,
            'admin.or.manager' => \App\Http\Middleware\AdminOrManagerMiddleware::class,
            'rate.limit.signatures' => \App\Http\Middleware\RateLimitSignatures::class,
        ]);
    })
    ->withProviders([
        \Barryvdh\DomPDF\ServiceProvider::class,
    ])
    ->withAliases([
        'Pdf' => \Barryvdh\DomPDF\Facade\Pdf::class,
    ])
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
