<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckOperatorAccess
{
    /**
     * Handle an incoming request.
     * Prevents operators from accessing admin/users routes
     * Only Admin and Manager can create/manage users
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->role === 'operator') {
            abort(403, __('messages.operator_cannot_access_user_management'));
        }

        return $next($request);
    }
}
