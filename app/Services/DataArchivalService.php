<?php

namespace App\Services;

use App\Models\CTEEvent;
use App\Models\TraceRecord;
use App\Models\TraceRelationship;
use App\Models\AuditLog;
use App\Models\ArchivalLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\DataArchivalCompletedMail;

class DataArchivalService
{
    private string $strategy;
    private int $hotDataMonths;
    private int $batchSize;

    public function __construct()
    {
        $this->strategy = config('archival.strategy', 'database');
        $this->hotDataMonths = config('archival.hot_data_months', 36);
        $this->batchSize = config('archival.batch_size', 1000);
    }

    /**
     * Archive old CTE data to cold storage
     * This maintains FSMA 204 compliance while optimizing database performance
     */
    public function archiveOldData(string $dataType, bool $dryRun = false): array
    {
        $config = config("archival.archival_data_types.{$dataType}");
        
        if (!$config || !$config['enabled']) {
            return [
                'status' => 'skipped',
                'reason' => "Data type '{$dataType}' is not configured for archival",
            ];
        }

        $cutoffDate = now()->subMonths($config['hot_months']);
        $stats = [
            'records_archived' => 0,
            'records_verified' => 0,
            'records_deleted_from_hot' => 0,
            'archival_location' => null,
            'status' => 'success',
            'error_message' => null,
        ];

        try {
            Log::info("Starting archival for {$dataType}", [
                'cutoff_date' => $cutoffDate,
                'strategy' => $this->strategy,
                'dry_run' => $dryRun,
            ]);

            // Step 1: Archive data to cold storage
            $archivedCount = $this->archiveToStorage($dataType, $config, $cutoffDate, $dryRun);
            $stats['records_archived'] = $archivedCount;

            // Step 2: Verify archived data (if enabled)
            if (config('archival.verify_after_archival') && !$dryRun) {
                $verifiedCount = $this->verifyArchivedData($dataType, $cutoffDate);
                $stats['records_verified'] = $verifiedCount;

                if ($verifiedCount !== $archivedCount) {
                    throw new \Exception("Verification failed: archived {$archivedCount} but verified {$verifiedCount}");
                }
            }

            // Step 3: Delete from hot storage (only after successful archival and verification)
            if (!$dryRun && $archivedCount > 0) {
                $deletedCount = $this->deleteFromHotStorage($dataType, $config, $cutoffDate);
                $stats['records_deleted_from_hot'] = $deletedCount;
            }

            $stats['archival_location'] = $this->getArchivalLocation($dataType);

            // Log the archival action
            if (!$dryRun) {
                $this->logArchival($dataType, $stats);
            }

            // Send notification
            if (config('archival.notifications.enabled') && config('archival.notifications.on_success') && !$dryRun) {
                $this->sendNotification($dataType, $stats);
            }

        } catch (\Exception $e) {
            $stats['status'] = 'failed';
            $stats['error_message'] = $e->getMessage();
            Log::error("Archival failed for {$dataType}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            // Send failure notification
            if (config('archival.notifications.enabled') && config('archival.notifications.on_failure')) {
                $this->sendNotification($dataType, $stats);
            }
        }

        return $stats;
    }

    /**
     * Archive data to cold storage based on strategy
     */
    private function archiveToStorage(string $dataType, array $config, Carbon $cutoffDate, bool $dryRun): int
    {
        $modelClass = $config['model'];
        $totalArchived = 0;

        // Get records to archive
        $query = $modelClass::where('created_at', '<', $cutoffDate);
        
        if ($dryRun) {
            return $query->count();
        }

        // Process in batches
        $query->chunkById($this->batchSize, function ($records) use ($dataType, $config, &$totalArchived) {
            DB::transaction(function () use ($records, $dataType, $config, &$totalArchived) {
                switch ($this->strategy) {
                    case 'database':
                        $this->archiveToDatabase($records, $config['archival_table']);
                        break;
                    
                    case 's3_glacier':
                        $this->archiveToS3Glacier($records, $dataType);
                        break;
                    
                    case 'local':
                        $this->archiveToLocal($records, $dataType);
                        break;
                }

                $totalArchived += $records->count();
            });

            // Small delay to prevent database overload
            usleep(100000); // 100ms
        });

        return $totalArchived;
    }

    /**
     * Archive to separate database tables
     */
    private function archiveToDatabase($records, string $archivalTable): void
    {
        foreach ($records as $record) {
            $data = $record->toArray();
            $data['archived_at'] = now();
            $data['original_id'] = $record->id;
            
            DB::table($archivalTable)->insert($data);
        }
    }

    /**
     * Archive to AWS S3 Glacier
     */
    private function archiveToS3Glacier($records, string $dataType): void
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = "{$dataType}_{$timestamp}.json";
        $s3Config = config('archival.s3_glacier');
        $key = $s3Config['prefix'] . $filename;

        $data = $records->map(function ($record) {
            $array = $record->toArray();
            $array['archived_at'] = now()->toIso8601String();
            return $array;
        });

        Storage::disk('s3')->put($key, json_encode($data, JSON_PRETTY_PRINT), [
            'StorageClass' => $s3Config['storage_class'],
        ]);
    }

    /**
     * Archive to local storage (not recommended for production)
     */
    private function archiveToLocal($records, string $dataType): void
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = "archival_{$dataType}_{$timestamp}.json";
        $path = "archival/{$dataType}/{$filename}";

        $data = $records->map(function ($record) {
            $array = $record->toArray();
            $array['archived_at'] = now()->toIso8601String();
            return $array;
        });

        Storage::disk('local')->put($path, json_encode($data, JSON_PRETTY_PRINT));
    }

    /**
     * Verify archived data integrity
     */
    private function verifyArchivedData(string $dataType, Carbon $cutoffDate): int
    {
        // TODO: Implement verification logic based on strategy
        // For database: count records in archival table
        // For S3: verify file exists and is readable
        
        return 0; // Placeholder
    }

    /**
     * Delete records from hot storage after successful archival
     */
    private function deleteFromHotStorage(string $dataType, array $config, Carbon $cutoffDate): int
    {
        $modelClass = $config['model'];
        $totalDeleted = 0;

        // Delete in chunks to prevent performance issues
        do {
            $deleted = $modelClass::where('created_at', '<', $cutoffDate)
                ->limit($this->batchSize)
                ->delete();
            
            $totalDeleted += $deleted;
            
            if ($deleted > 0) {
                usleep(100000); // 100ms delay
            }
        } while ($deleted > 0);

        return $totalDeleted;
    }

    /**
     * Get archival location description
     */
    private function getArchivalLocation(string $dataType): string
    {
        return match($this->strategy) {
            'database' => "Archival database table: " . config("archival.archival_data_types.{$dataType}.archival_table"),
            's3_glacier' => "AWS S3 Glacier: " . config('archival.s3_glacier.bucket'),
            'local' => "Local storage: storage/app/archival/{$dataType}/",
            default => 'Unknown',
        };
    }

    /**
     * Log archival action
     */
    private function logArchival(string $dataType, array $stats): void
    {
        ArchivalLog::create([
            'data_type' => $dataType,
            'strategy' => $this->strategy,
            'records_archived' => $stats['records_archived'],
            'records_verified' => $stats['records_verified'],
            'records_deleted_from_hot' => $stats['records_deleted_from_hot'],
            'archival_location' => $stats['archival_location'],
            'executed_at' => now(),
            'executed_by' => auth()->user()?->email ?? 'system',
            'status' => $stats['status'],
            'error_message' => $stats['error_message'],
        ]);
    }

    /**
     * Send email notification
     */
    private function sendNotification(string $dataType, array $stats): void
    {
        $recipients = config('archival.notifications.recipients', []);
        
        foreach ($recipients as $recipient) {
            Mail::to($recipient)->send(new DataArchivalCompletedMail($dataType, $stats));
        }
    }

    /**
     * Get archival statistics
     */
    public function getArchivalStats(): array
    {
        $stats = [];
        $dataTypes = config('archival.archival_data_types', []);

        foreach ($dataTypes as $dataType => $config) {
            if (!$config['enabled']) {
                continue;
            }

            $cutoffDate = now()->subMonths($config['hot_months']);
            $modelClass = $config['model'];

            $stats[$dataType] = [
                'hot_months' => $config['hot_months'],
                'records_in_hot_storage' => $modelClass::count(),
                'records_eligible_for_archival' => $modelClass::where('created_at', '<', $cutoffDate)->count(),
                'last_archival' => ArchivalLog::where('data_type', $dataType)
                    ->where('status', 'success')
                    ->latest('executed_at')
                    ->first()?->executed_at,
                'total_archived_all_time' => ArchivalLog::where('data_type', $dataType)
                    ->where('status', 'success')
                    ->sum('records_archived'),
            ];
        }

        return $stats;
    }

    /**
     * Retrieve archived data for audit purposes
     */
    public function retrieveArchivedData(string $dataType, array $filters = []): array
    {
        // TODO: Implement retrieval logic based on strategy
        // This is critical for FDA audits - must be able to retrieve archived data
        
        return [
            'status' => 'not_implemented',
            'message' => 'Archival retrieval feature coming soon',
        ];
    }
}
