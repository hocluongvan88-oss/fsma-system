<?php

namespace App\Services;

use App\Models\ESignature;
use Illuminate\Support\Facades\DB;

class SignatureExpirationService
{
    protected AuditLogService $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    /**
     * Set expiration date for a signature
     */
    public function setExpiration(ESignature $signature, int $expirationDays = 365): void
    {
        $expiresAt = now()->addDays($expirationDays);

        $signature->update([
            'signature_expires_at' => $expiresAt,
            'expiration_status' => 'active',
        ]);

        $this->auditLogService->log(
            'SET_SIGNATURE_EXPIRATION',
            'e_signatures',
            $signature->id,
            null,
            [
                'expires_at' => $expiresAt,
                'expiration_days' => $expirationDays,
            ]
        );
    }

    /**
     * Check if signature is expired
     */
    public function isExpired(ESignature $signature): bool
    {
        if (!$signature->signature_expires_at) {
            return false;
        }

        return now()->isAfter($signature->signature_expires_at);
    }

    /**
     * Mark signature as expired
     */
    public function markAsExpired(ESignature $signature): void
    {
        $signature->update([
            'is_expired' => true,
            'expiration_status' => 'expired',
            'expiration_checked_at' => now(),
        ]);

        $this->auditLogService->log(
            'MARK_SIGNATURE_EXPIRED',
            'e_signatures',
            $signature->id,
            ['is_expired' => false],
            ['is_expired' => true]
        );
    }

    /**
     * Check all signatures for expiration
     */
    public function checkAllExpiredSignatures(): array
    {
        $expiredSignatures = ESignature::where('is_expired', false)
            ->whereNotNull('signature_expires_at')
            ->where('signature_expires_at', '<', now())
            ->get();

        $count = 0;
        foreach ($expiredSignatures as $signature) {
            $this->markAsExpired($signature);
            $count++;
        }

        return [
            'total_expired' => $count,
            'checked_at' => now(),
        ];
    }

    /**
     * Get expiration status for a signature
     */
    public function getExpirationStatus(ESignature $signature): array
    {
        $expiresAt = $signature->signature_expires_at;
        $isExpired = $this->isExpired($signature);
        $daysUntilExpiration = $expiresAt ? now()->diffInDays($expiresAt, false) : null;

        return [
            'expires_at' => $expiresAt,
            'is_expired' => $isExpired,
            'days_until_expiration' => $daysUntilExpiration,
            'expiration_status' => $signature->expiration_status,
            'last_checked_at' => $signature->expiration_checked_at,
            'is_expiring_soon' => $daysUntilExpiration !== null && $daysUntilExpiration <= 30 && $daysUntilExpiration > 0,
        ];
    }

    /**
     * Get signatures expiring soon
     */
    public function getExpiringSignatures(int $daysThreshold = 30): \Illuminate\Database\Eloquent\Collection
    {
        $thresholdDate = now()->addDays($daysThreshold);

        return ESignature::where('is_expired', false)
            ->whereNotNull('signature_expires_at')
            ->where('signature_expires_at', '<=', $thresholdDate)
            ->where('signature_expires_at', '>', now())
            ->with('user')
            ->orderBy('signature_expires_at')
            ->get();
    }

    /**
     * Renew signature expiration
     */
    public function renewExpiration(ESignature $signature, int $additionalDays = 365): void
    {
        $currentExpiration = $signature->signature_expires_at ?? now();
        $newExpiration = $currentExpiration->copy()->addDays($additionalDays);

        $signature->update([
            'signature_expires_at' => $newExpiration,
            'is_expired' => false,
            'expiration_status' => 'active',
            'expiration_checked_at' => now(),
        ]);

        $this->auditLogService->log(
            'RENEW_SIGNATURE_EXPIRATION',
            'e_signatures',
            $signature->id,
            null,
            [
                'new_expiration' => $newExpiration,
                'additional_days' => $additionalDays,
            ]
        );
    }
}
