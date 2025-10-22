<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class TraceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'tlc',
        'product_id',
        'quantity',
        'available_quantity', // Added for quantity tracking
        'consumed_quantity',  // Added for quantity tracking
        'unit',
        'location_id',
        'harvest_date',
        'pack_date',
        'path',
        'depth',
        'status',
        'organization_id', // Added for multi-tenancy
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'available_quantity' => 'decimal:2', // Added cast
            'consumed_quantity' => 'decimal:2',  // Added cast
            'harvest_date' => 'date',
            'pack_date' => 'date',
            'depth' => 'integer',
        ];
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
     */
    public function traceBackward()
    {
        $query = '
            WITH RECURSIVE ancestors AS (
                -- Base case: direct parents
                SELECT parent_id as id, 1 as level
                FROM trace_relationships
                WHERE child_id = ?
                
                UNION ALL
                
                -- Recursive case: parents of parents
                SELECT tr.parent_id, a.level + 1
                FROM trace_relationships tr
                INNER JOIN ancestors a ON tr.child_id = a.id
                WHERE a.level < 100
            )
            SELECT DISTINCT id FROM ancestors
        ';

        $ancestorIds = DB::select($query, [$this->id]);
        $ids = array_map(fn($row) => $row->id, $ancestorIds);
        
        return self::whereIn('id', $ids);
    }

    /**
     * Trace forward (find all destination/output records)
     * Uses recursive CTE to traverse child relationships
     */
    public function traceForward()
    {
        $query = '
            WITH RECURSIVE descendants AS (
                -- Base case: direct children
                SELECT child_id as id, 1 as level
                FROM trace_relationships
                WHERE parent_id = ?
                
                UNION ALL
                
                -- Recursive case: children of children
                SELECT tr.child_id, d.level + 1
                FROM trace_relationships tr
                INNER JOIN descendants d ON tr.parent_id = d.id
                WHERE d.level < 100
            )
            SELECT DISTINCT id FROM descendants
        ';

        $descendantIds = DB::select($query, [$this->id]);
        $ids = array_map(fn($row) => $row->id, $descendantIds);
        
        return self::whereIn('id', $ids);
    }

    /**
     * Get all ancestors (inputs) using materialized path
     * Fallback method if trace_relationships is not populated
     */
    public function getAncestors()
    {
        if (empty($this->path)) {
            return collect();
        }

        $ancestorIds = explode('/', trim($this->path, '/'));
        return self::whereIn('id', $ancestorIds)->orderBy('depth')->get();
    }

    /**
     * Get all descendants (outputs) using materialized path
     * Fallback method if trace_relationships is not populated
     */
    public function getDescendants()
    {
        return self::where('path', 'like', $this->path . $this->id . '/%')->get();
    }

    // Helper methods
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function updatePath($parentPath = '')
    {
        $this->path = $parentPath . $this->id . '/';
        $this->depth = substr_count($this->path, '/') - 1;
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
