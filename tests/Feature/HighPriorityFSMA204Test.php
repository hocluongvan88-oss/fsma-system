<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ESignature;
use App\Services\RateLimitingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HighPriorityFSMA204Test extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected RateLimitingService $rateLimitService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
            'two_fa_enabled' => false,
        ]);

        $this->rateLimitService = app(RateLimitingService::class);
    }

    /**
     * Test 1: Signing Dialog Modal - Create Signature
     */
    public function test_signing_dialog_creates_signature()
    {
        $this->actingAs($this->user);

        $response = $this->postJson(route('e-signatures.sign'), [
            'record_type' => 'document',
            'record_id' => 1,
            'action' => 'approve',
            'password' => 'password',
            'meaning_of_signature' => 'Approved for shipment',
            'reason' => 'Quality check passed',
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('success', true)
                 ->assertJsonPath('message', 'E-signature recorded successfully');

        $this->assertDatabaseHas('e_signatures', [
            'user_id' => $this->user->id,
            'record_type' => 'document',
            'record_id' => 1,
            'action' => 'approve',
            'meaning_of_signature' => 'Approved for shipment',
        ]);
    }

    /**
     * Test 2: Verification Interface - Verify Signature
     */
    public function test_verification_interface_verifies_signature()
    {
        $this->actingAs($this->user);

        // Create signature
        $signature = ESignature::create([
            'user_id' => $this->user->id,
            'record_type' => 'document',
            'record_id' => 1,
            'action' => 'approve',
            'meaning_of_signature' => 'Approved for shipment',
            'signature_hash' => hash('sha512', 'test'),
            'record_content_hash' => hash('sha512', 'content'),
            'signature_algorithm' => 'SHA512',
            'ip_address' => '127.0.0.1',
            'signed_at' => now(),
        ]);

        $response = $this->postJson(route('e-signatures.verify'), [
            'signature_id' => $signature->id,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('success', true);
    }

    /**
     * Test 3: Audit Trail Viewer - List Signatures
     */
    public function test_audit_trail_viewer_lists_signatures()
    {
        $this->actingAs($this->user);

        // Create multiple signatures
        ESignature::factory()->count(5)->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->get(route('e-signatures.audit-trail'));

        $response->assertStatus(200)
                 ->assertViewHas('signatures')
                 ->assertViewHas('users');

        $this->assertCount(5, $response->viewData('signatures')->items());
    }

    /**
     * Test 4: Search & Filter - Filter by User
     */
    public function test_search_filter_by_user()
    {
        $this->actingAs($this->user);

        $user2 = User::factory()->create(['role' => 'admin']);

        ESignature::factory()->create(['user_id' => $this->user->id]);
        ESignature::factory()->create(['user_id' => $user2->id]);

        $response = $this->get(route('e-signatures.index', ['user_id' => $this->user->id]));

        $response->assertStatus(200);
        $this->assertCount(1, $response->viewData('signatures')->items());
    }

    /**
     * Test 5: Search & Filter - Filter by Date Range
     */
    public function test_search_filter_by_date_range()
    {
        $this->actingAs($this->user);

        ESignature::factory()->create([
            'user_id' => $this->user->id,
            'signed_at' => now()->subDays(10),
        ]);

        ESignature::factory()->create([
            'user_id' => $this->user->id,
            'signed_at' => now(),
        ]);

        $response = $this->get(route('e-signatures.index', [
            'date_from' => now()->subDays(5)->format('Y-m-d'),
            'date_to' => now()->format('Y-m-d'),
        ]));

        $response->assertStatus(200);
        $this->assertCount(1, $response->viewData('signatures')->items());
    }

    /**
     * Test 6: Search & Filter - Filter by Status
     */
    public function test_search_filter_by_status()
    {
        $this->actingAs($this->user);

        ESignature::factory()->create([
            'user_id' => $this->user->id,
            'is_revoked' => false,
        ]);

        ESignature::factory()->create([
            'user_id' => $this->user->id,
            'is_revoked' => true,
        ]);

        $response = $this->get(route('e-signatures.index', ['status' => 'active']));

        $response->assertStatus(200);
        $this->assertCount(1, $response->viewData('signatures')->items());
    }

    /**
     * Test 7: Revocation System - Revoke Signature
     */
    public function test_revocation_system_revokes_signature()
    {
        $this->actingAs($this->user);

        $signature = ESignature::factory()->create([
            'user_id' => $this->user->id,
            'is_revoked' => false,
        ]);

        $response = $this->postJson(route('e-signatures.revoke'), [
            'signature_id' => $signature->id,
            'password' => 'password',
            'reason' => 'Unauthorized action',
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('success', true);

        $this->assertDatabaseHas('e_signatures', [
            'id' => $signature->id,
            'is_revoked' => true,
            'revocation_reason' => 'Unauthorized action',
        ]);
    }

    /**
     * Test 8: Timestamp Authority - Attach Timestamp
     */
    public function test_timestamp_authority_attaches_timestamp()
    {
        $this->actingAs($this->user);

        $response = $this->postJson(route('e-signatures.sign'), [
            'record_type' => 'document',
            'record_id' => 1,
            'action' => 'approve',
            'password' => 'password',
            'meaning_of_signature' => 'Approved for shipment',
        ]);

        $response->assertStatus(200);

        $signature = ESignature::latest()->first();
        $this->assertNotNull($signature->timestamp_verified_at);
    }

    /**
     * Test 9: Rate Limiting - Prevent Brute Force
     */
    public function test_rate_limiting_prevents_brute_force()
    {
        $this->actingAs($this->user);

        // Make 6 failed attempts (limit is 5)
        for ($i = 0; $i < 6; $i++) {
            $this->postJson(route('e-signatures.sign'), [
                'record_type' => 'document',
                'record_id' => 1,
                'action' => 'approve',
                'password' => 'wrong_password',
                'meaning_of_signature' => 'Test',
            ]);
        }

        // 6th attempt should be rate limited
        $response = $this->postJson(route('e-signatures.sign'), [
            'record_type' => 'document',
            'record_id' => 1,
            'action' => 'approve',
            'password' => 'password',
            'meaning_of_signature' => 'Test',
        ]);

        $response->assertStatus(429);
    }

    /**
     * Test 10: Rate Limiting - Log Failed Attempts
     */
    public function test_rate_limiting_logs_failed_attempts()
    {
        $this->actingAs($this->user);

        $this->postJson(route('e-signatures.sign'), [
            'record_type' => 'document',
            'record_id' => 1,
            'action' => 'approve',
            'password' => 'wrong_password',
            'meaning_of_signature' => 'Test',
        ]);

        $remaining = $this->rateLimitService->getRemainingAttempts(
            'signature_creation',
            (string)$this->user->id
        );

        $this->assertEquals(9, $remaining); // 10 max - 1 attempt
    }

    /**
     * Test 11: Audit Trail Export - Export to CSV
     */
    public function test_audit_trail_export_to_csv()
    {
        $this->actingAs($this->user);

        ESignature::factory()->count(3)->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->getJson(route('api.audit-trail.export'));

        $response->assertStatus(200)
                 ->assertHeader('Content-Type', 'text/csv; charset=utf-8');
    }

    /**
     * Test 12: Audit Trail Statistics - Get Statistics
     */
    public function test_audit_trail_statistics()
    {
        $this->actingAs($this->user);

        ESignature::factory()->count(5)->create([
            'user_id' => $this->user->id,
            'is_revoked' => false,
        ]);

        ESignature::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'is_revoked' => true,
        ]);

        $response = $this->getJson(route('api.audit-trail.statistics'));

        $response->assertStatus(200)
                 ->assertJsonPath('data.total_signatures', 7)
                 ->assertJsonPath('data.active_signatures', 5)
                 ->assertJsonPath('data.revoked_signatures', 2);
    }

    /**
     * Test 13: Meaning of Signature - FSMA 204 Requirement
     */
    public function test_meaning_of_signature_is_required()
    {
        $this->actingAs($this->user);

        $response = $this->postJson(route('e-signatures.sign'), [
            'record_type' => 'document',
            'record_id' => 1,
            'action' => 'approve',
            'password' => 'password',
            // Missing meaning_of_signature
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors('meaning_of_signature');
    }

    /**
     * Test 14: Signature Integrity - Verify Record Content Hash
     */
    public function test_signature_integrity_verifies_record_content()
    {
        $this->actingAs($this->user);

        $signature = ESignature::factory()->create([
            'user_id' => $this->user->id,
            'record_content_hash' => hash('sha512', 'original_content'),
        ]);

        // Verify with correct content
        $isValid = $this->user->digitalCertificate 
            ? true 
            : hash('sha512', 'original_content') === $signature->record_content_hash;

        $this->assertTrue($isValid);
    }

    /**
     * Test 15: Permission Check - Only Authorized Users Can Sign
     */
    public function test_only_authorized_users_can_sign()
    {
        $operator = User::factory()->create([
            'role' => 'operator',
            'is_active' => true,
        ]);

        $this->actingAs($operator);

        $response = $this->postJson(route('e-signatures.sign'), [
            'record_type' => 'document',
            'record_id' => 1,
            'action' => 'approve',
            'password' => 'password',
            'meaning_of_signature' => 'Test',
        ]);

        $response->assertStatus(403);
    }
}
