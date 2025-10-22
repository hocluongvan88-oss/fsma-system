<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\SuspiciousActivityAlert;

class RateLimitingService
{
    /**
     * Rate limiting configuration
     */
    private const LIMITS = [
        'signature_creation' => [
            'max_attempts' => 10,
            'window_minutes' => 15,
            'lockout_minutes' => 30,
        ],
        'signature_verification' => [
            'max_attempts' => 20,
            'window_minutes' => 15,
            'lockout_minutes' => 15,
        ],
        'password_attempts' => [
            'max_attempts' => 5,
            'window_minutes' => 15,
            'lockout_minutes' => 30,
        ],
        'two_fa_attempts' => [
            'max_attempts' => 5,
            'window_minutes' => 15,
            'lockout_minutes' => 30,
        ],
    ];

    /**
     * Check if action is rate limited
     */
    public function isRateLimited(string $action, string $identifier): bool
    {
        $key = $this->getCacheKey($action, $identifier);
        $lockoutKey = $this->getLockoutKey($action, $identifier);

        // Check if user is in lockout
        if (Cache::has($lockoutKey)) {
            Log::warning('Rate limit lockout active', [
                'action' => $action,
                'identifier' => $identifier,
            ]);
            return true;
        }

        return false;
    }

    /**
     * Record an attempt
     */
    public function recordAttempt(string $action, string $identifier, bool $success = true): void
    {
        $config = self::LIMITS[$action] ?? null;
        if (!$config) {
            return;
        }

        $key = $this->getCacheKey($action, $identifier);
        $attempts = Cache::get($key, 0);
        $attempts++;

        // Store attempt count
        Cache::put($key, $attempts, now()->addMinutes($config['window_minutes']));

        // Check if limit exceeded
        if ($attempts > $config['max_attempts']) {
            $this->triggerLockout($action, $identifier, $config);
        }

        // Log failed attempts
        if (!$success) {
            Log::warning('Failed attempt recorded', [
                'action' => $action,
                'identifier' => $identifier,
                'attempts' => $attempts,
                'max_allowed' => $config['max_attempts'],
            ]);
        }
    }

    /**
     * Reset attempts for an action
     */
    public function resetAttempts(string $action, string $identifier): void
    {
        $key = $this->getCacheKey($action, $identifier);
        Cache::forget($key);

        Log::info('Rate limit attempts reset', [
            'action' => $action,
            'identifier' => $identifier,
        ]);
    }

    /**
     * Get remaining attempts
     */
    public function getRemainingAttempts(string $action, string $identifier): int
    {
        $config = self::LIMITS[$action] ?? null;
        if (!$config) {
            return -1;
        }

        $key = $this->getCacheKey($action, $identifier);
        $attempts = Cache::get($key, 0);

        return max(0, $config['max_attempts'] - $attempts);
    }

    /**
     * Get lockout time remaining (in seconds)
     */
    public function getLockoutTimeRemaining(string $action, string $identifier): int
    {
        $lockoutKey = $this->getLockoutKey($action, $identifier);
        
        try {
            // Try to get TTL from cache store
            $store = Cache::getStore();
            if (method_exists($store, 'connection') && method_exists($store->connection(), 'ttl')) {
                $ttl = $store->connection()->ttl($lockoutKey);
                return max(0, $ttl);
            }
            
            // Fallback: check if key exists and estimate remaining time
            if (Cache::has($lockoutKey)) {
                // Return a reasonable default (e.g., 30 seconds) if we can't get exact TTL
                return 30;
            }
            
            return 0;
        } catch (\Exception $e) {
            // If TTL retrieval fails, check if key exists
            return Cache::has($lockoutKey) ? 30 : 0;
        }
    }

    /**
     * Trigger lockout
     */
    private function triggerLockout(string $action, string $identifier, array $config): void
    {
        $lockoutKey = $this->getLockoutKey($action, $identifier);

        // Set lockout
        Cache::put($lockoutKey, true, now()->addMinutes($config['lockout_minutes']));

        // Log security event
        Log::alert('Rate limit lockout triggered', [
            'action' => $action,
            'identifier' => $identifier,
            'lockout_minutes' => $config['lockout_minutes'],
        ]);

        // Send alert email if identifier is a user ID
        if (is_numeric($identifier)) {
            $this->sendLockoutAlert($action, $identifier, $config['lockout_minutes']);
        }
    }

    /**
     * Send lockout alert email
     */
    private function sendLockoutAlert(string $action, string $userId, int $lockoutMinutes): void
    {
        try {
            $user = \App\Models\User::find($userId);
            if ($user && $user->email) {
                Mail::to($user->email)->queue(new SuspiciousActivityAlert(
                    $user,
                    $action,
                    $lockoutMinutes
                ));
            }
        } catch (\Exception $e) {
            Log::error('Failed to send lockout alert: ' . $e->getMessage());
        }
    }

    /**
     * Get cache key for rate limiting
     */
    private function getCacheKey(string $action, string $identifier): string
    {
        return "rate_limit:{$action}:{$identifier}";
    }

    /**
     * Get lockout cache key
     */
    private function getLockoutKey(string $action, string $identifier): string
    {
        return "rate_limit_lockout:{$action}:{$identifier}";
    }

    /**
     * Get rate limit statistics
     */
    public function getStatistics(string $identifier): array
    {
        $stats = [];

        foreach (array_keys(self::LIMITS) as $action) {
            $key = $this->getCacheKey($action, $identifier);
            $lockoutKey = $this->getLockoutKey($action, $identifier);
            $attempts = Cache::get($key, 0);
            $isLocked = Cache::has($lockoutKey);

            $stats[$action] = [
                'attempts' => $attempts,
                'is_locked' => $isLocked,
                'remaining_attempts' => $this->getRemainingAttempts($action, $identifier),
                'lockout_time_remaining' => $isLocked ? $this->getLockoutTimeRemaining($action, $identifier) : 0,
            ];
        }

        return $stats;
    }
}
