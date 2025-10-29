<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasOrganizationScope;

class ExportLog extends Model
{
    use HasFactory, HasOrganizationScope;

    protected $table = 'export_logs';

    protected $fillable = [
        'user_id',
        'export_id',
        'file_type',
        'export_scope',
        'scope_value',
        'content_hash',
        'file_size',
        'record_count',
        'start_record_id',
        'end_record_id',
        'ip_address',
        'user_agent',
        'is_verified',
        'verified_at',
        'verification_notes',
    ];

    protected function casts(): array
    {
        return [
            'ip_address' => 'encrypted',      // Encrypt IP address at rest
            'user_agent' => 'encrypted',      // Encrypt user agent at rest
            'is_verified' => 'boolean',
            'verified_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeUnverified($query)
    {
        return $query->where('is_verified', false);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('file_type', $type);
    }

    public function scopeByScope($query, string $scope)
    {
        return $query->where('export_scope', $scope);
    }

    public function scopeRecentFirst($query)
    {
        return $query->orderByDesc('created_at');
    }

    // Helper methods
    public function getMetadata(): array
    {
        return [
            'export_id' => $this->export_id,
            'file_type' => $this->file_type,
            'content_hash' => $this->content_hash,
            'exported_by' => $this->user?->name ?? 'System',
            'exported_at' => $this->created_at->toIso8601String(),
            'record_count' => $this->record_count,
            'is_verified' => $this->is_verified,
        ];
    }

    public function markAsVerified(string $notes = null): void
    {
        $this->update([
            'is_verified' => true,
            'verified_at' => now(),
            'verification_notes' => $notes,
        ]);
    }

    public function markAsAltered(string $reason = null): void
    {
        $this->update([
            'is_verified' => false,
            'verification_notes' => 'File has been altered: ' . ($reason ?? 'Hash mismatch'),
        ]);
    }
}
