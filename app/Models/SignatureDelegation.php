<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SignatureDelegation extends Model
{
    protected $fillable = [
        'delegator_user_id',
        'delegatee_user_id',
        'delegation_authority',
        'delegation_scope',
        'valid_from',
        'valid_until',
        'is_active',
        'revocation_reason',
        'revoked_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'revoked_at' => 'datetime',
        'is_active' => 'boolean',
        'delegation_scope' => 'array',
    ];

    public function delegator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delegator_user_id');
    }

    public function delegatee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delegatee_user_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('valid_from', '<=', now())
            ->where('valid_until', '>=', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('valid_until', '<', now());
    }
}
