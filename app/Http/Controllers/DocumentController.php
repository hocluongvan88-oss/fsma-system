<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = auth()->user();
        $query = Document::with(['uploader', 'approver'])
            ->active();

        if (!$currentUser->isAdmin()) {
            $query->where('organization_id', $currentUser->organization_id);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('doc_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $documents = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get expiring documents
        $expiringDocs = Document::expiringSoon(30)->count();

        return view('documents.index', compact('documents', 'expiringDocs'));
    }

    public function create()
    {
        return view('documents.create');
    }

    public function store(Request $request)
    {
        if (!auth()->user()->canUploadDocument()) {
            return back()->withInput()
                ->with('error', 'You have reached your document limit (' . auth()->user()->max_documents . ' documents). Please upgrade to Pro or Enterprise package for unlimited documents.');
        }

        $validated = $request->validate([
            'doc_number' => 'required|string|unique:documents,doc_number',
            'title' => 'required|string|max:255',
            'type' => 'required|in:traceability_plan,sop,fda_correspondence,training_material,audit_report,other',
            'description' => 'nullable|string',
            'file' => 'required|file|max:10240', // 10MB max
            'effective_date' => 'nullable|date',
            'review_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
        ]);

        // Handle file upload
        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();
        $filePath = $file->store('documents', 'local');

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
            'uploaded_by' => auth()->id(),
            'organization_id' => auth()->user()->organization_id,
        ]);

        return redirect()->route('documents.index')
            ->with('success', 'Document uploaded successfully.');
    }

    public function show(Document $document)
    {
        $this->authorize('view', $document);
        
        $document->load(['uploader', 'approver', 'versions.creator']);
        return view('documents.show', compact('document'));
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
        
        // Soft delete
        $document->delete();

        return redirect()->route('documents.index')
            ->with('success', 'Document archived successfully.');
    }

    public function download(Document $document)
    {
        $this->authorize('download', $document);
        
        if (!Storage::exists($document->file_path)) {
            abort(404, 'File not found.');
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
        ]);

        $file = $request->file('file');
        $filePath = $file->store('documents', 'local');

        $newVersion = $document->createVersion($filePath, $validated['change_notes'], auth()->user());

        // Update file metadata
        $document->update([
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
        ]);

        return back()->with('success', "New version {$newVersion} created successfully.");
    }
}
