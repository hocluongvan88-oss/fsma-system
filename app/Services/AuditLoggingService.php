<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

class AuditLoggingService
{
    /**
     * Log user creation
     */
    public static function logUserCreated(int $userId, array $userData, ?int $createdById = null): void
    {
        $createdById = $createdById ?? auth()->id();
        
        self::createAuditLog(
            'user_created',
            'User',
            $userId,
            [
                'username' => $userData['username'] ?? null,
                'email' => $userData['email'] ?? null,
                'role' => $userData['role'] ?? null,
                'organization_id' => $userData['organization_id'] ?? null,
            ],
            null,
            $createdById
        );

        Log::info('User created', [
            'user_id' => $userId,
            'created_by' => $createdById,
            'username' => $userData['username'] ?? null,
            'organization_id' => $userData['organization_id'] ?? null,
        ]);
    }

    /**
     * Log user update
     */
    public static function logUserUpdated(int $userId, array $oldData, array $newData, ?int $updatedById = null): void
    {
        $updatedById = $updatedById ?? auth()->id();
        
        $changes = [];
        foreach ($newData as $key => $value) {
            if (($oldData[$key] ?? null) !== $value) {
                $changes[$key] = [
                    'old' => $oldData[$key] ?? null,
                    'new' => $value,
                ];
            }
        }

        if (empty($changes)) {
            return;
        }

        self::createAuditLog(
            'user_updated',
            'User',
            $userId,
            $changes,
            $oldData,
            $updatedById
        );

        Log::info('User updated', [
            'user_id' => $userId,
            'updated_by' => $updatedById,
            'changes' => array_keys($changes),
        ]);
    }

    /**
     * Log user deletion
     */
    public static function logUserDeleted(int $userId, array $userData, ?int $deletedById = null): void
    {
        $deletedById = $deletedById ?? auth()->id();
        
        self::createAuditLog(
            'user_deleted',
            'User',
            $userId,
            [
                'username' => $userData['username'] ?? null,
                'email' => $userData['email'] ?? null,
                'organization_id' => $userData['organization_id'] ?? null,
            ],
            $userData,
            $deletedById
        );

        Log::warning('User deleted', [
            'user_id' => $userId,
            'deleted_by' => $deletedById,
            'username' => $userData['username'] ?? null,
        ]);
    }

    /**
     * Log organization package change
     * Updated to log organization-level package changes instead of user-level
     */
    public static function logPackageChanged(int $organizationId, string $oldPackage, string $newPackage, ?int $changedById = null): void
    {
        $changedById = $changedById ?? auth()->id();
        
        self::createAuditLog(
            'package_changed',
            'Organization',
            $organizationId,
            [
                'old_package' => $oldPackage,
                'new_package' => $newPackage,
            ],
            ['package_id' => $oldPackage],
            $changedById
        );

        Log::info('Organization package changed', [
            'organization_id' => $organizationId,
            'changed_by' => $changedById,
            'old_package' => $oldPackage,
            'new_package' => $newPackage,
        ]);
    }

    /**
     * Log e-signature creation
     */
    public static function logESignatureCreated(int $signatureId, string $action, int $recordId, ?int $userId = null): void
    {
        $userId = $userId ?? auth()->id();
        
        self::createAuditLog(
            'e_signature_created',
            'ESignature',
            $signatureId,
            [
                'action' => $action,
                'record_id' => $recordId,
            ],
            null,
            $userId
        );

        Log::info('E-signature created', [
            'signature_id' => $signatureId,
            'user_id' => $userId,
            'action' => $action,
            'record_id' => $recordId,
        ]);
    }

    /**
     * Log e-signature revocation
     */
    public static function logESignatureRevoked(int $signatureId, string $reason, ?int $revokedById = null): void
    {
        $revokedById = $revokedById ?? auth()->id();
        
        self::createAuditLog(
            'e_signature_revoked',
            'ESignature',
            $signatureId,
            [
                'reason' => $reason,
            ],
            null,
            $revokedById
        );

        Log::warning('E-signature revoked', [
            'signature_id' => $signatureId,
            'revoked_by' => $revokedById,
            'reason' => $reason,
        ]);
    }

    /**
     * Log cross-tenant access attempt
     */
    public static function logCrossTenantAccessAttempt(int $userId, int $attemptedTenantId, string $action, string $resource): void
    {
        $userTenantId = auth()->user()?->organization_id;
        
        self::createAuditLog(
            'cross_tenant_access_attempt',
            $resource,
            $attemptedTenantId,
            [
                'user_id' => $userId,
                'user_tenant_id' => $userTenantId,
                'attempted_tenant_id' => $attemptedTenantId,
                'action' => $action,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ],
            null,
            $userId
        );

        Log::warning('Cross-tenant access attempt detected', [
            'user_id' => $userId,
            'user_tenant_id' => $userTenantId,
            'attempted_tenant_id' => $attemptedTenantId,
            'action' => $action,
            'ip' => Request::ip(),
        ]);
    }

    /**
     * Log unauthorized access attempt
     */
    public static function logUnauthorizedAccessAttempt(int $userId, string $resource, int $resourceId, string $action): void
    {
        self::createAuditLog(
            'unauthorized_access_attempt',
            $resource,
            $resourceId,
            [
                'action' => $action,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ],
            null,
            $userId
        );

        Log::warning('Unauthorized access attempt', [
            'user_id' => $userId,
            'resource' => $resource,
            'resource_id' => $resourceId,
            'action' => $action,
            'ip' => Request::ip(),
        ]);
    }

    /**
     * Log CTE event creation
     */
    public static function logCTEEventCreated(int $eventId, string $eventType, array $data, ?int $createdById = null): void
    {
        $createdById = $createdById ?? auth()->id();
        
        self::createAuditLog(
            'cte_event_created',
            'CTEEvent',
            $eventId,
            array_merge(['event_type' => $eventType], $data),
            null,
            $createdById
        );

        Log::info('CTE event created', [
            'event_id' => $eventId,
            'event_type' => $eventType,
            'created_by' => $createdById,
        ]);
    }

    /**
     * Log CTE event void
     */
    public static function logCTEEventVoided(int $eventId, string $reason, array $metadata = [], ?int $voidedById = null): void
    {
        $voidedById = $voidedById ?? auth()->id();
        
        self::createAuditLog(
            'cte_event_voided',
            'CTEEvent',
            $eventId,
            array_merge(['reason' => $reason], $metadata),
            null,
            $voidedById
        );

        Log::warning('CTE event voided', [
            'event_id' => $eventId,
            'voided_by' => $voidedById,
            'reason' => $reason,
        ]);
    }

    /**
     * Log document upload
     */
    public static function logDocumentUploaded(int $documentId, string $fileName, int $fileSize, ?int $uploadedById = null): void
    {
        $uploadedById = $uploadedById ?? auth()->id();
        
        self::createAuditLog(
            'document_uploaded',
            'Document',
            $documentId,
            [
                'file_name' => $fileName,
                'file_size' => $fileSize,
            ],
            null,
            $uploadedById
        );

        Log::info('Document uploaded', [
            'document_id' => $documentId,
            'uploaded_by' => $uploadedById,
            'file_name' => $fileName,
        ]);
    }

    /**
     * Log document approval
     */
    public static function logDocumentApproved(int $documentId, ?int $approvedById = null): void
    {
        $approvedById = $approvedById ?? auth()->id();
        
        self::createAuditLog(
            'document_approved',
            'Document',
            $documentId,
            [],
            null,
            $approvedById
        );

        Log::info('Document approved', [
            'document_id' => $documentId,
            'approved_by' => $approvedById,
        ]);
    }

    /**
     * Create audit log entry
     */
    private static function createAuditLog(
        string $action,
        string $resourceType,
        int $resourceId,
        array $newData,
        ?array $oldData,
        ?int $userId
    ): void {
        try {
            AuditLog::create([
                'user_id' => $userId,
                'action' => $action,
                'resource_type' => $resourceType,
                'resource_id' => $resourceId,
                'old_data' => $oldData ? json_encode($oldData) : null,
                'new_data' => json_encode($newData),
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'organization_id' => auth()->user()?->organization_id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create audit log', [
                'action' => $action,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get audit logs for organization
     */
    public static function getOrganizationAuditLogs(int $organizationId, int $limit = 100)
    {
        return AuditLog::where('organization_id', $organizationId)
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get audit logs for user
     */
    public static function getUserAuditLogs(int $userId, int $limit = 100)
    {
        return AuditLog::where('user_id', $userId)
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get suspicious activity logs
     */
    public static function getSuspiciousActivityLogs(int $organizationId, int $days = 7)
    {
        return AuditLog::where('organization_id', $organizationId)
            ->where('created_at', '>=', now()->subDays($days))
            ->whereIn('action', [
                'cross_tenant_access_attempt',
                'unauthorized_access_attempt',
                'user_deleted',
                'e_signature_revoked',
            ])
            ->latest('created_at')
            ->get();
    }
}
