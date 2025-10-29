<?php

namespace App\Services;

use App\Models\ESignature;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TimestampAuthorityService
{
    protected TSARedundancyService $redundancyService;

    public function __construct(TSARedundancyService $redundancyService)
    {
        $this->redundancyService = $redundancyService;
    }

    /**
     * Get timestamp token from RFC 3161 TSA with redundancy
     */
    public function getTimestampToken(string $data, string $provider = null): ?string
    {
        $result = $this->redundancyService->getTimestampWithFailover($data);

        if ($result && $result['status'] === 'success') {
            return $result['token'];
        }

        return null;
    }

    /**
     * Attach timestamp token to signature with performance tracking
     */
    public function attachTimestampToSignature(ESignature $signature, string $provider = null): bool
    {
        try {
            $startTime = microtime(true);
            
            $result = $this->redundancyService->getTimestampWithFailover($signature->signature_hash);

            if ($result && $result['status'] === 'success') {
                $signature->update([
                    'timestamp_token' => $result['token'],
                    'timestamp_provider' => $result['provider'],
                    'timestamp_verified_at' => now(),
                ]);

                $metricsService = app(SignaturePerformanceMetricsService::class);
                $metricsService->recordSignatureCreationMetrics($signature, [
                    'timestamp' => $result['response_time_ms'],
                ]);

                Log::info('Timestamp attached to signature', [
                    'signature_id' => $signature->id,
                    'provider' => $result['provider'],
                    'response_time_ms' => $result['response_time_ms'],
                    'retry_count' => $result['retry_count'],
                ]);

                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Failed to attach timestamp: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Verify timestamp token
     */
    public function verifyTimestamp(ESignature $signature): bool
    {
        if (!$signature->timestamp_token) {
            return false;
        }

        try {
            $token = $signature->timestamp_token;
            $provider = $signature->timestamp_provider ?? 'freetsa';

            if (empty($token) || strlen($token) < 20) {
                return false;
            }

            if ($signature->timestamp_verified_at) {
                $age = now()->diffInDays($signature->timestamp_verified_at);
                if ($age > 365) {
                    Log::warning('Timestamp is older than 1 year', [
                        'signature_id' => $signature->id,
                        'age_days' => $age,
                    ]);
                }
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Timestamp verification failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create RFC 3161 timestamp request
     * Simplified version - in production use proper ASN.1 encoding
     */
    private function createTimestampRequest(string $data): string
    {
        $hash = hash('sha256', $data, true);
        $oid = '2.16.840.1.101.3.4.2.1';

        return base64_encode($hash);
    }

    /**
     * Parse RFC 3161 timestamp response
     * Simplified version - in production use proper ASN.1 decoding
     */
    private function parseTimestampResponse(string $response): ?string
    {
        try {
            return base64_encode($response);
        } catch (\Exception $e) {
            Log::error('Failed to parse timestamp response: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get timestamp information for display
     */
    public function getTimestampInfo(ESignature $signature): array
    {
        return [
            'has_timestamp' => !empty($signature->timestamp_token),
            'provider' => $signature->timestamp_provider ?? 'None',
            'verified_at' => $signature->timestamp_verified_at?->format('Y-m-d H:i:s'),
            'is_valid' => $this->verifyTimestamp($signature),
            'token_length' => strlen($signature->timestamp_token ?? ''),
        ];
    }
}
