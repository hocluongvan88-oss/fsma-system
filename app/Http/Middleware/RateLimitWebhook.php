<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class RateLimitWebhook
{
    public function handle(Request $request, Closure $next)
    {
        $ip = $request->ip();
        $key = "webhook_{$ip}";
        
        if (RateLimiter::tooManyAttempts($key, 100)) {
            return response()->json(['error' => 'Too many requests'], 429);
        }

        RateLimiter::hit($key, 60); // 100 requests per 60 seconds

        return $next($request);
    }
}
