<?php

namespace App\Services;

use App\Models\User;
use App\Models\ESignature;
use App\Models\SignatureDelegation;
use Illuminate\Support\Facades\DB;

class SignatureDelegationService
{
    protected AuditLogService $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    /**
     * Create a delegation of signature authority
     */
    public function createDelegation(
        User $delegator,
        User $delegatee,
        string $authority,
        ?array $scope = null,
        int $validDays = 30
    ): SignatureDelegation {
        $validFrom = now();
        $validUntil = $validFrom->copy()->addDays($validDays);

        $delegation = SignatureDelegation::create([
            'delegator_user_id' => $delegator->id,
            'delegatee_user_id' => $delegatee->id,
            'delegation_authority' => $authority,
            'delegation_scope' => $scope ? json_encode($scope) : null,
            'valid_from' => $validFrom,
            'valid_until' => $validUntil,
            'is_active' => true,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        $this->auditLogService->log(
            'CREATE_DELEGATION',
            'signature_delegations',
            $delegation->id,
            null,
            [
                'delegator_id' => $delegator->id,
                'delegatee_id' => $delegatee->id,
                'authority' => $authority,
                'valid_days' => $validDays,
            ]
        );

        return $delegation;
    }

    /**
     * Check if user can sign on behalf of another user
     */
    public function canDelegateSign(User $delegatee, User $delegator, string $authority): bool
    {
        $delegation = SignatureDelegation::where('delegator_user_id', $delegator->id)
            ->where('delegatee_user_id', $delegatee->id)
            ->where('delegation_authority', $authority)
            ->where('is_active', true)
            ->where('valid_from', '<=', now())
            ->where('valid_until', '>=', now())
            ->first();

        return $delegation !== null;
    }

    /**
     * Get active delegations for a user
     */
    public function getActiveDelegations(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return SignatureDelegation::where('delegatee_user_id', $user->id)
            ->where('is_active', true)
            ->where('valid_until', '>=', now())
            ->with('delegator')
            ->get();
    }

    /**
     * Revoke a delegation
     */
    public function revokeDelegation(SignatureDelegation $delegation, string $reason): void
    {
        $delegation->update([
            'is_active' => false,
            'revocation_reason' => $reason,
            'revoked_at' => now(),
        ]);

        $this->auditLogService->log(
            'REVOKE_DELEGATION',
            'signature_delegations',
            $delegation->id,
            ['is_active' => true],
            ['is_active' => false, 'reason' => $reason]
        );
    }

    /**
     * Create delegated signature
     */
    public function createDelegatedSignature(
        User $delegatee,
        User $delegator,
        string $recordType,
        int $recordId,
        string $action,
        string $meaningOfSignature,
        string $delegationAuthority,
        ?string $reason = null
    ): ESignature {
        // Verify delegation is valid
        if (!$this->canDelegateSign($delegatee, $delegator, $delegationAuthority)) {
            throw new \Exception('Invalid or expired delegation');
        }

        // Create signature using EnhancedSignatureService
        $signatureService = app(EnhancedSignatureService::class);
        
        // Get delegator's password (should be provided separately in real implementation)
        // For now, we'll create the signature with delegatee's credentials
        $signature = ESignature::create([
            'user_id' => $delegator->id,
            'delegated_by_user_id' => $delegatee->id,
            'record_type' => $recordType,
            'record_id' => $recordId,
            'action' => $action,
            'reason' => $reason,
            'meaning_of_signature' => $meaningOfSignature,
            'is_delegated_signature' => true,
            'delegation_authority' => $delegationAuthority,
            'delegation_valid_until' => SignatureDelegation::where('delegator_user_id', $delegator->id)
                ->where('delegatee_user_id', $delegatee->id)
                ->where('delegation_authority', $delegationAuthority)
                ->first()?->valid_until,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'signed_at' => now(),
        ]);

        $this->auditLogService->log(
            'CREATE_DELEGATED_SIGNATURE',
            'e_signatures',
            $signature->id,
            null,
            [
                'delegator_id' => $delegator->id,
                'delegatee_id' => $delegatee->id,
                'delegation_authority' => $delegationAuthority,
                'record_type' => $recordType,
            ]
        );

        return $signature;
    }
}
