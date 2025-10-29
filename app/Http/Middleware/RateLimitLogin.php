<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
class RateLimitLogin
{
    public function handle(Request $request, Closure $next)
    {
        $key = 'login:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json(['error' => 'Too many attempts'], 429);
        }
        RateLimiter::hit($key, 60);
        return $next($request);
    }
}
