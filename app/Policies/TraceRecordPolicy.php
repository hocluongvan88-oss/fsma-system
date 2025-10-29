<?php

namespace App\Policies;

use App\Models\User;
use App\Models\TraceRecord;

class TraceRecordPolicy
{
    /**
     * Determine if the user can view trace records.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can view a specific trace record.
     */
    public function view(User $user, TraceRecord $traceRecord): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Organization Admin must be in same org
        return $user->organization_id === $traceRecord->organization_id;
    }

    /**
     * Determine if the user can create trace records.
     */
    public function create(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine if the user can update a trace record.
     */
    public function update(User $user, TraceRecord $traceRecord): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Check organization isolation first
        if ($user->organization_id !== $traceRecord->organization_id) {
            return false;
        }

        // Only manager and above can update trace records within their org
        return $user->isManager();
    }

    /**
     * Determine if the user can delete a trace record.
     */
    public function delete(User $user, TraceRecord $traceRecord): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Organization Admin can only delete within their org
        if (!$user->isAdmin()) {
            return false;
        }

        return $user->organization_id === $traceRecord->organization_id;
    }
}
