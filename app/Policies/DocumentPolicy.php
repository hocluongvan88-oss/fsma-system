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
        // Admin can view any document
        if ($user->isAdmin()) {
            return true;
        }

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
        // Admin can update any document
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->organization_id !== $document->organization_id) {
            return false;
        }

        // Only manager and admin can update
        return $user->isManager();
    }

    /**
     * Determine if the user can delete the document.
     */
    public function delete(User $user, Document $document): bool
    {
        // Admin can delete any document
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->organization_id !== $document->organization_id) {
            return false;
        }

        // Only manager and admin can delete
        return $user->isManager();
    }

    /**
     * Determine if the user can approve the document.
     */
    public function approve(User $user, Document $document): bool
    {
        // Admin can approve any document
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->organization_id !== $document->organization_id) {
            return false;
        }

        // Only manager and admin can approve
        return $user->isManager();
    }

    /**
     * Determine if the user can download the document.
     */
    public function download(User $user, Document $document): bool
    {
        // Admin can download any document
        if ($user->isAdmin()) {
            return true;
        }

        return $user->organization_id === $document->organization_id;
    }
}
