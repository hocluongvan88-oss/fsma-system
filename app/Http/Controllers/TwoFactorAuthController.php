<?php

namespace App\Http\Controllers;

use App\Services\TwoFactorAuthService;
use Illuminate\Http\Request;

class TwoFactorAuthController extends Controller
{
    protected TwoFactorAuthService $twoFAService;

    public function __construct(TwoFactorAuthService $twoFAService)
    {
        $this->twoFAService = $twoFAService;
    }

    /**
     * Show 2FA setup page
     */
    public function showSetup()
    {
        $user = auth()->user();

        if ($user->two_fa_enabled) {
            return redirect()->route('settings.security')->with('info', '2FA is already enabled');
        }

        $secret = $this->twoFAService->generateSecret($user);
        $qrCodeUrl = $this->twoFAService->getQRCodeUrl($user, $secret);

        return view('auth.two-fa-setup', [
            'secret' => $secret,
            'qrCodeUrl' => $qrCodeUrl,
        ]);
    }

    /**
     * Enable 2FA
     */
    public function enable(Request $request)
    {
        $validated = $request->validate([
            'secret' => 'required|string',
            'code' => 'required|string|size:6',
        ]);

        $user = auth()->user();

        if (!$this->twoFAService->enableTwoFA($user, $validated['secret'], $validated['code'])) {
            return back()->withErrors(['code' => 'Invalid verification code']);
        }

        $backupCodes = json_decode($user->backup_codes, true);

        return view('auth.two-fa-backup-codes', [
            'backupCodes' => $backupCodes,
        ])->with('success', '2FA enabled successfully. Save your backup codes in a safe place.');
    }

    /**
     * Disable 2FA
     */
    public function disable(Request $request)
    {
        $validated = $request->validate([
            'password' => 'required|string',
        ]);

        $user = auth()->user();

        if (!\Hash::check($validated['password'], $user->password)) {
            return back()->withErrors(['password' => 'Invalid password']);
        }

        $this->twoFAService->disableTwoFA($user);

        return redirect()->route('settings.security')->with('success', '2FA disabled successfully');
    }

    /**
     * Verify 2FA code during login
     */
    public function verify(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string',
            'method' => 'required|in:totp,backup_code',
        ]);

        $user = auth()->user();

        if (!$user->two_fa_enabled) {
            return back()->withErrors(['code' => '2FA is not enabled']);
        }

        $verified = false;
        if ($validated['method'] === 'totp') {
            $verified = $this->twoFAService->verifyUserCode($user, $validated['code']);
        } elseif ($validated['method'] === 'backup_code') {
            $verified = $this->twoFAService->verifyBackupCode($user, $validated['code']);
        }

        if (!$verified) {
            $this->twoFAService->logAttempt($user, $validated['method'], false, 'Invalid code');
            return back()->withErrors(['code' => 'Invalid code']);
        }

        $this->twoFAService->logAttempt($user, $validated['method'], true);
        session(['2fa_verified' => true]);

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Show 2FA verification page
     */
    public function showVerify()
    {
        if (session('2fa_verified')) {
            return redirect()->route('dashboard');
        }

        return view('auth.two-fa-verify');
    }
}
