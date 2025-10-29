<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasOrganizationScope;

class SignatureRevocation extends Model
{
    use HasOrganizationScope;

    protected $fillable = [
        'signature_id',
        'revoked_by_user_id',
        'revocation_reason',
        'revocation_category',
        'revocation_details',
        'ip_address',
        'user_agent',
        'is_emergency_revocation',
        'revoked_at',
    ];

    protected $casts = [
        'revoked_at' => 'datetime',
        'is_emergency_revocation' => 'boolean',
    ];

    public function signature(): BelongsTo
    {
        return $this->belongsTo(ESignature::class);
    }

    public function revokedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by_user_id');
    }

    public function scopeEmergency($query)
    {
        return $query->where('is_emergency_revocation', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('revocation_category', $category);
    }

    public static function getCategories(): array
    {
        return [
            'user_request' => 'User Request',
            'security_breach' => 'Security Breach',
            'data_modification' => 'Data Modification Detected',
            'compliance' => 'Compliance Requirement',
            'other' => 'Other',
        ];
    }
}
