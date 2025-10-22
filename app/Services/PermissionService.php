<?php

namespace App\Services;

use App\Models\User;

class PermissionService
{
    /**
     * Define role-based permissions
     */
    protected $rolePermissions = [
        'admin' => [
            // User Management
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'users.reset-password',
            'users.update-package',
            
            // Document Management
            'documents.view',
            'documents.create',
            'documents.edit',
            'documents.delete',
            'documents.approve',
            'documents.download',
            
            // Audit & Reports
            'audit-logs.view',
            'audit-logs.export',
            'reports.view',
            'reports.export',
            
            // Master Data
            'master-data.view',
            'master-data.create',
            'master-data.edit',
            'master-data.delete',
            
            // CTE Data
            'cte.view',
            'cte.create',
            'cte.edit',
            'cte.delete',
            
            // Admin Features
            'packages.manage',
            'e-signatures.manage',
            'compliance-reports.view',
        ],
        
        'manager' => [
            // User Management (limited)
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'users.reset-password',
            
            // Document Management
            'documents.view',
            'documents.create',
            'documents.edit',
            'documents.delete',
            'documents.approve',
            'documents.download',
            
            // Audit & Reports
            'audit-logs.view',
            'reports.view',
            'reports.export',
            
            // Master Data
            'master-data.view',
            'master-data.create',
            'master-data.edit',
            'master-data.delete',
            
            // CTE Data
            'cte.view',
            'cte.create',
            'cte.edit',
            'cte.delete',
        ],
        
        'operator' => [
            // Document Management (limited)
            'documents.view',
            'documents.create',
            'documents.download',
            
            // Master Data (view only)
            'master-data.view',
            
            // CTE Data
            'cte.view',
            'cte.create',
            'cte.edit',
        ],
    ];

    /**
     * Check if user has permission
     */
    public function hasPermission(User $user, string $permission): bool
    {
        // Admin has all permissions
        if ($user->isAdmin()) {
            return true;
        }

        $permissions = $this->rolePermissions[$user->role] ?? [];

        return in_array($permission, $permissions);
    }

    /**
     * Check if user has any of the given permissions
     */
    public function hasAnyPermission(User $user, array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($user, $permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has all of the given permissions
     */
    public function hasAllPermissions(User $user, array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($user, $permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get all permissions for a role
     */
    public function getPermissionsForRole(string $role): array
    {
        return $this->rolePermissions[$role] ?? [];
    }

    /**
     * Get all permissions for a user
     */
    public function getPermissionsForUser(User $user): array
    {
        if ($user->isAdmin()) {
            return array_merge(...array_values($this->rolePermissions));
        }

        return $this->getPermissionsForRole($user->role);
    }

    /**
     * Check if user can perform action on resource
     */
    public function canAccessResource(User $user, string $resource, string $action = 'view'): bool
    {
        $permission = "{$resource}.{$action}";
        return $this->hasPermission($user, $permission);
    }
}
