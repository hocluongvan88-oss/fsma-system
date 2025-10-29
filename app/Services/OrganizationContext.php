<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;

/**
 * OrganizationContext Service
 * 
 * Centralized service for managing organization context and access control.
 * Provides methods to check current organization, admin status, and organization access.
 * Replaced isSystemAdmin() with isAdmin()
 * 
 * SECURITY: All organization-scoped queries should use this service to ensure consistent
 * multi-tenant isolation across the application.
 */
class OrganizationContext
{
    /**
     * Get the current user's organization ID
     * 
     * @return int|null
     */
    public static function current(): ?int
    {
        return Auth::user()?->organization_id;
    }

    /**
     * Check if current user is an administrator
     * Admins can access all organizations
     * Renamed from isSystemAdmin to isAdmin
     * 
     * @return bool
     */
    public static function isAdmin(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    /**
     * Check if current user can access a specific organization
     * Admins can access any organization
     * Regular users can only access their own organization
     * 
     * @param int $organizationId
     * @return bool
     */
    public static function canAccessOrganization(int $organizationId): bool
    {
        if (self::isAdmin()) {
            return true;
        }

        return self::current() === $organizationId;
    }

    /**
     * Check if current user can access a resource
     * Validates that the resource belongs to the user's organization
     * 
     * @param mixed $resource
     * @return bool
     */
    public static function canAccessResource($resource): bool
    {
        if (!Auth::check()) {
            return false;
        }

        if (self::isAdmin()) {
            return true;
        }

        // Check if resource has organization_id attribute
        if (!isset($resource->organization_id)) {
            return false;
        }

        return $resource->organization_id === self::current();
    }

    /**
     * Get the current user
     * 
     * @return \App\Models\User|null
     */
    public static function user()
    {
        return Auth::user();
    }

    /**
     * Check if user is authenticated
     * 
     * @return bool
     */
    public static function isAuthenticated(): bool
    {
        return Auth::check();
    }
}
