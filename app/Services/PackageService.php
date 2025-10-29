<?php

namespace App\Services;

use App\Models\Package;
use App\Models\Organization;
use App\Models\User;
use App\Notifications\PackageChangedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PackageService
{
    /**
     * Update organization's package and sync all related quotas
     * 
     * @param Organization $organization
     * @param string $packageId
     * @param User $changedBy
     * @return bool
     */
    public function updateOrganizationPackage(Organization $organization, string $packageId, User $changedBy): bool
    {
        DB::beginTransaction();
        
        try {
            $package = Package::where('id', $packageId)
                ->where('is_visible', true)
                ->first();
            
            if (!$package) {
                throw new \Exception("Invalid or unavailable package: {$packageId}");
            }
            
            $oldPackageId = $organization->package_id;
            
            $violations = $this->checkDowngradeViolations($organization, $package);
            if (!empty($violations)) {
                throw new \Exception("Cannot downgrade: " . implode(", ", $violations));
            }
            
            // Update organization with new package
            $organization->update([
                'package_id' => $package->id,
            ]);
            
            // Update organization quotas based on package limits
            $this->updateOrganizationQuotas($organization, $package);
            
            Log::info('Organization package updated', [
                'organization_id' => $organization->id,
                'old_package' => $oldPackageId,
                'new_package' => $packageId,
                'changed_by' => $changedBy->id,
                'timestamp' => now(),
            ]);
            
            // Notify all users in the organization
            foreach ($organization->users as $user) {
                $user->notify(new PackageChangedNotification($oldPackageId, $packageId, $package));
            }
            
            DB::commit();
            return true;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update organization package', [
                'organization_id' => $organization->id,
                'package_id' => $packageId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Update organization quotas based on package limits
     * 
     * @param Organization $organization
     * @param Package $package
     * @return void
     */
    private function updateOrganizationQuotas(Organization $organization, Package $package): void
    {
        $limits = [
            'cte_records_monthly' => max(0, $package->max_cte_records_monthly ?? 0),
            'documents' => max(0, $package->max_documents ?? 0),
            'users' => max(0, $package->max_users ?? 0),
        ];

        // Update CTE records quota
        $cteQuota = $organization->quotas()->where('feature_name', 'cte_records_monthly')->first();
        if ($cteQuota) {
            $cteQuota->update([
                'limit_count' => $limits['cte_records_monthly'],
                'reset_date' => now()->addMonth(),
            ]);
        }

        // Update documents quota
        $docQuota = $organization->quotas()->where('feature_name', 'documents')->first();
        if ($docQuota) {
            $docQuota->update([
                'limit_count' => $limits['documents'],
            ]);
        }

        // Update users quota
        $userQuota = $organization->quotas()->where('feature_name', 'users')->first();
        if ($userQuota) {
            $userQuota->update([
                'limit_count' => $limits['users'],
            ]);
        }
    }
    
    /**
     * Get package limits from database
     * 
     * @param string $packageId
     * @return array
     */
    public function getPackageLimits(string $packageId): array
    {
        $package = Package::where('id', $packageId)
            ->where('is_visible', true)
            ->first();
        
        if (!$package) {
            return [
                'max_cte_records_monthly' => 0,
                'max_documents' => 0,
                'max_users' => 1,
            ];
        }
        
        return [
            'max_cte_records_monthly' => $package->max_cte_records_monthly ?? 0,
            'max_documents' => $package->max_documents ?? 0,
            'max_users' => $package->max_users ?? 0,
        ];
    }
    
    /**
     * Check if package change would violate current usage
     * 
     * @param Organization $organization
     * @param string $newPackageId
     * @return array ['can_change' => bool, 'violations' => array]
     */
    public function canChangePackage(Organization $organization, string $newPackageId): array
    {
        $newPackage = Package::where('id', $newPackageId)
            ->where('is_visible', true)
            ->where('is_selectable', true)
            ->first();
        
        if (!$newPackage) {
            return [
                'can_change' => false,
                'violations' => ['Invalid or unavailable package']
            ];
        }

        $violations = $this->checkDowngradeViolations($organization, $newPackage);
        
        return [
            'can_change' => empty($violations),
            'violations' => $violations,
        ];
    }

    /**
     * Check if downgrading to a package would violate current usage
     * 
     * @param Organization $organization
     * @param Package $newPackage
     * @return array violations
     */
    private function checkDowngradeViolations(Organization $organization, Package $newPackage): array
    {
        $violations = [];
        
        // Check CTE records usage
        $cteQuota = $organization->getQuota('cte_records_monthly');
        if ($cteQuota && $newPackage->max_cte_records_monthly > 0 && $cteQuota->used_count > $newPackage->max_cte_records_monthly) {
            $violations[] = "Current CTE usage ({$cteQuota->used_count}) exceeds new package limit ({$newPackage->max_cte_records_monthly})";
        }
        
        // Check document count
        $docQuota = $organization->getQuota('documents');
        if ($docQuota && $newPackage->max_documents > 0 && $docQuota->used_count > $newPackage->max_documents) {
            $violations[] = "Current document count ({$docQuota->used_count}) exceeds new package limit ({$newPackage->max_documents})";
        }
        
        // Check user count
        $userQuota = $organization->getQuota('users');
        if ($userQuota && $newPackage->max_users > 0 && $userQuota->used_count > $newPackage->max_users) {
            $violations[] = "Current user count ({$userQuota->used_count}) exceeds new package limit ({$newPackage->max_users})";
        }
        
        return $violations;
    }
}
