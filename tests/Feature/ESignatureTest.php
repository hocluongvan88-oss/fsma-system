<?php

namespace Tests\Feature;

use App\Models\ESignature;
use App\Models\User;
use App\Models\TraceRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

class ESignatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'package' => 'enterprise',
        ]);

        $this->manager = User::factory()->create([
            'password' => Hash::make('password123'),
            'role' => 'manager',
            'package' => 'enterprise',
        ]);
    }

    public function test_can_create_e_signature(): void
    {
        $traceRecord = TraceRecord::factory()->create();

        $signature = ESignature::createSignature(
            $this->user,
            'TraceRecord',
            $traceRecord->id,
            'create',
            'password123',
            'Test signature creation'
        );

        $this->assertNotNull($signature);
        $this->assertEquals($this->user->id, $signature->user_id);
        $this->assertEquals('TraceRecord', $signature->record_type);
        $this->assertEquals('create', $signature->action);
    }

    public function test_cannot_create_signature_with_wrong_password(): void
    {
        $traceRecord = TraceRecord::factory()->create();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid password for e-signature');

        ESignature::createSignature(
            $this->user,
            'TraceRecord',
            $traceRecord->id,
            'create',
            'wrongpassword',
            'Test signature'
        );
    }

    public function test_can_verify_e_signature(): void
    {
        $traceRecord = TraceRecord::factory()->create();

        $signature = ESignature::createSignature(
            $this->user,
            'TraceRecord',
            $traceRecord->id,
            'create',
            'password123',
            'Test signature'
        );

        $isValid = $signature->verify($this->user, 'password123');
        $this->assertTrue($isValid);
    }

    public function test_signature_verification_fails_with_wrong_password(): void
    {
        $traceRecord = TraceRecord::factory()->create();

        $signature = ESignature::createSignature(
            $this->user,
            'TraceRecord',
            $traceRecord->id,
            'create',
            'password123',
            'Test signature'
        );

        $isValid = $signature->verify($this->user, 'wrongpassword');
        $this->assertFalse($isValid);
    }

    public function test_can_sign_via_api(): void
    {
        $traceRecord = TraceRecord::factory()->create();

        $response = $this->actingAs($this->user)->postJson('/api/e-signatures/sign', [
            'record_type' => 'TraceRecord',
            'record_id' => $traceRecord->id,
            'action' => 'create',
            'password' => 'password123',
            'reason' => 'API test signature',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $this->assertDatabaseHas('e_signatures', [
            'user_id' => $this->user->id,
            'record_type' => 'TraceRecord',
            'record_id' => $traceRecord->id,
        ]);
    }

    public function test_can_view_signatures_list(): void
    {
        ESignature::factory(5)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->get('/admin/e-signatures');

        $response->assertStatus(200);
        $response->assertViewHas('signatures');
    }

    public function test_signature_includes_ip_address(): void
    {
        $traceRecord = TraceRecord::factory()->create();

        $signature = ESignature::createSignature(
            $this->user,
            'TraceRecord',
            $traceRecord->id,
            'create',
            'password123',
            'Test signature'
        );

        $this->assertNotNull($signature->ip_address);
    }

    public function test_signature_includes_timestamp(): void
    {
        $traceRecord = TraceRecord::factory()->create();

        $signature = ESignature::createSignature(
            $this->user,
            'TraceRecord',
            $traceRecord->id,
            'create',
            'password123',
            'Test signature'
        );

        $this->assertNotNull($signature->signed_at);
    }

    public function test_can_query_signatures_by_record(): void
    {
        $traceRecord = TraceRecord::factory()->create();

        ESignature::createSignature(
            $this->user,
            'TraceRecord',
            $traceRecord->id,
            'create',
            'password123'
        );

        $signatures = ESignature::byRecord('TraceRecord', $traceRecord->id)->get();

        $this->assertCount(1, $signatures);
    }

    public function test_can_query_recent_signatures(): void
    {
        ESignature::factory(3)->create(['user_id' => $this->user->id]);

        $recent = ESignature::recent(30)->get();

        $this->assertGreaterThanOrEqual(3, $recent->count());
    }
}
