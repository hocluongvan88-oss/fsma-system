<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\QuotaAuditService;

class CheckPackageFeatureWithQuota
{
    protected QuotaAuditService $quotaService;

    public function __construct(QuotaAuditService $quotaService)
    {
        $this->quotaService = $quotaService;
    }

    /**
     * Handle an incoming request to check both feature access and quota limits.
     * New middleware that combines feature flag and quota checking
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $user = auth()->user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        // Admins bypass feature checks but still need quota validation
        if (!method_exists($user, 'isAdmin') || !$user->isAdmin()) {
            // Check if user has the required feature
            if (method_exists($user, 'hasFeature') && !$user->hasFeature($feature)) {
                $message = (method_exists($user, 'isFree') && $user->isFree() && method_exists($user, 'hasTrialExpired') && $user->hasTrialExpired())
                    ? __('messages.trial_expired_upgrade_required')
                    : __('messages.feature_not_available_in_package');
                
                return back()->with('error', $message);
            }
        }

        // Check quota limits for the feature
        $organization = $user->organization;
        if ($organization) {
            $quotaCheck = $this->quotaService->checkQuotaLimit($organization, $feature);
            
            if (!$quotaCheck['allowed']) {
                return back()->with('error', $quotaCheck['message'] ?? __('messages.quota_limit_exceeded'));
            }
        }

        return $next($request);
    }
}
