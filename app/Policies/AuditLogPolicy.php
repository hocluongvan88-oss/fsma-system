<?php

namespace App\Policies;

use App\Models\AuditLog;
use App\Models\User;

class AuditLogPolicy
{
    /**
     * Determine if the user can view the audit log.
     */
    public function view(User $user, AuditLog $log): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Ensure audit log belongs to same organization
        if (!isset($log->organization_id)) {
            // Fallback to checking log user's organization if log doesn't have organization_id
            return $user->organization_id === $log->user->organization_id;
        }

        // Organization Admin must be in same org
        return $user->organization_id === $log->organization_id;
    }

    /**
     * Determine if the user can view audit logs list.
     */
    public function viewAny(User $user): bool
    {
        // Only manager and admin can view audit logs
        return $user->isManager();
    }
}
