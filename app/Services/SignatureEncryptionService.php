<?php

namespace App\Services;

use App\Models\ESignature;
use Illuminate\Support\Facades\Crypt;

class SignatureEncryptionService
{
    /**
     * List of fields that should be encrypted
     */
    protected array $encryptableFields = [
        'signature_hash',
        'record_content_hash',
        'timestamp_token',
        'timestamp_token_der',
        'ip_address',
        'user_agent',
        'meaning_of_signature',
        'reason',
        'revocation_reason',
        'certificate_revocation_reason',
        'xades_metadata',
        'ltv_timestamp_chain',
        'ltv_certificate_chain',
        'ltv_crl_response',
        'ltv_ocsp_response',
        'signature_attributes',
        'signature_metadata',
    ];

    /**
     * Encrypt sensitive fields in signature
     */
    public function encryptSignatureFields(ESignature $signature): void
    {
        $encryptedFields = [];

        foreach ($this->encryptableFields as $field) {
            if ($signature->$field !== null) {
                try {
                    $value = is_array($signature->$field) 
                        ? json_encode($signature->$field) 
                        : $signature->$field;
                    
                    $signature->$field = Crypt::encryptString($value);
                    $encryptedFields[] = $field;
                } catch (\Exception $e) {
                    \Log::warning("Failed to encrypt field {$field}: " . $e->getMessage());
                }
            }
        }

        $signature->update([
            'encrypted_fields' => implode(',', $encryptedFields),
            'encryption_algorithm' => 'AES-256-CBC',
        ]);
    }

    /**
     * Decrypt sensitive fields in signature
     */
    public function decryptSignatureFields(ESignature $signature): array
    {
        $decrypted = [];
        $encryptedFields = explode(',', $signature->encrypted_fields ?? '');

        foreach ($encryptedFields as $field) {
            $field = trim($field);
            if (empty($field) || !isset($signature->$field)) {
                continue;
            }

            try {
                $decrypted[$field] = Crypt::decryptString($signature->$field);
            } catch (\Exception $e) {
                \Log::warning("Failed to decrypt field {$field}: " . $e->getMessage());
                $decrypted[$field] = null;
            }
        }

        return $decrypted;
    }

    /**
     * Get all encryptable fields
     */
    public function getEncryptableFields(): array
    {
        return $this->encryptableFields;
    }

    /**
     * Check if field is encrypted
     */
    public function isFieldEncrypted(ESignature $signature, string $field): bool
    {
        $encryptedFields = explode(',', $signature->encrypted_fields ?? '');
        return in_array($field, $encryptedFields);
    }
}
