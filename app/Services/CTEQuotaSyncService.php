<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\Package;
use App\Models\OrganizationQuota;
use App\Models\CTEEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CTEQuotaSyncService
{
    protected $auditService;

    public function __construct(PaymentAuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    /**
     * Sync all organization quotas with their package limits
     * This ensures quotas match the package configuration
     */
    public function syncAllOrganizationQuotas(): array
    {
        $results = [
            'total_organizations' => 0,
            'synced' => 0,
            'errors' => [],
            'details' => [],
        ];

        try {
            $organizations = Organization::with('package')->get();
            $results['total_organizations'] = $organizations->count();

            foreach ($organizations as $org) {
                try {
                    $this->syncOrganizationQuota($org);
                    $results['synced']++;
                    $results['details'][] = [
                        'organization_id' => $org->id,
                        'organization_name' => $org->name,
                        'status' => 'success',
                    ];
                } catch (\Exception $e) {
                    $results['errors'][] = [
                        'organization_id' => $org->id,
                        'organization_name' => $org->name,
                        'error' => $e->getMessage(),
                    ];
                    Log::error("Failed to sync quota for organization {$org->id}", [
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        } catch (\Exception $e) {
            $results['errors'][] = [
                'error' => 'Failed to sync all organizations: ' . $e->getMessage(),
            ];
            Log::error('Failed to sync all organization quotas', ['error' => $e->getMessage()]);
        }

        return $results;
    }

    /**
     * Sync quota for a specific organization
     * Made atomic with transaction and added audit logging
     */
    public function syncOrganizationQuota(Organization $organization): void
    {
        DB::beginTransaction();
        try {
            $package = $organization->getPackage();

            if (!$package) {
                throw new \Exception("Organization {$organization->id} has no valid package");
            }

            $quotaData = [];

            $this->syncQuotaFeature(
                $organization,
                'cte_records_monthly',
                $package->max_cte_records_monthly ?? 0
            );
            $quotaData['cte_records_monthly'] = $package->max_cte_records_monthly ?? 0;

            $this->syncQuotaFeature(
                $organization,
                'documents',
                $package->max_documents ?? 0
            );
            $quotaData['documents'] = $package->max_documents ?? 0;

            $this->syncQuotaFeature(
                $organization,
                'users',
                $package->max_users ?? 0
            );
            $quotaData['users'] = $package->max_users ?? 0;

            $this->auditService->logQuotaSync($organization, $quotaData);

            DB::commit();
            Log::info("Quota synced for organization {$organization->id}", [
                'package_id' => $package->id,
                'cte_limit' => $package->max_cte_records_monthly,
                'documents_limit' => $package->max_documents,
                'users_limit' => $package->max_users,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Sync a specific quota feature
     * Added validation for limit values
     */
    private function syncQuotaFeature(Organization $organization, string $featureName, int $limit): void
    {
        if ($limit < 0) {
            throw new \Exception("Invalid limit value for {$featureName}: {$limit}");
        }

        $quota = $organization->quotas()
            ->where('feature_name', $featureName)
            ->first();

        $actualUsage = $this->getActualUsageCount($organization, $featureName);

        if (!$quota) {
            $quota = OrganizationQuota::create([
                'organization_id' => $organization->id,
                'feature_name' => $featureName,
                'used_count' => $actualUsage,
                'limit_count' => $limit,
                'reset_at' => now()->addMonth(),
            ]);
            
            Log::info("Created quota for organization {$organization->id}", [
                'feature' => $featureName,
                'limit' => $limit,
                'current_usage' => $actualUsage,
            ]);
        } else {
            $quota->update([
                'limit_count' => $limit,
                'used_count' => $actualUsage,
            ]);
            
            Log::info("Updated quota for organization {$organization->id}", [
                'feature' => $featureName,
                'limit' => $limit,
                'current_usage' => $actualUsage,
            ]);
        }
    }

    /**
     * Get actual usage count for a feature
     */
    private function getActualUsageCount(Organization $organization, string $featureName): int
    {
        switch ($featureName) {
            case 'cte_records_monthly':
                return CTEEvent::where('organization_id', $organization->id)
                    ->whereYear('created_at', now()->year)
                    ->whereMonth('created_at', now()->month)
                    ->count();

            case 'documents':
                return DB::table('documents')
                    ->where('organization_id', $organization->id)
                    ->count();

            case 'users':
                return \App\Models\User::where('organization_id', $organization->id)
                    ->where('is_active', true)
                    ->count();

            default:
                return 0;
        }
    }

    /**
     * Check if organization can create CTE record
     * Throws exception if quota exceeded
     * Auto-syncs quota from package if missing
     */
    public function validateCTERecordCreation(Organization $organization): bool
    {
        $quota = $organization->getQuota('cte_records_monthly');

        if (!$quota) {
            $package = $organization->getPackage();
            
            if (!$package) {
                throw new \Exception(
                    "No package assigned to organization {$organization->name}. Please contact administrator to assign a package."
                );
            }

            Log::warning("No CTE quota found for organization {$organization->id}, syncing from package {$package->id}");
            
            try {
                $this->syncOrganizationQuota($organization);
                $quota = $organization->fresh()->getQuota('cte_records_monthly');
            } catch (\Exception $e) {
                Log::error("Failed to sync quota for organization {$organization->id}", [
                    'error' => $e->getMessage(),
                    'package_id' => $package->id,
                ]);
                throw new \Exception(
                    "Failed to initialize quota for your organization. Please contact administrator. Error: {$e->getMessage()}"
                );
            }
            
            if (!$quota) {
                throw new \Exception(
                    "Failed to initialize CTE quota for organization {$organization->name}. Please contact administrator."
                );
            }
        }

        if ($quota->limit_count === 0 || $quota->limit_count === null) {
            return true;
        }

        if ($quota->used_count >= $quota->limit_count) {
            throw new \Exception(
                "You have reached your CTE record limit ({$quota->limit_count} records per month). Please upgrade your package or contact support."
            );
        }

        $usagePercentage = ($quota->used_count / $quota->limit_count) * 100;
        if ($usagePercentage >= 80) {
            Log::warning("Organization {$organization->id} CTE quota near limit", [
                'used' => $quota->used_count,
                'limit' => $quota->limit_count,
                'percentage' => $usagePercentage,
            ]);
        }

        return true;
    }

    /**
     * Increment CTE record usage
     * Auto-creates quota if missing
     */
    public function incrementCTEUsage(Organization $organization, int $amount = 1): void
    {
        $quota = $organization->getQuota('cte_records_monthly');

        if (!$quota) {
            Log::warning("No CTE quota found for organization {$organization->id} during increment, creating one now");
            $this->syncOrganizationQuota($organization);
            $quota = $organization->getQuota('cte_records_monthly');
        }

        if ($quota) {
            $quota->increment('used_count', $amount);
            Log::info("CTE usage incremented for organization {$organization->id}", [
                'amount' => $amount,
                'new_total' => $quota->used_count,
                'limit' => $quota->limit_count,
            ]);
        }
    }

    /**
     * Decrement CTE record usage (for void operations)
     * Auto-creates quota if missing
     */
    public function decrementCTEUsage(Organization $organization, int $amount = 1): void
    {
        $quota = $organization->getQuota('cte_records_monthly');

        if (!$quota) {
            Log::warning("No CTE quota found for organization {$organization->id} during decrement, creating one now");
            $this->syncOrganizationQuota($organization);
            $quota = $organization->getQuota('cte_records_monthly');
        }

        if ($quota && $quota->used_count > 0) {
            $quota->decrement('used_count', min($quota->used_count, $amount));
            Log::info("CTE usage decremented for organization {$organization->id}", [
                'amount' => $amount,
                'new_total' => $quota->used_count,
                'limit' => $quota->limit_count,
            ]);
        }
    }

    /**
     * Reset monthly quotas if needed
     */
    public function resetMonthlyQuotasIfNeeded(): array
    {
        $results = [
            'reset_count' => 0,
            'errors' => [],
        ];

        try {
            $quotas = OrganizationQuota::where('feature_name', 'cte_records_monthly')
                ->where('reset_at', '<=', now())
                ->get();

            foreach ($quotas as $quota) {
                try {
                    $quota->update([
                        'used_count' => 0,
                        'reset_at' => now()->addMonth(),
                    ]);
                    $results['reset_count']++;
                    Log::info("Monthly quota reset for organization {$quota->organization_id}");
                } catch (\Exception $e) {
                    $results['errors'][] = [
                        'quota_id' => $quota->id,
                        'error' => $e->getMessage(),
                    ];
                }
            }
        } catch (\Exception $e) {
            $results['errors'][] = [
                'error' => 'Failed to reset monthly quotas: ' . $e->getMessage(),
            ];
            Log::error('Failed to reset monthly quotas', ['error' => $e->getMessage()]);
        }

        return $results;
    }

    /**
     * Get quota status for organization
     */
    public function getQuotaStatus(Organization $organization): array
    {
        $quotas = $organization->quotas()->get();
        $status = [];

        foreach ($quotas as $quota) {
            $displayKey = $quota->feature_name;
            if ($quota->feature_name === 'cte_records_monthly') {
                $displayKey = 'cte_records';
            }
            
            $status[$displayKey] = [
                'used' => $quota->used_count,
                'limit' => $quota->limit_count,
                'unlimited' => $quota->limit_count === 0 || $quota->limit_count === null,
                'percentage' => $quota->limit_count > 0 ? ($quota->used_count / $quota->limit_count) * 100 : 0,
                'remaining' => max(0, $quota->limit_count - $quota->used_count),
                'exceeded' => $quota->used_count >= $quota->limit_count,
            ];
        }

        return $status;
    }

    /**
     * Get quota status for organization by ID
     */
    public function getOrganizationQuotaStatus(int $organizationId): array
    {
        $organization = Organization::findOrFail($organizationId);
        return $this->getQuotaStatus($organization);
    }

    /**
     * Ensure quota exists for organization
     * Creates if missing
     */
    public function ensureQuotaExists(Organization $organization): void
    {
        $quota = $organization->getQuota('cte_records_monthly');
        
        if (!$quota) {
            Log::info("Creating missing quota for organization {$organization->id}");
            $this->syncOrganizationQuota($organization);
        }
    }

    /**
     * Increment user usage count
     */
    public function incrementUserUsage(Organization $organization, int $amount = 1): void
    {
        $quota = $organization->quotas()
            ->where('feature_name', 'users')
            ->first();

        if (!$quota) {
            Log::warning("No user quota found for organization {$organization->id} during increment, creating one now");
            $this->syncOrganizationQuota($organization);
            $quota = $organization->quotas()->where('feature_name', 'users')->first();
        }

        if ($quota) {
            $quota->increment('used_count', $amount);
            Log::info("User usage incremented for organization {$organization->id}", [
                'amount' => $amount,
                'new_total' => $quota->used_count,
                'limit' => $quota->limit_count,
            ]);
        }
    }

    /**
     * Decrement user usage count
     */
    public function decrementUserUsage(Organization $organization, int $amount = 1): void
    {
        $quota = $organization->quotas()
            ->where('feature_name', 'users')
            ->first();

        if (!$quota) {
            Log::warning("No user quota found for organization {$organization->id} during decrement, creating one now");
            $this->syncOrganizationQuota($organization);
            $quota = $organization->quotas()->where('feature_name', 'users')->first();
        }

        if ($quota && $quota->used_count > 0) {
            $quota->decrement('used_count', min($quota->used_count, $amount));
            Log::info("User usage decremented for organization {$organization->id}", [
                'amount' => $amount,
                'new_total' => $quota->used_count,
                'limit' => $quota->limit_count,
            ]);
        }
    }

    /**
     * Validate if organization can create a new user
     * Throws exception if quota exceeded
     */
    public function validateUserCreation(Organization $organization): bool
    {
        $quota = $organization->quotas()
            ->where('feature_name', 'users')
            ->first();

        if (!$quota) {
            $package = $organization->getPackage();
            
            if (!$package) {
                throw new \Exception(
                    "No package assigned to organization {$organization->name}. Please contact administrator to assign a package."
                );
            }

            Log::warning("No user quota found for organization {$organization->id}, syncing from package {$package->id}");
            
            try {
                $this->syncOrganizationQuota($organization);
                $quota = $organization->fresh()->quotas()->where('feature_name', 'users')->first();
            } catch (\Exception $e) {
                Log::error("Failed to sync quota for organization {$organization->id}", [
                    'error' => $e->getMessage(),
                    'package_id' => $package->id,
                ]);
                throw new \Exception(
                    "Failed to initialize quota for your organization. Please contact administrator. Error: {$e->getMessage()}"
                );
            }
            
            if (!$quota) {
                throw new \Exception(
                    "Failed to initialize user quota for organization {$organization->name}. Please contact administrator."
                );
            }
        }

        if ($quota->limit_count === 0 || $quota->limit_count === null) {
            return true;
        }

        if ($quota->used_count >= $quota->limit_count) {
            throw new \Exception(
                "You have reached your user limit ({$quota->limit_count} users). Please upgrade your package to add more users."
            );
        }

        $usagePercentage = ($quota->used_count / $quota->limit_count) * 100;
        if ($usagePercentage >= 80) {
            Log::warning("Organization {$organization->id} user quota near limit", [
                'used' => $quota->used_count,
                'limit' => $quota->limit_count,
                'percentage' => $usagePercentage,
            ]);
        }

        return true;
    }

    /**
     * Increment document usage count
     */
    public function incrementDocumentUsage(Organization $organization, int $amount = 1): void
    {
        $quota = $organization->quotas()
            ->where('feature_name', 'documents')
            ->first();

        if (!$quota) {
            Log::warning("No document quota found for organization {$organization->id} during increment, creating one now");
            $this->syncOrganizationQuota($organization);
            $quota = $organization->quotas()->where('feature_name', 'documents')->first();
        }

        if ($quota) {
            $quota->increment('used_count', $amount);
            Log::info("Document usage incremented for organization {$organization->id}", [
                'amount' => $amount,
                'new_total' => $quota->used_count,
                'limit' => $quota->limit_count,
            ]);
        }
    }

    /**
     * Decrement document usage count
     */
    public function decrementDocumentUsage(Organization $organization, int $amount = 1): void
    {
        $quota = $organization->quotas()
            ->where('feature_name', 'documents')
            ->first();

        if (!$quota) {
            Log::warning("No document quota found for organization {$organization->id} during decrement, creating one now");
            $this->syncOrganizationQuota($organization);
            $quota = $organization->quotas()->where('feature_name', 'documents')->first();
        }

        if ($quota && $quota->used_count > 0) {
            $quota->decrement('used_count', min($quota->used_count, $amount));
            Log::info("Document usage decremented for organization {$organization->id}", [
                'amount' => $amount,
                'new_total' => $quota->used_count,
                'limit' => $quota->limit_count,
            ]);
        }
    }

    /**
     * Validate if organization can upload a new document
     * Throws exception if quota exceeded
     */
    public function validateDocumentCreation(Organization $organization): bool
    {
        $quota = $organization->quotas()
            ->where('feature_name', 'documents')
            ->first();

        if (!$quota) {
            $package = $organization->getPackage();
            
            if (!$package) {
                throw new \Exception(
                    "No package assigned to organization {$organization->name}. Please contact administrator to assign a package."
                );
            }

            Log::warning("No document quota found for organization {$organization->id}, syncing from package {$package->id}");
            
            try {
                $this->syncOrganizationQuota($organization);
                $quota = $organization->fresh()->quotas()->where('feature_name', 'documents')->first();
            } catch (\Exception $e) {
                Log::error("Failed to sync quota for organization {$organization->id}", [
                    'error' => $e->getMessage(),
                    'package_id' => $package->id,
                ]);
                throw new \Exception(
                    "Failed to initialize quota for your organization. Please contact administrator. Error: {$e->getMessage()}"
                );
            }
            
            if (!$quota) {
                throw new \Exception(
                    "Failed to initialize document quota for organization {$organization->name}. Please contact administrator."
                );
            }
        }

        if ($quota->limit_count === 0 || $quota->limit_count === null) {
            return true;
        }

        if ($quota->used_count >= $quota->limit_count) {
            throw new \Exception(
                "You have reached your document limit ({$quota->limit_count} documents). Please upgrade your package to upload more documents."
            );
        }

        $usagePercentage = ($quota->used_count / $quota->limit_count) * 100;
        if ($usagePercentage >= 80) {
            Log::warning("Organization {$organization->id} document quota near limit", [
                'used' => $quota->used_count,
                'limit' => $quota->limit_count,
                'percentage' => $usagePercentage,
            ]);
        }

        return true;
    }
}
