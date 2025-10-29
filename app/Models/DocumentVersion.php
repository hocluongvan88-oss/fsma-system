<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasOrganizationScope;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentVersion extends Model
{
    use HasFactory, HasOrganizationScope, SoftDeletes;

    protected $fillable = [
        'document_id',
        'organization_id',
        'version',
        'change_type', // Added for semantic versioning
        'file_path',
        'file_hash', // Added for file integrity
        'change_notes',
        'created_by',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeByChangeType($query, string $changeType)
    {
        return $query->where('change_type', $changeType);
    }

    public function scopeMajorVersions($query)
    {
        return $query->where('change_type', 'major');
    }
}
