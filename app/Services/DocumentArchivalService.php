<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\ESignature;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DocumentArchivalService
{
    protected ArchivalService $archivalService;
    protected int $hotDataMonths;

    public function __construct(ArchivalService $archivalService)
    {
        $this->archivalService = $archivalService;
        $this->hotDataMonths = config('archival.hot_data_months', 36);
    }

    /**
     * Archive documents older than hot data retention period
     * FSMA 204 Compliance: Signed documents are soft-archived (not deleted)
     */
    public function archiveDocuments(bool $dryRun = false): array
    {
        $cutoffDate = now()->subMonths($this->hotDataMonths);
        
        Log::info("Starting document archival", [
            'cutoff_date' => $cutoffDate->toDateTimeString(),
            'dry_run' => $dryRun,
        ]);

        // Get documents to archive (only approved documents)
        $documents = Document::where('created_at', '<', $cutoffDate)
            ->where('status', 'approved')
            ->whereNull('archived_at')
            ->with(['versions', 'signatures'])
            ->get();

        if ($documents->isEmpty()) {
            Log::info("No documents to archive");
            return [
                'status' => 'success',
                'documents_archived' => 0,
                'versions_archived' => 0,
                'signatures_archived' => 0,
                'documents_deleted' => 0,
            ];
        }

        if ($dryRun) {
            Log::info("DRY RUN: Would archive {$documents->count()} documents");
            return [
                'status' => 'dry_run',
                'documents_to_archive' => $documents->count(),
                'versions_to_archive' => $documents->sum(fn($d) => $d->versions->count()),
                'signatures_to_archive' => $documents->sum(fn($d) => $d->signatures->count()),
            ];
        }

        // Execute archival in transaction
        return DB::transaction(function () use ($documents) {
            $documentsArchived = 0;
            $versionsArchived = 0;
            $signaturesArchived = 0;
            $documentsDeleted = 0;

            foreach ($documents as $document) {
                try {
                    // Archive document
                    $this->archiveDocument($document);
                    $documentsArchived++;

                    // Archive versions
                    foreach ($document->versions as $version) {
                        $this->archiveDocumentVersion($version);
                        $versionsArchived++;
                    }

                    // Archive signatures
                    $signatures = ESignature::where('record_type', 'documents')
                        ->where('record_id', $document->id)
                        ->get();
                    
                    foreach ($signatures as $signature) {
                        $this->archiveESignature($signature);
                        $signaturesArchived++;
                    }

                    // Soft archive or hard delete based on signatures
                    if ($signatures->isNotEmpty()) {
                        // FSMA 204 & Part 11: Keep signed documents (soft archive)
                        $document->update(['archived_at' => now()]);
                        Log::info("Document soft-archived (has signatures)", [
                            'document_id' => $document->id,
                            'signatures_count' => $signatures->count(),
                        ]);
                    } else {
                        // Hard delete if no signatures
                        $document->forceDelete();
                        $documentsDeleted++;
                        Log::info("Document hard-deleted (no signatures)", [
                            'document_id' => $document->id,
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error("Failed to archive document", [
                        'document_id' => $document->id,
                        'error' => $e->getMessage(),
                    ]);
                    throw $e;
                }
            }

            Log::info("Document archival completed", [
                'documents_archived' => $documentsArchived,
                'versions_archived' => $versionsArchived,
                'signatures_archived' => $signaturesArchived,
                'documents_deleted' => $documentsDeleted,
            ]);

            return [
                'status' => 'success',
                'documents_archived' => $documentsArchived,
                'versions_archived' => $versionsArchived,
                'signatures_archived' => $signaturesArchived,
                'documents_deleted' => $documentsDeleted,
            ];
        });
    }

    /**
     * Archive a single document to archival_documents table
     */
    protected function archiveDocument(Document $document): void
    {
        $hasSignatures = ESignature::where('record_type', 'documents')
            ->where('record_id', $document->id)
            ->exists();

        DB::table('archival_documents')->insert([
            'original_id' => $document->id,
            'doc_number' => $document->doc_number,
            'title' => $document->title,
            'type' => $document->type,
            'description' => $document->description,
            'file_path' => $document->file_path,
            'file_name' => $document->file_name,
            'file_type' => $document->file_type,
            'file_size' => $document->file_size,
            'version' => $document->version,
            'status' => $document->status,
            'effective_date' => $document->effective_date,
            'review_date' => $document->review_date,
            'expiry_date' => $document->expiry_date,
            'uploaded_by' => $document->uploaded_by,
            'approved_by' => $document->approved_by,
            'approved_at' => $document->approved_at,
            'metadata' => json_encode($document->metadata),
            'organization_id' => $document->organization_id,
            'file_hash' => $document->file_hash,
            'metadata_hash' => $document->metadata_hash,
            'is_encrypted' => $document->is_encrypted,
            'encrypted_at' => $document->encrypted_at,
            'archived_at' => now(),
            'archived_by' => auth()->id(),
            'has_signatures' => $hasSignatures,
            'created_at' => $document->created_at,
            'updated_at' => $document->updated_at,
            'deleted_at' => $document->deleted_at,
        ]);
    }

    /**
     * Archive a document version
     */
    protected function archiveDocumentVersion(DocumentVersion $version): void
    {
        DB::table('archival_document_versions')->insert([
            'original_id' => $version->id,
            'document_id' => $version->document_id,
            'version' => $version->version,
            'file_path' => $version->file_path,
            'file_name' => $version->file_name,
            'file_size' => $version->file_size,
            'change_notes' => $version->change_notes,
            'created_by' => $version->created_by,
            'archived_at' => now(),
            'archived_by' => auth()->id(),
            'created_at' => $version->created_at,
            'updated_at' => $version->updated_at,
        ]);
    }

    /**
     * Archive an e-signature (FSMA 204 & Part 11 compliance)
     */
    protected function archiveESignature(ESignature $signature): void
    {
        DB::table('archival_e_signatures')->insert([
            'original_id' => $signature->id,
            'record_type' => $signature->record_type,
            'record_id' => $signature->record_id,
            'user_id' => $signature->user_id,
            'action' => $signature->action,
            'meaning_of_signature' => $signature->meaning_of_signature,
            'reason' => $signature->reason,
            'signature_hash' => $signature->signature_hash,
            'timestamp_token' => $signature->timestamp_token,
            'certificate_id' => $signature->certificate_id,
            'verification_report' => json_encode($signature->verification_report),
            'signed_at' => $signature->signed_at,
            'expires_at' => $signature->expires_at,
            'is_revoked' => $signature->is_revoked,
            'revoked_at' => $signature->revoked_at,
            'revoked_by' => $signature->revoked_by,
            'revocation_reason' => $signature->revocation_reason,
            'archived_at' => now(),
            'archived_by' => auth()->id(),
            'created_at' => $signature->created_at,
            'updated_at' => $signature->updated_at,
        ]);
    }

    /**
     * Retrieve archived document with all related data
     */
    public function retrieveArchivedDocument(int $documentId): ?array
    {
        $document = DB::table('archival_documents')
            ->where('original_id', $documentId)
            ->first();

        if (!$document) {
            return null;
        }

        $versions = DB::table('archival_document_versions')
            ->where('document_id', $documentId)
            ->get();

        $signatures = DB::table('archival_e_signatures')
            ->where('record_type', 'documents')
            ->where('record_id', $documentId)
            ->get();

        return [
            'document' => $document,
            'versions' => $versions,
            'signatures' => $signatures,
        ];
    }

    /**
     * Get archival statistics for documents
     */
    public function getStatistics(): array
    {
        return [
            'total_archived_documents' => DB::table('archival_documents')->count(),
            'total_archived_versions' => DB::table('archival_document_versions')->count(),
            'total_archived_signatures' => DB::table('archival_e_signatures')
                ->where('record_type', 'documents')
                ->count(),
            'documents_with_signatures' => DB::table('archival_documents')
                ->where('has_signatures', true)
                ->count(),
            'documents_without_signatures' => DB::table('archival_documents')
                ->where('has_signatures', false)
                ->count(),
            'by_type' => DB::table('archival_documents')
                ->select('type')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('type')
                ->get(),
            'by_organization' => DB::table('archival_documents')
                ->select('organization_id')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('organization_id')
                ->get(),
        ];
    }
}
