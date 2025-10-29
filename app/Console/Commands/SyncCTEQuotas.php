<?php

namespace App\Console\Commands;

use App\Services\CTEQuotaSyncService;
use Illuminate\Console\Command;

class SyncCTEQuotas extends Command
{
    protected $signature = 'cte:sync-quotas {--organization-id= : Sync quota for specific organization}';
    protected $description = 'Sync CTE quotas with package limits and reset monthly quotas if needed';

    public function handle()
    {
        $quotaService = new CTEQuotaSyncService();

        if ($this->option('organization-id')) {
            $organization = \App\Models\Organization::find($this->option('organization-id'));
            
            if (!$organization) {
                $this->error("Organization not found");
                return 1;
            }

            try {
                $quotaService->syncOrganizationQuota($organization);
                $this->info("✓ Quota synced for organization: {$organization->name}");
                
                $status = $quotaService->getQuotaStatus($organization);
                $this->table(
                    ['Feature', 'Used', 'Limit', 'Percentage', 'Exceeded'],
                    array_map(function($feature, $data) {
                        return [
                            $feature,
                            $data['used'],
                            $data['unlimited'] ? 'Unlimited' : $data['limit'],
                            round($data['percentage'], 2) . '%',
                            $data['exceeded'] ? 'YES' : 'NO',
                        ];
                    }, array_keys($status), $status)
                );
                return 0;
            } catch (\Exception $e) {
                $this->error("Failed to sync quota: " . $e->getMessage());
                return 1;
            }
        }

        $this->info("Syncing all organization quotas...");
        $results = $quotaService->syncAllOrganizationQuotas();

        $this->info("Total organizations: {$results['total_organizations']}");
        $this->info("Successfully synced: {$results['synced']}");

        if (!empty($results['errors'])) {
            $this->error("Errors encountered:");
            foreach ($results['errors'] as $error) {
                $this->error("  - " . json_encode($error));
            }
        }

        $this->info("\nResetting monthly quotas if needed...");
        $resetResults = $quotaService->resetMonthlyQuotasIfNeeded();
        $this->info("Monthly quotas reset: {$resetResults['reset_count']}");

        if (!empty($resetResults['errors'])) {
            $this->error("Reset errors:");
            foreach ($resetResults['errors'] as $error) {
                $this->error("  - " . json_encode($error));
            }
        }

        $this->info("\n✓ Quota sync completed successfully!");
        return 0;
    }
}
