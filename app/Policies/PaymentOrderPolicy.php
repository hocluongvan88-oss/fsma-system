<?php

namespace App\Policies;

use App\Models\PaymentOrder;
use App\Models\User;

class PaymentOrderPolicy
{
    /**
     * Determine if the user can view payment orders.
     */
    public function viewAny(User $user): bool
    {
        // Only manager and admin can view payment orders
        return $user->isManager();
    }

    /**
     * Determine if the user can view a specific payment order.
     */
    public function view(User $user, PaymentOrder $order): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Organization Admin must be in same org
        return $user->organization_id === $order->organization_id;
    }

    /**
     * Determine if the user can create payment orders.
     */
    public function create(User $user): bool
    {
        // Only admin can create payment orders
        return $user->isAdmin();
    }

    /**
     * Determine if the user can update payment orders.
     */
    public function update(User $user, PaymentOrder $order): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Check organization isolation first
        if ($user->organization_id !== $order->organization_id) {
            return false;
        }

        // Only admin can update within their org
        return $user->isAdmin();
    }

    /**
     * Determine if the user can delete payment orders.
     */
    public function delete(User $user, PaymentOrder $order): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Check organization isolation first
        if ($user->organization_id !== $order->organization_id) {
            return false;
        }

        // Only admin can delete within their org
        return $user->isAdmin();
    }
}
