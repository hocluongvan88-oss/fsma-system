<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\CacheService;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'product_name',
        'description',
        'is_ftl',
        'category',
        'unit_of_measure',
        'organization_id',
    ];

    protected function casts(): array
    {
        return [
            'is_ftl' => 'boolean',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($product) {
            CacheService::forgetByTag('products');
            CacheService::forget(CacheService::orgKey('products', $product->organization_id));
        });

        static::deleted(function ($product) {
            CacheService::forgetByTag('products');
            CacheService::forget(CacheService::orgKey('products', $product->organization_id));
        });
    }

    public static function getAllCached(?int $organizationId = null)
    {
        $cacheKey = CacheService::orgKey('products', $organizationId);
        
        return CacheService::remember($cacheKey, CacheService::TTL_STATIC_DATA, function () use ($organizationId) {
            $query = self::query();
            
            if ($organizationId) {
                $query->where('organization_id', $organizationId);
            }
            
            return $query->select('id', 'sku', 'product_name', 'is_ftl', 'category', 'unit_of_measure', 'organization_id')
                ->orderBy('product_name')
                ->get();
        });
    }

    public static function getFtlCached(?int $organizationId = null)
    {
        $cacheKey = CacheService::orgKey('products:ftl', $organizationId);
        
        return CacheService::remember($cacheKey, CacheService::TTL_STATIC_DATA, function () use ($organizationId) {
            $query = self::where('is_ftl', true);
            
            if ($organizationId) {
                $query->where('organization_id', $organizationId);
            }
            
            return $query->select('id', 'sku', 'product_name', 'category', 'unit_of_measure', 'organization_id')
                ->orderBy('product_name')
                ->get();
        });
    }

    // Relationships
    public function traceRecords()
    {
        return $this->hasMany(TraceRecord::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    // Scopes
    public function scopeFtl($query)
    {
        return $query->where('is_ftl', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeForOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    // Helper methods
    public function isFoodTraceabilityList(): bool
    {
        return $this->is_ftl;
    }

    public function getActiveInventory()
    {
        return $this->traceRecords()
            ->where('status', 'active')
            ->sum('quantity');
    }
}
