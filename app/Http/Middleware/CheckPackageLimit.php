<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPackageLimit
{
    /**
     * Handle an incoming request to check package limits.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $limitType): Response
    {
        $user = auth()->user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->isAdmin()) {
            return $next($request);
        }

        switch ($limitType) {
            case 'cte_record':
                if (!$user->canCreateCteRecord()) {
                    $message = $user->isFree() && $user->hasTrialExpired() 
                        ? __('messages.trial_expired_upgrade_required')
                        : __('messages.cte_record_limit_reached');
                    return back()->with('error', $message);
                }
                break;
                
            case 'document':
                if (!$user->canUploadDocument()) {
                    $message = $user->isFree() && $user->hasTrialExpired() 
                        ? __('messages.trial_expired_upgrade_required')
                        : __('messages.document_limit_reached');
                    return back()->with('error', $message);
                }
                break;
                
            case 'user':
                if (!$user->canCreateUser()) {
                    $message = $user->isFree() && $user->hasTrialExpired() 
                        ? __('messages.trial_expired_upgrade_required')
                        : __('messages.user_limit_reached');
                    return back()->with('error', $message);
                }
                break;
                
            default:
                // Unknown limit type, allow by default
                break;
        }

        return $next($request);
    }
}
