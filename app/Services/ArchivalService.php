<?php

namespace App\Services;

use App\Models\ArchivalLog;
use App\Models\CTEEvent;
use App\Models\TraceRecord;
use App\Models\TraceRelationship;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ArchivalService
{
    protected string $strategy;
    protected int $hotDataMonths;
    protected int $batchSize;
    protected bool $verifyAfterArchival;

    public function __construct()
    {
        $this->strategy = config('archival.strategy', 'database');
        $this->hotDataMonths = config('archival.hot_data_months', 36);
        $this->batchSize = config('archival.batch_size', 1000);
        $this->verifyAfterArchival = config('archival.verify_after_archival', true);
    }

    /**
     * Execute archival process for all configured data types
     */
    public function executeArchival(bool $dryRun = false): array
    {
        $results = [];
        $dataTypes = config('archival.archival_data_types', []);

        foreach ($dataTypes as $dataType => $config) {
            if (!$config['enabled']) {
                Log::info("Archival skipped for {$dataType} (disabled in config)");
                continue;
            }

            try {
                $result = $this->archiveDataType($dataType, $config, $dryRun);
                $results[$dataType] = $result;
            } catch (\Exception $e) {
                Log::error("Archival failed for {$dataType}: " . $e->getMessage());
                $results[$dataType] = [
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Archive a specific data type
     */
    protected function archiveDataType(string $dataType, array $config, bool $dryRun): array
    {
        $cutoffDate = now()->subMonths($config['hot_months']);
        $model = $config['model'];
        
        Log::info("Starting archival for {$dataType}", [
            'cutoff_date' => $cutoffDate->toDateTimeString(),
            'strategy' => $this->strategy,
            'dry_run' => $dryRun,
        ]);

        // Get records to archive
        $query = $model::where('created_at', '<', $cutoffDate);
        $totalRecords = $query->count();

        if ($totalRecords === 0) {
            Log::info("No records to archive for {$dataType}");
            return [
                'status' => 'success',
                'records_archived' => 0,
                'records_verified' => 0,
                'records_deleted' => 0,
            ];
        }

        if ($dryRun) {
            Log::info("DRY RUN: Would archive {$totalRecords} records for {$dataType}");
            return [
                'status' => 'dry_run',
                'records_to_archive' => $totalRecords,
            ];
        }

        // Execute archival in transaction
        return DB::transaction(function () use ($dataType, $config, $query, $totalRecords) {
            $archived = 0;
            $verified = 0;
            $deleted = 0;
            $archivalLocation = null;

            // Process in batches
            $query->chunkById($this->batchSize, function ($records) use (
                &$archived,
                &$verified,
                &$deleted,
                &$archivalLocation,
                $dataType,
                $config
            ) {
                // Archive based on strategy
                switch ($this->strategy) {
                    case 'database':
                        $result = $this->archiveToDatabase($records, $config['archival_table']);
                        break;
                    case 's3_glacier':
                        $result = $this->archiveToS3Glacier($records, $dataType);
                        break;
                    case 'local':
                        $result = $this->archiveToLocal($records, $dataType);
                        break;
                    default:
                        throw new \Exception("Unknown archival strategy: {$this->strategy}");
                }

                $archived += $result['archived'];
                $archivalLocation = $result['location'];

                // Verify if enabled
                if ($this->verifyAfterArchival) {
                    $verifiedCount = $this->verifyArchival($records, $result['location']);
                    $verified += $verifiedCount;
                }

                // Delete from hot storage only after successful archival and verification
                if (!$this->verifyAfterArchival || $verified === $archived) {
                    $records->each->delete();
                    $deleted += $records->count();
                }
            });

            // Log archival operation
            ArchivalLog::create([
                'data_type' => $dataType,
                'strategy' => $this->strategy,
                'records_archived' => $archived,
                'records_verified' => $verified,
                'records_deleted_from_hot' => $deleted,
                'archival_location' => $archivalLocation,
                'executed_at' => now(),
                'executed_by' => auth()->id(),
                'status' => ($archived === $deleted) ? 'success' : 'partial',
            ]);

            Log::info("Archival completed for {$dataType}", [
                'archived' => $archived,
                'verified' => $verified,
                'deleted' => $deleted,
            ]);

            return [
                'status' => 'success',
                'records_archived' => $archived,
                'records_verified' => $verified,
                'records_deleted' => $deleted,
                'location' => $archivalLocation,
            ];
        });
    }

    /**
     * Archive records to separate database tables
     */
    protected function archiveToDatabase($records, string $archivalTable): array
    {
        $archived = 0;

        foreach ($records as $record) {
            $archivalData = $record->toArray();
            $archivalData['original_id'] = $record->id;
            $archivalData['archived_at'] = now();
            unset($archivalData['id']); // Remove original ID

            DB::table($archivalTable)->insert($archivalData);
            $archived++;
        }

        return [
            'archived' => $archived,
            'location' => "database:{$archivalTable}",
        ];
    }

    /**
     * Archive records to AWS S3 Glacier
     */
    protected function archiveToS3Glacier($records, string $dataType): array
    {
        $bucket = config('archival.s3_glacier.bucket');
        $prefix = config('archival.s3_glacier.prefix');
        $timestamp = now()->format('Y-m-d_His');
        $filename = "{$prefix}{$dataType}_{$timestamp}.json";

        $data = $records->map(function ($record) {
            $archivalData = $record->toArray();
            $archivalData['original_id'] = $record->id;
            $archivalData['archived_at'] = now()->toDateTimeString();
            return $archivalData;
        })->toArray();

        Storage::disk('s3')->put($filename, json_encode($data, JSON_PRETTY_PRINT), [
            'StorageClass' => config('archival.s3_glacier.storage_class', 'GLACIER'),
        ]);

        return [
            'archived' => count($data),
            'location' => "s3://{$bucket}/{$filename}",
        ];
    }

    /**
     * Archive records to local storage
     */
    protected function archiveToLocal($records, string $dataType): array
    {
        $timestamp = now()->format('Y-m-d_His');
        $filename = "archival/{$dataType}_{$timestamp}.json";

        $data = $records->map(function ($record) {
            $archivalData = $record->toArray();
            $archivalData['original_id'] = $record->id;
            $archivalData['archived_at'] = now()->toDateTimeString();
            return $archivalData;
        })->toArray();

        Storage::disk('local')->put($filename, json_encode($data, JSON_PRETTY_PRINT));

        return [
            'archived' => count($data),
            'location' => "local:{$filename}",
        ];
    }

    /**
     * Verify archived data integrity
     */
    protected function verifyArchival($records, string $location): int
    {
        // For database strategy, verify records exist in archival table
        if (str_starts_with($location, 'database:')) {
            $table = str_replace('database:', '', $location);
            $verified = 0;

            foreach ($records as $record) {
                $exists = DB::table($table)
                    ->where('original_id', $record->id)
                    ->exists();
                
                if ($exists) {
                    $verified++;
                }
            }

            return $verified;
        }

        // For S3/local, verify file exists and is readable
        if (str_starts_with($location, 's3://') || str_starts_with($location, 'local:')) {
            $path = str_replace(['s3://', 'local:'], '', $location);
            $disk = str_starts_with($location, 's3://') ? 's3' : 'local';
            
            return Storage::disk($disk)->exists($path) ? $records->count() : 0;
        }

        return 0;
    }

    /**
     * Get archival statistics
     */
    public function getStatistics(): array
    {
        return [
            'total_archival_operations' => ArchivalLog::count(),
            'successful_operations' => ArchivalLog::successful()->count(),
            'failed_operations' => ArchivalLog::failed()->count(),
            'total_records_archived' => ArchivalLog::sum('records_archived'),
            'total_records_deleted' => ArchivalLog::sum('records_deleted_from_hot'),
            'last_archival' => ArchivalLog::latest('executed_at')->first(),
            'by_data_type' => ArchivalLog::select('data_type')
                ->selectRaw('COUNT(*) as operations')
                ->selectRaw('SUM(records_archived) as total_archived')
                ->groupBy('data_type')
                ->get(),
        ];
    }
}
