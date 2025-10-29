<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
    /**
     * Sensitive fields that should be masked in audit logs
     */
    protected $sensitiveFields = [
        'password',
        'password_confirmation',
        'current_password',
        'new_password',
        'old_password',
        'api_key',
        'api_secret',
        'secret',
        'token',
        'access_token',
        'refresh_token',
        'bearer_token',
        'credit_card',
        'card_number',
        'cvv',
        'cvc',
        'ssn',
        'social_security_number',
        'pin',
        'private_key',
        'encryption_key',
        'stripe_secret',
        'vnpay_hash_secret',
        'two_fa_code',
        'backup_code',
        'totp_secret',
        'recovery_code',
    ];

    /**
     * Log an action with automatic sensitive data masking
     * 
     * Made $tableName nullable to handle non-critical operations
     * Made $recordId nullable to handle cases where record ID is not available
     */
    public function log(
        string $action,
        ?string $tableName = null,
        ?int $recordId = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): AuditLog {
        $oldValues = $this->maskSensitiveData($oldValues);
        $newValues = $this->maskSensitiveData($newValues);

        $tableName = $tableName ?? 'unknown_operation';

        return AuditLog::createLog([
            'user_id' => Auth::id(),
            'action' => $action,
            'table_name' => $tableName,
            'record_id' => $recordId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'organization_id' => Auth::user()?->organization_id,
            'created_at' => now(),
        ]);
    }

    /**
     * Mask sensitive fields in data array
     */
    public function maskSensitiveData(?array $data): ?array
    {
        if (!$data) {
            return $data;
        }

        foreach ($data as $key => $value) {
            if ($this->isSensitiveField($key)) {
                $data[$key] = $this->maskValue($value);
            } elseif (is_array($value)) {
                $data[$key] = $this->maskSensitiveData($value);
            }
        }

        return $data;
    }

    /**
     * Check if a field is sensitive
     */
    protected function isSensitiveField(string $fieldName): bool
    {
        $fieldLower = strtolower($fieldName);

        foreach ($this->sensitiveFields as $sensitive) {
            if (str_contains($fieldLower, strtolower($sensitive))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Mask a sensitive value
     */
    protected function maskValue($value): string
    {
        if (!is_string($value) || empty($value)) {
            return '[MASKED]';
        }

        $length = strlen($value);

        if ($length <= 4) {
            return '[MASKED]';
        }

        // Show first 2 and last 2 characters
        $first = substr($value, 0, 2);
        $last = substr($value, -2);
        $masked = str_repeat('*', $length - 4);

        return $first . $masked . $last;
    }

    /**
     * Get audit logs for a user with organization isolation
     */
    public function getUserLogs(int $userId, int $limit = 50)
    {
        $currentUser = Auth::user();

        $query = AuditLog::where('user_id', $userId);

        if (!$currentUser->isAdmin()) {
            $query->whereHas('user', function ($q) use ($currentUser) {
                $q->where('organization_id', $currentUser->organization_id);
            });
        }

        return $query->latest('created_at')->limit($limit)->get();
    }

    /**
     * Get audit logs for a record with organization isolation
     */
    public function getRecordLogs(string $tableName, int $recordId)
    {
        $currentUser = Auth::user();

        $query = AuditLog::where('table_name', $tableName)
            ->where('record_id', $recordId);

        if (!$currentUser->isAdmin()) {
            $query->whereHas('user', function ($q) use ($currentUser) {
                $q->where('organization_id', $currentUser->organization_id);
            });
        }

        return $query->latest('created_at')->get();
    }
}
