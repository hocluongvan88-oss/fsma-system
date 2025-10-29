<?php

namespace App\Console\Commands;

use App\Services\QuotaAuditService;
use Illuminate\Console\Command;

class AuditQuotas extends Command
{
    protected $signature = 'quota:audit 
                            {--organization-id= : Audit specific organization}
                            {--fix : Automatically fix mismatches}';

    protected $description = 'Audit and sync quotas with package limits for all modules';

    protected QuotaAuditService $auditService;

    public function __construct(QuotaAuditService $auditService)
    {
        parent::__construct();
        $this->auditService = $auditService;
    }

    public function handle()
    {
        $this->info('Starting quota audit...');

        if ($organizationId = $this->option('organization-id')) {
            $this->auditSingleOrganization($organizationId);
        } else {
            $this->auditAllOrganizations();
        }

        $this->info('Quota audit completed!');
    }

    protected function auditSingleOrganization($organizationId)
    {
        $organization = \App\Models\Organization::find($organizationId);
        
        if (!$organization) {
            $this->error("Organization {$organizationId} not found");
            return;
        }

        $this->info("Auditing organization: {$organization->name} (ID: {$organization->id})");
        
        $result = $this->auditService->auditOrganization($organization);
        
        $this->displayOrganizationResult($result);
    }

    protected function auditAllOrganizations()
    {
        $results = $this->auditService->auditAllOrganizations();

        $this->info("Total organizations: {$results['total_organizations']}");
        $this->info("Synced: {$results['synced']}");

        if (!empty($results['errors'])) {
            $this->error("\nErrors encountered:");
            foreach ($results['errors'] as $error) {
                $this->error("  Organization {$error['organization_id']}: {$error['error']}");
            }
        }

        $this->info("\nDetailed results:");
        foreach ($results['details'] as $orgId => $detail) {
            $this->displayOrganizationResult($detail);
        }
    }

    protected function displayOrganizationResult(array $result)
    {
        $this->line("\n" . str_repeat('=', 60));
        $this->info("Organization: {$result['organization_name']} (ID: {$result['organization_id']})");
        $this->info("Package: {$result['package_name']} ({$result['package_id']})");
        $this->info("Status: {$result['status']}");

        if (!empty($result['mismatches'])) {
            $this->warn("Mismatches found: " . implode(', ', $result['mismatches']));
        }

        if (!empty($result['missing_quotas'])) {
            $this->warn("Missing quotas created: " . implode(', ', $result['missing_quotas']));
        }

        $this->line("\nQuota Details:");
        $this->table(
            ['Feature', 'Expected Limit', 'Current Limit', 'Usage', 'Status', 'Action'],
            array_map(function($quota) {
                return [
                    $quota['feature'],
                    $quota['expected_limit'] ?? 'Unlimited',
                    $quota['current_limit'] ?? 'Unlimited',
                    $quota['actual_usage'],
                    $quota['status'],
                    $quota['action'] ?? '-',
                ];
            }, $result['quotas'])
        );
    }
}
