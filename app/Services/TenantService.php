<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class TenantService
{
    /**
     * Get current tenant from authenticated user
     */
    public static function getCurrentTenant(): ?Organization
    {
        if (!auth()->check()) {
            return null;
        }

        $user = auth()->user();
        
        if ($user->isAdmin()) {
            return null; // Admin không thuộc tenant cụ thể
        }

        return Organization::find($user->organization_id);
    }

    /**
     * Get current tenant ID
     */
    public static function getCurrentTenantId(): ?int
    {
        if (!auth()->check()) {
            return null;
        }

        $user = auth()->user();
        
        if ($user->isAdmin()) {
            return null;
        }

        return $user->organization_id;
    }

    /**
     * Check if user belongs to tenant
     */
    public static function userBelongsToTenant(User $user, int $tenantId): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->organization_id === $tenantId;
    }

    /**
     * Check if user can access tenant
     */
    public static function canAccessTenant(User $user, int $tenantId): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->organization_id === null) {
            return false;
        }

        return $user->organization_id === $tenantId;
    }

    /**
     * Log cross-tenant access attempt
     */
    public static function logCrossTenantAttempt(User $user, int $attemptedTenantId, string $action): void
    {
        Log::warning('Cross-tenant access attempt', [
            'user_id' => $user->id,
            'user_tenant_id' => $user->organization_id,
            'attempted_tenant_id' => $attemptedTenantId,
            'action' => $action,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Get all tenants (admin only)
     */
    public static function getAllTenants()
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            throw new \Exception('Only administrators can access all tenants');
        }

        return Organization::where('is_active', true)->get();
    }

    /**
     * Get tenant users
     */
    public static function getTenantUsers(int $tenantId)
    {
        $user = auth()->user();

        if (!self::canAccessTenant($user, $tenantId)) {
            throw new \Exception('You do not have access to this tenant');
        }

        return User::where('organization_id', $tenantId)
            ->where('is_active', true)
            ->get();
    }

    /**
     * Create tenant (admin only)
     */
    public static function createTenant(array $data): Organization
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            throw new \Exception('Only administrators can create tenants');
        }

        return Organization::create($data);
    }

    /**
     * Update tenant (admin only)
     */
    public static function updateTenant(int $tenantId, array $data): Organization
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            throw new \Exception('Only administrators can update tenants');
        }

        $tenant = Organization::findOrFail($tenantId);
        $tenant->update($data);

        Log::info('Tenant updated', [
            'admin_id' => auth()->id(),
            'tenant_id' => $tenantId,
            'changes' => array_keys($data),
        ]);

        return $tenant;
    }

    /**
     * Deactivate tenant (admin only)
     */
    public static function deactivateTenant(int $tenantId): void
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            throw new \Exception('Only administrators can deactivate tenants');
        }

        $tenant = Organization::findOrFail($tenantId);
        $tenant->update(['is_active' => false]);

        Log::warning('Tenant deactivated', [
            'admin_id' => auth()->id(),
            'tenant_id' => $tenantId,
        ]);
    }
}
