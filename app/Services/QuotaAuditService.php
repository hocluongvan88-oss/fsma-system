<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\OrganizationQuota;
use App\Models\Package;
use App\Models\CTEEvent;
use App\Models\Document;
use App\Models\ESignature;
use App\Models\DigitalCertificate;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QuotaAuditService
{
    /**
     * Feature names mapped to package columns
     */
    const FEATURE_MAP = [
        'cte_records_monthly' => 'max_cte_records_monthly',
        'documents' => 'max_documents',
        'users' => 'max_users',
        'e_signatures' => 'has_e_signatures',
        'certificates' => 'has_certificates',
        'data_retention' => 'has_data_retention',
        'archival' => 'has_archival',
        'compliance_report' => 'has_compliance_report',
    ];

    /**
     * New method to check quota limit for middleware
     * Add admin bypass parameter
     */
    public function checkQuotaLimit(Organization $organization, string $featureName, $user = null): array
    {
        $validation = $this->validateAction($organization, $featureName, $user);
        
        return [
            'allowed' => $validation['allowed'],
            'message' => $validation['reason'] ?? null,
            'admin_bypass' => $validation['admin_bypass'] ?? false,
        ];
    }

    /**
     * Audit all organizations and sync quotas with packages
     */
    public function auditAllOrganizations(): array
    {
        $results = [
            'total_organizations' => 0,
            'synced' => 0,
            'errors' => [],
            'details' => [],
        ];

        $organizations = Organization::with('package')->get();
        $results['total_organizations'] = $organizations->count();

        foreach ($organizations as $organization) {
            try {
                $auditResult = $this->auditOrganization($organization);
                $results['details'][$organization->id] = $auditResult;
                
                if ($auditResult['status'] === 'synced') {
                    $results['synced']++;
                }
            } catch (\Exception $e) {
                $results['errors'][] = [
                    'organization_id' => $organization->id,
                    'error' => $e->getMessage(),
                ];
                Log::error('Quota audit failed for organization', [
                    'organization_id' => $organization->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $results;
    }

    /**
     * Audit single organization
     */
    public function auditOrganization(Organization $organization): array
    {
        $package = $organization->getPackage();
        
        if (!$package) {
            throw new \Exception("No package found for organization {$organization->id}");
        }

        $result = [
            'organization_id' => $organization->id,
            'organization_name' => $organization->name,
            'package_id' => $package->id,
            'package_name' => $package->name,
            'status' => 'ok',
            'quotas' => [],
            'mismatches' => [],
            'missing_quotas' => [],
        ];

        // Check each feature
        foreach (self::FEATURE_MAP as $featureName => $packageColumn) {
            $quotaCheck = $this->checkFeatureQuota($organization, $package, $featureName, $packageColumn);
            $result['quotas'][$featureName] = $quotaCheck;

            if ($quotaCheck['status'] === 'mismatch') {
                $result['mismatches'][] = $featureName;
                $result['status'] = 'synced';
            } elseif ($quotaCheck['status'] === 'missing') {
                $result['missing_quotas'][] = $featureName;
                $result['status'] = 'synced';
            }
        }

        return $result;
    }

    /**
     * Check and sync quota for a specific feature
     */
    protected function checkFeatureQuota(Organization $organization, Package $package, string $featureName, string $packageColumn): array
    {
        $quota = OrganizationQuota::where('organization_id', $organization->id)
            ->where('feature_name', $featureName)
            ->first();

        // Get expected limit from package
        $expectedLimit = $this->getExpectedLimit($package, $packageColumn);
        
        // Get actual usage
        $actualUsage = $this->getActualUsage($organization, $featureName);

        $result = [
            'feature' => $featureName,
            'expected_limit' => $expectedLimit,
            'actual_usage' => $actualUsage,
            'status' => 'ok',
        ];

        if (!$quota) {
            // Create missing quota
            $quota = OrganizationQuota::create([
                'organization_id' => $organization->id,
                'feature_name' => $featureName,
                'used_count' => $actualUsage,
                'limit_count' => $expectedLimit,
                'reset_at' => $this->shouldResetMonthly($featureName) ? now()->addMonth() : null,
            ]);
            
            $result['status'] = 'missing';
            $result['action'] = 'created';
            $result['current_limit'] = $expectedLimit;
            $result['current_usage'] = $actualUsage;
        } else {
            $result['current_limit'] = $quota->limit_count;
            $result['current_usage'] = $quota->used_count;

            // Check if limit matches package
            if ($quota->limit_count != $expectedLimit) {
                $quota->limit_count = $expectedLimit;
                $quota->save();
                
                $result['status'] = 'mismatch';
                $result['action'] = 'synced_limit';
            }

            // Check if usage matches actual
            if ($quota->used_count != $actualUsage) {
                $quota->used_count = $actualUsage;
                $quota->save();
                
                $result['status'] = 'mismatch';
                $result['action'] = 'synced_usage';
            }
        }

        return $result;
    }

    /**
     * Get expected limit from package
     */
    protected function getExpectedLimit(Package $package, string $packageColumn): ?int
    {
        // For boolean features (has_*)
        if (str_starts_with($packageColumn, 'has_')) {
            return $package->$packageColumn ? null : 0; // null = unlimited, 0 = disabled
        }

        // For numeric limits
        $limit = $package->$packageColumn;
        
        // 0 or null means unlimited
        if ($limit === 0 || $limit === null) {
            return null;
        }

        // Very high numbers (999999) also mean unlimited
        if ($limit >= 999999) {
            return null;
        }

        return $limit;
    }

    /**
     * Get actual usage count from database
     */
    protected function getActualUsage(Organization $organization, string $featureName): int
    {
        switch ($featureName) {
            case 'cte_records_monthly':
                return CTEEvent::where('organization_id', $organization->id)
                    ->whereYear('created_at', now()->year)
                    ->whereMonth('created_at', now()->month)
                    ->count();

            case 'documents':
                return Document::where('organization_id', $organization->id)
                    ->whereNull('deleted_at')
                    ->count();

            case 'users':
                return User::where('organization_id', $organization->id)
                    ->where('is_active', true)
                    ->where('email', '!=', 'admin@fsma204.com')
                    ->count();

            case 'e_signatures':
                return ESignature::where('organization_id', $organization->id)
                    ->whereYear('signed_at', now()->year)
                    ->whereMonth('signed_at', now()->month)
                    ->count();

            case 'certificates':
                return DigitalCertificate::whereHas('user', function($query) use ($organization) {
                    $query->where('organization_id', $organization->id);
                })->where('is_revoked', false)->count();

            default:
                return 0;
        }
    }

    /**
     * Check if feature should reset monthly
     */
    protected function shouldResetMonthly(string $featureName): bool
    {
        return in_array($featureName, ['cte_records_monthly', 'e_signatures']);
    }

    /**
     * Validate if organization can perform action
     * Added admin bypass - admins should not be subject to quota limits
     */
    public function validateAction(Organization $organization, string $featureName, $user = null): array
    {
        if ($user && method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return [
                'allowed' => true,
                'admin_bypass' => true,
                'unlimited' => true,
            ];
        }

        $quota = OrganizationQuota::where('organization_id', $organization->id)
            ->where('feature_name', $featureName)
            ->first();

        if (!$quota) {
            // Try to sync quota first
            $package = $organization->getPackage();
            if (!$package) {
                return [
                    'allowed' => false,
                    'reason' => 'No package assigned to organization',
                    'error_code' => 'NO_PACKAGE',
                ];
            }

            $packageColumn = self::FEATURE_MAP[$featureName] ?? null;
            if (!$packageColumn) {
                return [
                    'allowed' => false,
                    'reason' => 'Invalid feature name',
                    'error_code' => 'INVALID_FEATURE',
                ];
            }

            // Create quota from package
            $this->checkFeatureQuota($organization, $package, $featureName, $packageColumn);
            
            // Retry
            $quota = OrganizationQuota::where('organization_id', $organization->id)
                ->where('feature_name', $featureName)
                ->first();
        }

        // Check if feature is disabled
        if ($quota->limit_count === 0) {
            return [
                'allowed' => false,
                'reason' => 'Feature not available in your package',
                'error_code' => 'FEATURE_DISABLED',
                'upgrade_required' => true,
            ];
        }

        // Check if unlimited
        if ($quota->isUnlimited()) {
            return [
                'allowed' => true,
                'unlimited' => true,
            ];
        }

        // Check if quota exceeded
        if ($quota->isExceeded()) {
            return [
                'allowed' => false,
                'reason' => "You have reached your {$featureName} limit",
                'error_code' => 'QUOTA_EXCEEDED',
                'used' => $quota->used_count,
                'limit' => $quota->limit_count,
                'upgrade_required' => true,
            ];
        }

        return [
            'allowed' => true,
            'used' => $quota->used_count,
            'limit' => $quota->limit_count,
            'remaining' => $quota->getRemainingCount(),
        ];
    }

    /**
     * Increment usage for a feature
     */
    public function incrementUsage(Organization $organization, string $featureName, int $amount = 1): void
    {
        $quota = OrganizationQuota::where('organization_id', $organization->id)
            ->where('feature_name', $featureName)
            ->first();

        if (!$quota) {
            throw new \Exception("No quota found for feature {$featureName}");
        }

        $quota->used_count += $amount;
        $quota->save();
    }

    /**
     * Decrement usage for a feature
     */
    public function decrementUsage(Organization $organization, string $featureName, int $amount = 1): void
    {
        $quota = OrganizationQuota::where('organization_id', $organization->id)
            ->where('feature_name', $featureName)
            ->first();

        if (!$quota) {
            throw new \Exception("No quota found for feature {$featureName}");
        }

        $quota->used_count = max(0, $quota->used_count - $amount);
        $quota->save();
    }
}
