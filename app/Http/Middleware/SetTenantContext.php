<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetTenantContext
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return $next($request);
        }

        try {
            $user = auth()->user();
            
            // Set tenant context in request attributes (not session to avoid memory buildup)
            $request->attributes->set('tenant_id', $user->organization_id);
            $request->attributes->set('is_admin', $user->isAdmin());
            
        } catch (\Exception $e) {
            // Log error but don't fail the request
            \Log::error('SetTenantContext middleware error: ' . $e->getMessage());
        }
        
        return $next($request);
    }
}
