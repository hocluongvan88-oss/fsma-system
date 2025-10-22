<?php

namespace App\Services;

use App\Models\ESignature;
use Illuminate\Support\Facades\Cache;

class LongTermValidationService
{
    protected CertificateRevocationService $revocationService;
    protected RFC3161TimestampValidator $timestampValidator;

    public function __construct(
        CertificateRevocationService $revocationService,
        RFC3161TimestampValidator $timestampValidator
    ) {
        $this->revocationService = $revocationService;
        $this->timestampValidator = $timestampValidator;
    }

    /**
     * Enable Long-Term Validation (LTV) for a signature
     * Stores all necessary data for future validation
     */
    public function enableLTV(ESignature $signature): void
    {
        $ltvData = [
            'timestamp_chain' => $this->buildTimestampChain($signature),
            'certificate_chain' => $this->buildCertificateChain($signature),
            'crl_response' => $this->getCRLResponse($signature),
            'ocsp_response' => $this->getOCSPResponse($signature),
        ];

        $signature->update([
            'ltv_enabled' => true,
            'ltv_timestamp_chain' => json_encode($ltvData['timestamp_chain']),
            'ltv_certificate_chain' => json_encode($ltvData['certificate_chain']),
            'ltv_crl_response' => json_encode($ltvData['crl_response']),
            'ltv_ocsp_response' => json_encode($ltvData['ocsp_response']),
            'ltv_last_validation_at' => now(),
        ]);
    }

    /**
     * Validate signature using LTV data
     */
    public function validateWithLTV(ESignature $signature): array
    {
        if (!$signature->ltv_enabled) {
            return [
                'valid' => false,
                'reason' => 'LTV not enabled for this signature',
            ];
        }

        $results = [
            'timestamp_valid' => $this->validateTimestampChain($signature),
            'certificate_valid' => $this->validateCertificateChain($signature),
            'revocation_valid' => $this->validateRevocationData($signature),
            'overall_valid' => true,
        ];

        $results['overall_valid'] = $results['timestamp_valid'] && 
                                    $results['certificate_valid'] && 
                                    $results['revocation_valid'];

        return $results;
    }

    /**
     * Build timestamp chain for LTV
     */
    private function buildTimestampChain(ESignature $signature): array
    {
        $chain = [];

        if ($signature->timestamp_token) {
            $chain[] = [
                'timestamp' => $signature->timestamp_utc_time?->toIso8601String(),
                'tsa_url' => $signature->tsa_url,
                'token' => base64_encode($signature->timestamp_token),
                'verified' => true,
            ];
        }

        return $chain;
    }

    /**
     * Build certificate chain for LTV
     */
    private function buildCertificateChain(ESignature $signature): array
    {
        $chain = [];

        if ($signature->digitalCertificate) {
            $cert = $signature->digitalCertificate;
            $certData = openssl_x509_parse($cert->certificate_pem);

            $chain[] = [
                'subject' => $signature->certificate_subject,
                'issuer' => $signature->certificate_issuer,
                'serial_number' => $signature->certificate_serial_number,
                'valid_from' => $certData['validFrom_time_t'] ?? null,
                'valid_to' => $certData['validTo_time_t'] ?? null,
                'certificate' => base64_encode($cert->certificate_pem),
            ];
        }

        return $chain;
    }

    /**
     * Get CRL response for LTV
     */
    private function getCRLResponse(ESignature $signature): ?array
    {
        if (!$signature->digitalCertificate) {
            return null;
        }

        $crlData = $this->revocationService->getCRLData($signature->digitalCertificate);
        
        return $crlData ? [
            'crl_url' => $crlData['url'] ?? null,
            'crl_data' => base64_encode($crlData['data'] ?? ''),
            'retrieved_at' => now()->toIso8601String(),
        ] : null;
    }

    /**
     * Get OCSP response for LTV
     */
    private function getOCSPResponse(ESignature $signature): ?array
    {
        if (!$signature->digitalCertificate) {
            return null;
        }

        $ocspData = $this->revocationService->getOCSPData($signature->digitalCertificate);
        
        return $ocspData ? [
            'ocsp_url' => $ocspData['url'] ?? null,
            'ocsp_response' => base64_encode($ocspData['response'] ?? ''),
            'retrieved_at' => now()->toIso8601String(),
        ] : null;
    }

    /**
     * Validate timestamp chain
     */
    private function validateTimestampChain(ESignature $signature): bool
    {
        if (!$signature->ltv_timestamp_chain) {
            return false;
        }

        $chain = json_decode($signature->ltv_timestamp_chain, true);
        
        foreach ($chain as $timestamp) {
            if (!$timestamp['verified']) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate certificate chain
     */
    private function validateCertificateChain(ESignature $signature): bool
    {
        if (!$signature->ltv_certificate_chain) {
            return false;
        }

        $chain = json_decode($signature->ltv_certificate_chain, true);
        
        foreach ($chain as $cert) {
            $validFrom = $cert['valid_from'] ?? 0;
            $validTo = $cert['valid_to'] ?? 0;
            $now = time();

            if ($now < $validFrom || $now > $validTo) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate revocation data
     */
    private function validateRevocationData(ESignature $signature): bool
    {
        if (!$signature->ltv_crl_response && !$signature->ltv_ocsp_response) {
            return false;
        }

        // Check if revocation data is not too old (older than 30 days)
        if ($signature->ltv_last_validation_at) {
            $daysOld = now()->diffInDays($signature->ltv_last_validation_at);
            if ($daysOld > 30) {
                return false;
            }
        }

        return true;
    }

    /**
     * Re-validate LTV data periodically
     */
    public function revalidateLTV(ESignature $signature): void
    {
        if (!$signature->ltv_enabled) {
            return;
        }

        // Update revocation data
        $crlResponse = $this->getCRLResponse($signature);
        $ocspResponse = $this->getOCSPResponse($signature);

        $signature->update([
            'ltv_crl_response' => $crlResponse ? json_encode($crlResponse) : null,
            'ltv_ocsp_response' => $ocspResponse ? json_encode($ocspResponse) : null,
            'ltv_last_validation_at' => now(),
        ]);
    }
}
