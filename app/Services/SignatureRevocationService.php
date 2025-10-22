<?php

namespace App\Services;

use App\Models\ESignature;
use App\Models\SignatureRevocation;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Notification;

class SignatureRevocationService
{
    protected AuditLogService $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    public function revokeSignature(
        ESignature $signature,
        User $revokedByUser,
        string $reason,
        string $category = 'user_request',
        ?string $details = null,
        bool $isEmergency = false
    ): SignatureRevocation {
        // Create revocation record
        $revocation = SignatureRevocation::create([
            'signature_id' => $signature->id,
            'revoked_by_user_id' => $revokedByUser->id,
            'revocation_reason' => $reason,
            'revocation_category' => $category,
            'revocation_details' => $details,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'is_emergency_revocation' => $isEmergency,
            'revoked_at' => now(),
        ]);

        // Update signature status
        $signature->update([
            'is_revoked' => true,
            'revoked_at' => now(),
            'revocation_reason' => $reason,
        ]);

        // Log to audit trail
        $this->auditLogService->log(
            'REVOKE_SIGNATURE',
            'e_signatures',
            $signature->id,
            ['is_revoked' => false],
            [
                'is_revoked' => true,
                'revocation_reason' => $reason,
                'revocation_category' => $category,
                'is_emergency' => $isEmergency,
            ]
        );

        // Notify relevant users
        $this->notifyRevocation($signature, $revocation, $revokedByUser);

        return $revocation;
    }

    public function bulkRevokeSignatures(
        array $signatureIds,
        User $revokedByUser,
        string $reason,
        string $category = 'security_breach'
    ): array {
        $revocations = [];
        $signatures = ESignature::whereIn('id', $signatureIds)
            ->where('is_revoked', false)
            ->get();

        foreach ($signatures as $signature) {
            $revocations[] = $this->revokeSignature(
                $signature,
                $revokedByUser,
                $reason,
                $category,
                null,
                true
            );
        }

        return $revocations;
    }

    public function getRevocationHistory(ESignature $signature): ?SignatureRevocation
    {
        return SignatureRevocation::where('signature_id', $signature->id)
            ->latest('revoked_at')
            ->first();
    }

    public function getRevocationsByCategory(string $category, int $limit = 100)
    {
        return SignatureRevocation::byCategory($category)
            ->with(['signature', 'revokedByUser'])
            ->latest('revoked_at')
            ->limit($limit)
            ->get();
    }

    public function getEmergencyRevocations(int $limit = 50)
    {
        return SignatureRevocation::emergency()
            ->with(['signature', 'revokedByUser'])
            ->latest('revoked_at')
            ->limit($limit)
            ->get();
    }

    public function getRevocationStats(): array
    {
        return [
            'total_revocations' => SignatureRevocation::count(),
            'emergency_revocations' => SignatureRevocation::emergency()->count(),
            'by_category' => SignatureRevocation::selectRaw('revocation_category, COUNT(*) as count')
                ->groupBy('revocation_category')
                ->pluck('count', 'revocation_category')
                ->toArray(),
            'last_30_days' => SignatureRevocation::where('revoked_at', '>=', now()->subDays(30))
                ->count(),
        ];
    }

    private function notifyRevocation(ESignature $signature, SignatureRevocation $revocation, User $revokedByUser): void
    {
        // Notify the user who created the signature
        try {
            $signature->user->notify(new \App\Notifications\SignatureRevokedNotification($signature, $revocation));
        } catch (\Exception $e) {
            // Log notification error but don't fail the revocation
            \Log::warning("Failed to notify user about signature revocation: " . $e->getMessage());
        }

        // Notify admins if emergency revocation
        if ($revocation->is_emergency_revocation) {
            $admins = User::where('is_admin', true)->get();
            foreach ($admins as $admin) {
                try {
                    $admin->notify(new \App\Notifications\EmergencySignatureRevocationNotification($signature, $revocation));
                } catch (\Exception $e) {
                    \Log::warning("Failed to notify admin about emergency revocation: " . $e->getMessage());
                }
            }
        }
    }
}
