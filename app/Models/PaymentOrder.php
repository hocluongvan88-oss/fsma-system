<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_id',
        'package_id',
        'billing_period',
        'amount',
        'status',
        'payment_gateway',
        'transaction_id',
        'idempotency_key',
        'metadata',
        'response_data',
        'ip_address',
        'user_agent',
        'expires_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'response_data' => 'array',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'pending')
            ->where('expires_at', '>', now());
    }

    // Helper methods
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at < now();
    }

    public function markAsCompleted(string $transactionId, string $gateway = 'vnpay'): void
    {
        $this->update([
            'status' => 'completed',
            'transaction_id' => $transactionId,
            'payment_gateway' => $gateway,
        ]);
    }

    public function markAsFailed(string $reason = null): void
    {
        $this->update([
            'status' => 'failed',
            'response_data' => array_merge($this->response_data ?? [], ['error' => $reason]),
        ]);
    }
}
