<?php

namespace App\Policies;

use App\Models\User;
use App\Models\RetentionPolicy;

class RetentionPolicyPolicy
{
    /**
     * Determine if the user can view any retention policies.
     * FSMA 204 Compliance: Only admins can access retention management
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if the user can view a specific retention policy.
     * FSMA 204 Compliance: Admin must be in same organization
     */
    public function view(User $user, RetentionPolicy $policy): bool
    {
        // System admin can view any policy
        if ($user->isSystemAdmin()) {
            return true;
        }

        // Organization admin can only view policies in their organization
        if ($user->isAdmin() && $user->organization_id === $policy->organization_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can create a retention policy.
     * FSMA 204 Compliance: Only admins can create policies
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if the user can update a retention policy.
     * FSMA 204 Compliance: Admin must be in same organization
     */
    public function update(User $user, RetentionPolicy $policy): bool
    {
        // System admin can update any policy
        if ($user->isSystemAdmin()) {
            return true;
        }

        // Organization admin can only update policies in their organization
        if ($user->isAdmin() && $user->organization_id === $policy->organization_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can delete a retention policy.
     * FSMA 204 Compliance: Only admins can delete policies
     */
    public function delete(User $user, RetentionPolicy $policy): bool
    {
        // System admin can delete any policy
        if ($user->isSystemAdmin()) {
            return true;
        }

        // Organization admin can only delete policies in their organization
        if ($user->isAdmin() && $user->organization_id === $policy->organization_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can execute a retention cleanup.
     * FSMA 204 Compliance: Only admins can execute cleanup operations
     * This is a critical operation that must be audited
     */
    public function execute(User $user, RetentionPolicy $policy): bool
    {
        // System admin can execute any cleanup
        if ($user->isSystemAdmin()) {
            return true;
        }

        // Organization admin can only execute cleanup for their organization
        if ($user->isAdmin() && $user->organization_id === $policy->organization_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can view retention logs.
     * FSMA 204 Compliance: Only admins can view audit logs
     */
    public function viewLogs(User $user): bool
    {
        return $user->isAdmin();
    }
}
