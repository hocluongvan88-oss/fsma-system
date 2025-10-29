<?php

namespace App\Helpers;

use App\Models\User;

class EmailHelper
{
    public static function isEmailVerified(User $user): bool
    {
        if (!$user->email) {
            return false;
        }

        if (!$user->email_verified_at) {
            return false;
        }

        return true;
    }

    public static function logEmailSkipped(User $user, string $reason): void
    {
        \Log::warning("Email skipped for user {$user->id}: {$reason}", [
            'user_id' => $user->id,
            'email' => $user->email,
            'reason' => $reason,
        ]);
    }

    public static function logEmailSent(User $user, string $emailType): void
    {
        \Log::info("Email sent to user {$user->id}", [
            'user_id' => $user->id,
            'email' => $user->email,
            'type' => $emailType,
        ]);
    }

    public static function logEmailError(User $user, string $emailType, \Exception $e): void
    {
        \Log::error("Failed to send {$emailType} email to user {$user->id}: {$e->getMessage()}", [
            'user_id' => $user->id,
            'email' => $user->email,
            'type' => $emailType,
            'error' => $e->getMessage(),
        ]);
    }
}
