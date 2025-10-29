<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'email',
        'phone',
        'address',
        'is_active',
        'settings',
        'package_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    protected static function booted()
    {
        static::created(function ($organization) {
            if ($organization->package_id) {
                try {
                    app(\App\Services\CTEQuotaSyncService::class)->syncOrganizationQuota($organization);
                } catch (\Exception $e) {
                    \Log::error("Failed to sync quota for new organization {$organization->id}", [
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        });

        static::updated(function ($organization) {
            if ($organization->isDirty('package_id')) {
                try {
                    app(\App\Services\CTEQuotaSyncService::class)->syncOrganizationQuota($organization);
                } catch (\Exception $e) {
                    \Log::error("Failed to sync quota for organization {$organization->id} after package change", [
                        'error' => $e->getMessage(),
                        'old_package' => $organization->getOriginal('package_id'),
                        'new_package' => $organization->package_id,
                    ]);
                }
            }
        });
    }

    /**
     * Get all users belonging to this organization
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get all documents belonging to this organization
     */
    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Get all CTE events belonging to this organization
     */
    public function cteEvents()
    {
        return $this->hasMany(CTEEvent::class);
    }

    /**
     * Get all e-signatures belonging to this organization
     */
    public function eSignatures()
    {
        return $this->hasMany(ESignature::class);
    }

    /**
     * Get all trace records belonging to this organization
     */
    public function traceRecords()
    {
        return $this->hasMany(TraceRecord::class);
    }

    /**
     * Get all products belonging to this organization
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get all locations belonging to this organization
     */
    public function locations()
    {
        return $this->hasMany(Location::class);
    }

    /**
     * Get all partners belonging to this organization
     */
    public function partners()
    {
        return $this->hasMany(Partner::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id', 'id');
    }

    public function quotas()
    {
        return $this->hasMany(OrganizationQuota::class);
    }

    public function getPackage()
    {
        // Package model uses HasOrganizationScope trait, so we need to bypass it
        $package = $this->package()->withoutGlobalScope(\App\Traits\OrganizationScope::class)->first();
        
        if (!$package) {
            // Fallback to free package if no package assigned
            return Package::withoutGlobalScope(\App\Traits\OrganizationScope::class)
                ->where('id', 'free')
                ->first();
        }
        
        return $package;
    }

    public function hasFeature(string $feature): bool
    {
        $package = $this->getPackage();
        return $package ? $package->hasFeature($feature) : false;
    }

    public function getQuota(string $featureName)
    {
        $quota = $this->quotas()->where('feature_name', $featureName)->first();
        
        if (!$quota && $this->package_id) {
            \Log::warning("Quota '{$featureName}' not found for organization {$this->id}, syncing from package");
            try {
                app(\App\Services\CTEQuotaSyncService::class)->syncOrganizationQuota($this);
                // Refresh the relationship to get the newly created quota
                $this->load('quotas');
                $quota = $this->quotas()->where('feature_name', $featureName)->first();
            } catch (\Exception $e) {
                \Log::error("Failed to sync quota for organization {$this->id}", [
                    'feature' => $featureName,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        return $quota;
    }

    public function canPerformAction(string $featureName): bool
    {
        $user = auth()->user();
        if ($user && $user->isSystemAdmin()) {
            return true;
        }

        $quota = $this->getQuota($featureName);
        
        if (!$quota) {
            // If no quota exists and we couldn't create one, deny action
            return false;
        }

        // Unlimited quota (0 or null means unlimited)
        if ($quota->limit_count === 0 || $quota->limit_count === null) {
            return true;
        }

        return $quota->used_count < $quota->limit_count;
    }

    public function getQuotaUsagePercentage(string $featureName): float
    {
        $quota = $this->getQuota($featureName);
        
        if (!$quota || $quota->limit_count === 0 || $quota->limit_count === null) {
            return 0;
        }

        return ($quota->used_count / $quota->limit_count) * 100;
    }

    public function incrementQuotaUsage(string $featureName, int $amount = 1): void
    {
        $quota = $this->getQuota($featureName);
        
        if ($quota) {
            $quota->increment('used_count', $amount);
        }
    }

    public function decrementQuotaUsage(string $featureName, int $amount = 1): void
    {
        $quota = $this->getQuota($featureName);
        
        if ($quota) {
            $quota->decrement('used_count', $amount);
        }
    }

    /**
     * Scope to get only active organizations
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
