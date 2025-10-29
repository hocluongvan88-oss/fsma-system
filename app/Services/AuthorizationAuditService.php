<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AuthorizationAuditService
{
    /**
     * Log authorization attempt (success or failure)
     */
    public function logAuthorizationAttempt(
        User $user,
        string $action,
        string $resource,
        bool $authorized,
        string $reason = '',
        array $context = []
    ): void {
        $logData = [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_role' => $user->role,
            'action' => $action,
            'resource' => $resource,
            'authorized' => $authorized,
            'reason' => $reason,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->url(),
            'method' => request()->method(),
            'timestamp' => now()->toIso8601String(),
        ];

        // Merge additional context
        $logData = array_merge($logData, $context);

        if ($authorized) {
            Log::info('Authorization granted', $logData);
        } else {
            Log::warning('Authorization denied', $logData);
        }

        // Store in database for audit trail
        $this->storeAuditLog($logData);
    }

    /**
     * Log admin access
     */
    public function logAdminAccess(User $user, string $action, array $context = []): void
    {
        $this->logAuthorizationAttempt(
            $user,
            $action,
            'admin_panel',
            true,
            'Admin access granted',
            $context
        );
    }

    /**
     * Log admin access denial
     */
    public function logAdminAccessDenial(User $user, string $action, string $reason = '', array $context = []): void
    {
        $this->logAuthorizationAttempt(
            $user,
            $action,
            'admin_panel',
            false,
            $reason ?: 'Admin privileges required',
            $context
        );
    }

    /**
     * Log feature access
     */
    public function logFeatureAccess(User $user, string $feature, bool $authorized, array $context = []): void
    {
        $this->logAuthorizationAttempt(
            $user,
            "access_feature",
            $feature,
            $authorized,
            $authorized ? "Feature '{$feature}' accessed" : "Feature '{$feature}' not available",
            $context
        );
    }

    /**
     * Log permission check
     */
    public function logPermissionCheck(User $user, string $permission, bool $authorized, array $context = []): void
    {
        $this->logAuthorizationAttempt(
            $user,
            "check_permission",
            $permission,
            $authorized,
            $authorized ? "Permission '{$permission}' granted" : "Permission '{$permission}' denied",
            $context
        );
    }

    /**
     * Log data access
     */
    public function logDataAccess(User $user, string $dataType, string $action, bool $authorized, array $context = []): void
    {
        $this->logAuthorizationAttempt(
            $user,
            $action,
            "data:{$dataType}",
            $authorized,
            $authorized ? "Data access granted" : "Data access denied",
            $context
        );
    }

    /**
     * Store audit log in database
     */
    private function storeAuditLog(array $logData): void
    {
        try {
            // Check if audit_logs table exists
            if (!DB::getSchemaBuilder()->hasTable('audit_logs')) {
                return;
            }

            DB::table('audit_logs')->insert([
                'user_id' => $logData['user_id'],
                'action' => $logData['action'],
                'resource' => $logData['resource'],
                'authorized' => $logData['authorized'],
                'reason' => $logData['reason'],
                'ip_address' => $logData['ip_address'],
                'user_agent' => $logData['user_agent'],
                'url' => $logData['url'],
                'method' => $logData['method'],
                'context' => json_encode($logData),
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to store audit log', [
                'error' => $e->getMessage(),
                'log_data' => $logData,
            ]);
        }
    }

    /**
     * Get authorization audit logs for a user
     */
    public function getUserAuditLogs(User $user, int $limit = 100)
    {
        if (!DB::getSchemaBuilder()->hasTable('audit_logs')) {
            return collect();
        }

        return DB::table('audit_logs')
            ->where('user_id', $user->id)
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get failed authorization attempts
     */
    public function getFailedAttempts(int $limit = 100)
    {
        if (!DB::getSchemaBuilder()->hasTable('audit_logs')) {
            return collect();
        }

        return DB::table('audit_logs')
            ->where('authorized', false)
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get failed attempts for a specific user
     */
    public function getUserFailedAttempts(User $user, int $limit = 50)
    {
        if (!DB::getSchemaBuilder()->hasTable('audit_logs')) {
            return collect();
        }

        return DB::table('audit_logs')
            ->where('user_id', $user->id)
            ->where('authorized', false)
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get suspicious activity (multiple failed attempts)
     */
    public function getSuspiciousActivity(int $failureThreshold = 5, int $minutesWindow = 15)
    {
        if (!DB::getSchemaBuilder()->hasTable('audit_logs')) {
            return collect();
        }

        $timeWindow = now()->subMinutes($minutesWindow);

        return DB::table('audit_logs')
            ->where('authorized', false)
            ->where('created_at', '>=', $timeWindow)
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) >= ?', [$failureThreshold])
            ->select('user_id', DB::raw('COUNT(*) as attempt_count'))
            ->get();
    }
}
