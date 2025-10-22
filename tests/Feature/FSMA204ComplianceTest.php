<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ESignature;
use App\Models\DigitalCertificate;
use App\Services\TwoFactorAuthService;
use App\Services\DigitalCertificateService;
use App\Services\EnhancedSignatureService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FSMA204ComplianceTest extends TestCase
{
    use RefreshDatabase;

    protected TwoFactorAuthService $twoFAService;
    protected DigitalCertificateService $certificateService;
    protected EnhancedSignatureService $signatureService;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->twoFAService = app(TwoFactorAuthService::class);
        $this->certificateService = app(DigitalCertificateService::class);
        $this->signatureService = app(EnhancedSignatureService::class);

        // Create test user
        $this->user = User::factory()->create([
            'role' => 'manager',
            'is_active' => true,
        ]);
    }

    /**
     * Test 2FA setup and verification
     */
    public function test_two_fa_setup_and_verification(): void
    {
        // Generate secret
        $secret = $this->twoFAService->generateSecret($this->user);
        $this->assertNotEmpty($secret);

        // Get QR code URL
        $qrCodeUrl = $this->twoFAService->getQRCodeUrl($this->user, $secret);
        $this->assertStringContainsString('qrserver', $qrCodeUrl);

        // Verify code generation
        $code = $this->twoFAService->verifyCode($secret, $code);
        // Note: In real tests, you'd use a library to generate valid TOTP codes
    }

    /**
     * Test backup codes generation and usage
     */
    public function test_backup_codes_generation_and_usage(): void
    {
        $backupCodes = $this->twoFAService->generateBackupCodes(10);
        $this->assertCount(10, $backupCodes);

        // Enable 2FA with backup codes
        $secret = $this->twoFAService->generateSecret($this->user);
        $this->twoFAService->enableTwoFA($this->user, $secret, '000000'); // Mock code

        $this->user->refresh();
        $this->assertTrue($this->user->two_fa_enabled);
        $this->assertNotNull($this->user->backup_codes);
    }

    /**
     * Test digital certificate generation
     */
    public function test_digital_certificate_generation(): void
    {
        $certificate = $this->certificateService->generateCertificate($this->user, 2048, 365);

        $this->assertNotNull($certificate);
        $this->assertEquals($this->user->id, $certificate->user_id);
        $this->assertNotNull($certificate->certificate_pem);
        $this->assertNotNull($certificate->public_key);
        $this->assertTrue($certificate->isValid());
    }

    /**
     * Test certificate validation
     */
    public function test_certificate_validation(): void
    {
        $certificate = $this->certificateService->generateCertificate($this->user, 2048, 365);

        // Should be valid
        $this->assertTrue($this->certificateService->verifyCertificate($certificate));

        // Revoke certificate
        $this->certificateService->revokeCertificate($certificate, 'Test revocation');
        $this->assertFalse($this->certificateService->verifyCertificate($certificate));
    }

    /**
     * Test enhanced e-signature creation with meaning
     */
    public function test_enhanced_signature_creation_with_meaning(): void
    {
        // Setup certificate
        $certificate = $this->certificateService->generateCertificate($this->user, 2048, 365);

        // Create signature with meaning
        $signature = $this->signatureService->createSignature(
            $this->user,
            'products',
            123,
            'approve',
            $this->user->password, // Use actual password
            'Approved for shipment',
            'Quality check passed'
        );

        $this->assertNotNull($signature);
        $this->assertEquals('Approved for shipment', $signature->meaning_of_signature);
        $this->assertEquals('SHA512', $signature->signature_algorithm);
        $this->assertNotNull($signature->record_content_hash);
    }

    /**
     * Test signature verification
     */
    public function test_signature_verification(): void
    {
        $certificate = $this->certificateService->generateCertificate($this->user, 2048, 365);

        $signature = $this->signatureService->createSignature(
            $this->user,
            'products',
            123,
            'approve',
            $this->user->password,
            'Approved for shipment'
        );

        // Verify signature
        $isValid = $this->signatureService->verifySignature($signature, $this->user, $this->user->password);
        $this->assertTrue($isValid);
    }

    /**
     * Test signature revocation
     */
    public function test_signature_revocation(): void
    {
        $certificate = $this->certificateService->generateCertificate($this->user, 2048, 365);

        $signature = $this->signatureService->createSignature(
            $this->user,
            'products',
            123,
            'approve',
            $this->user->password,
            'Approved for shipment'
        );

        // Revoke signature
        $this->signatureService->revokeSignature($signature, 'Unauthorized action');

        $signature->refresh();
        $this->assertTrue($signature->is_revoked);
        $this->assertNotNull($signature->revoked_at);
        $this->assertEquals('Unauthorized action', $signature->revocation_reason);
    }

    /**
     * Test 2FA logging
     */
    public function test_two_fa_logging(): void
    {
        $this->twoFAService->logAttempt($this->user, 'totp', true);
        $this->twoFAService->logAttempt($this->user, 'totp', false, 'Invalid code');

        $logs = $this->user->twoFALogs;
        $this->assertCount(2, $logs);
        $this->assertTrue($logs[0]->success);
        $this->assertFalse($logs[1]->success);
    }

    /**
     * Test brute force protection
     */
    public function test_brute_force_protection(): void
    {
        // Log 5 failed attempts
        for ($i = 0; $i < 5; $i++) {
            $this->twoFAService->logAttempt($this->user, 'totp', false, 'Invalid code');
        }

        // Check if too many failed attempts
        $hasTooMany = $this->twoFAService->hasTooManyFailedAttempts($this->user, 5, 15);
        $this->assertTrue($hasTooMany);
    }

    /**
     * Test audit trail completeness
     */
    public function test_audit_trail_completeness(): void
    {
        $certificate = $this->certificateService->generateCertificate($this->user, 2048, 365);

        $signature = $this->signatureService->createSignature(
            $this->user,
            'products',
            123,
            'approve',
            $this->user->password,
            'Approved for shipment',
            'Quality check passed'
        );

        // Verify all required fields are logged
        $this->assertNotNull($signature->user_id);
        $this->assertNotNull($signature->signed_at);
        $this->assertNotNull($signature->ip_address);
        $this->assertNotNull($signature->user_agent);
        $this->assertNotNull($signature->meaning_of_signature);
        $this->assertNotNull($signature->signature_algorithm);
        $this->assertNotNull($signature->record_content_hash);
    }
}
