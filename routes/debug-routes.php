<?php

use Illuminate\Support\Facades\Route;

Route::get('/debug/routes-check', function() {
    $routes = collect(Route::getRoutes())->map(function($route) {
        return [
            'method' => implode('|', $route->methods()),
            'uri' => $route->uri(),
            'name' => $route->getName(),
            'action' => $route->getActionName(),
        ];
    });
    
    // Tìm tất cả routes có chứa 'retention'
    $retentionRoutes = $routes->filter(function($route) {
        return str_contains($route['uri'], 'retention') || 
               str_contains($route['name'] ?? '', 'retention');
    });
    
    return response()->json([
        'total_routes' => $routes->count(),
        'retention_routes' => $retentionRoutes->values(),
        'admin_retention_index_exists' => Route::has('admin.retention.index'),
        'features_file_loaded' => file_exists(base_path('routes/features.php')),
    ]);
})->name('debug.routes-check');
