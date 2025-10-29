<?php

namespace App\Policies;

use App\Models\Notification;
use App\Models\User;

class NotificationPolicy
{
    /**
     * Determine if the user can view notifications.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view their own notifications
        return $user->is_active;
    }

    /**
     * Determine if the user can view a specific notification.
     */
    public function view(User $user, Notification $notification): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // User can only view notifications from their organization
        return $user->organization_id === $notification->organization_id;
    }

    /**
     * Determine if the user can delete notifications.
     */
    public function delete(User $user, Notification $notification): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Check organization isolation first
        if ($user->organization_id !== $notification->organization_id) {
            return false;
        }

        // User can delete their own notifications
        return $user->id === $notification->user_id;
    }
}
