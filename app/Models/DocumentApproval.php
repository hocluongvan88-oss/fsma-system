<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasOrganizationScope;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentApproval extends Model
{
    use HasFactory, HasOrganizationScope, SoftDeletes;

    protected $fillable = [
        'document_id',
        'organization_id',
        'approval_level',
        'approved_by',
        'status',
        'notes',
        'rejection_reason',
        'approved_at',
        'rejected_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function scopeByLevel($query, int $level)
    {
        return $query->where('approval_level', $level);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}
