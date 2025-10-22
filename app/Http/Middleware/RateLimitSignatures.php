<?php

namespace App\Http\Middleware;

use App\Services\RateLimitingService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RateLimitSignatures
{
    public function handle(Request $request, Closure $next, $limit = 60, $decay = 1): Response
    {
        $rateLimitService = app(RateLimitingService::class);
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // Determine action type
        $action = $this->getActionFromRoute($request);

        // Nếu route không cần giới hạn → bỏ qua middleware
        if (!$action) {
            return $next($request);
        }

        $identifier = $user->id;

        // Check if user is currently rate limited
        if ($rateLimitService->isRateLimited($action, $identifier)) {
            $timeRemaining = $rateLimitService->getLockoutTimeRemaining($action, $identifier);

            return response()->json([
                'error' => 'Too many attempts. Please try again later.',
                'retry_after' => $timeRemaining,
            ], 429);
        }

        // Record attempt
        $rateLimitService->recordAttempt($action, $identifier);

        return $next($request);
    }

    /**
     * Determine which type of rate limit applies based on route name.
     */
    private function getActionFromRoute(Request $request): ?string
    {
        $routeName = $request->route()?->getName();

        if (!$routeName) {
            return null;
        }

        // 🚫 Bỏ qua hoàn toàn các route chỉ hiển thị dữ liệu / dashboard
        if (str_contains($routeName, 'performance') ||
            str_contains($routeName, 'audit') ||
            str_contains($routeName, 'dashboard') ||
            str_contains($routeName, 'index') ||
            str_contains($routeName, 'show')) {
            return null;
        }

        // ✅ Áp dụng giới hạn cho các hành động quan trọng
        if (str_contains($routeName, 'sign')) {
            return 'signature_creation';
        }

        if (str_contains($routeName, 'verify')) {
            return 'signature_verification';
        }

        if (str_contains($routeName, 'revoke')) {
            return 'signature_revocation';
        }

        if (str_contains($routeName, '2fa') || str_contains($routeName, 'two-factor')) {
            return 'two_fa_attempts';
        }

        return null;
    }
}
