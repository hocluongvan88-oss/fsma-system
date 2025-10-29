<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class CheckPackage
{
    /**
     * Handle an incoming request.
     * Updated to check organization package instead of user package
     */
    public function handle(Request $request, Closure $next, string $requiredPackage): Response
    {
        try {
            $user = $request->user();

            if (!$user) {
                return redirect()->route('login');
            }

            $organization = $user->organization;
            if (!$organization) {
                return redirect()->route('organization.select')
                    ->with('error', 'Please select an organization to continue.');
            }

            // Package hierarchy: free = 0, basic = 1, premium = 2, enterprise = 3
            $packageLevels = [
                'free' => 0,
                'basic' => 1,
                'premium' => 2,
                'enterprise' => 3,
            ];

            $organizationLevel = $packageLevels[$organization->package_id] ?? 0;
            $requiredLevel = $packageLevels[$requiredPackage] ?? 0;

            if ($organizationLevel < $requiredLevel) {
                try {
                    $notificationService = new \App\Services\NotificationService();
                    $notificationService->sendFeatureLocked($user, $requiredPackage);
                } catch (\Exception $e) {
                    Log::warning('Failed to send feature locked notification: ' . $e->getMessage());
                }

                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => "This feature requires {$requiredPackage} package or higher.",
                        'blocked' => true,
                    ], 403);
                }

                return redirect()->route('pricing')
                    ->with('error', "This feature requires {$requiredPackage} package or higher. Please upgrade your subscription.");
            }

            return $next($request);
            
        } catch (\Exception $e) {
            Log::error('CheckPackage middleware error: ' . $e->getMessage(), [
                'user_id' => $request->user()?->id,
                'required_package' => $requiredPackage,
                'trace' => $e->getTraceAsString()
            ]);
            
            // Fail open - allow access if middleware has errors
            return $next($request);
        }
    }
}
