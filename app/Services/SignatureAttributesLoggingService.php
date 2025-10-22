<?php

namespace App\Services;

use App\Models\ESignature;
use App\Models\DigitalCertificate;

class SignatureAttributesLoggingService
{
    /**
     * Log complete signature attributes for FSMA 204 compliance
     */
    public function logSignatureAttributes(ESignature $signature): void
    {
        $attributes = $this->collectSignatureAttributes($signature);
        $metadata = $this->collectSignatureMetadata($signature);

        $signature->update([
            'signature_attributes' => json_encode($attributes),
            'signature_metadata' => json_encode($metadata),
        ]);
    }

    /**
     * Collect all signature attributes
     */
    private function collectSignatureAttributes(ESignature $signature): array
    {
        return [
            // Basic Signature Info
            'signature_id' => $signature->id,
            'signature_hash' => substr($signature->signature_hash, 0, 16) . '...',
            'signature_algorithm' => $signature->signature_algorithm,
            'signature_format' => $signature->signature_format ?? 'SHA512',
            
            // User Information
            'user_id' => $signature->user_id,
            'user_name' => $signature->user->name ?? 'Unknown',
            'user_email' => $signature->user->email ?? 'Unknown',
            
            // Record Information
            'record_type' => $signature->record_type,
            'record_id' => $signature->record_id,
            'action' => $signature->action,
            'reason' => $signature->reason,
            'meaning_of_signature' => $signature->meaning_of_signature,
            
            // Timestamp Information
            'signed_at' => $signature->signed_at?->toIso8601String(),
            'timestamp_utc_time' => $signature->timestamp_utc_time?->toIso8601String(),
            'timestamp_tsa_url' => $signature->tsa_url,
            'timestamp_tsa_certificate_subject' => $signature->tsa_certificate_subject,
            
            // Validity Period
            'signature_valid_from' => $signature->signature_valid_from?->toIso8601String(),
            'signature_valid_until' => $signature->signature_valid_until?->toIso8601String(),
            'signature_validity_period_days' => $signature->signature_validity_period_days,
            
            // Certificate Information
            'certificate_subject' => $signature->certificate_subject,
            'certificate_issuer' => $signature->certificate_issuer,
            'certificate_serial_number' => $signature->certificate_serial_number,
            
            // Authentication Information
            'mfa_method' => $signature->mfa_method,
            'ip_address' => substr($signature->ip_address ?? '', 0, 10) . '...',
            'user_agent' => substr($signature->user_agent ?? '', 0, 50) . '...',
            
            // Revocation Information
            'is_revoked' => $signature->is_revoked,
            'revoked_at' => $signature->revoked_at?->toIso8601String(),
            'revocation_reason' => $signature->revocation_reason,
            
            // Verification Information
            'verification_passed' => $signature->verification_passed,
            'last_verified_at' => $signature->last_verified_at?->toIso8601String(),
            
            // LTV Information
            'ltv_enabled' => $signature->ltv_enabled,
            'ltv_last_validation_at' => $signature->ltv_last_validation_at?->toIso8601String(),
        ];
    }

    /**
     * Collect signature metadata
     */
    private function collectSignatureMetadata(ESignature $signature): array
    {
        return [
            // Content Hash Information
            'record_content_hash' => substr($signature->record_content_hash, 0, 16) . '...',
            'record_content_hash_algorithm' => 'SHA512',
            
            // Timestamp Token Information
            'timestamp_token_present' => !empty($signature->timestamp_token),
            'timestamp_token_der_present' => !empty($signature->timestamp_token_der),
            'timestamp_token_verified' => $signature->certificate_revocation_checked,
            
            // Certificate Revocation Information
            'certificate_revocation_checked' => $signature->certificate_revocation_checked,
            'certificate_revocation_checked_at' => $signature->certificate_revocation_checked_at?->toIso8601String(),
            'certificate_revocation_status' => $signature->certificate_revocation_status,
            'certificate_revocation_reason' => $signature->certificate_revocation_reason,
            
            // Batch Operation Information
            'batch_operation_id' => $signature->batch_operation_id,
            'batch_operation_type' => $signature->batch_operation_type,
            'batch_operation_sequence' => $signature->batch_operation_sequence,
            'batch_total_count' => $signature->batch_total_count,
            
            // XAdES Information
            'xades_metadata' => $signature->xades_metadata ? json_decode($signature->xades_metadata, true) : null,
            
            // LTV Information
            'ltv_timestamp_chain_present' => !empty($signature->ltv_timestamp_chain),
            'ltv_certificate_chain_present' => !empty($signature->ltv_certificate_chain),
            'ltv_crl_response_present' => !empty($signature->ltv_crl_response),
            'ltv_ocsp_response_present' => !empty($signature->ltv_ocsp_response),
            
            // Status Information
            'signature_status' => $signature->signature_status,
            'created_at' => $signature->created_at?->toIso8601String(),
            'updated_at' => $signature->updated_at?->toIso8601String(),
        ];
    }

    /**
     * Get formatted signature attributes for display
     */
    public function getFormattedAttributes(ESignature $signature): array
    {
        $attributes = json_decode($signature->signature_attributes, true) ?? [];
        $metadata = json_decode($signature->signature_metadata, true) ?? [];

        return [
            'attributes' => $attributes,
            'metadata' => $metadata,
            'summary' => $this->generateSummary($attributes, $metadata),
        ];
    }

    /**
     * Generate summary of signature attributes
     */
    private function generateSummary(array $attributes, array $metadata): array
    {
        return [
            'total_attributes' => count($attributes),
            'total_metadata' => count($metadata),
            'is_valid' => $attributes['verification_passed'] ?? false,
            'is_revoked' => $attributes['is_revoked'] ?? false,
            'has_timestamp' => $metadata['timestamp_token_present'] ?? false,
            'has_certificate' => !empty($attributes['certificate_subject']),
            'has_mfa' => !empty($attributes['mfa_method']),
            'ltv_enabled' => $attributes['ltv_enabled'] ?? false,
        ];
    }
}
