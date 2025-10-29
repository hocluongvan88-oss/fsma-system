<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentVersion;
use App\Services\AuditLogService;
use App\Services\FileIntegrityService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class DocumentVersioningService
{
    protected $auditLogService;
    protected $fileIntegrityService;

    public function __construct(
        AuditLogService $auditLogService,
        FileIntegrityService $fileIntegrityService
    ) {
        $this->auditLogService = $auditLogService;
        $this->fileIntegrityService = $fileIntegrityService;
    }

    /**
     * Create a new version of a document with semantic versioning
     * 
     * @param Document $document
     * @param string $changeType 'major'|'minor'|'patch'
     * @param string $changeNotes
     * @param mixed $file
     * @return DocumentVersion
     */
    public function createVersion(
        Document $document,
        string $changeType,
        string $changeNotes,
        $file = null
    ): DocumentVersion {
        DB::beginTransaction();
        
        try {
            // Parse current version
            $currentVersion = $document->version ?? '1.0.0';
            $newVersion = $this->incrementVersion($currentVersion, $changeType);
            
            // Store file if provided
            $filePath = null;
            $fileHash = null;
            
            if ($file) {
                $filePath = $file->store('documents/versions', 'local');
                $fileHash = $this->fileIntegrityService->calculateFileHash($file);
            } else {
                // Copy current file
                if ($document->file_path && Storage::disk('local')->exists($document->file_path)) {
                    $originalPath = $document->file_path;
                    $newPath = 'documents/versions/' . uniqid() . '_' . basename($originalPath);
                    Storage::disk('local')->copy($originalPath, $newPath);
                    $filePath = $newPath;
                    $fileHash = $document->file_hash;
                }
            }
            
            // Create version record
            $version = DocumentVersion::create([
                'document_id' => $document->id,
                'organization_id' => $document->organization_id,
                'version' => $newVersion,
                'file_path' => $filePath,
                'file_hash' => $fileHash,
                'change_notes' => $changeNotes,
                'change_type' => $changeType,
                'created_by' => auth()->id(),
            ]);
            
            // Update document version
            $document->update([
                'version' => $newVersion,
            ]);
            
            // Audit log
            $this->auditLogService->log(
                'DOCUMENT_VERSION_CREATED',
                'document_versions',
                $version->id,
                null,
                [
                    'document_id' => $document->id,
                    'doc_number' => $document->doc_number,
                    'old_version' => $currentVersion,
                    'new_version' => $newVersion,
                    'change_type' => $changeType,
                    'has_file' => $file !== null,
                ]
            );
            
            DB::commit();
            
            return $version;
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Clean up uploaded file if exists
            if (isset($filePath) && $filePath) {
                Storage::disk('local')->delete($filePath);
            }
            
            throw $e;
        }
    }

    /**
     * Increment version number based on semantic versioning
     * 
     * @param string $currentVersion
     * @param string $changeType 'major'|'minor'|'patch'
     * @return string
     */
    public function incrementVersion(string $currentVersion, string $changeType): string
    {
        // Parse version (e.g., "1.2.3" or "1.0")
        $parts = explode('.', $currentVersion);
        
        // Ensure we have 3 parts (major.minor.patch)
        while (count($parts) < 3) {
            $parts[] = '0';
        }
        
        $major = (int) ($parts[0] ?? 1);
        $minor = (int) ($parts[1] ?? 0);
        $patch = (int) ($parts[2] ?? 0);
        
        switch ($changeType) {
            case 'major':
                $major++;
                $minor = 0;
                $patch = 0;
                break;
            case 'minor':
                $minor++;
                $patch = 0;
                break;
            case 'patch':
            default:
                $patch++;
                break;
        }
        
        return "{$major}.{$minor}.{$patch}";
    }

    /**
     * Get version history for a document
     */
    public function getVersionHistory(Document $document): array
    {
        $versions = DocumentVersion::where('document_id', $document->id)
            ->with('creator')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return $versions->map(function ($version) {
            return [
                'id' => $version->id,
                'version' => $version->version,
                'change_type' => $version->change_type,
                'change_notes' => $version->change_notes,
                'created_by' => $version->creator?->name,
                'created_at' => $version->created_at,
                'file_hash' => $version->file_hash,
            ];
        })->toArray();
    }

    /**
     * Compare two versions
     */
    public function compareVersions(DocumentVersion $version1, DocumentVersion $version2): array
    {
        return [
            'version1' => [
                'version' => $version1->version,
                'created_at' => $version1->created_at,
                'change_notes' => $version1->change_notes,
                'file_hash' => $version1->file_hash,
            ],
            'version2' => [
                'version' => $version2->version,
                'created_at' => $version2->created_at,
                'change_notes' => $version2->change_notes,
                'file_hash' => $version2->file_hash,
            ],
            'file_changed' => $version1->file_hash !== $version2->file_hash,
            'time_difference' => $version1->created_at->diffForHumans($version2->created_at),
        ];
    }

    /**
     * Rollback to a specific version
     */
    public function rollbackToVersion(Document $document, DocumentVersion $targetVersion): Document
    {
        DB::beginTransaction();
        
        try {
            // Copy version file to document
            if ($targetVersion->file_path && Storage::disk('local')->exists($targetVersion->file_path)) {
                $newPath = 'documents/' . uniqid() . '_' . basename($targetVersion->file_path);
                Storage::disk('local')->copy($targetVersion->file_path, $newPath);
                
                // Delete old document file
                if ($document->file_path && Storage::disk('local')->exists($document->file_path)) {
                    Storage::disk('local')->delete($document->file_path);
                }
                
                $document->update([
                    'file_path' => $newPath,
                    'file_hash' => $targetVersion->file_hash,
                    'version' => $targetVersion->version,
                ]);
            }
            
            // Create a new version record for the rollback
            $this->createVersion(
                $document,
                'patch',
                "Rolled back to version {$targetVersion->version}"
            );
            
            // Audit log
            $this->auditLogService->log(
                'DOCUMENT_VERSION_ROLLBACK',
                'documents',
                $document->id,
                null,
                [
                    'doc_number' => $document->doc_number,
                    'target_version' => $targetVersion->version,
                    'current_version' => $document->version,
                ]
            );
            
            DB::commit();
            
            return $document->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Parse version string to components
     */
    public function parseVersion(string $version): array
    {
        $parts = explode('.', $version);
        
        return [
            'major' => (int) ($parts[0] ?? 1),
            'minor' => (int) ($parts[1] ?? 0),
            'patch' => (int) ($parts[2] ?? 0),
            'full' => $version,
        ];
    }

    /**
     * Validate version format
     */
    public function isValidVersion(string $version): bool
    {
        return preg_match('/^\d+\.\d+\.\d+$/', $version) === 1;
    }

    /**
     * Get change type recommendation based on changes
     */
    public function recommendChangeType(array $changes): string
    {
        // Major: Breaking changes, complete rewrites
        $majorKeywords = ['breaking', 'rewrite', 'major', 'complete', 'overhaul'];
        
        // Minor: New features, enhancements
        $minorKeywords = ['feature', 'enhancement', 'add', 'new', 'improve'];
        
        // Patch: Bug fixes, minor updates
        $patchKeywords = ['fix', 'bug', 'patch', 'typo', 'correction'];
        
        $changeText = strtolower(json_encode($changes));
        
        foreach ($majorKeywords as $keyword) {
            if (str_contains($changeText, $keyword)) {
                return 'major';
            }
        }
        
        foreach ($minorKeywords as $keyword) {
            if (str_contains($changeText, $keyword)) {
                return 'minor';
            }
        }
        
        return 'patch';
    }
}
