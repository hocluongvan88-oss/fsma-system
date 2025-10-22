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
        // Admin can view any audit log
        if ($user->isAdmin()) {
            return true;
        }

        return $user->organization_id === $log->user->organization_id;
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
