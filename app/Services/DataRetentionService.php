<?php

namespace App\Services;

use App\Models\RetentionPolicy;
use App\Models\RetentionLog;
use App\Models\ErrorLog;
use App\Models\Notification;
use App\Models\CTEEvent;
use App\Models\TraceRecord;
use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Cache;

class DataRetentionService
{
    public const PROTECTED_DATA_TYPES = [
        'trace_records',
        'cte_events',
        'trace_relationships',
        'audit_logs',
        'e_signatures',
    ];

    public const DELETABLE_DATA_TYPES = [
        'error_logs',
        'notifications',
    ];

    public const FSMA_204_MINIMUM_RETENTION_MONTHS = 27;
    
    private const RETENTION_STATS_CACHE_KEY = 'retention_stats_org_{org_id}';
    private const RETENTION_STATS_CACHE_TTL = 3600; // 1 hour

    /**
     * Get organization ID with validation
     * Throws exception if organization context is missing
     * FSMA 204 Compliance: Prevents cross-organization data access
     */
    private function getOrganizationId(): int
    {
        $orgId = auth()->user()?->organization_id;
        
        if (!$orgId) {
            throw new \InvalidArgumentException(
                'Organization context required for retention operations. ' .
                'User must be authenticated and belong to an organization.'
            );
        }

        return $orgId;
    }

    public function getActivePolicies(): array
    {
        return RetentionPolicy::active()->get()->keyBy('data_type')->toArray();
    }

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

            $this->logRetention($policy, $stats, $dryRun);
            
            $this->clearRetentionStatsCache();

        } catch (\Exception $e) {
            $stats['status'] = 'failed';
            $stats['error_message'] = $e->getMessage();
            Log::error("Retention cleanup failed for {$dataType}: " . $e->getMessage());
        }

        return $stats;
    }

    private function countRecordsToDelete(string $dataType, Carbon $cutoffDate): int
    {
        $orgId = $this->getOrganizationId();
        
        return match($dataType) {
            'error_logs' => ErrorLog::where('created_at', '<', $cutoffDate)
                ->where('organization_id', $orgId)
                ->count(),
            'notifications' => Notification::where('created_at', '<', $cutoffDate)
                ->where('organization_id', $orgId)
                ->count(),
            default => 0,
        };
    }

    private function deleteOldData(string $dataType, Carbon $cutoffDate): int
    {
        $orgId = $this->getOrganizationId();
        
        return match($dataType) {
            'error_logs' => $this->deleteInChunks(ErrorLog::class, $cutoffDate, organizationId: $orgId),
            'notifications' => $this->deleteInChunks(Notification::class, $cutoffDate, organizationId: $orgId),
            default => 0,
        };
    }

    private function deleteInChunks(string $modelClass, Carbon $cutoffDate, int $chunkSize = 1000, ?int $organizationId = null): int
    {
        $totalDeleted = 0;
        
        do {
            $query = $modelClass::where('created_at', '<', $cutoffDate);
            
            if ($organizationId) {
                $query->where('organization_id', $organizationId);
            }
            
            $deleted = $query->limit($chunkSize)->delete();
            
            $totalDeleted += $deleted;
            
            if ($deleted > 0) {
                usleep(100000);
            }
        } while ($deleted > 0);
        
        return $totalDeleted;
    }

    private function backupData(string $dataType, Carbon $cutoffDate): string
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = "retention_backup_{$dataType}_{$timestamp}.json";
        $path = "backups/retention/{$filename}";

        $orgId = $this->getOrganizationId();
        
        $data = match($dataType) {
            'error_logs' => ErrorLog::where('created_at', '<', $cutoffDate)
                ->where('organization_id', $orgId)
                ->get(),
            'notifications' => Notification::where('created_at', '<', $cutoffDate)
                ->where('organization_id', $orgId)
                ->get(),
            default => [],
        };

        try {
            $jsonData = json_encode($data, JSON_PRETTY_PRINT);
            $encryptedData = Crypt::encryptString($jsonData);
            
            Storage::disk('local')->put($path, $encryptedData);
            
            // Verify file exists and has content
            if (!Storage::disk('local')->exists($path) || Storage::disk('local')->size($path) === 0) {
                throw new \Exception("Backup file is empty or failed to write");
            }
            
            Log::info("Data retention backup created", [
                'data_type' => $dataType,
                'backup_path' => $path,
                'organization_id' => $orgId,
                'record_count' => count($data),
                'created_by' => auth()->user()?->email,
            ]);
            
            return $path;
        } catch (\Exception $e) {
            Log::error("Backup failed for {$dataType}: " . $e->getMessage());
            throw $e;
        }
    }

    private function logRetention(RetentionPolicy $policy, array $stats, bool $isDryRun = false): void
    {
        $orgId = $this->getOrganizationId();
        
        RetentionLog::create([
            'organization_id' => $orgId,
            'retention_policy_id' => $policy->id,
            'data_type' => $policy->data_type,
            'records_deleted' => $stats['records_deleted'],
            'records_backed_up' => $stats['records_backed_up'],
            'backup_file_path' => $stats['backup_file_path'],
            'executed_at' => now(),
            'executed_by' => auth()->user()?->email ?? 'system',
            'status' => $isDryRun ? 'dry_run' : $stats['status'],
            'error_message' => $stats['error_message'],
        ]);
    }

    public function getRetentionStats(): array
    {
        $orgId = $this->getOrganizationId();
        $cacheKey = str_replace('{org_id}', $orgId, self::RETENTION_STATS_CACHE_KEY);

        return Cache::remember($cacheKey, self::RETENTION_STATS_CACHE_TTL, function () {
            return $this->calculateRetentionStats();
        });
    }

    private function calculateRetentionStats(): array
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

    private function clearRetentionStatsCache(): void
    {
        try {
            $orgId = $this->getOrganizationId();
            $cacheKey = str_replace('{org_id}', $orgId, self::RETENTION_STATS_CACHE_KEY);
            Cache::forget($cacheKey);
        } catch (\Exception $e) {
            Log::warning("Failed to clear retention stats cache: " . $e->getMessage());
        }
    }

    public function archiveOldData(string $dataType, int $monthsOld = 36): array
    {
        if (!in_array($dataType, ['cte_events', 'trace_records'])) {
            return [
                'status' => 'skipped',
                'reason' => 'Data type not configured for archival',
            ];
        }

        $orgId = $this->getOrganizationId();
        $cutoffDate = now()->subMonths($monthsOld);
        $archiveStats = [
            'status' => 'success',
            'data_type' => $dataType,
            'records_archived' => 0,
            'archive_path' => null,
            'error_message' => null,
        ];

        try {
            DB::transaction(function () use ($dataType, $cutoffDate, $orgId, &$archiveStats) {
                // Get records to archive
                $query = match($dataType) {
                    'cte_events' => CTEEvent::where('created_at', '<', $cutoffDate)
                        ->where('organization_id', $orgId),
                    'trace_records' => TraceRecord::where('created_at', '<', $cutoffDate)
                        ->where('organization_id', $orgId),
                    default => null,
                };

                if (!$query) {
                    throw new \Exception("Invalid data type for archival: {$dataType}");
                }

                $records = $query->get();
                $archiveStats['records_archived'] = $records->count();

                if ($records->count() === 0) {
                    $archiveStats['status'] = 'skipped';
                    $archiveStats['reason'] = 'No records found matching archival criteria';
                    return;
                }

                // Create encrypted archive
                $timestamp = now()->format('Y-m-d_H-i-s');
                $filename = "archive_{$dataType}_{$timestamp}.json";
                $path = "archives/{$dataType}/{$filename}";

                $jsonData = json_encode($records, JSON_PRETTY_PRINT);
                $encryptedData = Crypt::encryptString($jsonData);

                Storage::disk('local')->put($path, $encryptedData);

                // Verify archive
                if (!Storage::disk('local')->exists($path) || Storage::disk('local')->size($path) === 0) {
                    throw new \Exception("Archive file is empty or failed to write");
                }

                $archiveStats['archive_path'] = $path;

                // Log archival operation
                Log::info("Data archival completed", [
                    'data_type' => $dataType,
                    'archive_path' => $path,
                    'organization_id' => $orgId,
                    'record_count' => $records->count(),
                    'archived_by' => auth()->user()?->email,
                ]);

                // Log to AuditLog
                AuditLog::createLog([
                    'user_id' => auth()->user()?->id,
                    'action' => 'archive_data',
                    'table_name' => $dataType,
                    'organization_id' => $orgId,
                    'new_values' => [
                        'archive_path' => $path,
                        'record_count' => $records->count(),
                        'months_old' => $monthsOld,
                    ],
                ]);
            });

        } catch (\Exception $e) {
            $archiveStats['status'] = 'failed';
            $archiveStats['error_message'] = $e->getMessage();
            Log::error("Data archival failed for {$dataType}: " . $e->getMessage());
        }

        return $archiveStats;
    }

    public function validateRetentionPolicy(string $dataType, int $retentionMonths): array
    {
        $errors = [];

        if (in_array($dataType, self::PROTECTED_DATA_TYPES)) {
            $errors[] = "Data type '{$dataType}' is protected by FSMA 204 and cannot have a deletion policy.";
        }

        if ($retentionMonths > 0 && $retentionMonths < self::FSMA_204_MINIMUM_RETENTION_MONTHS) {
            $errors[] = "Retention period must be at least " . self::FSMA_204_MINIMUM_RETENTION_MONTHS . 
                        " months per FSMA 204 Section 204.6 requirements (recommended: indefinite retention).";
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
}
