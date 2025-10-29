<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class RateLimitPayment
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        
        if (!$user) {
            return $next($request);
        }

        $key = "payment_checkout_{$user->id}";
        
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->with('error', 'Too many payment attempts. Please try again in ' . RateLimiter::availableIn($key) . ' seconds.');
        }

        RateLimiter::hit($key, 60); // 5 attempts per 60 seconds

        return $next($request);
    }
}
