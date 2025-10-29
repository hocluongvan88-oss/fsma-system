<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\CTEQuotaSyncService;
use App\Services\FileIntegrityService;
use App\Services\DocumentMetadataValidator;
use App\Services\FileEncryptionService;
use App\Services\DocumentVersioningService;

class DocumentController extends Controller
{
    protected $quotaSyncService;

    public function __construct(CTEQuotaSyncService $quotaSyncService)
    {
        $this->quotaSyncService = $quotaSyncService;
    }

    public function index(Request $request)
    {
        $currentUser = auth()->user();
        $query = Document::with(['uploader', 'approver'])->active();

        if (!$currentUser->isAdmin()) {
            $query->where('organization_id', $currentUser->organization_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('doc_number', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $documents = $query->orderBy('created_at', 'desc')->paginate(20);
        $expiringDocs = Document::expiringSoon(30)->count();

        return view('documents.index', compact('documents', 'expiringDocs'));
    }

    public function create()
    {
        return view('documents.create');
    }

    public function store(Request $request)
    {
        $currentUser = auth()->user();
        $organization = $currentUser->organization;

        if (!$organization->hasFeature('document_management')) {
            return back()->withInput()
                ->with('error', 'Document management is not available in your current package. Please upgrade to enable this feature.');
        }

        if (!$currentUser->isAdmin()) {
            try {
                $this->quotaSyncService->validateDocumentCreation($organization);
            } catch (\Exception $e) {
                Log::warning("Document upload quota validation failed for organization {$organization->id}", [
                    'error' => $e->getMessage(),
                    'user_id' => $currentUser->id,
                ]);
                return back()->withInput()
                    ->with('error', $e->getMessage());
            }
        }

        $validated = $request->validate([
            'doc_number' => 'required|string|unique:documents,doc_number',
            'title' => 'required|string|max:255',
            'type' => 'required|in:traceability_plan,sop,fda_correspondence,training_material,audit_report,other',
            'description' => 'nullable|string',
            'file' => 'required|file|max:10240',
            'effective_date' => 'nullable|date',
            'review_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'metadata' => 'nullable|array',
        ]);

        $fileIntegrityService = app(FileIntegrityService::class);
        $fileValidation = $fileIntegrityService->validateFileBeforeUpload($request->file('file'));

        if (!$fileValidation['valid']) {
            return back()->withInput()
                ->with('error', 'File validation failed: ' . implode(', ', $fileValidation['errors']));
        }

        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();
        $filePath = $file->store('documents', 'local');

        $metadataValidator = app(DocumentMetadataValidator::class);
        $metadata = $validated['metadata'] ?? [];
        $metadata = $metadataValidator->enrichMetadata($metadata, $validated['type']);

        $metadataValidation = $metadataValidator->validateMetadata($metadata);
        if (!$metadataValidation['valid']) {
            Storage::delete($filePath);
            return back()->withInput()
                ->with('error', 'Metadata validation failed: ' . json_encode($metadataValidation['errors']));
        }

        DB::beginTransaction();
        try {
            $document = Document::create([
                'doc_number' => $validated['doc_number'],
                'title' => $validated['title'],
                'type' => $validated['type'],
                'description' => $validated['description'] ?? null,
                'file_path' => $filePath,
                'file_name' => $fileName,
                'file_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'effective_date' => $validated['effective_date'] ?? null,
                'review_date' => $validated['review_date'] ?? null,
                'expiry_date' => $validated['expiry_date'] ?? null,
                'uploaded_by' => $currentUser->id,
                'organization_id' => $organization->id,
                'metadata' => $metadataValidation['validated_data'],
                'version' => '1.0.0',
            ]);

            $fileIntegrityService->storeFileHash($document);
            $metadataValidator->storeMetadata($document, $metadataValidation['validated_data']);

            $this->quotaSyncService->incrementDocumentUsage($organization);

            Log::info("Document uploaded successfully", [
                'document_id' => $document->id,
                'organization_id' => $organization->id,
                'user_id' => $currentUser->id,
                'file_size' => $file->getSize(),
            ]);

            DB::commit();
            return redirect()->route('documents.index')
                ->with('success', 'Document uploaded successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Storage::delete($filePath);
            Log::error("Failed to upload document", [
                'organization_id' => $organization->id,
                'user_id' => $currentUser->id,
                'error' => $e->getMessage(),
            ]);
            return back()->withInput()
                ->with('error', 'Failed to upload document: ' . $e->getMessage());
        }
    }

    public function show(Document $document)
    {
        $this->authorize('view', $document);

        $document->load(['uploader', 'approver', 'versions.creator']);

        $expiryService = app('App\Services\DocumentExpiryNotificationService');
        $expiryStatus = $expiryService->getExpiryStatus($document);

        return view('documents.show', compact('document', 'expiryStatus'));
    }

    public function edit(Document $document)
    {
        $this->authorize('update', $document);

        return view('documents.edit', compact('document'));
    }

    public function update(Request $request, Document $document)
    {
        $this->authorize('update', $document);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:traceability_plan,sop,fda_correspondence,training_material,audit_report,other',
            'description' => 'nullable|string',
            'effective_date' => 'nullable|date',
            'review_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'status' => 'required|in:draft,review,approved,archived',
        ]);

        $document->update($validated);

        return redirect()->route('documents.show', $document)
            ->with('success', 'Document updated successfully.');
    }

    public function destroy(Document $document)
    {
        $this->authorize('delete', $document);

        $organization = $document->organization;

        if ($document->forceDelete()) {
            if ($organization) {
                $this->quotaSyncService->decrementDocumentUsage($organization);
                Log::info("Document permanently deleted and quota decremented", [
                    'document_id' => $document->id,
                    'organization_id' => $organization->id,
                ]);
            }
            return redirect()->route('documents.index')
                ->with('success', 'Document permanently deleted.');
        }

        return redirect()->route('documents.index')
            ->with('error', 'Failed to delete document.');
    }

    public function download(Document $document)
    {
        $this->authorize('download', $document);

        if (!Storage::exists($document->file_path)) {
            abort(404, 'File not found.');
        }

        $fileIntegrityService = app(FileIntegrityService::class);
        $integrityCheck = $fileIntegrityService->verifyFileIntegrity($document);

        if (!$integrityCheck['valid']) {
            abort(403, 'File integrity check failed: ' . $integrityCheck['reason']);
        }

        $fileEncryptionService = app(FileEncryptionService::class);

        if ($document->is_encrypted) {
            try {
                $fileContent = $fileEncryptionService->getDecryptedFileContent($document);
                return response($fileContent, 200, [
                    'Content-Type' => $document->file_type,
                    'Content-Disposition' => 'attachment; filename="' . $document->file_name . '"',
                ]);
            } catch (\Exception $e) {
                abort(403, 'Failed to decrypt file: ' . $e->getMessage());
            }
        }

        return Storage::download($document->file_path, $document->file_name);
    }

    public function approve(Document $document)
    {
        $this->authorize('approve', $document);

        if (!$document->canBeApproved()) {
            return back()->with('error', 'Document must be in review status to be approved.');
        }

        $document->approve(auth()->user());

        return back()->with('success', 'Document approved successfully.');
    }

    public function newVersion(Request $request, Document $document)
    {
        $this->authorize('update', $document);

        $validated = $request->validate([
            'file' => 'required|file|max:10240',
            'change_notes' => 'required|string',
            'change_type' => 'required|in:major,minor,patch',
        ]);

        $versioningService = app(DocumentVersioningService::class);

        try {
            $newVersion = $versioningService->createVersion(
                $document,
                $validated['change_type'],
                $validated['change_notes'],
                $request->file('file')
            );

            $file = $request->file('file');
            $document->update([
                'file_name' => $file->getClientOriginalName(),
                'file_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
            ]);

            return back()->with('success', "New version {$newVersion->version} created successfully.");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create new version: ' . $e->getMessage());
        }
    }

    public function bulkApprove(Request $request)
    {
        $validated = $request->validate([
            'document_ids' => 'required|array',
            'document_ids.*' => 'exists:documents,id',
        ]);

        $documents = Document::whereIn('id', $validated['document_ids'])->get();
        $approved = 0;
        $failed = 0;

        foreach ($documents as $document) {
            if ($document->canBeApproved()) {
                $document->approve(auth()->user());
                $approved++;
            } else {
                $failed++;
            }
        }

        return back()->with('success', __('messages.bulk_approve_success', ['approved' => $approved, 'failed' => $failed]));
    }

    public function bulkArchive(Request $request)
    {
        $validated = $request->validate([
            'document_ids' => 'required|array',
            'document_ids.*' => 'exists:documents,id',
        ]);

        $documents = Document::whereIn('id', $validated['document_ids'])->get();
        $archived = 0;

        DB::beginTransaction();
        try {
            foreach ($documents as $document) {
                if (Gate::allows('delete', $document)) {
                    $organization = $document->organization;

                    if ($document->forceDelete()) {
                        if ($organization) {
                            $this->quotaSyncService->decrementDocumentUsage($organization);
                        }
                        $archived++;
                    }
                }
            }

            Log::info("Bulk archive completed", [
                'archived_count' => $archived,
                'user_id' => auth()->id(),
            ]);

            DB::commit();
            return back()->with('success', __('messages.bulk_archive_success', ['count' => $archived]));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Bulk archive failed", [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            return back()->with('error', 'Bulk archive failed: ' . $e->getMessage());
        }
    }

    public function bulkExport(Request $request)
    {
        $validated = $request->validate([
            'document_ids' => 'required|array',
            'document_ids.*' => 'exists:documents,id',
        ]);

        $documents = Document::with(['uploader', 'approver'])
            ->whereIn('id', $validated['document_ids'])
            ->get();

        $csvData = [];
        $csvData[] = [
            'Doc Number',
            'Title',
            'Type',
            'Status',
            'Version',
            'Effective Date',
            'Expiry Date',
            'Uploaded By',
            'Approved By',
            'File Size',
        ];

        foreach ($documents as $document) {
            $csvData[] = [
                $document->doc_number,
                $document->title,
                $document->type,
                $document->status,
                $document->version,
                $document->effective_date,
                $document->expiry_date,
                $document->uploader->name ?? '',
                $document->approver->name ?? '',
                $document->file_size_human,
            ];
        }

        $filename = 'documents_export_' . date('Y-m-d_His') . '.csv';
        $handle = fopen('php://temp', 'r+');

        foreach ($csvData as $row) {
            fputcsv($handle, $row);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
