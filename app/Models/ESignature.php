<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\Casts\Attribute;

class ESignature extends Model
{
    use HasFactory;

    protected $table = 'e_signatures';

    protected $fillable = [
        'user_id',
        'record_type',
        'record_id',
        'action',
        'reason',
        'meaning_of_signature',
        'signature_hash',
        'signature_algorithm',
        'record_content_hash',
        'certificate_id',
        'timestamp_token',
        'is_revoked',
        'revoked_at',
        'revocation_reason',
        'mfa_method',
        'ip_address',
        'user_agent',
        'signed_at',
        'signature_valid_from',
        'signature_valid_until',
        'signature_validity_period_days',
        'timestamp_token_der',
        'timestamp_utc_time',
        'timestamp_tsa_url',
        'timestamp_tsa_certificate',
        'certificate_revocation_checked',
        'certificate_revocation_checked_at',
        'certificate_revocation_status',
        'certificate_revocation_reason',
        'verification_report',
        'verification_passed',
        'last_verified_at',
        'signature_format',
        'xades_metadata',
        'certificate_subject',
        'certificate_issuer',
        'certificate_serial_number',
        'tsa_url',
        'tsa_certificate_subject',
        'ltv_timestamp_chain',
        'ltv_certificate_chain',
        'ltv_crl_response',
        'ltv_ocsp_response',
        'ltv_last_validation_at',
        'ltv_enabled',
        'batch_operation_id',
        'batch_operation_type',
        'batch_operation_sequence',
        'batch_total_count',
        'signature_attributes',
        'signature_metadata',
        'signature_status',
        'delegated_by_user_id',
        'delegation_authority',
        'delegation_valid_until',
        'is_delegated_signature',
        'signature_expires_at',
        'is_expired',
        'expiration_checked_at',
        'expiration_status',
        'encryption_algorithm',
        'encrypted_fields',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
        'revoked_at' => 'datetime',
        'is_revoked' => 'boolean',
        'signature_valid_from' => 'datetime',
        'signature_valid_until' => 'datetime',
        'timestamp_utc_time' => 'datetime',
        'certificate_revocation_checked' => 'boolean',
        'certificate_revocation_checked_at' => 'datetime',
        'verification_report' => 'array',
        'verification_passed' => 'boolean',
        'last_verified_at' => 'datetime',
        'ltv_timestamp_chain' => 'array',
        'ltv_certificate_chain' => 'array',
        'ltv_crl_response' => 'array',
        'ltv_ocsp_response' => 'array',
        'ltv_last_validation_at' => 'datetime',
        'ltv_enabled' => 'boolean',
        'signature_attributes' => 'array',
        'signature_metadata' => 'array',
        'delegation_valid_until' => 'datetime',
        'is_delegated_signature' => 'boolean',
        'signature_expires_at' => 'datetime',
        'is_expired' => 'boolean',
        'expiration_checked_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Accessors & Mutators (Tự động mã hóa & giải mã dữ li��u nhạy cảm)
    |--------------------------------------------------------------------------
    */
    protected function signatureHash(): Attribute
    {
        return Attribute::make(
            get: fn($v) => $v ? Crypt::decryptString($v) : null,
            set: fn($v) => $v ? Crypt::encryptString($v) : null,
        );
    }

    protected function recordContentHash(): Attribute
    {
        return Attribute::make(
            get: fn($v) => $v ? Crypt::decryptString($v) : null,
            set: fn($v) => $v ? Crypt::encryptString($v) : null,
        );
    }

    protected function ipAddress(): Attribute
    {
        return Attribute::make(
            get: fn($v) => $v ? Crypt::decryptString($v) : null,
            set: fn($v) => $v ? Crypt::encryptString($v) : null,
        );
    }

    protected function timestampToken(): Attribute
    {
        return Attribute::make(
            get: fn($v) => $v ? Crypt::decryptString($v) : null,
            set: fn($v) => $v ? Crypt::encryptString($v) : null,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function digitalCertificate()
    {
        return $this->belongsTo(DigitalCertificate::class, 'certificate_id');
    }

    public function recordType()
    {
        return $this->belongsTo(SignatureRecordType::class, 'record_type', 'record_type');
    }

    public function delegatedByUser()
    {
        return $this->belongsTo(User::class, 'delegated_by_user_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */
    public function scopeByRecord($query, string $type, int $id)
    {
        return $query->where('record_type', $type)
                     ->where('record_id', $id);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('signed_at', '>=', now()->subDays($days));
    }

    /*
    |--------------------------------------------------------------------------
    | Business Logic
    |--------------------------------------------------------------------------
    */
    public static function createSignature(
        User $user,
        string $recordType,
        int $recordId,
        string $action,
        string $password,
        ?string $reason = null
    ): self {
        // 1️⃣ Kiểm tra mật khẩu người ký
        if (!Hash::check($password, $user->password)) {
            throw new \Exception('Invalid password for e-signature');
        }

        // 2️⃣ Lấy nội dung bản ghi để băm
        $recordContent = self::getRecordContent($recordType, $recordId);
        $recordContentHash = hash('sha512', $recordContent);

        // 3️⃣ Tạo dữ liệu chữ ký
        $signatureData = implode('|', [
            $user->id,
            $recordContentHash,
            now()->toIso8601String(),
            $recordType,
            $recordId,
            $action,
        ]);

        // 4️⃣ Tạo bản ghi chữ ký
        return self::create([
            'user_id' => $user->id,
            'record_type' => $recordType,
            'record_id' => $recordId,
            'action' => $action,
            'reason' => $reason,
            'signature_hash' => hash('sha512', $signatureData),
            'signature_algorithm' => 'SHA512',
            'record_content_hash' => $recordContentHash,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp_token' => uniqid(),
            'signed_at' => now(),
        ]);
    }

    public function verify(User $user, string $password): bool
    {
        if ($this->user_id !== $user->id) return false;
        if (!Hash::check($password, $user->password)) return false;
        if ($this->is_revoked) return false;

        $recordContent = self::getRecordContent($this->record_type, $this->record_id);
        $currentHash = hash('sha512', $recordContent);
        if (!hash_equals($currentHash, $this->record_content_hash)) return false;

        $signatureData = implode('|', [
            $user->id,
            $this->record_content_hash,
            $this->signed_at->toIso8601String(),
            $this->record_type,
            $this->record_id,
            $this->action,
        ]);

        $expectedHash = hash('sha512', $signatureData);
        return hash_equals($expectedHash, $this->signature_hash);
    }

    /*
    |--------------------------------------------------------------------------
    | Helper
    |--------------------------------------------------------------------------
    */
    private static function getRecordContent(string $recordType, int $recordId): string
    {
        $recordTypeModel = SignatureRecordType::where('record_type', $recordType)->first();
        if (!$recordTypeModel) {
            return json_encode(['type' => $recordType, 'id' => $recordId]);
        }

        try {
            return $recordTypeModel->extractRecordContent($recordId);
        } catch (\Throwable $e) {
            return json_encode(['error' => $e->getMessage()]);
        }
    }
}
