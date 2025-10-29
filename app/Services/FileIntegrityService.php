<?php

namespace App\Services;

use App\Models\Document;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class FileIntegrityService
{
    protected AuditLogService $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    /**
     * Calculate SHA-256 hash for file integrity verification
     */
    public function calculateFileHash(string $filePath): string
    {
        if (!Storage::exists($filePath)) {
            throw new \Exception("File not found: {$filePath}");
        }

        $fileContent = Storage::get($filePath);
        return hash('sha256', $fileContent);
    }

    /**
     * Verify file integrity by comparing hashes
     */
    public function verifyFileIntegrity(Document $document): array
    {
        if (!Storage::exists($document->file_path)) {
            return [
                'valid' => false,
                'reason' => 'File not found',
                'timestamp' => now(),
            ];
        }

        if (!$document->file_hash) {
            return [
                'valid' => false,
                'reason' => 'No stored hash found - file integrity cannot be verified',
                'timestamp' => now(),
            ];
        }

        try {
            $currentHash = $this->calculateFileHash($document->file_path);
            $storedHash = $document->file_hash;

            $isValid = hash_equals($currentHash, $storedHash);

            $result = [
                'valid' => $isValid,
                'current_hash' => $currentHash,
                'stored_hash' => $storedHash,
                'timestamp' => now(),
                'reason' => $isValid ? 'Hash match' : 'Hash mismatch - file may have been altered',
            ];

            // Log verification
            $this->auditLogService->log(
                'VERIFY_FILE_INTEGRITY',
                'documents',
                $document->id,
                null,
                [
                    'file_path' => $document->file_path,
                    'valid' => $isValid,
                    'current_hash' => $currentHash,
                ]
            );

            return $result;
        } catch (\Exception $e) {
            return [
                'valid' => false,
                'reason' => 'Verification error: ' . $e->getMessage(),
                'timestamp' => now(),
            ];
        }
    }

    /**
     * Store file hash when document is created or updated
     */
    public function storeFileHash(Document $document): void
    {
        try {
            $hash = $this->calculateFileHash($document->file_path);
            $document->update(['file_hash' => $hash]);

            $this->auditLogService->log(
                'STORE_FILE_HASH',
                'documents',
                $document->id,
                null,
                ['file_hash' => $hash]
            );
        } catch (\Exception $e) {
            \Log::error("Failed to store file hash for document {$document->id}: " . $e->getMessage());
        }
    }

    /**
     * Validate file before upload (FSMA 204 compliance)
     */
    public function validateFileBeforeUpload($file): array
    {
        $errors = [];

        // Check file size
        $maxSize = 10 * 1024 * 1024; // 10MB
        if ($file->getSize() > $maxSize) {
            $errors[] = 'File size exceeds maximum limit of 10MB';
        }

        // Check file type (whitelist)
        $allowedMimeTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain',
            'image/jpeg',
            'image/png',
        ];

        if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
            $errors[] = 'File type not allowed: ' . $file->getMimeType();
        }

        $content = file_get_contents($file->getRealPath());
        if ($this->containsSuspiciousContent($content, $file->getMimeType())) {
            $errors[] = 'File contains suspicious content';
        }

        return [
            'valid' => count($errors) === 0,
            'errors' => $errors,
        ];
    }

    /**
     * Enhanced check for suspicious content with MIME type awareness
     */
    private function containsSuspiciousContent(string $content, string $mimeType): bool
    {
        // Only check for executable patterns in text/plain files
        // PDF, Word, Excel files are binary and won't contain these patterns
        if ($mimeType !== 'text/plain') {
            return false;
        }

        // Check for executable patterns only in actual code context
        $suspiciousPatterns = [
            '/\bexec\s*\(/i',           // exec(
            '/\bsystem\s*\(/i',         // system(
            '/\bpassthru\s*\(/i',       // passthru(
            '/\bshell_exec\s*\(/i',     // shell_exec(
            '/\beval\s*\(/i',           // eval(
            '/\bproc_open\s*\(/i',      // proc_open(
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Calculate metadata hash for integrity checking
     */
    public function calculateMetadataHash(array $metadata): string
    {
        $metadataJson = json_encode($metadata, JSON_UNESCAPED_SLASHES);
        return hash('sha256', $metadataJson);
    }

    /**
     * Verify metadata integrity
     */
    public function verifyMetadataIntegrity(Document $document): bool
    {
        if (!$document->metadata_hash) {
            return true; // No hash to verify
        }

        $currentHash = $this->calculateMetadataHash($document->metadata ?? []);
        
        return hash_equals($currentHash, $document->metadata_hash);
    }
}
