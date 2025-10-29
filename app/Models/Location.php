<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\CacheService;
use App\Traits\HasOrganizationScope;
use Illuminate\Support\Facades\Auth;

class Location extends Model
{
    use HasFactory, HasOrganizationScope;

    protected $fillable = [
        'location_name',
        'gln',
        'ffrn',
        'address',
        'city',
        'state',
        'zip_code',
        'country',
        'location_type',
        'organization_id',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($location) {
            if (empty($location->organization_id) && Auth::check()) {
                $location->organization_id = Auth::user()->organization_id;
            }
        });

        static::saved(function ($location) {
            CacheService::forgetByTag('locations');
            CacheService::forget(CacheService::orgKey('locations', $location->organization_id));
        });

        static::deleted(function ($location) {
            CacheService::forgetByTag('locations');
            CacheService::forget(CacheService::orgKey('locations', $location->organization_id));
        });
    }

    public static function getAllCached(?int $organizationId = null)
    {
        $cacheKey = CacheService::orgKey('locations', $organizationId);
        
        return CacheService::remember($cacheKey, CacheService::TTL_STATIC_DATA, function () use ($organizationId) {
            $query = self::query();
            
            if ($organizationId) {
                $query->where('organization_id', $organizationId);
            }
            
            return $query->select('id', 'location_name', 'gln', 'ffrn', 'location_type', 'city', 'state', 'organization_id')
                ->orderBy('location_name')
                ->get();
        });
    }

    // Relationships
    public function traceRecords()
    {
        return $this->hasMany(TraceRecord::class);
    }

    public function cteEvents()
    {
        return $this->hasMany(CTEEvent::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    // Scopes
    public function scopeByType($query, $type)
    {
        return $query->where('location_type', $type);
    }

    public function scopeWarehouses($query)
    {
        return $query->where('location_type', 'warehouse');
    }

    public function scopeFarms($query)
    {
        return $query->where('location_type', 'farm');
    }

    public function scopeForOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    // Helper methods
    public function getFullAddress(): string
    {
        return implode(', ', array_filter([
            $this->address,
            $this->city,
            $this->state,
            $this->zip_code,
            $this->country,
        ]));
    }

    public function hasGLN(): bool
    {
        return !empty($this->gln);
    }

    public function hasFFRN(): bool
    {
        return !empty($this->ffrn);
    }
}
