<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitTraceability
{
    /**
     * Handle an incoming request for traceability endpoints
     * 
     * Public trace: 30 requests per minute per IP
     * Authenticated: 100 requests per minute per user
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            // Authenticated users get higher limit
            $key = 'trace_auth_' . auth()->id();
            $maxAttempts = 100;
        } else {
            // Public users limited by IP
            $key = 'trace_public_' . $request->ip();
            $maxAttempts = 30;
        }
        
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            
            return response()->json([
                'success' => false,
                'message' => 'Too many requests. Please try again in ' . $seconds . ' seconds.',
                'retry_after' => $seconds
            ], 429);
        }
        
        RateLimiter::hit($key, 60); // 60 seconds decay
        
        $response = $next($request);
        
        // Add rate limit headers
        $response->headers->set('X-RateLimit-Limit', $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', RateLimiter::remaining($key, $maxAttempts));
        
        return $response;
    }
}
