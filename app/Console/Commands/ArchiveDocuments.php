<?php

namespace App\Console\Commands;

use App\Services\DocumentArchivalService;
use Illuminate\Console\Command;

class ArchiveDocuments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'documents:archive 
                            {--dry-run : Run without actually archiving}
                            {--force : Force archival without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Archive old documents to cold storage (FSMA 204 compliance)';

    protected DocumentArchivalService $archivalService;

    /**
     * Create a new command instance.
     */
    public function __construct(DocumentArchivalService $archivalService)
    {
        parent::__construct();
        $this->archivalService = $archivalService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        $this->info('Document Archival Process');
        $this->info('========================');
        $this->newLine();

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No data will be modified');
            $this->newLine();
        }

        // Confirm before proceeding
        if (!$force && !$dryRun) {
            if (!$this->confirm('This will archive old documents. Continue?')) {
                $this->info('Archival cancelled.');
                return 0;
            }
        }

        $this->info('Starting document archival...');
        $this->newLine();

        try {
            $result = $this->archivalService->archiveDocuments($dryRun);

            if ($result['status'] === 'dry_run') {
                $this->table(
                    ['Metric', 'Count'],
                    [
                        ['Documents to archive', $result['documents_to_archive']],
                        ['Versions to archive', $result['versions_to_archive']],
                        ['Signatures to archive', $result['signatures_to_archive']],
                    ]
                );
            } else {
                $this->table(
                    ['Metric', 'Count'],
                    [
                        ['Documents archived', $result['documents_archived']],
                        ['Versions archived', $result['versions_archived']],
                        ['Signatures archived', $result['signatures_archived']],
                        ['Documents deleted', $result['documents_deleted']],
                    ]
                );
            }

            $this->newLine();
            $this->info('✓ Document archival completed successfully');

            // Show statistics
            $this->newLine();
            $this->info('Archival Statistics:');
            $stats = $this->archivalService->getStatistics();
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Total archived documents', $stats['total_archived_documents']],
                    ['Total archived versions', $stats['total_archived_versions']],
                    ['Total archived signatures', $stats['total_archived_signatures']],
                    ['Documents with signatures', $stats['documents_with_signatures']],
                    ['Documents without signatures', $stats['documents_without_signatures']],
                ]
            );

            return 0;
        } catch (\Exception $e) {
            $this->error('✗ Archival failed: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
}
