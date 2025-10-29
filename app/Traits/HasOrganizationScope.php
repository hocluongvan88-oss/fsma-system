<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

/**
 * Global Scope for automatic organization filtering
 * Implements P0 security fix for consistent data isolation
 */
class OrganizationScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $user = Auth::user();

        if (!$user) {
            // No user authenticated - restrict to nothing
            $builder->whereRaw('1 = 0');
            return;
        }

        // System Admins should use explicit withoutOrganizationScope() if needed
        // This prevents accidental data leakage across organizations
        
        if ($user->organization_id) {
            $builder->where($builder->getModel()->getTable() . '.organization_id', $user->organization_id);
        } else {
            $builder->whereRaw('1 = 0');
        }
    }
}

/**
 * Trait HasOrganizationScope
 * 
 * Automatically filters queries by organization_id for multi-tenant data isolation.
 * ALL users are scoped by organization_id - no exceptions.
 * 
 * P0 FIX: Now implements Global Scope for automatic filtering with NO bypass for admins
 */
trait HasOrganizationScope
{
    /**
     * Boot the trait and add global scope
     */
    protected static function bootHasOrganizationScope(): void
    {
        static::addGlobalScope(new OrganizationScope);
    }

    /**
     * Scope to explicitly bypass organization filtering (use with caution - requires explicit call)
     */
    public function scopeWithoutOrganizationScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope(OrganizationScope::class);
    }

    /**
     * Scope to filter by specific organization
     */
    public function scopeForOrganization(Builder $query, ?int $organizationId): Builder
    {
        return $query->withoutGlobalScope(OrganizationScope::class)
            ->where('organization_id', $organizationId);
    }
}
