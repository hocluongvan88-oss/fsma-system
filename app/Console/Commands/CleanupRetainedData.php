<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DataRetentionService;
use App\Models\RetentionPolicy;

class CleanupRetainedData extends Command
{
    protected $signature = 'retention:cleanup {--dry-run : Show what would be deleted without actually deleting}';
    protected $description = 'Delete NON-CRITICAL data older than retention period (FSMA 204 compliant - never deletes CTE data)';

    protected DataRetentionService $retentionService;

    public function __construct(DataRetentionService $retentionService)
    {
        parent::__construct();
        $this->retentionService = $retentionService;
    }

    public function handle()
    {
        $this->info('Starting FSMA 204 Compliant Data Retention Cleanup...');
        $this->newLine();
        
        $this->warn('IMPORTANT: CTE Events, Trace Records, and Audit Logs are PROTECTED');
        $this->warn('These records are IMMUTABLE per FSMA 204 and will NEVER be deleted.');
        $this->warn('Only non-critical operational data (errors, notifications) will be cleaned up.');
        $this->newLine();
        
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No data will be deleted');
            $this->newLine();
        }

        $policies = RetentionPolicy::active()->get();
        
        if ($policies->isEmpty()) {
            $this->warn('No active retention policies found. Using default policies.');
            $this->initializeDefaultPolicies();
            $policies = RetentionPolicy::active()->get();
        }

        $totalStats = [
            'total_deleted' => 0,
            'total_backed_up' => 0,
            'policies_executed' => 0,
            'policies_failed' => 0,
            'policies_blocked' => 0, // Track blocked deletions
        ];

        foreach ($policies as $policy) {
            $this->line("Processing: {$policy->policy_name} ({$policy->data_type})");
            
            $result = $this->retentionService->executeCleanup($policy->data_type, $dryRun);

            if ($result['status'] === 'success') {
                $this->info("✓ {$policy->data_type}: {$result['records_deleted']} records processed");
                $totalStats['total_deleted'] += $result['records_deleted'];
                $totalStats['total_backed_up'] += $result['records_backed_up'];
                $totalStats['policies_executed']++;
            } elseif ($result['status'] === 'blocked') {
                $this->warn("⊘ {$policy->data_type}: PROTECTED - {$result['reason']}");
                $totalStats['policies_blocked']++;
            } elseif ($result['status'] === 'skipped') {
                $this->comment("⊙ {$policy->data_type}: SKIPPED - {$result['reason']}");
            } else {
                $this->error("✗ {$policy->data_type}: {$result['error_message']}");
                $totalStats['policies_failed']++;
            }
        }

        // Summary
        $this->newLine();
        $this->info('Data Retention Cleanup Summary:');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Records Deleted', $totalStats['total_deleted']],
                ['Total Records Backed Up', $totalStats['total_backed_up']],
                ['Policies Executed', $totalStats['policies_executed']],
                ['Policies Protected (FSMA 204)', $totalStats['policies_blocked']], // 
                ['Policies Failed', $totalStats['policies_failed']],
            ]
        );

        if (!$dryRun) {
            $this->info('✓ Data retention cleanup completed successfully');
            $this->newLine();
            $this->info('FSMA 204 Compliance: All critical traceability data preserved.');
        } else {
            $this->warn('DRY RUN - No data was actually deleted');
        }
    }

    private function initializeDefaultPolicies(): void
    {
        $defaults = RetentionPolicy::getDefaultPolicies();
        
        foreach ($defaults as $dataType => $months) {
            RetentionPolicy::firstOrCreate(
                ['data_type' => $dataType],
                [
                    'policy_name' => "Default {$dataType}",
                    'retention_months' => $months,
                    'backup_before_deletion' => true,
                    'is_active' => true,
                    'description' => "Default retention policy for {$dataType}",
                ]
            );
        }
    }
}
