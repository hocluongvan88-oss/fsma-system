<?php

namespace App\Services;

use App\Models\DigitalCertificate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class CertificateRevocationService
{
    /**
     * Check certificate revocation status via OCSP
     */
    public function checkOCSPStatus(DigitalCertificate $certificate): array
    {
        try {
            // Try to get OCSP responder URL from certificate
            $ocspUrl = $this->extractOCSPUrl($certificate);
            
            if (!$ocspUrl) {
                return [
                    'status' => 'unknown',
                    'method' => 'none',
                    'error' => 'No OCSP responder URL found',
                    'checked_at' => now()->toIso8601String(),
                ];
            }

            // Check cache first
            $cacheKey = 'ocsp_status_' . md5($certificate->certificate_data);
            $cached = Cache::get($cacheKey);
            
            if ($cached) {
                return array_merge($cached, ['from_cache' => true]);
            }

            // Create OCSP request
            $ocspRequest = $this->createOCSPRequest($certificate);
            
            // Send OCSP request
            $response = Http::timeout(10)
                ->withHeaders([
                    'Content-Type' => 'application/ocsp-request',
                ])
                ->post($ocspUrl, $ocspRequest);

            if (!$response->successful()) {
                return [
                    'status' => 'unknown',
                    'method' => 'ocsp',
                    'error' => 'OCSP request failed: ' . $response->status(),
                    'checked_at' => now()->toIso8601String(),
                ];
            }

            // Parse OCSP response
            $result = $this->parseOCSPResponse($response->body());
            
            // Cache result for 24 hours
            Cache::put($cacheKey, $result, now()->addHours(24));

            return $result;
        } catch (\Exception $e) {
            Log::error('OCSP check error: ' . $e->getMessage());
            return [
                'status' => 'unknown',
                'method' => 'ocsp',
                'error' => $e->getMessage(),
                'checked_at' => now()->toIso8601String(),
            ];
        }
    }

    /**
     * Check certificate revocation status via CRL
     */
    public function checkCRLStatus(DigitalCertificate $certificate): array
    {
        try {
            // Extract CRL distribution points from certificate
            $crlUrls = $this->extractCRLUrls($certificate);
            
            if (empty($crlUrls)) {
                return [
                    'status' => 'unknown',
                    'method' => 'none',
                    'error' => 'No CRL distribution points found',
                    'checked_at' => now()->toIso8601String(),
                ];
            }

            // Try each CRL URL
            foreach ($crlUrls as $crlUrl) {
                $result = $this->checkCRLUrl($crlUrl, $certificate);
                
                if ($result['status'] !== 'unknown') {
                    return $result;
                }
            }

            return [
                'status' => 'unknown',
                'method' => 'crl',
                'error' => 'All CRL URLs failed',
                'checked_at' => now()->toIso8601String(),
            ];
        } catch (\Exception $e) {
            Log::error('CRL check error: ' . $e->getMessage());
            return [
                'status' => 'unknown',
                'method' => 'crl',
                'error' => $e->getMessage(),
                'checked_at' => now()->toIso8601String(),
            ];
        }
    }

    /**
     * Check single CRL URL
     */
    private function checkCRLUrl(string $crlUrl, DigitalCertificate $certificate): array
    {
        try {
            // Check cache first
            $cacheKey = 'crl_' . md5($crlUrl);
            $cachedCRL = Cache::get($cacheKey);
            
            if (!$cachedCRL) {
                // Download CRL
                $response = Http::timeout(30)->get($crlUrl);
                
                if (!$response->successful()) {
                    return [
                        'status' => 'unknown',
                        'method' => 'crl',
                        'error' => 'Failed to download CRL',
                        'checked_at' => now()->toIso8601String(),
                    ];
                }
                
                $cachedCRL = $response->body();
                
                // Cache CRL for 7 days
                Cache::put($cacheKey, $cachedCRL, now()->addDays(7));
            }

            // Parse CRL and check if certificate is revoked
            $isRevoked = $this->isCertificateInCRL($cachedCRL, $certificate);
            
            return [
                'status' => $isRevoked ? 'revoked' : 'good',
                'method' => 'crl',
                'crl_url' => $crlUrl,
                'checked_at' => now()->toIso8601String(),
            ];
        } catch (\Exception $e) {
            Log::error('CRL URL check error: ' . $e->getMessage());
            return [
                'status' => 'unknown',
                'method' => 'crl',
                'error' => $e->getMessage(),
                'checked_at' => now()->toIso8601String(),
            ];
        }
    }

    /**
     * Check if certificate is in CRL
     */
    private function isCertificateInCRL(string $crlData, DigitalCertificate $certificate): bool
    {
        try {
            // Parse CRL and check serial number
            // This is simplified - proper implementation requires X.509 parsing
            
            $serialNumber = $certificate->serial_number;
            
            // Check if serial number appears in CRL
            if (strpos($crlData, $serialNumber) !== false) {
                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('CRL parsing error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Extract OCSP responder URL from certificate
     */
    private function extractOCSPUrl(DigitalCertificate $certificate): ?string
    {
        try {
            // Parse certificate to extract OCSP URL
            // This is simplified - proper implementation requires X.509 parsing
            
            $certData = $certificate->certificate_data;
            
            // Look for OCSP URL in certificate extensions
            if (preg_match('/OCSP\s*-\s*URI:([^\s,]+)/i', $certData, $matches)) {
                return $matches[1];
            }

            return null;
        } catch (\Exception $e) {
            Log::error('OCSP URL extraction error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Extract CRL distribution points from certificate
     */
    private function extractCRLUrls(DigitalCertificate $certificate): array
    {
        try {
            $urls = [];
            $certData = $certificate->certificate_data;
            
            // Look for CRL URLs in certificate extensions
            if (preg_match_all('/CRL\s*Distribution\s*Points:([^\n]+)/i', $certData, $matches)) {
                foreach ($matches[1] as $match) {
                    if (preg_match('/URI:([^\s,]+)/i', $match, $urlMatch)) {
                        $urls[] = $urlMatch[1];
                    }
                }
            }

            return $urls;
        } catch (\Exception $e) {
            Log::error('CRL URL extraction error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Create OCSP request
     */
    private function createOCSPRequest(DigitalCertificate $certificate): string
    {
        // Simplified OCSP request creation
        // In production, use proper ASN.1 encoding
        
        return json_encode([
            'version' => 'v1',
            'serialNumber' => $certificate->serial_number,
            'issuerNameHash' => hash('sha1', $certificate->issuer),
            'issuerKeyHash' => hash('sha1', $certificate->public_key ?? ''),
        ]);
    }

    /**
     * Parse OCSP response
     */
    private function parseOCSPResponse(string $response): array
    {
        try {
            // Simplified OCSP response parsing
            // In production, use proper ASN.1 decoding
            
            // Check response status
            if (strpos($response, 'good') !== false) {
                return [
                    'status' => 'good',
                    'method' => 'ocsp',
                    'checked_at' => now()->toIso8601String(),
                ];
            } elseif (strpos($response, 'revoked') !== false) {
                return [
                    'status' => 'revoked',
                    'method' => 'ocsp',
                    'checked_at' => now()->toIso8601String(),
                ];
            }

            return [
                'status' => 'unknown',
                'method' => 'ocsp',
                'checked_at' => now()->toIso8601String(),
            ];
        } catch (\Exception $e) {
            Log::error('OCSP response parsing error: ' . $e->getMessage());
            return [
                'status' => 'unknown',
                'method' => 'ocsp',
                'error' => $e->getMessage(),
                'checked_at' => now()->toIso8601String(),
            ];
        }
    }
}
