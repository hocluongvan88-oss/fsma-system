<?php

namespace App\Traits;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait TenantScoped
{
    /**
     * Boot the tenant scoped trait for a model.
     */
    protected static function bootTenantScoped(): void
    {
        // Automatically add organization_id when creating
        static::creating(function (Model $model) {
            if (auth()->check() && !$model->organization_id) {
                // Only set if user is not admin
                if (!auth()->user()->isAdmin()) {
                    $model->organization_id = auth()->user()->organization_id;
                }
            }
        });

        // Automatically scope all queries by organization_id
        static::addGlobalScope('organization', function (Builder $builder) {
            if (auth()->check()) {
                $user = auth()->user();
                
                // Admin can see everything
                if ($user->isAdmin()) {
                    return;
                }
                
                // Regular users only see their organization's data
                if ($user->organization_id) {
                    $builder->where($builder->getModel()->getTable() . '.organization_id', $user->organization_id);
                }
            }
        });
    }

    /**
     * Get the organization that owns the model.
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Scope a query to include all organizations (bypass tenant scope).
     * Only use this when explicitly needed and with proper authorization!
     */
    public function scopeWithAllOrganizations(Builder $query): Builder
    {
        return $query->withoutGlobalScope('organization');
    }

    /**
     * Scope a query to a specific organization.
     */
    public function scopeForOrganization(Builder $query, int $organizationId): Builder
    {
        return $query->withoutGlobalScope('organization')
            ->where($query->getModel()->getTable() . '.organization_id', $organizationId);
    }
}
