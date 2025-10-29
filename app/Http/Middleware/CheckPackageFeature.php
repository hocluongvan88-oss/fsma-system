<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPackageFeature
{
    /**
     * Handle an incoming request to check if user has access to a specific feature.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $user = auth()->user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return $next($request);
        }

        if (method_exists($user, 'hasFeature') && !$user->hasFeature($feature)) {
            $message = (method_exists($user, 'isFree') && $user->isFree() && method_exists($user, 'hasTrialExpired') && $user->hasTrialExpired())
                ? __('messages.trial_expired_upgrade_required')
                : __('messages.feature_not_available_in_package');
            
            return back()->with('error', $message);
        }

        return $next($request);
    }
}
