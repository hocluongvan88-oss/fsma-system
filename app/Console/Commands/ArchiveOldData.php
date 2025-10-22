<?php

namespace App\Console\Commands;

use App\Services\ArchivalService;
use Illuminate\Console\Command;

class ArchiveOldData extends Command
{
    protected $signature = 'archival:execute 
                            {--dry-run : Show what would be archived without actually archiving}
                            {--type= : Archive specific data type only}';

    protected $description = 'Archive old CTE data to cold storage per FSMA 204 compliance';

    public function handle(ArchivalService $archivalService): int
    {
        $dryRun = $this->option('dry-run');
        $specificType = $this->option('type');

        $this->info('===========================================');
        $this->info('  FSMA 204 Data Archival Process');
        $this->info('===========================================');
        $this->newLine();

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No data will be archived');
            $this->newLine();
        }

        $this->info('Configuration:');
        $this->line('  Strategy: ' . config('archival.strategy'));
        $this->line('  Hot Data Period: ' . config('archival.hot_data_months') . ' months');
        $this->line('  Batch Size: ' . config('archival.batch_size'));
        $this->line('  Verify After Archival: ' . (config('archival.verify_after_archival') ? 'Yes' : 'No'));
        $this->newLine();

        $this->info('Starting archival process...');
        $this->newLine();

        try {
            $results = $archivalService->executeArchival($dryRun);

            $this->displayResults($results, $dryRun);

            if (!$dryRun) {
                $this->newLine();
                $this->info('Archival Statistics:');
                $stats = $archivalService->getStatistics();
                $this->line('  Total Operations: ' . $stats['total_archival_operations']);
                $this->line('  Successful: ' . $stats['successful_operations']);
                $this->line('  Failed: ' . $stats['failed_operations']);
                $this->line('  Total Records Archived: ' . $stats['total_records_archived']);
            }

            $this->newLine();
            $this->info('Archival process completed successfully!');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Archival process failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    protected function displayResults(array $results, bool $dryRun): void
    {
        foreach ($results as $dataType => $result) {
            $this->line("Data Type: {$dataType}");

            if ($result['status'] === 'dry_run') {
                $this->line("  Would archive: {$result['records_to_archive']} records");
            } elseif ($result['status'] === 'success') {
                $this->line("  Archived: {$result['records_archived']} records");
                $this->line("  Verified: {$result['records_verified']} records");
                $this->line("  Deleted from hot: {$result['records_deleted']} records");
                if (isset($result['location'])) {
                    $this->line("  Location: {$result['location']}");
                }
            } elseif ($result['status'] === 'failed') {
                $this->error("  Failed: {$result['error']}");
            }

            $this->newLine();
        }
    }
}
