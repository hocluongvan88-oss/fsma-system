<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasOrganizationScope;

class PaymentOrder extends Model
{
    use HasFactory, HasOrganizationScope;

    protected $fillable = [
        'user_id',
        'organization_id',
        'order_id',
        'package_id',
        'billing_period',
        'amount',
        'currency',
        'status',
        'payment_gateway',
        'transaction_id',
        'stripe_session_id',
        'stripe_invoice_id',
        'idempotency_key',
        'metadata',
        'response_data',
        'error_message',
        'ip_address',
        'user_agent',
        'expires_at',
        'completed_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'response_data' => 'array',
        'expires_at' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $unique = [
        'idempotency_key',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id', 'id');
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

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
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

    public function markAsCompleted(string $transactionId, string $gateway = 'stripe'): void
    {
        $this->update([
            'status' => 'completed',
            'transaction_id' => $transactionId,
            'payment_gateway' => $gateway,
            'completed_at' => now(),
        ]);
    }

    public function markAsFailed(string $reason = null): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $reason,
            'response_data' => array_merge($this->response_data ?? [], ['error' => $reason]),
        ]);
    }
}
