<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebhookLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'gateway',
        'event_id',
        'event_type',
        'user_id',
        'status',
        'payload',
        'response',
        'error_message',
        'ip_address',
        'attempt_count',
        'last_attempt_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'response' => 'array',
        'last_attempt_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeProcessed($query)
    {
        return $query->where('status', 'processed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeByGateway($query, string $gateway)
    {
        return $query->where('gateway', $gateway);
    }

    // Helper methods
    public function markAsProcessed(array $response = []): void
    {
        $this->update([
            'status' => 'processed',
            'response' => $response,
            'last_attempt_at' => now(),
        ]);
    }

    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
            'attempt_count' => $this->attempt_count + 1,
            'last_attempt_at' => now(),
        ]);
    }

    public static function findByEventId(string $eventId)
    {
        return self::where('event_id', $eventId)->first();
    }
}
