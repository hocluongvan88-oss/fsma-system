<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Services\SecurityAuditService;

class SessionTimeout
{
    protected $securityAuditService;

    public function __construct(SecurityAuditService $securityAuditService)
    {
        $this->securityAuditService = $securityAuditService;
    }

    /**
     * Handle an incoming request.
     * 
     * Implements session timeout based on inactivity.
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $sessionTimeout = config('session.lifetime', 120); // minutes
            $lastActivityKey = 'last_activity_' . $user->id;
            $lastActivity = session()->get($lastActivityKey);

            if ($lastActivity && now()->diffInMinutes($lastActivity) > $sessionTimeout) {
                $locale = Session::get('locale') ?? app()->getLocale();
                
                $this->securityAuditService->logSessionTimeout($user);

                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                Session::put('locale', $locale);

                return redirect()->route('login')
                    ->with('message', __('messages.session_expired'));
            }

            // Update last activity timestamp
            session()->put($lastActivityKey, now());
        }

        return $next($request);
    }
}
