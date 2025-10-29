<?php

namespace App\Policies;

use App\Models\ArchivalLog;
use App\Models\User;

class ArchivalLogPolicy
{
    /**
     * Determine if the user can view archival logs.
     */
    public function viewAny(User $user): bool
    {
        // Only manager and admin can view archival logs
        return $user->isManager();
    }

    /**
     * Determine if the user can view a specific archival log.
     */
    public function view(User $user, ArchivalLog $log): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Organization Admin must be in same org
        return $user->organization_id === $log->organization_id;
    }

    /**
     * Determine if the user can create archival logs.
     */
    public function create(User $user): bool
    {
        // Only admin can create archival logs
        return $user->isAdmin();
    }

    /**
     * Determine if the user can delete archival logs.
     */
    public function delete(User $user, ArchivalLog $log): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Check organization isolation first
        if ($user->organization_id !== $log->organization_id) {
            return false;
        }

        // Only admin can delete within their org
        return $user->isAdmin();
    }
}
