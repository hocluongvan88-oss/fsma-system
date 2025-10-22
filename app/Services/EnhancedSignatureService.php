<?php

namespace App\Services;

use App\Models\User;
use App\Models\ESignature;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Hash;
use App\Services\FlexibleRecordTypeService;
use App\Services\SignatureRevocationService;
use App\Services\XAdESSignatureService;
use App\Services\LongTermValidationService;
use App\Services\SignatureAttributesLoggingService;
use App\Services\SignatureEncryptionService;
use App\Services\SignatureExpirationService;
use App\Services\SignatureVerificationAuditService;

class EnhancedSignatureService
{
    protected DigitalCertificateService $certificateService;
    protected TwoFactorAuthService $twoFAService;
    protected TimestampAuthorityService $timestampService;
    protected AuditLogService $auditLogService;
    protected RFC3161TimestampValidator $timestampValidator;
    protected SignatureVerificationReportService $verificationReportService;

    public function __construct(
        DigitalCertificateService $certificateService,
        TwoFactorAuthService $twoFAService,
        TimestampAuthorityService $timestampService,
        AuditLogService $auditLogService,
        RFC3161TimestampValidator $timestampValidator,
        SignatureVerificationReportService $verificationReportService
    ) {
        $this->certificateService = $certificateService;
        $this->twoFAService = $twoFAService;
        $this->timestampService = $timestampService;
        $this->auditLogService = $auditLogService;
        $this->timestampValidator = $timestampValidator;
        $this->verificationReportService = $verificationReportService;
    }

    /**
     * Create enhanced e-signature with FSMA 204 compliance
     */
    public function createSignature(
        User $user,
        string $recordType,
        int $recordId,
        string $action,
        string $password,
        string $meaningOfSignature,
        ?string $reason = null,
        ?string $twoFACode = null,
        ?string $twoFAMethod = null,
        ?int $validityPeriodDays = 365,
        ?int $delegatedByUserId = null,
        ?string $delegationAuthority = null,
        ?int $expirationDays = 365
    ): ESignature {
        $flexibleRecordTypeService = app(FlexibleRecordTypeService::class);
        if (!$flexibleRecordTypeService->validateRecordType($recordType)) {
            throw new \Exception("Unsupported record type: {$recordType}");
        }

        // Verify password
        if (!Hash::check($password, $user->password)) {
            throw new \Exception('Invalid password for e-signature');
        }

        // Verify 2FA if enabled
        $mfaMethod = null;
        if ($user->two_fa_enabled) {
            if (!$twoFACode) {
                throw new \Exception('2FA code required');
            }

            $verified = false;
            if ($twoFAMethod === 'totp') {
                $verified = $this->twoFAService->verifyUserCode($user, $twoFACode);
                $mfaMethod = 'totp';
            } elseif ($twoFAMethod === 'backup_code') {
                $verified = $this->twoFAService->verifyBackupCode($user, $twoFACode);
                $mfaMethod = 'backup_code';
            }

            if (!$verified) {
                $this->twoFAService->logAttempt($user, $twoFAMethod ?? 'unknown', false, 'Invalid code');
                throw new \Exception('Invalid 2FA code');
            }

            $this->twoFAService->logAttempt($user, $mfaMethod, true);
        }

        $recordContent = $flexibleRecordTypeService->extractRecordContent($recordType, $recordId);
        $recordContentHash = hash('sha512', $recordContent);

        // Create signature data (improved - no password included)
        $signatureData = implode('|', [
            $user->id,
            $recordContentHash,
            now()->toIso8601String(),
            $recordType,
            $recordId,
            $action,
            $meaningOfSignature,
        ]);

        // Sign with certificate if available
        $certificateId = null;
        $signatureHash = null;

        if ($user->certificate_id) {
            $certificate = $user->digitalCertificate;
            if ($certificate && $this->certificateService->verifyCertificate($certificate)) {
                try {
                    $signatureHash = $this->certificateService->signData($certificate, $signatureData);
                    $certificateId = $certificate->id;
                } catch (\Exception $e) {
                    // Fall back to SHA512 hash if certificate signing fails
                    $signatureHash = hash('sha512', $signatureData);
                }
            }
        }

        // Fall back to SHA512 hash if no certificate
        if (!$signatureHash) {
            $signatureHash = hash('sha512', $signatureData);
        }

        $signedAt = now();
        $validFrom = $signedAt;
        $validUntil = $signedAt->copy()->addDays($validityPeriodDays ?? 365);
        $expiresAt = $signedAt->copy()->addDays($expirationDays ?? 365);

        // Create signature record
        $signature = ESignature::create([
            'user_id' => $user->id,
            'record_type' => $recordType,
            'record_id' => $recordId,
            'action' => $action,
            'reason' => $reason,
            'meaning_of_signature' => $meaningOfSignature,
            'signature_hash' => $signatureHash,
            'signature_algorithm' => 'SHA512',
            'record_content_hash' => $recordContentHash,
            'certificate_id' => $certificateId,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'mfa_method' => $mfaMethod,
            'signed_at' => $signedAt,
            'signature_valid_from' => $validFrom,
            'signature_valid_until' => $validUntil,
            'signature_validity_period_days' => $validityPeriodDays ?? 365,
            'delegated_by_user_id' => $delegatedByUserId,
            'delegation_authority' => $delegationAuthority,
            'is_delegated_signature' => $delegatedByUserId !== null,
            'signature_expires_at' => $expiresAt,
            'expiration_status' => 'active',
        ]);

        $this->timestampService->attachTimestampToSignature($signature);

        $xadesService = app(XAdESSignatureService::class);
        $ltvService = app(LongTermValidationService::class);
        $attributesService = app(SignatureAttributesLoggingService::class);
        $encryptionService = app(SignatureEncryptionService::class);
        $expirationService = app(SignatureExpirationService::class);

        // Generate XAdES metadata
        if ($certificateId) {
            $certificate = $user->digitalCertificate;
            if ($certificate) {
                $certInfo = $xadesService->extractCertificateInfo($certificate);
                $xadesService->storeXAdESMetadata($signature, [
                    'certificate_subject' => $certInfo['subject'],
                    'certificate_issuer' => $certInfo['issuer'],
                    'certificate_serial_number' => $certInfo['serial_number'],
                    'tsa_url' => config('services.tsa.primary_url'),
                    'tsa_certificate_subject' => 'TSA Certificate',
                ]);
            }
        }

        // Enable LTV
        $ltvService->enableLTV($signature);

        // Log signature attributes
        $attributesService->logSignatureAttributes($signature);

        $encryptionService->encryptSignatureFields($signature);

        $expirationService->setExpiration($signature, $expirationDays ?? 365);

        $report = $this->verificationReportService->generateVerificationReport($signature);
        $signature->update([
            'verification_report' => $report,
            'verification_passed' => $report['overall_status'] === 'valid',
            'last_verified_at' => now(),
        ]);

        $this->auditLogService->log(
            'CREATE_SIGNATURE',
            'e_signatures',
            $signature->id,
            null,
            [
                'record_type' => $recordType,
                'record_id' => $recordId,
                'action' => $action,
                'mfa_used' => $mfaMethod !== null,
                'validity_period_days' => $validityPeriodDays,
                'xades_enabled' => $certificateId !== null,
                'ltv_enabled' => true,
                'is_delegated' => $delegatedByUserId !== null,
                'expiration_days' => $expirationDays,
            ]
        );

        return $signature;
    }

    /**
     * Verify signature integrity with full FSMA 204 compliance
     */
    public function verifySignature(ESignature $signature, User $user, string $password): array
    {
        $verificationService = app(SignatureVerificationAuditService::class);
        
        $report = $this->verificationReportService->generateVerificationReport($signature);
        
        // Update signature with latest verification
        $signature->update([
            'verification_report' => $report,
            'verification_passed' => $report['overall_status'] === 'valid',
            'last_verified_at' => now(),
        ]);

        $verificationService->logVerification(
            $signature,
            $report['overall_status'],
            'manual',
            $user,
            $report['checks'] ?? null
        );

        return $report;
    }

    /**
     * Revoke signature
     */
    public function revokeSignature(ESignature $signature, string $reason, ?User $revokedByUser = null): SignatureRevocation
    {
        $revokedByUser = $revokedByUser ?? auth()->user();
        
        $revocationService = app(SignatureRevocationService::class);
        return $revocationService->revokeSignature(
            $signature,
            $revokedByUser,
            $reason,
            'user_request'
        );
    }

    /**
     * Get record content for hashing
     */
    private function getRecordContent(string $recordType, int $recordId): string
    {
        // This should be implemented based on your record types
        // For now, return a placeholder
        return json_encode([
            'type' => $recordType,
            'id' => $recordId,
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
