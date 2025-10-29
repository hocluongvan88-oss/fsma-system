<?php

// Diagnostic script to check routes and middleware
// Access via: php artisan tinker then: include 'routes/diagnostic.php'

use Illuminate\Support\Facades\Route;

echo "=== ROUTE DIAGNOSTICS ===\n\n";

// Check if audit-logs.index route exists
$routes = Route::getRoutes();
$auditRoute = $routes->getByName('audit-logs.index');

if ($auditRoute) {
    echo "✓ Route 'audit-logs.index' FOUND\n";
    echo "  URI: " . $auditRoute->uri . "\n";
    echo "  Methods: " . implode(', ', $auditRoute->methods) . "\n";
    echo "  Middleware: " . implode(', ', $auditRoute->middleware()) . "\n";
} else {
    echo "✗ Route 'audit-logs.index' NOT FOUND\n";
    echo "\nAvailable audit routes:\n";
    foreach ($routes as $route) {
        if (strpos($route->name, 'audit') !== false) {
            echo "  - " . $route->name . " (" . $route->uri . ")\n";
        }
    }
}

echo "\n=== MIDDLEWARE DIAGNOSTICS ===\n\n";

// Check if middleware is registered
$kernel = app(\App\Http\Kernel::class);
$middlewareAliases = $kernel->getMiddlewareAliases();

if (isset($middlewareAliases['ensure.organization'])) {
    echo "✓ Middleware 'ensure.organization' REGISTERED\n";
    echo "  Class: " . $middlewareAliases['ensure.organization'] . "\n";
    
    // Check if class exists
    $class = $middlewareAliases['ensure.organization'];
    if (class_exists($class)) {
        echo "  ✓ Class EXISTS\n";
    } else {
        echo "  ✗ Class DOES NOT EXIST\n";
    }
} else {
    echo "✗ Middleware 'ensure.organization' NOT REGISTERED\n";
}

echo "\n=== CONTROLLER DIAGNOSTICS ===\n\n";

// Check if AuditController exists
if (class_exists(\App\Http\Controllers\AuditController::class)) {
    echo "✓ AuditController EXISTS\n";
    
    $controller = new \App\Http\Controllers\AuditController(app(\App\Services\AuditLogService::class));
    if (method_exists($controller, 'index')) {
        echo "  ✓ Method 'index' EXISTS\n";
    } else {
        echo "  ✗ Method 'index' DOES NOT EXIST\n";
    }
} else {
    echo "✗ AuditController DOES NOT EXIST\n";
}
