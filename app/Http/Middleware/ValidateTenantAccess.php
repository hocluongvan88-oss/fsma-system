<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateTenantAccess
{
    /**
     * Validate that user can access the requested tenant resource.
     * 
     * This middleware should be applied to routes that accept tenant_id or organization_id parameters.
     */
    public function handle(Request $request, Closure $next, string $paramName = 'organization_id'): Response
    {
        $user = auth()->user();
        $requestedTenantId = $request->route($paramName);
        
        if ($user->isAdmin()) {
            return $next($request);
        }
        
        if ($requestedTenantId && $requestedTenantId != $user->organization_id) {
            abort(403, 'You do not have access to this organization.');
        }
        
        return $next($request);
    }
}
