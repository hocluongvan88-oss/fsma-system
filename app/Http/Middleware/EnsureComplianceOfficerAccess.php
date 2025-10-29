<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureComplianceOfficerAccess extends Middleware
{
    /**
     * Handle an incoming request.
     * FSMA 204 Compliance: Allows admins and compliance officers to access retention management
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

        // Compliance officers have access (role = 'compliance_officer')
        if ($user->role === 'compliance_officer') {
            return $next($request);
        }

        abort(403, 'Only administrators and compliance officers can access retention management.');
    }
}
