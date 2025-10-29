<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOrManagerMiddleware
{
    /**
     * Handle an incoming request.
     * Allow both Admin and Organization Manager roles to access user management
     * Replaced isSystemAdmin() with isAdmin()
     * 
     * SECURITY FIX: Ensures managers can only manage users within their own organization
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        
        if (!$user) {
            abort(403, 'Unauthorized access. Authentication required.');
        }
        
        // Admin has full access
        if ($user->isAdmin()) {
            return $next($request);
        }
        
        // Organization Manager can only access their own organization
        if ($user->isManager()) {
            // If accessing a specific user resource, verify organization match
            $targetUser = $request->route('user');
            if ($targetUser && $targetUser->organization_id !== $user->organization_id) {
                abort(403, 'Cannot access users from other organizations.');
            }
            
            return $next($request);
        }
        
        abort(403, 'Unauthorized access. Admin or Manager privileges required.');
    }
}
