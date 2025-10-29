<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use App\Traits\HasOrganizationScope;
use Illuminate\Support\Facades\Auth;

class TraceRecord extends Model
{
    use HasFactory, HasOrganizationScope;

    protected $fillable = [
        'tlc',
        'product_id',
        'quantity',
        'available_quantity',
        'consumed_quantity',
        'unit',
        'location_id',
        'harvest_date',
        'pack_date',
        'path',
        'depth',
        'status',
        'organization_id',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'available_quantity' => 'decimal:2',
            'consumed_quantity' => 'decimal:2',
            'harvest_date' => 'date',
            'pack_date' => 'date',
            'depth' => 'integer',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($traceRecord) {
            if (empty($traceRecord->organization_id) && Auth::check()) {
                $traceRecord->organization_id = Auth::user()->organization_id;
            }
        });
    }

    // Relationships
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function cteEvents(): HasMany
    {
        return $this->hasMany(CTEEvent::class);
    }

    public function parentRecords()
    {
        return $this->belongsToMany(
            TraceRecord::class,
            'trace_relationships',
            'child_id',
            'parent_id'
        )->withPivot('relationship_type');
    }

    public function childRecords()
    {
        return $this->belongsToMany(
            TraceRecord::class,
            'trace_relationships',
            'parent_id',
            'child_id'
        )->withPivot('relationship_type');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeByLocation($query, $locationId)
    {
        return $query->where('location_id', $locationId);
    }

    /**
     * Trace backward (find all source/input records)
     * Uses recursive CTE to traverse parent relationships
     * Added depth limit (100) to prevent infinite loops
     * Added organization_id filter to prevent cross-org data leakage
     */
    public function traceBackward()
    {
        $organizationId = $this->organization_id;
        
        if (!$organizationId) {
            \Log::warning('TraceRecord::traceBackward called without organization_id', [
                'trace_record_id' => $this->id
            ]);
            return self::whereRaw('1 = 0'); // Return empty query
        }
        
        $query = '
            WITH RECURSIVE ancestors AS (
                -- Base case: direct parents
                SELECT parent_id as id, 1 as level
                FROM trace_relationships
                WHERE child_id = ? AND organization_id = ?
                
                UNION ALL
                
                -- Recursive case: parents of parents
                -- Added level < 100 limit to prevent infinite loops
                SELECT tr.parent_id, a.level + 1
                FROM trace_relationships tr
                INNER JOIN ancestors a ON tr.child_id = a.id
                WHERE a.level < 100 AND tr.organization_id = ?
            )
            SELECT DISTINCT id FROM ancestors
        ';

        try {
            $ancestorIds = DB::select($query, [$this->id, $organizationId, $organizationId]);
            $ids = array_map(fn($row) => $row->id, $ancestorIds);
            
            if (empty($ids)) {
                return self::whereRaw('1 = 0');
            }
            
            return self::whereIn('id', $ids)->where('organization_id', $organizationId);
        } catch (\Exception $e) {
            \Log::error('TraceRecord::traceBackward error', [
                'trace_record_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            return self::whereRaw('1 = 0');
        }
    }

    /**
     * Trace forward (find all destination/output records)
     * Uses recursive CTE to traverse child relationships
     * Added depth limit (100) to prevent infinite loops
     * Added organization_id filter to prevent cross-org data leakage
     */
    public function traceForward()
    {
        $organizationId = $this->organization_id;
        
        if (!$organizationId) {
            \Log::warning('TraceRecord::traceForward called without organization_id', [
                'trace_record_id' => $this->id
            ]);
            return self::whereRaw('1 = 0'); // Return empty query
        }
        
        $query = '
            WITH RECURSIVE descendants AS (
                -- Base case: direct children
                SELECT child_id as id, 1 as level
                FROM trace_relationships
                WHERE parent_id = ? AND organization_id = ?
                
                UNION ALL
                
                -- Recursive case: children of children
                -- Added level < 100 limit to prevent infinite loops
                SELECT tr.child_id, d.level + 1
                FROM trace_relationships tr
                INNER JOIN descendants d ON tr.parent_id = d.id
                WHERE d.level < 100 AND tr.organization_id = ?
            )
            SELECT DISTINCT id FROM descendants
        ';

        try {
            $descendantIds = DB::select($query, [$this->id, $organizationId, $organizationId]);
            $ids = array_map(fn($row) => $row->id, $descendantIds);
            
            if (empty($ids)) {
                return self::whereRaw('1 = 0');
            }
            
            return self::whereIn('id', $ids)->where('organization_id', $organizationId);
        } catch (\Exception $e) {
            \Log::error('TraceRecord::traceForward error', [
                'trace_record_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            return self::whereRaw('1 = 0');
        }
    }

    /**
     * Get all ancestors (inputs) using materialized path
     * Fallback method if trace_relationships is not populated
     * Added depth limit check to prevent infinite loops
     */
    public function getAncestors()
    {
        if (empty($this->path)) {
            return collect();
        }

        if ($this->depth > 100) {
            \Log::warning('TraceRecord::getAncestors depth limit exceeded', [
                'trace_record_id' => $this->id,
                'depth' => $this->depth
            ]);
            return collect();
        }

        $ancestorIds = explode('/', trim($this->path, '/'));
        
        $ancestorIds = array_filter($ancestorIds);
        $ancestorIds = array_slice($ancestorIds, 0, 100);
        
        return self::whereIn('id', $ancestorIds)
            ->where('organization_id', $this->organization_id)
            ->orderBy('depth')
            ->get();
    }

    /**
     * Get all descendants (outputs) using materialized path
     * Fallback method if trace_relationships is not populated
     * Added limit to prevent excessive results
     */
    public function getDescendants()
    {
        return self::where('path', 'like', $this->path . $this->id . '/%')
            ->where('organization_id', $this->organization_id)
            ->limit(1000)
            ->get();
    }

    // Helper methods
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function updatePath($parentPath = '')
    {
        $newDepth = substr_count($parentPath, '/');
        
        if ($newDepth > 100) {
            \Log::error('TraceRecord::updatePath depth limit exceeded', [
                'trace_record_id' => $this->id,
                'parent_path' => $parentPath,
                'new_depth' => $newDepth
            ]);
            throw new \Exception('Maximum path depth exceeded (100 levels)');
        }
        
        $this->path = $parentPath . $this->id . '/';
        $this->depth = $newDepth;
        $this->save();
    }

    
    /**
     * Get remaining available quantity
     */
    public function getRemainingQuantity(): float
    {
        return (float) $this->available_quantity;
    }

    /**
     * Check if can consume specified amount
     */
    public function canConsume(float $amount): bool
    {
        return $this->available_quantity >= $amount;
    }

    /**
     * Consume specified amount from this record
     * Returns true if successful, false if insufficient quantity
     */
    public function consume(float $amount): bool
    {
        if (!$this->canConsume($amount)) {
            return false;
        }
        
        $this->consumed_quantity += $amount;
        $this->available_quantity -= $amount;
        
        if ($this->available_quantity <= 0.001) { // Use small epsilon for float comparison
            $this->status = 'consumed';
        }
        
        $this->save();
        return true;
    }

    /**
     * Get consumption percentage
     */
    public function getConsumptionPercentage(): float
    {
        if ($this->quantity <= 0) {
            return 0;
        }
        
        return ($this->consumed_quantity / $this->quantity) * 100;
    }

    /**
     * Check if record is fully consumed
     */
    public function isFullyConsumed(): bool
    {
        return $this->available_quantity <= 0.001;
    }

    /**
     * Check if record is partially consumed
     */
    public function isPartiallyConsumed(): bool
    {
        return $this->consumed_quantity > 0 && $this->available_quantity > 0;
    }
}
