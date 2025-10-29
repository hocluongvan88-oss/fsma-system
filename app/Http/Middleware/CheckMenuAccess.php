<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Helpers\MenuHelper;

class CheckMenuAccess extends \Illuminate\Routing\Middleware\ThrottleRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $permission
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $permission)
    {
        $user = auth()->user();

        if ($user && $user->isAdmin()) {
            return $next($request);
        }

        if (!MenuHelper::canAccess($permission)) {
            return response()->json([
                'success' => false,
                'message' => MenuHelper::getFeatureLockMessage($permission),
            ], 403);
        }

        return $next($request);
    }
}
