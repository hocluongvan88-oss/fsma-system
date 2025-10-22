<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasOrganizationAccess;

class Document extends Model
{
    use HasFactory, HasOrganizationAccess;

    protected $fillable = [
        'doc_number',
        'title',
        'type',
        'description',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'version',
        'status',
        'effective_date',
        'review_date',
        'expiry_date',
        'uploaded_by',
        'approved_by',
        'approved_at',
        'metadata',
        'organization_id',
    ];

    protected $casts = [
        'metadata' => 'array',
        'effective_date' => 'date',
        'review_date' => 'date',
        'expiry_date' => 'date',
        'approved_at' => 'datetime',
    ];

    // Relationships
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function versions()
    {
        return $this->hasMany(DocumentVersion::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', '!=', 'archived');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [now(), now()->addDays($days)]);
    }

    // Helper methods
    public function getFileSizeHumanAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function isExpired()
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function needsReview()
    {
        return $this->review_date && $this->review_date->isPast();
    }

    public function canBeApproved()
    {
        return $this->status === 'review';
    }

    public function approve(User $user)
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);
    }

    public function createVersion($filePath, $changeNotes, User $user)
    {
        // Increment version
        $versionParts = explode('.', $this->version);
        $versionParts[count($versionParts) - 1]++;
        $newVersion = implode('.', $versionParts);

        // Create version record
        $this->versions()->create([
            'version' => $this->version,
            'file_path' => $this->file_path,
            'change_notes' => $changeNotes,
            'created_by' => $user->id,
        ]);

        // Update document
        $this->update([
            'version' => $newVersion,
            'file_path' => $filePath,
            'status' => 'draft',
        ]);

        return $newVersion;
    }
}
