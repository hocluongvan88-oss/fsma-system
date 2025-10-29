<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class AuthorizationService
{
    /**
     * Check if user has admin access
     */
    public function isAdmin(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Check if user is manager or admin
     */
    public function isManagerOrAdmin(User $user): bool
    {
        return $user->isManager();
    }

    /**
     * Check if user has a specific feature
     */
    public function hasFeature(User $user, string $feature): bool
    {
        // Admins always have all features
        if ($this->isAdmin($user)) {
            return true;
        }

        return $user->hasFeature($feature);
    }

    /**
     * Check if user has a specific permission
     */
    public function hasPermission(User $user, string $permission): bool
    {
        $permissionService = new PermissionService();
        return $permissionService->hasPermission($user, $permission);
    }

    /**
     * Check if user has any of the given permissions
     */
    public function hasAnyPermission(User $user, array $permissions): bool
    {
        $permissionService = new PermissionService();
        return $permissionService->hasAnyPermission($user, $permissions);
    }

    /**
     * Check if user has all of the given permissions
     */
    public function hasAllPermissions(User $user, array $permissions): bool
    {
        $permissionService = new PermissionService();
        return $permissionService->hasAllPermissions($user, $permissions);
    }

    /**
     * Check if user can access a resource
     */
    public function canAccessResource(User $user, string $resource, string $action = 'view'): bool
    {
        $permissionService = new PermissionService();
        return $permissionService->canAccessResource($user, $resource, $action);
    }

    /**
     * Check if user can manage another user
     */
    public function canManageUser(User $currentUser, User $targetUser): bool
    {
        return $currentUser->canManageUser($targetUser);
    }

    /**
     * Check if user can view another user
     */
    public function canViewUser(User $currentUser, User $targetUser): bool
    {
        return $currentUser->canViewUser($targetUser);
    }

    /**
     * Verify authorization and log the result
     * Throws exception if unauthorized
     */
    public function authorize(User $user, string $action, string $resource = null, array $context = []): void
    {
        $authorized = false;
        $reason = '';

        // Check admin access
        if (str_contains($action, 'admin')) {
            $authorized = $this->isAdmin($user);
            $reason = 'Admin access required';
        }
        // Check feature access
        elseif (str_contains($action, 'feature:')) {
            $feature = str_replace('feature:', '', $action);
            $authorized = $this->hasFeature($user, $feature);
            $reason = "Feature '{$feature}' required";
        }
        // Check permission access
        else {
            $authorized = $this->hasPermission($user, $action);
            $reason = "Permission '{$action}' required";
        }

        if (!$authorized) {
            $this->logAuthorizationFailure($user, $action, $resource, $reason, $context);
            abort(403, "Unauthorized: {$reason}");
        }

        $this->logAuthorizationSuccess($user, $action, $resource, $context);
    }

    /**
     * Log successful authorization
     */
    private function logAuthorizationSuccess(User $user, string $action, ?string $resource, array $context = []): void
    {
        Log::info('Authorization granted', array_merge([
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_role' => $user->role,
            'action' => $action,
            'resource' => $resource,
            'timestamp' => now()->toIso8601String(),
        ], $context));
    }

    /**
     * Log failed authorization attempt
     */
    private function logAuthorizationFailure(User $user, string $action, ?string $resource, string $reason, array $context = []): void
    {
        Log::warning('Authorization denied', array_merge([
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_role' => $user->role,
            'action' => $action,
            'resource' => $resource,
            'reason' => $reason,
            'timestamp' => now()->toIso8601String(),
        ], $context));
    }
}
