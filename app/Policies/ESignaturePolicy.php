<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ESignature;

class ESignaturePolicy
{
    /**
     * Determine if the user can view an e-signature.
     */
    public function view(User $user, ESignature $signature): bool
    {
        // Admin can view any signature
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->organization_id === null || $signature->organization_id === null) {
            return false;
        }

        // User can only view signatures from their organization
        return $user->organization_id === $signature->organization_id;
    }

    /**
     * Determine if the user can create an e-signature.
     */
    public function create(User $user): bool
    {
        // Only manager and admin can create signatures
        return $user->isManager();
    }

    /**
     * Determine if the user can revoke an e-signature.
     */
    public function revoke(User $user, ESignature $signature): bool
    {
        // Admin can revoke any signature
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->organization_id === null || $signature->organization_id === null) {
            return false;
        }

        // User can only revoke their own signatures
        if ($user->id !== $signature->user_id) {
            return false;
        }

        // User must be in the same organization
        return $user->organization_id === $signature->organization_id;
    }

    /**
     * Determine if the user can verify an e-signature.
     */
    public function verify(User $user, ESignature $signature): bool
    {
        // Admin can verify any signature
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->organization_id === null || $signature->organization_id === null) {
            return false;
        }

        // User can only verify signatures from their organization
        return $user->organization_id === $signature->organization_id;
    }
}
