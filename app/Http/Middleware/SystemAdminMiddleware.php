<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SystemAdminMiddleware
{
    /**
     * Middleware để kiểm tra quyền system admin cao nhất
     * Chỉ cho phép user có is_system_admin = true
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !auth()->user()->isSystemAdmin()) {
            abort(403, 'Unauthorized access. System Admin privileges required.');
        }

        return $next($request);
    }
}
