<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErrorLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'error_type',
        'error_message',
        'error_code',
        'file_path',
        'line_number',
        'stack_trace',
        'context',
        'user_id',
        'url',
        'method',
        'ip_address',
        'user_agent',
        'error_hash',
        'is_resolved',
        'resolved_at',
        'resolved_by',
        'notes',
        'severity',
        'frequency',
    ];

    protected $casts = [
        'context' => 'array',
        'stack_trace' => 'array',
        'is_resolved' => 'boolean',
        'resolved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    // Scopes
    public function scopeUnresolved($query)
    {
        return $query->where('is_resolved', false);
    }

    public function scopeResolved($query)
    {
        return $query->where('is_resolved', true);
    }

    public function scopeBySeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }

    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeByType($query, $type)
    {
        return $query->where('error_type', $type);
    }

    // Helper methods
    public function markAsResolved($userId, $notes = null)
    {
        $this->update([
            'is_resolved' => true,
            'resolved_at' => now(),
            'resolved_by' => $userId,
            'notes' => $notes,
        ]);
    }

    public function getErrorGroup()
    {
        return self::where('error_hash', $this->error_hash)
            ->where('is_resolved', false)
            ->count();
    }

    public function getSimilarErrors($limit = 10)
    {
        return self::where('error_hash', $this->error_hash)
            ->where('id', '!=', $this->id)
            ->latest()
            ->limit($limit)
            ->get();
    }
}
