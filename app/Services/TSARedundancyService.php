<?php

namespace App\Services;

use App\Models\ESignature;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class TSARedundancyService
{
    /**
     * Enhanced TSA configuration with redundancy
     */
    private const TSA_PROVIDERS = [
        'freetsa' => [
            'url' => 'http://freetsa.org/tst',
            'priority' => 1,
            'timeout' => 10,
            'retry_count' => 3,
        ],
        'digicert' => [
            'url' => 'http://timestamp.digicert.com',
            'priority' => 2,
            'timeout' => 10,
            'retry_count' => 3,
        ],
        'sectigo' => [
            'url' => 'http://timestamp.sectigo.com',
            'priority' => 3,
            'timeout' => 10,
            'retry_count' => 3,
        ],
        'apple' => [
            'url' => 'http://timestamp.apple.com/ts01',
            'priority' => 4,
            'timeout' => 10,
            'retry_count' => 3,
        ],
    ];

    /**
     * Get timestamp with automatic failover
     */
    public function getTimestampWithFailover(string $data): ?array
    {
        $providers = collect(self::TSA_PROVIDERS)
            ->sortBy('priority')
            ->toArray();
        
        foreach ($providers as $providerName => $config) {
            $result = $this->tryTimestampProvider($providerName, $config, $data);
            
            if ($result) {
                return $result;
            }
        }
        
        Log::error('All TSA providers failed', [
            'providers' => array_keys($providers),
        ]);
        
        return null;
    }

    /**
     * Try a specific TSA provider with retry logic
     */
    private function tryTimestampProvider(string $providerName, array $config, string $data): ?array
    {
        $startTime = microtime(true);
        $retryCount = 0;
        
        for ($attempt = 1; $attempt <= $config['retry_count']; $attempt++) {
            try {
                $response = Http::timeout($config['timeout'])
                    ->withHeaders([
                        'Content-Type' => 'application/timestamp-query',
                    ])
                    ->post($config['url'], base64_encode(hash('sha256', $data, true)));
                
                if ($response->successful()) {
                    $responseTime = (int)((microtime(true) - $startTime) * 1000);
                    
                    Log::info('TSA request successful', [
                        'provider' => $providerName,
                        'attempt' => $attempt,
                        'response_time_ms' => $responseTime,
                    ]);
                    
                    return [
                        'token' => base64_encode($response->body()),
                        'provider' => $providerName,
                        'response_time_ms' => $responseTime,
                        'retry_count' => $attempt - 1,
                        'status' => 'success',
                    ];
                }
                
                $retryCount = $attempt;
                
            } catch (\Exception $e) {
                $retryCount = $attempt;
                
                Log::warning('TSA request failed', [
                    'provider' => $providerName,
                    'attempt' => $attempt,
                    'error' => $e->getMessage(),
                ]);
                
                // Wait before retry (exponential backoff)
                if ($attempt < $config['retry_count']) {
                    usleep(1000 * (100 * $attempt)); // 100ms, 200ms, 300ms
                }
            }
        }
        
        $responseTime = (int)((microtime(true) - $startTime) * 1000);
        
        return [
            'token' => null,
            'provider' => $providerName,
            'response_time_ms' => $responseTime,
            'retry_count' => $retryCount,
            'status' => 'failed',
        ];
    }

    /**
     * Get TSA provider health status
     */
    public function getTSAHealthStatus(): array
    {
        $health = [];
        
        foreach (self::TSA_PROVIDERS as $providerName => $config) {
            $cacheKey = "tsa_health_{$providerName}";
            $cachedHealth = Cache::get($cacheKey);
            
            if ($cachedHealth) {
                $health[$providerName] = $cachedHealth;
                continue;
            }
            
            $status = $this->checkProviderHealth($providerName, $config);
            Cache::put($cacheKey, $status, now()->addMinutes(5));
            $health[$providerName] = $status;
        }
        
        return $health;
    }

    /**
     * Check individual provider health
     */
    private function checkProviderHealth(string $providerName, array $config): array
    {
        try {
            $startTime = microtime(true);
            
            $response = Http::timeout($config['timeout'])
                ->withHeaders([
                    'Content-Type' => 'application/timestamp-query',
                ])
                ->post($config['url'], base64_encode(hash('sha256', 'health-check', true)));
            
            $responseTime = (int)((microtime(true) - $startTime) * 1000);
            
            return [
                'provider' => $providerName,
                'status' => $response->successful() ? 'healthy' : 'unhealthy',
                'response_time_ms' => $responseTime,
                'last_checked_at' => now()->toIso8601String(),
                'priority' => $config['priority'],
            ];
            
        } catch (\Exception $e) {
            return [
                'provider' => $providerName,
                'status' => 'unreachable',
                'error' => $e->getMessage(),
                'last_checked_at' => now()->toIso8601String(),
                'priority' => $config['priority'],
            ];
        }
    }

    /**
     * Get recommended TSA provider based on health
     */
    public function getRecommendedProvider(): string
    {
        $health = $this->getTSAHealthStatus();
        
        $healthy = collect($health)
            ->filter(fn($h) => $h['status'] === 'healthy')
            ->sortBy('priority')
            ->first();
        
        return $healthy['provider'] ?? 'freetsa';
    }

    /**
     * Load balance across healthy providers
     */
    public function getLoadBalancedProvider(): string
    {
        $health = $this->getTSAHealthStatus();
        
        $healthy = collect($health)
            ->filter(fn($h) => $h['status'] === 'healthy')
            ->sortBy('response_time_ms')
            ->first();
        
        return $healthy['provider'] ?? 'freetsa';
    }
}
