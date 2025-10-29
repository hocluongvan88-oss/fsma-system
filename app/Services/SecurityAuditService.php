<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class SecurityAuditService
{
    protected $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    /**
     * Log authorization failure attempts
     */
    public function logAuthorizationFailure(
        $user,
        string $action,
        string $resource,
        array $context = []
    ): AuditLog {
        return $this->auditLogService->log(
            'AUTHORIZATION_FAILURE',
            $resource,
            $context['record_id'] ?? null,
            null,
            [
                'attempted_action' => $action,
                'reason' => $context['reason'] ?? 'Unauthorized',
                'user_id' => $user->id ?? null,
                'user_email' => $user->email ?? null,
                'user_role' => $user->role ?? null,
                'user_organization' => $user->organization_id ?? null,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]
        );
    }

    /**
     * Log privilege escalation attempts
     */
    public function logPrivilegeEscalationAttempt(
        $user,
        string $attemptedRole,
        array $context = []
    ): AuditLog {
        return $this->auditLogService->log(
            'PRIVILEGE_ESCALATION_ATTEMPT',
            'users',
            $context['target_user_id'] ?? null,
            null,
            [
                'current_role' => $user->role,
                'attempted_role' => $attemptedRole,
                'target_user_id' => $context['target_user_id'] ?? null,
                'reason' => $context['reason'] ?? 'Attempted unauthorized role assignment',
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]
        );
    }

    /**
     * Log suspicious activities (multiple failed attempts, etc.)
     */
    public function logSuspiciousActivity(
        $user,
        string $activityType,
        array $context = []
    ): AuditLog {
        return $this->auditLogService->log(
            'SUSPICIOUS_ACTIVITY',
            $context['table_name'] ?? 'unknown',
            $context['record_id'] ?? null,
            null,
            [
                'activity_type' => $activityType,
                'user_id' => $user->id ?? null,
                'user_email' => $user->email ?? null,
                'details' => $context['details'] ?? [],
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]
        );
    }

    /**
     * Log concurrent session detection
     */
    public function logConcurrentSessionDetected(
        $user,
        string $newSessionId,
        string $previousSessionId
    ): AuditLog {
        return $this->auditLogService->log(
            'CONCURRENT_SESSION_DETECTED',
            'sessions',
            null,
            null,
            [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'new_session_id' => $newSessionId,
                'previous_session_id' => $previousSessionId,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]
        );
    }

    /**
     * Log session timeout
     */
    public function logSessionTimeout($user): AuditLog
    {
        return $this->auditLogService->log(
            'SESSION_TIMEOUT',
            'sessions',
            null,
            null,
            [
                'user_id' => $user->id ?? null,
                'user_email' => $user->email ?? null,
                'reason' => 'Session expired due to inactivity',
            ]
        );
    }

    /**
     * Log mass assignment attempt
     */
    public function logMassAssignmentAttempt(
        $user,
        string $model,
        array $attemptedFields
    ): AuditLog {
        return $this->auditLogService->log(
            'MASS_ASSIGNMENT_ATTEMPT',
            $model,
            null,
            null,
            [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'attempted_fields' => $attemptedFields,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]
        );
    }
}
