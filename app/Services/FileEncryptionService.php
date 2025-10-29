<?php

namespace App\Services;

use App\Models\Document;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;

class FileEncryptionService
{
    protected AuditLogService $auditLogService;
    private const ENCRYPTION_ALGORITHM = 'AES-256-GCM';

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    /**
     * Encrypt file at rest using Laravel's encryption
     */
    public function encryptFile(string $filePath): bool
    {
        if (!Storage::exists($filePath)) {
            throw new \Exception("File not found: {$filePath}");
        }

        try {
            $fileContent = Storage::get($filePath);
            $encryptedContent = Crypt::encryptString($fileContent);
            
            Storage::put($filePath, $encryptedContent);

            return true;
        } catch (\Exception $e) {
            \Log::error("File encryption failed for {$filePath}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Decrypt file for reading
     */
    public function decryptFile(string $filePath): string
    {
        if (!Storage::exists($filePath)) {
            throw new \Exception("File not found: {$filePath}");
        }

        try {
            $encryptedContent = Storage::get($filePath);
            $decryptedContent = Crypt::decryptString($encryptedContent);
            
            return $decryptedContent;
        } catch (\Exception $e) {
            \Log::error("File decryption failed for {$filePath}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Encrypt document file
     */
    public function encryptDocumentFile(Document $document): bool
    {
        try {
            $this->encryptFile($document->file_path);

            $document->update([
                'is_encrypted' => true,
                'encrypted_at' => now(),
            ]);

            $this->auditLogService->log(
                'ENCRYPT_FILE',
                'documents',
                $document->id,
                null,
                ['file_path' => $document->file_path]
            );

            return true;
        } catch (\Exception $e) {
            \Log::error("Document encryption failed for document {$document->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Decrypt document file for download
     */
    public function getDecryptedFileContent(Document $document): string
    {
        if (!$document->is_encrypted) {
            return Storage::get($document->file_path);
        }

        try {
            $decryptedContent = $this->decryptFile($document->file_path);

            $this->auditLogService->log(
                'DECRYPT_FILE_ACCESS',
                'documents',
                $document->id,
                null,
                ['file_path' => $document->file_path]
            );

            return $decryptedContent;
        } catch (\Exception $e) {
            \Log::error("Document decryption failed for document {$document->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Encrypt all document versions
     */
    public function encryptDocumentVersions(Document $document): int
    {
        $encryptedCount = 0;

        foreach ($document->versions as $version) {
            try {
                $this->encryptFile($version->file_path);
                $version->update(['is_encrypted' => true]);
                $encryptedCount++;
            } catch (\Exception $e) {
                \Log::error("Failed to encrypt version {$version->id}: " . $e->getMessage());
            }
        }

        return $encryptedCount;
    }

    /**
     * Batch encrypt all documents for organization
     */
    public function encryptOrganizationDocuments(int $organizationId): array
    {
        $documents = Document::where('organization_id', $organizationId)
            ->where('is_encrypted', false)
            ->get();

        $results = [
            'total' => $documents->count(),
            'encrypted' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        foreach ($documents as $document) {
            try {
                $this->encryptDocumentFile($document);
                $this->encryptDocumentVersions($document);
                $results['encrypted']++;
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'document_id' => $document->id,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Check if file is encrypted
     */
    public function isFileEncrypted(string $filePath): bool
    {
        if (!Storage::exists($filePath)) {
            return false;
        }

        try {
            $content = Storage::get($filePath);
            // Try to decrypt - if it succeeds, it's encrypted
            Crypt::decryptString($content);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get encryption status for document
     */
    public function getEncryptionStatus(Document $document): array
    {
        $mainFileEncrypted = $this->isFileEncrypted($document->file_path);
        
        $versionEncryptionStatus = [];
        foreach ($document->versions as $version) {
            $versionEncryptionStatus[] = [
                'version_id' => $version->id,
                'version' => $version->version,
                'is_encrypted' => $this->isFileEncrypted($version->file_path),
            ];
        }

        return [
            'document_id' => $document->id,
            'main_file_encrypted' => $mainFileEncrypted,
            'versions_encrypted' => count(array_filter($versionEncryptionStatus, fn($v) => $v['is_encrypted'])),
            'total_versions' => count($versionEncryptionStatus),
            'version_details' => $versionEncryptionStatus,
            'encrypted_at' => $document->encrypted_at,
        ];
    }

    /**
     * Rotate encryption keys (re-encrypt with new key)
     */
    public function rotateEncryptionKeys(Document $document): bool
    {
        try {
            // Decrypt with old key
            $decryptedContent = $this->getDecryptedFileContent($document);

            // Re-encrypt with new key (Laravel will use the new APP_KEY)
            $encryptedContent = Crypt::encryptString($decryptedContent);
            Storage::put($document->file_path, $encryptedContent);

            $this->auditLogService->log(
                'ROTATE_ENCRYPTION_KEY',
                'documents',
                $document->id,
                null,
                ['file_path' => $document->file_path]
            );

            return true;
        } catch (\Exception $e) {
            \Log::error("Key rotation failed for document {$document->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Batch rotate encryption keys for organization
     */
    public function rotateOrganizationEncryptionKeys(int $organizationId): array
    {
        $documents = Document::where('organization_id', $organizationId)
            ->where('is_encrypted', true)
            ->get();

        $results = [
            'total' => $documents->count(),
            'rotated' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        foreach ($documents as $document) {
            try {
                $this->rotateEncryptionKeys($document);
                $results['rotated']++;
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'document_id' => $document->id,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }
}
