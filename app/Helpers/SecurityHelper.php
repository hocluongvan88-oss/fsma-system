<?php

namespace App\Helpers;

use App\Services\OrganizationContext;
use Illuminate\Support\Facades\Log;

/**
 * SecurityHelper
 * 
 * Helper functions for security operations and organization isolation checks.
 */
class SecurityHelper
{
    /**
     * Check if a user can access a resource
     * 
     * @param mixed $resource
     * @param \App\Models\User|null $user
     * @return bool
     */
    public static function canAccessResource($resource, $user = null): bool
    {
        $user = $user ?? auth()->user();

        if (!$user) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if (!isset($resource->organization_id)) {
            return false;
        }

        return $resource->organization_id === $user->organization_id;
    }

    /**
     * Log a security event
     * 
     * @param string $event
     * @param array $context
     * @return void
     */
    public static function logSecurityEvent(string $event, array $context = []): void
    {
        if (!config('security.audit_logging.enabled')) {
            return;
        }

        $defaultContext = [
            'user_id' => auth()->id(),
            'organization_id' => OrganizationContext::current(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toIso8601String(),
        ];

        Log::warning("SECURITY_EVENT: {$event}", array_merge($defaultContext, $context));
    }

    /**
     * Log a cross-organization access attempt
     * 
     * @param mixed $resource
     * @return void
     */
    public static function logCrossOrgAccessAttempt($resource): void
    {
        if (!config('security.audit_logging.log_cross_org_attempts')) {
            return;
        }

        self::logSecurityEvent('CROSS_ORG_ACCESS_ATTEMPT', [
            'resource_type' => get_class($resource),
            'resource_id' => $resource->id ?? null,
            'resource_org_id' => $resource->organization_id ?? null,
            'attempted_org_id' => OrganizationContext::current(),
        ]);
    }

    /**
     * Verify organization context
     * 
     * @param int $organizationId
     * @return bool
     */
    public static function verifyOrganizationContext(int $organizationId): bool
    {
        return OrganizationContext::canAccessOrganization($organizationId);
    }

    /**
     * Get security configuration
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function getConfig(string $key, $default = null)
    {
        return config("security.{$key}", $default);
    }
}
