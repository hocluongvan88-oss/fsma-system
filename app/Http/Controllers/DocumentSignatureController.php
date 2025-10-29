<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\ESignature;
use App\Services\DocumentSignatureService;
use Illuminate\Http\Request;

class DocumentSignatureController extends Controller
{
    protected DocumentSignatureService $signatureService;

    public function __construct(DocumentSignatureService $signatureService)
    {
        $this->signatureService = $signatureService;
    }

    /**
     * Show signature form for document
     */
    public function create(Document $document)
    {
        $this->authorize('view', $document);

        if ($document->status !== 'approved') {
            return back()->with('error', 'Only approved documents can be signed');
        }

        return view('documents.signatures.create', compact('document'));
    }

    /**
     * Sign document
     */
    public function store(Request $request, Document $document)
    {
        $this->authorize('view', $document);

        $validated = $request->validate([
            'password' => 'required|string',
            'meaning_of_signature' => 'required|string|in:approval,acknowledgment,consent,authorization',
            'reason' => 'nullable|string|max:500',
            'twofa_code' => 'nullable|string',
            'twofa_method' => 'nullable|string|in:totp,backup_code',
        ]);

        try {
            $signature = $this->signatureService->signDocument(
                document: $document,
                user: auth()->user(),
                password: $validated['password'],
                meaningOfSignature: $validated['meaning_of_signature'],
                reason: $validated['reason'],
                twoFACode: $validated['twofa_code'],
                twoFAMethod: $validated['twofa_method']
            );

            return redirect()->route('documents.show', $document)
                ->with('success', 'Document signed successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Signature failed: ' . $e->getMessage());
        }
    }

    /**
     * Get document signatures
     */
    public function index(Document $document)
    {
        $this->authorize('view', $document);

        $signatureStatus = $this->signatureService->getDocumentSignatureStatus($document);

        return view('documents.signatures.index', compact('document', 'signatureStatus'));
    }

    /**
     * Show signature details
     */
    public function show(Document $document, ESignature $signature)
    {
        $this->authorize('view', $document);

        if ($signature->record_type !== 'documents' || $signature->record_id !== $document->id) {
            abort(404);
        }

        $verificationReport = $this->signatureService->getSignatureVerificationReport($document, $signature);

        return view('documents.signatures.show', compact('document', 'signature', 'verificationReport'));
    }

    /**
     * Revoke signature
     */
    public function revoke(Request $request, Document $document, ESignature $signature)
    {
        $this->authorize('view', $document);

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $this->signatureService->revokeDocumentSignature(
                document: $document,
                signature: $signature,
                reason: $validated['reason'],
                revokedByUser: auth()->user()
            );

            return back()->with('success', 'Signature revoked successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Revocation failed: ' . $e->getMessage());
        }
    }

    /**
     * Verify signature
     */
    public function verify(Request $request, Document $document, ESignature $signature)
    {
        $this->authorize('view', $document);

        $validated = $request->validate([
            'password' => 'required|string',
        ]);

        try {
            $report = $this->signatureService->verifyDocumentSignature(
                document: $document,
                signature: $signature,
                user: auth()->user(),
                password: $validated['password']
            );

            return response()->json([
                'success' => true,
                'report' => $report,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
