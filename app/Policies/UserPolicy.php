<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine if the user can view another user.
     */
    public function view(User $user, User $targetUser): bool
    {
        // Admin can view any user
        if ($user->isAdmin()) {
            return true;
        }

        return $user->organization_id === $targetUser->organization_id;
    }

    /**
     * Determine if the user can create a user.
     */
    public function create(User $user): bool
    {
        // Only manager and admin can create users
        return $user->isManager();
    }

    /**
     * Determine if the user can update a user.
     */
    public function update(User $user, User $targetUser): bool
    {
        // Admin can update any user
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->organization_id !== $targetUser->organization_id) {
            return false;
        }

        // Manager can only update non-admin users
        return $user->isManager() && !$targetUser->isAdmin();
    }

    /**
     * Determine if the user can delete a user.
     */
    public function delete(User $user, User $targetUser): bool
    {
        // Admin can delete any user
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->organization_id !== $targetUser->organization_id) {
            return false;
        }

        // Manager can only delete non-admin users
        return $user->isManager() && !$targetUser->isAdmin();
    }

    /**
     * Determine if the user can update package.
     */
    public function updatePackage(User $user, User $targetUser): bool
    {
        // Only admin can update packages
        return $user->isAdmin();
    }
}
