<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasOrganizationAccess
{
    /**
     * Scope to filter by current user's organization
     */
    public function scopeForCurrentOrganization(Builder $query): Builder
    {
        $user = auth()->user();

        if (!$user) {
            return $query->whereNull('organization_id');
        }

        // Admin can see all organizations
        if ($user->isAdmin()) {
            return $query;
        }

        return $query->where('organization_id', $user->organization_id);
    }

    /**
     * Check if user can access this resource
     */
    public function canAccess($user = null): bool
    {
        $user = $user ?? auth()->user();

        if (!$user) {
            return false;
        }

        // Admin can access everything
        if ($user->isAdmin()) {
            return true;
        }

        // Check organization match
        return $this->organization_id === $user->organization_id;
    }

    /**
     * Check if user can edit this resource
     */
    public function canEdit($user = null): bool
    {
        $user = $user ?? auth()->user();

        if (!$user) {
            return false;
        }

        // Admin can edit everything
        if ($user->isAdmin()) {
            return true;
        }

        // Manager can edit within their organization
        if ($user->isManager() && $this->organization_id === $user->organization_id) {
            return true;
        }

        return false;
    }

    /**
     * Check if user can delete this resource
     */
    public function canDelete($user = null): bool
    {
        return $this->canEdit($user);
    }
}
