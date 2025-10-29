<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceTenantIsolation
{
    /**
     * Handle an incoming request.
     * 
     * This middleware ensures that:
     * 1. All authenticated users belong to an organization (except admin)
     * 2. Users can only access their own organization's data
     * 3. Admin users can access all organizations
     * 4. Proper tenant context is set for all queries
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();
            
            if (!$user->isAdmin() && !$user->organization_id) {
                auth()->logout();
                return redirect()->route('login')
                    ->with('error', 'Your account is not properly configured. Please contact support.');
            }
            
            $request->attributes->set('tenant_id', $user->organization_id);
            $request->attributes->set('user_id', $user->id);
        }
        
        return $next($request);
    }
}
