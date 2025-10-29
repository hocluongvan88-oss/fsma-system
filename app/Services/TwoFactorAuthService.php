<?php

namespace App\Services;

use App\Models\User;
use App\Models\TwoFALog;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Str;

class TwoFactorAuthService
{
    protected Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Generate TOTP secret for user
     */
    public function generateSecret(User $user): string
    {
        $secret = $this->google2fa->generateSecretKey();
        return $secret;
    }

    /**
     * Get QR code URL for TOTP setup
     */
    public function getQRCodeUrl(User $user, string $secret): string
    {
        return $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );
    }

    /**
     * Enable 2FA for user with TOTP secret
     */
    public function enableTwoFA(User $user, string $secret, string $code): bool
    {
        // Verify the code is correct
        if (!$this->verifyCode($secret, $code)) {
            return false;
        }

        // Generate backup codes
        $backupCodes = $this->generateBackupCodes($user);

        // Update user
        $user->update([
            'two_fa_enabled' => true,
            'two_fa_secret' => encrypt($secret),
            'backup_codes' => json_encode($backupCodes),
            'two_fa_enabled_at' => now(),
        ]);

        return true;
    }

    /**
     * Disable 2FA for user
     */
    public function disableTwoFA(User $user): void
    {
        $user->update([
            'two_fa_enabled' => false,
            'two_fa_secret' => null,
            'backup_codes' => null,
            'two_fa_enabled_at' => null,
        ]);
    }

    /**
     * Verify TOTP code
     */
    public function verifyCode(string $secret, string $code): bool
    {
        return $this->google2fa->verifyKey($secret, $code);
    }

    /**
     * Verify TOTP code for user
     */
    public function verifyUserCode(User $user, string $code): bool
    {
        if (!$user->two_fa_enabled || !$user->two_fa_secret) {
            return false;
        }

        $secret = decrypt($user->two_fa_secret);
        return $this->verifyCode($secret, $code);
    }

    /**
     * Generate backup codes
     */
    public function generateBackupCodes(User $user = null, int $count = 10): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(Str::random(8));
        }
        
        if ($user) {
            $user->update(['backup_codes' => json_encode($codes)]);
        }
        
        return $codes;
    }

    /**
     * Verify and use backup code
     */
    public function verifyBackupCode(User $user, string $code): bool
    {
        if (!$user->backup_codes) {
            return false;
        }

        $codes = json_decode($user->backup_codes, true);
        $key = array_search($code, $codes);

        if ($key === false) {
            return false;
        }

        // Remove used code
        unset($codes[$key]);
        $user->update(['backup_codes' => json_encode(array_values($codes))]);

        return true;
    }

    /**
     * Log 2FA attempt
     */
    public function logAttempt(User $user, string $method, bool $success, ?string $reason = null): void
    {
        TwoFALog::create([
            'user_id' => $user->id,
            'method' => $method,
            'success' => $success,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'failure_reason' => $reason,
            'attempted_at' => now(),
        ]);
    }

    /**
     * Check if user has too many failed attempts
     */
    public function hasTooManyFailedAttempts(User $user, int $maxAttempts = 5, int $minutes = 15): bool
    {
        $failedAttempts = TwoFALog::where('user_id', $user->id)
            ->where('success', false)
            ->where('attempted_at', '>=', now()->subMinutes($minutes))
            ->count();

        return $failedAttempts >= $maxAttempts;
    }
}
