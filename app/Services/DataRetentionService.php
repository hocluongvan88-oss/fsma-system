<?php

namespace App\Services;

use App\Models\RetentionPolicy;
use App\Models\RetentionLog;
use App\Models\ErrorLog;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class DataRetentionService
{
    private const PROTECTED_DATA_TYPES = [
        'trace_records',      // Core traceability data
        'cte_events',         // Immutable per FSMA 204
        'trace_relationships',// Audit trail
        'audit_logs',         // Compliance requirement
        'e_signatures',       // Legal requirement (21 CFR Part 11)
    ];

    private const DELETABLE_DATA_TYPES = [
        'error_logs',
        'notifications',
    ];

    /**
     * Get all active retention policies
     */
    public function getActivePolicies(): array
    {
        return RetentionPolicy::active()->get()->keyBy('data_type')->toArray();
    }

    /**
     * Create or update retention policy
     */
    public function createPolicy(array $data): RetentionPolicy
    {
        if (in_array($data['data_type'], self::PROTECTED_DATA_TYPES)) {
            throw new \InvalidArgumentException(
                "Cannot create deletion policy for protected data type: {$data['data_type']}. " .
                "This data is protected by FSMA 204 immutability requirements and must never be deleted."
            );
        }

        $existing = RetentionPolicy::where('data_type', $data['data_type'])->first();
        
        if ($existing) {
            $existing->update($data);
            return $existing;
        }

        return RetentionPolicy::create($data);
    }

    /**
     * Execute retention cleanup for a specific data type
     */
    public function executeCleanup(string $dataType, bool $dryRun = false): array
    {
        if (in_array($dataType, self::PROTECTED_DATA_TYPES)) {
            Log::warning("Attempted to delete protected data type: {$dataType}");
            return [
                'status' => 'blocked',
                'reason' => "Data type '{$dataType}' is protected by FSMA 204 immutability requirements and cannot be deleted. Use archival instead.",
                'records_deleted' => 0,
                'records_backed_up' => 0,
            ];
        }

        if (!in_array($dataType, self::DELETABLE_DATA_TYPES)) {
            return [
                'status' => 'skipped',
                'reason' => "Data type '{$dataType}' is not configured for deletion.",
                'records_deleted' => 0,
                'records_backed_up' => 0,
            ];
        }

        $policy = RetentionPolicy::active()->byDataType($dataType)->first();
        
        if (!$policy || $policy->retention_months <= 0) {
            return ['status' => 'skipped', 'reason' => 'No active policy or indefinite retention'];
        }

        $cutoffDate = now()->subMonths($policy->retention_months);
        $stats = [
            'records_deleted' => 0,
            'records_backed_up' => 0,
            'backup_file_path' => null,
            'status' => 'success',
            'error_message' => null,
        ];

        try {
            DB::transaction(function () use ($policy, $dataType, $cutoffDate, $dryRun, &$stats) {
                // Backup if enabled
                if ($policy->backup_before_deletion && !$dryRun) {
                    $backupPath = $this->backupData($dataType, $cutoffDate);
                    $stats['backup_file_path'] = $backupPath;
                    $stats['records_backed_up'] = $this->countRecordsToDelete($dataType, $cutoffDate);
                }

                // Delete data (only non-critical data)
                if (!$dryRun) {
                    $stats['records_deleted'] = $this->deleteOldData($dataType, $cutoffDate);
                } else {
                    $stats['records_deleted'] = $this->countRecordsToDelete($dataType, $cutoffDate);
                }
            });

            // Log the retention action
            if (!$dryRun) {
                $this->logRetention($policy, $stats);
            }

        } catch (\Exception $e) {
            $stats['status'] = 'failed';
            $stats['error_message'] = $e->getMessage();
            Log::error("Retention cleanup failed for {$dataType}: " . $e->getMessage());
        }

        return $stats;
    }

    /**
     * Count records to be deleted
     */
    private function countRecordsToDelete(string $dataType, Carbon $cutoffDate): int
    {
        return match($dataType) {
            'error_logs' => ErrorLog::where('created_at', '<', $cutoffDate)->count(),
            'notifications' => Notification::where('created_at', '<', $cutoffDate)->count(),
            default => 0,
        };
    }

    /**
     * Delete old data (only non-critical data)
     */
    private function deleteOldData(string $dataType, Carbon $cutoffDate): int
    {
        return match($dataType) {
            'error_logs' => $this->deleteInChunks(ErrorLog::class, $cutoffDate),
            'notifications' => $this->deleteInChunks(Notification::class, $cutoffDate),
            default => 0,
        };
    }

    /**
     * Delete records in chunks to prevent performance issues
     */
    private function deleteInChunks(string $modelClass, Carbon $cutoffDate, int $chunkSize = 1000): int
    {
        $totalDeleted = 0;
        
        do {
            $deleted = $modelClass::where('created_at', '<', $cutoffDate)
                ->limit($chunkSize)
                ->delete();
            
            $totalDeleted += $deleted;
            
            // Small delay to prevent database overload
            if ($deleted > 0) {
                usleep(100000); // 100ms
            }
        } while ($deleted > 0);
        
        return $totalDeleted;
    }

    /**
     * Backup data before deletion
     */
    private function backupData(string $dataType, Carbon $cutoffDate): string
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = "retention_backup_{$dataType}_{$timestamp}.json";
        $path = "backups/retention/{$filename}";

        $data = match($dataType) {
            'error_logs' => ErrorLog::where('created_at', '<', $cutoffDate)->get(),
            'notifications' => Notification::where('created_at', '<', $cutoffDate)->get(),
            default => [],
        };

        Storage::disk('local')->put($path, json_encode($data, JSON_PRETTY_PRINT));
        return $path;
    }

    /**
     * Log retention action
     */
    private function logRetention(RetentionPolicy $policy, array $stats): void
    {
        RetentionLog::create([
            'retention_policy_id' => $policy->id,
            'data_type' => $policy->data_type,
            'records_deleted' => $stats['records_deleted'],
            'records_backed_up' => $stats['records_backed_up'],
            'backup_file_path' => $stats['backup_file_path'],
            'executed_at' => now(),
            'executed_by' => auth()->user()?->email ?? 'system',
            'status' => $stats['status'],
            'error_message' => $stats['error_message'],
        ]);
    }

    /**
     * Get retention statistics
     */
    public function getRetentionStats(): array
    {
        $policies = RetentionPolicy::active()->get();
        $stats = [];

        foreach ($policies as $policy) {
            $cutoffDate = now()->subMonths($policy->retention_months);
            
            $isProtected = in_array($policy->data_type, self::PROTECTED_DATA_TYPES);
            
            $stats[$policy->data_type] = [
                'policy_name' => $policy->policy_name,
                'retention_months' => $policy->retention_months,
                'is_protected' => $isProtected,
                'protection_reason' => $isProtected ? 'FSMA 204 Immutability Requirement' : null,
                'records_to_delete' => $isProtected ? 0 : $this->countRecordsToDelete($policy->data_type, $cutoffDate),
                'last_cleanup' => RetentionLog::where('data_type', $policy->data_type)
                    ->where('status', 'success')
                    ->latest('executed_at')
                    ->first()?->executed_at,
            ];
        }

        return $stats;
    }

    /**
     * Archive old CTE data (move to cold storage, not delete)
     * This is for performance optimization while maintaining compliance
     */
    public function archiveOldData(string $dataType, int $monthsOld = 36): array
    {
        if (!in_array($dataType, ['cte_events', 'trace_records'])) {
            return [
                'status' => 'skipped',
                'reason' => 'Data type not configured for archival',
            ];
        }

        // TODO: Implement archival to separate tables or cold storage
        // This maintains data accessibility while improving query performance
        
        return [
            'status' => 'not_implemented',
            'reason' => 'Archival feature coming soon',
        ];
    }

    /**
     * Validate that critical data is not being deleted
     */
    public function validateRetentionPolicy(string $dataType, int $retentionMonths): array
    {
        $errors = [];

        if (in_array($dataType, self::PROTECTED_DATA_TYPES)) {
            $errors[] = "Data type '{$dataType}' is protected by FSMA 204 and cannot have a deletion policy.";
        }

        if ($retentionMonths > 0 && $retentionMonths < 24) {
            $errors[] = "Retention period must be at least 24 months for FDA compliance (recommended: indefinite).";
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
}
