<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAuditManagerAccess extends Middleware
{
    /**
     * Handle an incoming request.
     * FSMA 204 Compliance: Allows admins and audit managers to view retention logs
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            abort(401, 'Unauthenticated');
        }

        $user = auth()->user();
        
        // System admins always have access
        if ($user->isSystemAdmin()) {
            return $next($request);
        }

        // Organization admins have access
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Audit managers have access (role = 'audit_manager')
        if ($user->role === 'audit_manager') {
            return $next($request);
        }

        abort(403, 'Only administrators and audit managers can access retention logs.');
    }
}
