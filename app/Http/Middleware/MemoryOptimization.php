<?php

namespace App\Http\Middleware;

use App\Services\MemoryOptimizationService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MemoryOptimization
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check memory at start
        MemoryOptimizationService::checkMemoryThreshold(70);

        $response = $next($request);

        // Check memory at end
        MemoryOptimizationService::checkMemoryThreshold(80);
        
        // Clear unused memory
        MemoryOptimizationService::clearMemory();

        return $response;
    }
}
