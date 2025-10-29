<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use App\Services\OrganizationContext;

/**
 * AuditCrossOrgAccess Middleware
 * 
 * Logs and blocks any attempts to access resources from a different organization.
 * This middleware provides security audit trail for cross-organization access attempts.
 * 
 * SECURITY: Critical for detecting and preventing unauthorized cross-tenant access.
 */
class AuditCrossOrgAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user) {
            return $next($request);
        }

        // Admins are allowed to access any organization
        if ($user->isAdmin()) {
            return $next($request);
        }

        $resource = $this->getResourceFromRoute($request);
        
        if ($resource && isset($resource->organization_id)) {
            // Check if user is trying to access a resource from a different organization
            if ($resource->organization_id !== $user->organization_id) {
                Log::warning('Cross-organization access attempt detected', [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'user_organization_id' => $user->organization_id,
                    'resource_type' => get_class($resource),
                    'resource_id' => $resource->id,
                    'resource_organization_id' => $resource->organization_id,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'request_path' => $request->path(),
                    'request_method' => $request->method(),
                    'timestamp' => now()->toIso8601String(),
                ]);

                // Create audit log entry for security tracking
                try {
                    \App\Models\AuditLog::createLog([
                        'user_id' => $user->id,
                        'organization_id' => $user->organization_id,
                        'action' => 'CROSS_ORG_ACCESS_ATTEMPT',
                        'table_name' => get_class($resource),
                        'record_id' => $resource->id,
                        'old_values' => null,
                        'new_values' => [
                            'attempted_org_id' => $resource->organization_id,
                            'ip_address' => $request->ip(),
                            'user_agent' => $request->userAgent(),
                        ],
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to create audit log for cross-org access attempt', [
                        'error' => $e->getMessage(),
                    ]);
                }

                abort(403, __('messages.cannot_access_other_organization_data'));
            }
        }

        return $next($request);
    }

    /**
     * Get resource from route parameters
     * Supports all major resource types in the application
     * 
     * @param Request $request
     * @return mixed|null
     */
    private function getResourceFromRoute(Request $request)
    {
        $routeParams = [
            'user', 'document', 'product', 'location', 'partner', 
            'policy', 'event', 'signature', 'lead', 'notification',
            'traceRecord', 'cteEvent', 'auditLog', 'organization',
            'retentionPolicy', 'errorLog', 'exportLog', 'archivalLog'
        ];
        
        foreach ($routeParams as $param) {
            $resource = $request->route($param);
            if ($resource) {
                return $resource;
            }
        }

        return null;
    }
}
