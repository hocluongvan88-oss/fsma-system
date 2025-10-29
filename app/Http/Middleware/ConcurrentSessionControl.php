<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Services\SecurityAuditService;

class ConcurrentSessionControl
{
    protected $securityAuditService;

    public function __construct(SecurityAuditService $securityAuditService)
    {
        $this->securityAuditService = $securityAuditService;
    }

    /**
     * Handle an incoming request.
     * 
     * Prevents concurrent sessions for the same user.
     * Only allows one active session per user at a time.
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $sessionKey = 'user_session_' . $user->id;
            $currentSessionId = session()->getId();

            // Get the previous session ID from cache
            $previousSessionId = Cache::get($sessionKey);

            if ($previousSessionId && $previousSessionId !== $currentSessionId) {
                $this->securityAuditService->logConcurrentSessionDetected(
                    $user,
                    $currentSessionId,
                    $previousSessionId
                );

                // Invalidate the previous session
                $this->invalidateSession($previousSessionId);

                // Log the user out from the previous session
                session()->forget('_token');
            }

            // Store current session ID in cache with TTL matching session lifetime
            $sessionLifetime = config('session.lifetime', 120);
            Cache::put($sessionKey, $currentSessionId, now()->addMinutes($sessionLifetime));
        }

        return $next($request);
    }

    /**
     * Invalidate a specific session
     */
    protected function invalidateSession(string $sessionId)
    {
        // Delete session from database
        \DB::table(config('session.table', 'sessions'))
            ->where('id', $sessionId)
            ->delete();
    }
}
