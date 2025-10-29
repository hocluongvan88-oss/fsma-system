<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasOrganizationAccess
{
    /**
     * Scope to filter by current user's organization
     * Replaced isSystemAdmin() with isAdmin()
     * 
     * SECURITY FIX: Proper distinction between Admin and Organization Manager
     */
    public function scopeForCurrentOrganization(Builder $query): Builder
    {
        $user = auth()->user();

        if (!$user) {
            return $query->whereNull('organization_id');
        }

        if ($user->isAdmin()) {
            return $query;
        }

        return $query->where('organization_id', $user->organization_id);
    }

    /**
     * Check if user can access this resource
     * 
     * SECURITY FIX: Strict organization boundary enforcement
     */
    public function canAccess($user = null): bool
    {
        $user = $user ?? auth()->user();

        if (!$user) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $this->organization_id === $user->organization_id;
    }

    /**
     * Check if user can edit this resource
     * 
     * SECURITY FIX: Role-based editing with organization boundary
     */
    public function canEdit($user = null): bool
    {
        $user = $user ?? auth()->user();

        if (!$user) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isManager() && $this->organization_id === $user->organization_id) {
            return true;
        }

        return false;
    }

    /**
     * Check if user can delete this resource
     * 
     * SECURITY FIX: Deletion restricted to managers and admins within organization
     */
    public function canDelete($user = null): bool
    {
        $user = $user ?? auth()->user();

        if (!$user) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isManager() && $this->organization_id === $user->organization_id) {
            return true;
        }

        return false;
    }
}
