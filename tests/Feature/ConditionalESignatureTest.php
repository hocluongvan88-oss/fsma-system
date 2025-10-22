<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Package;
use App\Models\TraceRecord;
use App\Models\ESignature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

class ConditionalESignatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $freeUser;
    protected User $basicUser;
    protected User $premiumUser;
    protected User $enterpriseUser;
    protected TraceRecord $traceRecord;

    protected function setUp(): void
    {
        parent::setUp();

        // Create users with different packages
        $this->freeUser = User::factory()->create([
            'password' => Hash::make('password123'),
            'package' => 'free',
        ]);

        $this->basicUser = User::factory()->create([
            'password' => Hash::make('password123'),
            'package' => 'basic',
        ]);

        $this->premiumUser = User::factory()->create([
            'password' => Hash::make('password123'),
            'package' => 'premium',
        ]);

        $this->enterpriseUser = User::factory()->create([
            'password' => Hash::make('password123'),
            'package' => 'enterprise',
        ]);

        $this->traceRecord = TraceRecord::factory()->create();
    }

    /**
     * PHASE 3 - TEST 1: Free user should NOT see e-signature section
     */
    public function test_free_user_does_not_see_e_signature_section(): void
    {
        $response = $this->actingAs($this->freeUser)
            ->get(route('cte.receiving.create'));

        $response->assertStatus(200);
        $response->assertDontSee('MESSAGES.ELECTRONIC_SIGNATURE_REQUIRED');
        $response->assertDontSee('messages.password_for_signature');
        $response->assertSee('e_signatures_available_in_enterprise');
    }

    /**
     * PHASE 3 - TEST 2: Basic user should NOT see e-signature section
     */
    public function test_basic_user_does_not_see_e_signature_section(): void
    {
        $response = $this->actingAs($this->basicUser)
            ->get(route('cte.receiving.create'));

        $response->assertStatus(200);
        $response->assertDontSee('MESSAGES.ELECTRONIC_SIGNATURE_REQUIRED');
        $response->assertSee('e_signatures_available_in_enterprise');
    }

    /**
     * PHASE 3 - TEST 3: Premium user should NOT see e-signature section
     */
    public function test_premium_user_does_not_see_e_signature_section(): void
    {
        $response = $this->actingAs($this->premiumUser)
            ->get(route('cte.receiving.create'));

        $response->assertStatus(200);
        $response->assertDontSee('MESSAGES.ELECTRONIC_SIGNATURE_REQUIRED');
        $response->assertSee('e_signatures_available_in_enterprise');
    }

    /**
     * PHASE 3 - TEST 4: Enterprise user SHOULD see e-signature section
     */
    public function test_enterprise_user_sees_e_signature_section(): void
    {
        $response = $this->actingAs($this->enterpriseUser)
            ->get(route('cte.receiving.create'));

        $response->assertStatus(200);
        $response->assertSee('messages.password_for_signature');
        $response->assertSee('messages.signature_reason');
        $response->assertDontSee('e_signatures_available_in_enterprise');
    }

    /**
     * PHASE 3 - TEST 5: Free user can submit receiving WITHOUT signature
     */
    public function test_free_user_can_submit_receiving_without_signature(): void
    {
        $response = $this->actingAs($this->freeUser)
            ->postJson(route('cte.receiving.store'), [
                'tlc' => 'RCV-2024-001',
                'product_id' => 1,
                'quantity_received' => 100,
                'unit' => 'kg',
                'location_id' => 1,
                'receiving_location_gln' => '1234567890123',
                'partner_id' => 1,
                'reference_doc' => 'PO-12345',
                'event_date' => now()->toDateString(),
                // NO signature_password or signature_reason
            ]);

        $response->assertStatus(201);
        $response->assertJsonPath('success', true);
        $this->assertDatabaseHas('trace_records', [
            'tlc' => 'RCV-2024-001',
        ]);
    }

    /**
     * PHASE 3 - TEST 6: Enterprise user can submit receiving WITH signature
     */
    public function test_enterprise_user_can_submit_receiving_with_signature(): void
    {
        $response = $this->actingAs($this->enterpriseUser)
            ->postJson(route('cte.receiving.store'), [
                'tlc' => 'RCV-2024-002',
                'product_id' => 1,
                'quantity_received' => 100,
                'unit' => 'kg',
                'location_id' => 1,
                'receiving_location_gln' => '1234567890123',
                'partner_id' => 1,
                'reference_doc' => 'PO-12345',
                'event_date' => now()->toDateString(),
                'signature_password' => 'password123',
                'signature_reason' => 'Initial receiving record',
            ]);

        $response->assertStatus(201);
        $response->assertJsonPath('success', true);
        $this->assertDatabaseHas('trace_records', [
            'tlc' => 'RCV-2024-002',
        ]);
        $this->assertDatabaseHas('e_signatures', [
            'user_id' => $this->enterpriseUser->id,
        ]);
    }

    /**
     * PHASE 3 - TEST 7: Enterprise user can submit receiving WITHOUT signature (optional)
     */
    public function test_enterprise_user_can_submit_receiving_without_signature(): void
    {
        $response = $this->actingAs($this->enterpriseUser)
            ->postJson(route('cte.receiving.store'), [
                'tlc' => 'RCV-2024-003',
                'product_id' => 1,
                'quantity_received' => 100,
                'unit' => 'kg',
                'location_id' => 1,
                'receiving_location_gln' => '1234567890123',
                'partner_id' => 1,
                'reference_doc' => 'PO-12345',
                'event_date' => now()->toDateString(),
                // NO signature_password or signature_reason
            ]);

        $response->assertStatus(201);
        $response->assertJsonPath('success', true);
        $this->assertDatabaseHas('trace_records', [
            'tlc' => 'RCV-2024-003',
        ]);
    }

    /**
     * PHASE 3 - TEST 8: Free user button text should be "Record Receiving"
     */
    public function test_free_user_sees_correct_button_text(): void
    {
        $response = $this->actingAs($this->freeUser)
            ->get(route('cte.receiving.create'));

        $response->assertStatus(200);
        $response->assertSee('messages.record_receiving');
        $response->assertDontSee('messages.record_receiving_with_signature');
    }

    /**
     * PHASE 3 - TEST 9: Enterprise user button text should be "Record Receiving WITH Signature"
     */
    public function test_enterprise_user_sees_correct_button_text(): void
    {
        $response = $this->actingAs($this->enterpriseUser)
            ->get(route('cte.receiving.create'));

        $response->assertStatus(200);
        $response->assertSee('messages.record_receiving_with_signature');
    }

    /**
     * PHASE 3 - TEST 10: Upgrade prompt should be visible for non-enterprise users
     */
    public function test_upgrade_prompt_visible_for_non_enterprise_users(): void
    {
        $response = $this->actingAs($this->basicUser)
            ->get(route('cte.receiving.create'));

        $response->assertStatus(200);
        $response->assertSee('messages.e_signatures_available_in_enterprise');
        $response->assertSee(route('pricing'));
    }

    /**
     * PHASE 3 - TEST 11: API endpoint respects package restrictions
     */
    public function test_api_endpoint_respects_package_restrictions(): void
    {
        // Free user tries to create signature via API
        $response = $this->actingAs($this->freeUser)
            ->postJson(route('api.e-signatures.sign'), [
                'record_type' => 'TraceRecord',
                'record_id' => $this->traceRecord->id,
                'action' => 'create',
                'password' => 'password123',
                'reason' => 'Test',
            ]);

        $response->assertStatus(403);
        $response->assertJsonPath('message', 'E-signature feature not available in your package');
    }

    /**
     * PHASE 3 - TEST 12: Enterprise user can use API endpoint
     */
    public function test_enterprise_user_can_use_api_endpoint(): void
    {
        $response = $this->actingAs($this->enterpriseUser)
            ->postJson(route('api.e-signatures.sign'), [
                'record_type' => 'TraceRecord',
                'record_id' => $this->traceRecord->id,
                'action' => 'create',
                'password' => 'password123',
                'reason' => 'Test signature',
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
    }

    /**
     * PHASE 3 - TEST 13: Form validation - signature_password is optional
     */
    public function test_signature_password_is_optional(): void
    {
        $response = $this->actingAs($this->enterpriseUser)
            ->postJson(route('cte.receiving.store'), [
                'tlc' => 'RCV-2024-004',
                'product_id' => 1,
                'quantity_received' => 100,
                'unit' => 'kg',
                'location_id' => 1,
                'receiving_location_gln' => '1234567890123',
                'partner_id' => 1,
                'reference_doc' => 'PO-12345',
                'event_date' => now()->toDateString(),
                // Missing signature_password - should still be valid
            ]);

        $response->assertStatus(201);
    }

    /**
     * PHASE 3 - TEST 14: Form validation - signature_reason is optional
     */
    public function test_signature_reason_is_optional(): void
    {
        $response = $this->actingAs($this->enterpriseUser)
            ->postJson(route('cte.receiving.store'), [
                'tlc' => 'RCV-2024-005',
                'product_id' => 1,
                'quantity_received' => 100,
                'unit' => 'kg',
                'location_id' => 1,
                'receiving_location_gln' => '1234567890123',
                'partner_id' => 1,
                'reference_doc' => 'PO-12345',
                'event_date' => now()->toDateString(),
                'signature_password' => 'password123',
                // Missing signature_reason - should still be valid
            ]);

        $response->assertStatus(201);
    }

    /**
     * PHASE 3 - TEST 15: Signature is NOT created if password is missing
     */
    public function test_signature_not_created_without_password(): void
    {
        $response = $this->actingAs($this->enterpriseUser)
            ->postJson(route('cte.receiving.store'), [
                'tlc' => 'RCV-2024-006',
                'product_id' => 1,
                'quantity_received' => 100,
                'unit' => 'kg',
                'location_id' => 1,
                'receiving_location_gln' => '1234567890123',
                'partner_id' => 1,
                'reference_doc' => 'PO-12345',
                'event_date' => now()->toDateString(),
                // No signature_password
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseMissing('e_signatures', [
            'user_id' => $this->enterpriseUser->id,
        ]);
    }
}
