<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use App\Services\AuditLogService;

/**
 * CTE Logging Service
 * 
 * Centralized logging for CTE operations with FSMA 204 & Part 11 compliance
 * 
 * Features:
 * - Environment-aware logging (production vs development)
 * - Batch logging for loops (prevents N+1 logging)
 * - Structured audit logging via AuditLogService
 * - Performance monitoring
 * - 24-month retention for compliance
 */
class CTELoggingService
{
    protected $auditLogService;
    protected $isProduction;
    protected $debugEnabled;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
        $this->isProduction = app()->environment('production');
        $this->debugEnabled = env('CTE_DEBUG_ENABLED', false);
    }

    /**
     * Log CTE event creation (FSMA 204 compliance - required)
     */
    public function logCTEEventCreated(string $eventType, int $eventId, array $data = [])
    {
        Log::channel('cte_audit')->info("CTE Event Created: {$eventType}", [
            'event_id' => $eventId,
            'event_type' => $eventType,
            'user_id' => auth()->id(),
            'organization_id' => auth()->user()->organization_id ?? null,
            'timestamp' => now()->toIso8601String(),
            'data' => $data,
        ]);

        // Also log to audit_logs table for database queries
        $this->auditLogService->log(
            'create',
            'cte_events',
            $eventId,
            null,
            $data
        );
    }

    /**
     * Log VOID event (Part 11 compliance - required)
     */
    public function logVoidEvent(int $eventId, string $reason, array $data = [])
    {
        Log::channel('cte_audit')->warning("CTE Event Voided", [
            'event_id' => $eventId,
            'reason' => $reason,
            'user_id' => auth()->id(),
            'timestamp' => now()->toIso8601String(),
            'data' => $data,
        ]);

        $this->auditLogService->log(
            'void',
            'cte_events',
            $eventId,
            null,
            array_merge($data, ['reason' => $reason])
        );
    }

    /**
     * Log e-signature creation (Part 11 compliance - required)
     */
    public function logESignatureCreated(int $signatureId, string $action, int $recordId)
    {
        Log::channel('cte_audit')->info("E-Signature Created", [
            'signature_id' => $signatureId,
            'action' => $action,
            'record_id' => $recordId,
            'user_id' => auth()->id(),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Log validation failure (WARNING level)
     */
    public function logValidationFailure(string $context, array $errors)
    {
        Log::channel('cte_audit')->warning("Validation Failed: {$context}", [
            'errors' => $errors,
            'user_id' => auth()->id(),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Log transaction failure (ERROR level - required)
     */
    public function logTransactionFailure(string $operation, \Exception $exception)
    {
        Log::channel('cte_audit')->error("Transaction Failed: {$operation}", [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
            'user_id' => auth()->id(),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Debug logging (development only)
     * Only logs if CTE_DEBUG_ENABLED=true and not in production
     */
    public function debug(string $message, array $context = [])
    {
        if (!$this->isProduction && $this->debugEnabled) {
            Log::channel('cte_debug')->debug($message, $context);
        }
    }

    /**
     * Batch log for collections (prevents N+1 logging)
     * Example: Instead of logging 500 TLCs individually, log summary
     */
    public function logBatch(string $message, $collection, int $sampleSize = 10)
    {
        if (!$this->isProduction && $this->debugEnabled) {
            $count = is_countable($collection) ? count($collection) : $collection->count();
            $sample = is_array($collection) 
                ? array_slice($collection, 0, $sampleSize)
                : $collection->take($sampleSize);

            Log::channel('cte_debug')->debug($message, [
                'total_count' => $count,
                'sample_size' => min($count, $sampleSize),
                'sample' => $sample,
            ]);
        }
    }

    /**
     * Log performance metrics (development only)
     */
    public function logPerformance(string $operation, float $executionTimeMs, array $metrics = [])
    {
        if (!$this->isProduction && $this->debugEnabled) {
            Log::channel('cte_debug')->debug("Performance: {$operation}", array_merge([
                'execution_time_ms' => $executionTimeMs,
                'timestamp' => now()->toIso8601String(),
            ], $metrics));
        }
    }

    /**
     * Check if debug logging is enabled
     */
    public function isDebugEnabled(): bool
    {
        return !$this->isProduction && $this->debugEnabled;
    }
}
