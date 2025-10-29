<?php

namespace App\Services;

use App\Models\Document;
use App\Models\ESignature;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DocumentSignatureService
{
    protected EnhancedSignatureService $enhancedSignatureService;
    protected AuditLogService $auditLogService;

    public function __construct(
        EnhancedSignatureService $enhancedSignatureService,
        AuditLogService $auditLogService
    ) {
        $this->enhancedSignatureService = $enhancedSignatureService;
        $this->auditLogService = $auditLogService;
    }

    /**
     * Sign a document with e-signature
     */
    public function signDocument(
        Document $document,
        User $user,
        string $password,
        string $meaningOfSignature,
        ?string $reason = null,
        ?string $twoFACode = null,
        ?string $twoFAMethod = null
    ): ESignature {
        // Verify document is approved
        if ($document->status !== 'approved') {
            throw new \Exception('Only approved documents can be signed');
        }

        // Create signature using EnhancedSignatureService
        $signature = $this->enhancedSignatureService->createSignature(
            user: $user,
            recordType: 'documents',
            recordId: $document->id,
            action: 'sign',
            password: $password,
            meaningOfSignature: $meaningOfSignature,
            reason: $reason,
            twoFACode: $twoFACode,
            twoFAMethod: $twoFAMethod,
            validityPeriodDays: 365,
            expirationDays: 365
        );

        // Update document signature status
        $document->update([
            'signed_at' => now(),
            'signed_by' => $user->id,
            'signature_status' => 'signed',
        ]);

        $this->auditLogService->log(
            'SIGN_DOCUMENT',
            'documents',
            $document->id,
            null,
            [
                'signature_id' => $signature->id,
                'signed_by' => $user->id,
                'meaning_of_signature' => $meaningOfSignature,
            ]
        );

        return $signature;
    }

    /**
     * Get all signatures for a document
     */
    public function getDocumentSignatures(Document $document): array
    {
        $signatures = ESignature::where('record_type', 'documents')
            ->where('record_id', $document->id)
            ->with('user', 'digitalCertificate')
            ->orderBy('signed_at', 'desc')
            ->get();

        return $signatures->map(function ($sig) {
            return [
                'id' => $sig->id,
                'signed_by' => $sig->user->name,
                'signed_at' => $sig->signed_at,
                'meaning_of_signature' => $sig->meaning_of_signature,
                'reason' => $sig->reason,
                'status' => $sig->is_revoked ? 'revoked' : 'valid',
                'verification_passed' => $sig->verification_passed,
                'certificate_subject' => $sig->certificate_subject,
            ];
        })->toArray();
    }

    /**
     * Verify document signature
     */
    public function verifyDocumentSignature(Document $document, ESignature $signature, User $user, string $password): array
    {
        if ($signature->record_type !== 'documents' || $signature->record_id !== $document->id) {
            throw new \Exception('Signature does not belong to this document');
        }

        return $this->enhancedSignatureService->verifySignature($signature, $user, $password);
    }

    /**
     * Revoke document signature
     */
    public function revokeDocumentSignature(Document $document, ESignature $signature, string $reason, ?User $revokedByUser = null): void
    {
        if ($signature->record_type !== 'documents' || $signature->record_id !== $document->id) {
            throw new \Exception('Signature does not belong to this document');
        }

        $this->enhancedSignatureService->revokeSignature($signature, $reason, $revokedByUser);

        $this->auditLogService->log(
            'REVOKE_DOCUMENT_SIGNATURE',
            'documents',
            $document->id,
            null,
            [
                'signature_id' => $signature->id,
                'revocation_reason' => $reason,
            ]
        );
    }

    /**
     * Get signature verification report for document
     */
    public function getSignatureVerificationReport(Document $document, ESignature $signature): array
    {
        if ($signature->record_type !== 'documents' || $signature->record_id !== $document->id) {
            throw new \Exception('Signature does not belong to this document');
        }

        return [
            'document_id' => $document->id,
            'document_number' => $document->doc_number,
            'signature_id' => $signature->id,
            'signed_by' => $signature->user->name,
            'signed_at' => $signature->signed_at,
            'verification_report' => $signature->verification_report,
            'verification_passed' => $signature->verification_passed,
            'last_verified_at' => $signature->last_verified_at,
            'is_revoked' => $signature->is_revoked,
            'revoked_at' => $signature->revoked_at,
            'revocation_reason' => $signature->revocation_reason,
            'certificate_valid_from' => $signature->signature_valid_from,
            'certificate_valid_until' => $signature->signature_valid_until,
            'ltv_enabled' => $signature->ltv_enabled,
        ];
    }

    /**
     * Check if document is fully signed (all required signatures present)
     */
    public function isDocumentFullySigned(Document $document): bool
    {
        $requiredSignatures = $document->metadata['required_signatures'] ?? 1;
        $actualSignatures = ESignature::where('record_type', 'documents')
            ->where('record_id', $document->id)
            ->where('is_revoked', false)
            ->count();

        return $actualSignatures >= $requiredSignatures;
    }

    /**
     * Get signature status for document
     */
    public function getDocumentSignatureStatus(Document $document): array
    {
        $signatures = ESignature::where('record_type', 'documents')
            ->where('record_id', $document->id)
            ->get();

        $validSignatures = $signatures->where('is_revoked', false)->count();
        $revokedSignatures = $signatures->where('is_revoked', true)->count();
        $invalidSignatures = $signatures->where('verification_passed', false)->count();

        return [
            'total_signatures' => $signatures->count(),
            'valid_signatures' => $validSignatures,
            'revoked_signatures' => $revokedSignatures,
            'invalid_signatures' => $invalidSignatures,
            'is_fully_signed' => $this->isDocumentFullySigned($document),
            'signatures' => $this->getDocumentSignatures($document),
        ];
    }
}
