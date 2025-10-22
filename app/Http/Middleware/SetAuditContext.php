<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class SetAuditContext
{
    /**
     * Handle an incoming request.
     * Sets MySQL session variables for audit trail triggers
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            // Set current user ID for triggers
            DB::statement('SET @current_user_id = ?', [auth()->id()]);
        } else {
            DB::statement('SET @current_user_id = 0');
        }

        // Set client IP and user agent
        DB::statement('SET @client_ip = ?', [$request->ip()]);
        DB::statement('SET @user_agent = ?', [$request->userAgent()]);

        return $next($request);
    }
}
