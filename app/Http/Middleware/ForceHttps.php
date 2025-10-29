<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
class ForceHttps
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->secure() && config('app.env') === 'production') {
            return redirect()->secure($request->getRequestUri(), 301);
        }
        return $next($request);
    }
}
