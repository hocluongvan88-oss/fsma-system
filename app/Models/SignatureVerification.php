<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasOrganizationScope;

class SignatureVerification extends Model
{
    use HasOrganizationScope;

    protected $fillable = [
        'signature_id',
        'verified_by_user_id',
        'verification_type',
        'verification_status',
        'verification_details',
        'verification_checks',
        'ip_address',
        'user_agent',
        'verification_duration_ms',
        'is_brute_force_attempt',
    ];

    protected $casts = [
        'verification_checks' => 'array',
        'is_brute_force_attempt' => 'boolean',
    ];

    public function signature(): BelongsTo
    {
        return $this->belongsTo(ESignature::class);
    }

    public function verifiedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('verification_status', $status);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('verification_type', $type);
    }

    public function scopeBruteForcAttempts($query)
    {
        return $query->where('is_brute_force_attempt', true);
    }
}
