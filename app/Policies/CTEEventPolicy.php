<?php

namespace App\Policies;

use App\Models\User;
use App\Models\CTEEvent;

class CTEEventPolicy
{
    /**
     * Determine if the user can view CTE events.
     * Added organization_id check to prevent cross-organization data leakage
     */
    public function viewAny(User $user): bool
    {
        // Only users with valid organization can view CTE events
        return $user->organization_id !== null && $user->is_active;
    }

    /**
     * Determine if the user can view a specific CTE event.
     */
    public function view(User $user, CTEEvent $cteEvent): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Check organization isolation - even admins must be in same org
        return $user->organization_id === $cteEvent->traceRecord->organization_id;
    }

    /**
     * Determine if the user can create CTE events.
     */
    public function create(User $user): bool
    {
        // All authenticated users can create CTE events (package limits checked separately)
        return $user->is_active && $user->organization_id !== null;
    }

    /**
     * Determine if the user can update a CTE event.
     */
    public function update(User $user, CTEEvent $cteEvent): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Check organization isolation first
        if ($user->organization_id !== $cteEvent->traceRecord->organization_id) {
            return false;
        }

        // Organization Admin and Manager can update within their org
        return $user->isManager();
    }

    /**
     * Determine if the user can void a CTE event.
     */
    public function void(User $user, CTEEvent $cteEvent): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Check organization isolation first
        if ($user->organization_id !== $cteEvent->traceRecord->organization_id) {
            return false;
        }

        // Organization Admin can void within their org
        if ($user->isAdmin()) {
            return true;
        }

        // Manager can void within 2 hours
        if ($user->isManager()) {
            return $cteEvent->created_at->diffInHours(now()) <= 2;
        }

        return false;
    }

    /**
     * Determine if the user can delete a CTE event.
     */
    public function delete(User $user, CTEEvent $cteEvent): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Organization Admin can only delete within their org
        if (!$user->isAdmin()) {
            return false;
        }

        return $user->organization_id === $cteEvent->traceRecord->organization_id;
    }
}
