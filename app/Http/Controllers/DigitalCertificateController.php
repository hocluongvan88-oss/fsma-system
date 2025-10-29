<?php

namespace App\Http\Controllers;

use App\Services\DigitalCertificateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DigitalCertificateController extends Controller
{
    protected DigitalCertificateService $certificateService;

    public function __construct(DigitalCertificateService $certificateService)
    {
        $this->certificateService = $certificateService;
        $this->middleware('auth');
        $this->middleware('package.feature:certificates');
    }

    /**
     * Show certificate management page
     */
    public function index()
    {
        $user = auth()->user();
        $certificates = $user->digitalCertificates()
            ->orderBy('created_at', 'desc')
            ->get();
        
        $activeCertificate = $certificates->firstWhere('is_revoked', false);
        $warning = null;

        if ($activeCertificate) {
            $warning = $this->certificateService->getCertificateExpiryWarning($user);
        }

        return view('settings.certificates', [
            'certificate' => $activeCertificate,
            'certificates' => $certificates,
            'warning' => $warning,
        ]);
    }

    /**
     * Generate new certificate
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'password' => 'required|string',
            'key_size' => 'required|in:2048,4096',
            'valid_days' => 'required|integer|min:30|max:3650',
        ]);

        $user = auth()->user();

        if (!\Hash::check($validated['password'], $user->password)) {
            return back()->withErrors(['password' => 'Invalid password']);
        }

        try {
            $certificate = $this->certificateService->generateCertificate(
                $user,
                (int)$validated['key_size'],
                (int)$validated['valid_days']
            );

            return redirect()->route('certificates.index')
                ->with('success', 'Certificate generated successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to generate certificate: ' . $e->getMessage()]);
        }
    }

    /**
     * Download certificate
     * Serve certificate directly from database instead of file system
     */
    public function download()
    {
        $user = auth()->user();
        $certificate = $user->digitalCertificate;

        if (!$certificate) {
            return back()->withErrors(['error' => 'No certificate found']);
        }

        return response($certificate->certificate_pem, 200)
            ->header('Content-Type', 'application/x-pem-file')
            ->header('Content-Disposition', 'attachment; filename="certificate_' . $certificate->certificate_id . '.pem"')
            ->header('Content-Length', strlen($certificate->certificate_pem));
    }

    /**
     * Download public key
     */
    public function downloadPublicKey($id)
    {
        $user = auth()->user();
        $certificate = $user->digitalCertificates()->findOrFail($id);

        $publicKey = $certificate->public_key;
        
        return response($publicKey, 200)
            ->header('Content-Type', 'application/x-pem-file')
            ->header('Content-Disposition', 'attachment; filename="public_key_' . $certificate->certificate_id . '.pem"');
    }

    /**
     * Revoke certificate
     */
    public function revoke(Request $request)
    {
        $validated = $request->validate([
            'password' => 'required|string',
            'reason' => 'required|string|max:500',
        ]);

        $user = auth()->user();

        if (!\Hash::check($validated['password'], $user->password)) {
            return back()->withErrors(['password' => 'Invalid password']);
        }

        $certificate = $user->digitalCertificate;
        if (!$certificate) {
            return back()->withErrors(['error' => 'No certificate found']);
        }

        $this->certificateService->revokeCertificate($certificate, $validated['reason']);

        return redirect()->route('certificates.index')
            ->with('success', 'Certificate revoked successfully');
    }
}
