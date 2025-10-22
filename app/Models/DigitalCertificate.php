<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DigitalCertificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'certificate_id',
        'certificate_pem',
        'certificate_chain',
        'root_ca_certificate',
        'intermediate_ca_certificate',
        'public_key',
        'private_key_encrypted',
        'issuer',
        'subject',
        'serial_number',
        'issued_at',
        'expires_at',
        'is_revoked',
        'revoked_at',
        'revocation_reason',
        'signature_algorithm',
        'key_size',
        'crl_url',
        'ocsp_url',
        'crl_last_checked',
        'ocsp_last_checked',
        'is_crl_valid',
        'is_ocsp_valid',
        'certificate_usage',
        'signature_count',
        'last_used_at',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
            'expires_at' => 'datetime',
            'revoked_at' => 'datetime',
            'crl_last_checked' => 'datetime',
            'ocsp_last_checked' => 'datetime',
            'last_used_at' => 'datetime',
            'is_revoked' => 'boolean',
            'is_crl_valid' => 'boolean',
            'is_ocsp_valid' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_revoked', false)
                     ->where('expires_at', '>', now());
    }

    public function scopeForSigning($query)
    {
        return $query->active()
                     ->whereIn('certificate_usage', ['signing', 'both']);
    }

    public function isValid(): bool
    {
        return !$this->is_revoked 
            && $this->expires_at > now()
            && $this->is_crl_valid
            && $this->is_ocsp_valid;
    }

    public function isCertificateChainValid(): bool
    {
        return !empty($this->certificate_chain) 
            && !empty($this->root_ca_certificate)
            && $this->isValid();
    }

    public function incrementSignatureCount(): void
    {
        $this->increment('signature_count');
        $this->update(['last_used_at' => now()]);
    }
}
