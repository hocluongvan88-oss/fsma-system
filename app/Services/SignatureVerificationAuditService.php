<?php

namespace App\Services;

use App\Models\ESignature;
use App\Models\SignatureVerification;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SignatureVerificationAuditService
{
    protected AuditLogService $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    /**
     * Log a signature verification attempt
     */
    public function logVerification(
        ESignature $signature,
        string $status,
        string $type = 'manual',
        ?User $verifiedByUser = null,
        ?array $checks = null,
        ?string $details = null
    ): SignatureVerification {
        $startTime = microtime(true);

        $verification = SignatureVerification::create([
            'signature_id' => $signature->id,
            'verified_by_user_id' => $verifiedByUser?->id,
            'verification_type' => $type,
            'verification_status' => $status,
            'verification_details' => $details,
            'verification_checks' => $checks ? json_encode($checks) : null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'verification_duration_ms' => (int)((microtime(true) - $startTime) * 1000),
            'is_brute_force_attempt' => $this->detectBruteForce($signature, $verifiedByUser),
        ]);

        $this->auditLogService->log(
            'VERIFY_SIGNATURE',
            'signature_verifications',
            $verification->id,
            null,
            [
                'signature_id' => $signature->id,
                'status' => $status,
                'type' => $type,
                'verified_by' => $verifiedByUser?->id,
            ]
        );

        return $verification;
    }

    /**
     * Get verification history for a signature
     */
    public function getVerificationHistory(ESignature $signature, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return SignatureVerification::where('signature_id', $signature->id)
            ->with('verifiedByUser')
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get verification statistics
     */
    public function getVerificationStats(ESignature $signature): array
    {
        $verifications = SignatureVerification::where('signature_id', $signature->id)->get();

        return [
            'total_verifications' => $verifications->count(),
            'successful_verifications' => $verifications->where('verification_status', 'valid')->count(),
            'failed_verifications' => $verifications->where('verification_status', '!=', 'valid')->count(),
            'last_verified_at' => $verifications->max('created_at'),
            'average_verification_time_ms' => (int)$verifications->avg('verification_duration_ms'),
            'brute_force_attempts' => $verifications->where('is_brute_force_attempt', true)->count(),
        ];
    }

    /**
     * Detect potential brute force verification attempts
     */
    protected function detectBruteForce(ESignature $signature, ?User $user): bool
    {
        if (!$user) {
            return false;
        }

        // Check if there are more than 5 failed verification attempts in the last 5 minutes
        $failedAttempts = SignatureVerification::where('signature_id', $signature->id)
            ->where('verified_by_user_id', $user->id)
            ->where('verification_status', '!=', 'valid')
            ->where('created_at', '>=', now()->subMinutes(5))
            ->count();

        return $failedAttempts >= 5;
    }

    /**
     * Get all verification attempts by user
     */
    public function getUserVerificationAttempts(User $user, int $days = 30): \Illuminate\Database\Eloquent\Collection
    {
        return SignatureVerification::where('verified_by_user_id', $user->id)
            ->where('created_at', '>=', now()->subDays($days))
            ->with('signature')
            ->latest('created_at')
            ->get();
    }

    /**
     * Get brute force attempts
     */
    public function getBruteForceAttempts(int $limit = 100): \Illuminate\Database\Eloquent\Collection
    {
        return SignatureVerification::where('is_brute_force_attempt', true)
            ->with(['signature', 'verifiedByUser'])
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }
}
