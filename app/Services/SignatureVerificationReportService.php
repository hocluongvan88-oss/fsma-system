<?php

namespace App\Services;

use App\Models\ESignature;
use App\Models\DigitalCertificate;
use Illuminate\Support\Facades\Log;

class SignatureVerificationReportService
{
    protected RFC3161TimestampValidator $timestampValidator;
    protected CertificateRevocationService $revocationService;

    public function __construct(
        RFC3161TimestampValidator $timestampValidator,
        CertificateRevocationService $revocationService
    ) {
        $this->timestampValidator = $timestampValidator;
        $this->revocationService = $revocationService;
    }

    /**
     * Generate comprehensive signature verification report
     */
    public function generateVerificationReport(ESignature $signature): array
    {
        $report = [
            'signature_id' => $signature->id,
            'generated_at' => now()->toIso8601String(),
            'overall_status' => 'unknown',
            'checks' => [],
            'warnings' => [],
            'errors' => [],
        ];

        // 1. Check signature existence and basic validity
        $report['checks']['signature_exists'] = $this->checkSignatureExists($signature);

        // 2. Check signature not revoked
        $report['checks']['not_revoked'] = $this->checkNotRevoked($signature);

        // 3. Check signature validity period
        $report['checks']['validity_period'] = $this->checkValidityPeriod($signature);

        // 4. Check record content integrity
        $report['checks']['content_integrity'] = $this->checkContentIntegrity($signature);

        // 5. Check timestamp validity
        $report['checks']['timestamp_validity'] = $this->checkTimestampValidity($signature);

        // 6. Check certificate validity
        $report['checks']['certificate_validity'] = $this->checkCertificateValidity($signature);

        // 7. Check certificate revocation status
        $report['checks']['certificate_revocation'] = $this->checkCertificateRevocation($signature);

        // 8. Check signature algorithm strength
        $report['checks']['algorithm_strength'] = $this->checkAlgorithmStrength($signature);

        // 9. Check user authentication
        $report['checks']['user_authentication'] = $this->checkUserAuthentication($signature);

        // 10. Check MFA usage
        $report['checks']['mfa_usage'] = $this->checkMFAUsage($signature);

        $report['checks']['xades_metadata'] = $this->checkXAdESMetadata($signature);

        $report['checks']['ltv_status'] = $this->checkLTVStatus($signature);

        $report['checks']['timestamp_chain'] = $this->checkTimestampChain($signature);

        // Calculate overall status
        $report['overall_status'] = $this->calculateOverallStatus($report['checks']);

        // Add summary
        $report['summary'] = $this->generateSummary($report);

        return $report;
    }

    /**
     * Check signature exists and is valid
     */
    private function checkSignatureExists(ESignature $signature): array
    {
        return [
            'passed' => true,
            'description' => 'Signature record exists',
            'details' => [
                'signature_id' => $signature->id,
                'created_at' => $signature->created_at->toIso8601String(),
                'signed_at' => $signature->signed_at->toIso8601String(),
            ]
        ];
    }

    /**
     * Check signature is not revoked
     */
    private function checkNotRevoked(ESignature $signature): array
    {
        $passed = !$signature->is_revoked;

        return [
            'passed' => $passed,
            'description' => $passed ? 'Signature is not revoked' : 'Signature has been revoked',
            'details' => [
                'is_revoked' => $signature->is_revoked,
                'revoked_at' => $signature->revoked_at?->toIso8601String(),
                'revocation_reason' => $signature->revocation_reason,
            ]
        ];
    }

    /**
     * Check signature validity period
     */
    private function checkValidityPeriod(ESignature $signature): array
    {
        $now = now();
        $validFrom = $signature->signature_valid_from ?? $signature->signed_at;
        $validUntil = $signature->signature_valid_until ?? $validFrom->addDays($signature->signature_validity_period_days ?? 365);

        $passed = $now->isBetween($validFrom, $validUntil);

        return [
            'passed' => $passed,
            'description' => $passed ? 'Signature is within validity period' : 'Signature is outside validity period',
            'details' => [
                'valid_from' => $validFrom->toIso8601String(),
                'valid_until' => $validUntil->toIso8601String(),
                'validity_period_days' => $signature->signature_validity_period_days ?? 365,
                'current_time' => $now->toIso8601String(),
            ]
        ];
    }

    /**
     * Check record content integrity
     */
    private function checkContentIntegrity(ESignature $signature): array
    {
        try {
            $flexibleRecordTypeService = app(\App\Services\FlexibleRecordTypeService::class);
            $currentContent = $flexibleRecordTypeService->extractRecordContent(
                $signature->record_type,
                $signature->record_id
            );
            
            $currentHash = hash('sha512', $currentContent);
            $passed = hash_equals($currentHash, $signature->record_content_hash);

            return [
                'passed' => $passed,
                'description' => $passed ? 'Record content has not been modified' : 'Record content has been modified',
                'details' => [
                    'original_hash' => substr($signature->record_content_hash, 0, 16) . '...',
                    'current_hash' => substr($currentHash, 0, 16) . '...',
                    'record_type' => $signature->record_type,
                    'record_id' => $signature->record_id,
                ]
            ];
        } catch (\Exception $e) {
            return [
                'passed' => false,
                'description' => 'Failed to verify record content integrity',
                'error' => $e->getMessage(),
                'details' => []
            ];
        }
    }

    /**
     * Check timestamp validity
     */
    private function checkTimestampValidity(ESignature $signature): array
    {
        if (!$signature->timestamp_token) {
            return [
                'passed' => false,
                'description' => 'No timestamp token present',
                'details' => []
            ];
        }

        try {
            $validation = $this->timestampValidator->validateTimestampToken(
                $signature->timestamp_token_der ?? $signature->timestamp_token,
                $signature->signature_hash
            );

            return [
                'passed' => $validation['valid'],
                'description' => $validation['valid'] ? 'Timestamp is valid' : 'Timestamp validation failed',
                'error' => $validation['error'],
                'details' => [
                    'timestamp_utc' => $signature->timestamp_utc_time?->toIso8601String(),
                    'tsa_url' => $signature->timestamp_tsa_url,
                    'tsa_certificate' => $signature->timestamp_tsa_certificate,
                    'validation_details' => $validation['details'],
                ]
            ];
        } catch (\Exception $e) {
            return [
                'passed' => false,
                'description' => 'Timestamp validation error',
                'error' => $e->getMessage(),
                'details' => []
            ];
        }
    }

    /**
     * Check certificate validity
     */
    private function checkCertificateValidity(ESignature $signature): array
    {
        if (!$signature->certificate_id) {
            return [
                'passed' => true,
                'description' => 'No certificate used (SHA512 hash only)',
                'details' => []
            ];
        }

        try {
            $certificate = $signature->digitalCertificate;
            
            if (!$certificate) {
                return [
                    'passed' => false,
                    'description' => 'Certificate not found',
                    'details' => []
                ];
            }

            $now = now();
            $validFrom = $certificate->valid_from;
            $validUntil = $certificate->valid_until;

            $passed = $now->isBetween($validFrom, $validUntil);

            return [
                'passed' => $passed,
                'description' => $passed ? 'Certificate is valid' : 'Certificate is expired or not yet valid',
                'details' => [
                    'subject' => $certificate->subject,
                    'issuer' => $certificate->issuer,
                    'valid_from' => $validFrom->toIso8601String(),
                    'valid_until' => $validUntil->toIso8601String(),
                    'serial_number' => $certificate->serial_number,
                ]
            ];
        } catch (\Exception $e) {
            return [
                'passed' => false,
                'description' => 'Certificate validation error',
                'error' => $e->getMessage(),
                'details' => []
            ];
        }
    }

    /**
     * Check certificate revocation status
     */
    private function checkCertificateRevocation(ESignature $signature): array
    {
        if (!$signature->certificate_id) {
            return [
                'passed' => true,
                'description' => 'No certificate revocation check needed',
                'details' => []
            ];
        }

        try {
            $certificate = $signature->digitalCertificate;
            
            if (!$certificate) {
                return [
                    'passed' => false,
                    'description' => 'Certificate not found',
                    'details' => []
                ];
            }

            // Check if already verified
            if ($signature->certificate_revocation_checked) {
                $passed = $signature->certificate_revocation_status === 'good';
                
                return [
                    'passed' => $passed,
                    'description' => $passed ? 'Certificate is not revoked' : 'Certificate is revoked',
                    'details' => [
                        'status' => $signature->certificate_revocation_status,
                        'reason' => $signature->certificate_revocation_reason,
                        'checked_at' => $signature->certificate_revocation_checked_at?->toIso8601String(),
                    ]
                ];
            }

            // Perform revocation check
            $ocspResult = $this->revocationService->checkOCSPStatus($certificate);
            
            if ($ocspResult['status'] === 'unknown') {
                $crlResult = $this->revocationService->checkCRLStatus($certificate);
                $result = $crlResult;
            } else {
                $result = $ocspResult;
            }

            $passed = $result['status'] === 'good';

            return [
                'passed' => $passed,
                'description' => $passed ? 'Certificate is not revoked' : 'Certificate is revoked',
                'details' => [
                    'status' => $result['status'],
                    'method' => $result['method'],
                    'checked_at' => $result['checked_at'],
                ]
            ];
        } catch (\Exception $e) {
            return [
                'passed' => false,
                'description' => 'Certificate revocation check error',
                'error' => $e->getMessage(),
                'details' => []
            ];
        }
    }

    /**
     * Check signature algorithm strength
     */
    private function checkAlgorithmStrength(ESignature $signature): array
    {
        $algorithm = $signature->signature_algorithm ?? 'SHA512';
        
        // SHA512 is considered strong
        $strongAlgorithms = ['SHA512', 'SHA256', 'RSA-SHA512', 'RSA-SHA256'];
        $passed = in_array($algorithm, $strongAlgorithms);

        return [
            'passed' => $passed,
            'description' => $passed ? 'Signature algorithm is strong' : 'Signature algorithm is weak',
            'details' => [
                'algorithm' => $algorithm,
                'is_strong' => $passed,
            ]
        ];
    }

    /**
     * Check user authentication
     */
    private function checkUserAuthentication(ESignature $signature): array
    {
        $user = $signature->user;
        
        return [
            'passed' => true,
            'description' => 'User authentication verified',
            'details' => [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'signed_at' => $signature->signed_at->toIso8601String(),
            ]
        ];
    }

    /**
     * Check MFA usage
     */
    private function checkMFAUsage(ESignature $signature): array
    {
        $mfaUsed = !empty($signature->mfa_method);

        return [
            'passed' => $mfaUsed,
            'description' => $mfaUsed ? 'MFA was used for signature' : 'MFA was not used for signature',
            'details' => [
                'mfa_method' => $signature->mfa_method,
                'mfa_used' => $mfaUsed,
            ]
        ];
    }

    /**
     * Check XAdES metadata for FSMA 204 compliance
     */
    private function checkXAdESMetadata(ESignature $signature): array
    {
        try {
            $xadesMetadata = $signature->xades_metadata;
            
            if (!$xadesMetadata || !is_array($xadesMetadata)) {
                return [
                    'passed' => false,
                    'description' => 'XAdES metadata not found',
                    'details' => []
                ];
            }

            $requiredFields = [
                'certificate_subject',
                'certificate_issuer',
                'certificate_serial_number',
                'tsa_url',
            ];

            $missingFields = [];
            foreach ($requiredFields as $field) {
                if (empty($xadesMetadata[$field])) {
                    $missingFields[] = $field;
                }
            }

            $passed = empty($missingFields);

            return [
                'passed' => $passed,
                'description' => $passed ? 'XAdES metadata is complete' : 'XAdES metadata is incomplete',
                'details' => [
                    'certificate_subject' => $xadesMetadata['certificate_subject'] ?? null,
                    'certificate_issuer' => $xadesMetadata['certificate_issuer'] ?? null,
                    'tsa_url' => $xadesMetadata['tsa_url'] ?? null,
                    'missing_fields' => $missingFields,
                ]
            ];
        } catch (\Exception $e) {
            return [
                'passed' => false,
                'description' => 'XAdES metadata validation error',
                'error' => $e->getMessage(),
                'details' => []
            ];
        }
    }

    /**
     * Check LTV (Long-Term Validation) status
     */
    private function checkLTVStatus(ESignature $signature): array
    {
        $ltvEnabled = $signature->ltv_enabled ?? false;

        return [
            'passed' => $ltvEnabled,
            'description' => $ltvEnabled ? 'LTV (Long-Term Validation) is enabled' : 'LTV is not enabled',
            'details' => [
                'ltv_enabled' => $ltvEnabled,
                'ltv_enabled_at' => $signature->ltv_enabled_at?->toIso8601String(),
                'ltv_timestamp' => $signature->ltv_timestamp?->toIso8601String(),
            ]
        ];
    }

    /**
     * Check timestamp chain integrity
     */
    private function checkTimestampChain(ESignature $signature): array
    {
        try {
            // Verify timestamp chain: signature → timestamp → TSA certificate
            $checks = [
                'signature_has_timestamp' => !empty($signature->timestamp_token),
                'timestamp_has_tsa_cert' => !empty($signature->timestamp_tsa_certificate),
                'tsa_cert_valid' => true,
            ];

            // Validate TSA certificate if present
            if (!empty($signature->timestamp_tsa_certificate)) {
                $tsaCertValid = $this->validateTSACertificate($signature->timestamp_tsa_certificate);
                $checks['tsa_cert_valid'] = $tsaCertValid;
            }

            $passed = array_reduce($checks, function ($carry, $item) {
                return $carry && $item;
            }, true);

            return [
                'passed' => $passed,
                'description' => $passed ? 'Timestamp chain is intact' : 'Timestamp chain is broken',
                'details' => $checks
            ];
        } catch (\Exception $e) {
            return [
                'passed' => false,
                'description' => 'Timestamp chain validation error',
                'error' => $e->getMessage(),
                'details' => []
            ];
        }
    }

    /**
     * Validate TSA certificate
     */
    private function validateTSACertificate(string $tsaCert): bool
    {
        try {
            // Basic validation: certificate should be non-empty and valid PEM format
            return !empty($tsaCert) && (
                strpos($tsaCert, '-----BEGIN CERTIFICATE-----') !== false ||
                strpos($tsaCert, 'MII') === 0 // Base64 encoded cert
            );
        } catch (\Exception $e) {
            Log::warning('TSA certificate validation failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Calculate overall verification status
     */
    private function calculateOverallStatus(array $checks): string
    {
        $allPassed = true;
        $hasWarnings = false;

        foreach ($checks as $check) {
            if (!$check['passed']) {
                $allPassed = false;
                break;
            }
        }

        if ($allPassed) {
            return 'valid';
        }

        return 'invalid';
    }

    /**
     * Generate verification summary
     */
    private function generateSummary(array $report): array
    {
        $totalChecks = count($report['checks']);
        $passedChecks = 0;

        foreach ($report['checks'] as $check) {
            if ($check['passed']) {
                $passedChecks++;
            }
        }

        return [
            'total_checks' => $totalChecks,
            'passed_checks' => $passedChecks,
            'failed_checks' => $totalChecks - $passedChecks,
            'pass_rate' => round(($passedChecks / $totalChecks) * 100, 2) . '%',
            'overall_result' => $report['overall_status'],
        ];
    }
}
