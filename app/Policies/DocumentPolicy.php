<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    /**
     * Determine if the user can view the document.
     */
    public function view(User $user, Document $document): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Organization Admin must be in same org
        return $user->organization_id === $document->organization_id;
    }

    /**
     * Determine if the user can create a document.
     */
    public function create(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine if the user can update the document.
     */
    public function update(User $user, Document $document): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Check organization isolation first
        if ($user->organization_id !== $document->organization_id) {
            return false;
        }

        // Only manager and admin can update within their org
        return $user->isManager();
    }

    /**
     * Determine if the user can delete the document.
     */
    public function delete(User $user, Document $document): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Check organization isolation first
        if ($user->organization_id !== $document->organization_id) {
            return false;
        }

        // Only manager and admin can delete within their org
        return $user->isManager();
    }

    /**
     * Determine if the user can approve the document.
     */
    public function approve(User $user, Document $document): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Check organization isolation first
        if ($user->organization_id !== $document->organization_id) {
            return false;
        }

        // Only manager and admin can approve within their org
        return $user->isManager();
    }

    /**
     * Determine if the user can download the document.
     */
    public function download(User $user, Document $document): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Must be in same organization
        return $user->organization_id === $document->organization_id;
    }
}
