<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasOrganizationScope;

class ArchivalLog extends Model
{
    use HasOrganizationScope;

    protected $fillable = [
        'organization_id',
        'data_type',
        'strategy',
        'records_archived',
        'records_verified',
        'records_deleted_from_hot',
        'archival_location',
        'executed_at',
        'executed_by',
        'status',
        'error_message',
    ];

    protected $casts = [
        'executed_at' => 'datetime',
        'records_archived' => 'integer',
        'records_verified' => 'integer',
        'records_deleted_from_hot' => 'integer',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Scope for successful archival operations
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope for failed archival operations
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope by data type
     */
    public function scopeByDataType($query, string $dataType)
    {
        return $query->where('data_type', $dataType);
    }
}
