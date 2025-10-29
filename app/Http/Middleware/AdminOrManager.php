<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOrManager
{
    /**
     * Handle an incoming request.
     * 
     * IMPORTANT: This middleware checks role only, NOT organization scope.
     * Controllers MUST implement organization filtering separately.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            abort(403, 'Unauthorized access - Authentication required');
        }

        $user = auth()->user();

        // Only admin or manager roles can proceed
        if (!$user->isManager()) {
            abort(403, 'Unauthorized access - Admin or Manager role required');
        }

        return $next($request);
    }
}
