<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantAccess
{
    /**
     * Handle an incoming request.
     * Ensures user can only access resources within their tenant.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            abort(401, 'Unauthorized');
        }

        $user = auth()->user();
        $tenantId = $request->attributes->get('tenant_id');

        // Admin can access everything
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Regular users can only access their own tenant
        if ($user->organization_id !== $tenantId) {
            abort(403, 'You do not have access to this resource.');
        }

        return $next($request);
    }
}
