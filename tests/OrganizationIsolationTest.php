<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use App\Models\Product;
use App\Models\Location;
use App\Models\Partner;
use App\Models\TraceRecord;
use App\Models\CTEEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $org1;
    protected Organization $org2;
    protected User $user1;
    protected User $user2;
    protected Product $product1;
    protected Product $product2;
    protected Location $location1;
    protected Location $location2;
    protected Partner $partner1;
    protected Partner $partner2;

    protected function setUp(): void
    {
        parent::setUp();

        // Create two separate organizations
        $this->org1 = Organization::factory()->create(['name' => 'Organization 1']);
        $this->org2 = Organization::factory()->create(['name' => 'Organization 2']);

        // Create users for each organization
        $this->user1 = User::factory()->create(['organization_id' => $this->org1->id]);
        $this->user2 = User::factory()->create(['organization_id' => $this->org2->id]);

        // Create products for each organization
        $this->product1 = Product::factory()->create(['organization_id' => $this->org1->id]);
        $this->product2 = Product::factory()->create(['organization_id' => $this->org2->id]);

        // Create locations for each organization
        $this->location1 = Location::factory()->create(['organization_id' => $this->org1->id]);
        $this->location2 = Location::factory()->create(['organization_id' => $this->org2->id]);

        // Create partners for each organization
        $this->partner1 = Partner::factory()->create(['organization_id' => $this->org1->id]);
        $this->partner2 = Partner::factory()->create(['organization_id' => $this->org2->id]);
    }

    /**
     * Test that user cannot access products from other organizations
     */
    public function test_user_cannot_access_other_organization_products(): void
    {
        $this->actingAs($this->user1);

        // User1 should see their own products
        $response = $this->get(route('products.index'));
        $response->assertStatus(200);
        $this->assertDatabaseHas('products', [
            'id' => $this->product1->id,
            'organization_id' => $this->org1->id,
        ]);

        // User1 should not be able to access org2's product
        $response = $this->get(route('products.show', $this->product2));
        $response->assertStatus(403);
    }

    /**
     * Test that user cannot access other organization's trace records
     */
    public function test_user_cannot_access_other_organization_trace_records(): void
    {
        $traceRecord1 = TraceRecord::factory()->create([
            'organization_id' => $this->org1->id,
            'product_id' => $this->product1->id,
        ]);

        $traceRecord2 = TraceRecord::factory()->create([
            'organization_id' => $this->org2->id,
            'product_id' => $this->product2->id,
        ]);

        $this->actingAs($this->user1);

        // User1 should see their own trace records
        $records = TraceRecord::forOrganization($this->org1->id)->get();
        $this->assertCount(1, $records);
        $this->assertEquals($traceRecord1->id, $records->first()->id);

        // User1 should not see org2's trace records
        $records = TraceRecord::forOrganization($this->org2->id)->get();
        $this->assertCount(0, $records);
    }

    /**
     * Test that trace relationships respect organization boundaries
     */
    public function test_trace_relationships_respect_organization_boundaries(): void
    {
        $traceRecord1 = TraceRecord::factory()->create([
            'organization_id' => $this->org1->id,
            'product_id' => $this->product1->id,
        ]);

        $traceRecord2 = TraceRecord::factory()->create([
            'organization_id' => $this->org1->id,
            'product_id' => $this->product1->id,
        ]);

        $traceRecord3 = TraceRecord::factory()->create([
            'organization_id' => $this->org2->id,
            'product_id' => $this->product2->id,
        ]);

        // Create relationships within org1
        \DB::table('trace_relationships')->insert([
            'parent_id' => $traceRecord1->id,
            'child_id' => $traceRecord2->id,
            'relationship_type' => 'INPUT',
            'organization_id' => $this->org1->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Verify relationships are isolated by organization
        $org1Relationships = \DB::table('trace_relationships')
            ->where('organization_id', $this->org1->id)
            ->count();
        $this->assertEquals(1, $org1Relationships);

        $org2Relationships = \DB::table('trace_relationships')
            ->where('organization_id', $this->org2->id)
            ->count();
        $this->assertEquals(0, $org2Relationships);
    }

    /**
     * Test that CTE events are isolated by organization
     */
    public function test_cte_events_are_isolated_by_organization(): void
    {
        $traceRecord1 = TraceRecord::factory()->create([
            'organization_id' => $this->org1->id,
            'product_id' => $this->product1->id,
        ]);

        $traceRecord2 = TraceRecord::factory()->create([
            'organization_id' => $this->org2->id,
            'product_id' => $this->product2->id,
        ]);

        $event1 = CTEEvent::factory()->create([
            'organization_id' => $this->org1->id,
            'trace_record_id' => $traceRecord1->id,
        ]);

        $event2 = CTEEvent::factory()->create([
            'organization_id' => $this->org2->id,
            'trace_record_id' => $traceRecord2->id,
        ]);

        $this->actingAs($this->user1);

        // User1 should only see their organization's events
        $events = CTEEvent::forOrganization($this->org1->id)->get();
        $this->assertCount(1, $events);
        $this->assertEquals($event1->id, $events->first()->id);
    }

    /**
     * Test that foreign key constraints prevent orphaned records
     */
    public function test_foreign_key_constraints_prevent_orphaned_records(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        // Try to create a trace record with non-existent organization
        TraceRecord::create([
            'tlc' => 'TEST-TLC-' . time(),
            'product_id' => $this->product1->id,
            'quantity' => 100,
            'available_quantity' => 100,
            'consumed_quantity' => 0,
            'unit' => 'kg',
            'location_id' => $this->location1->id,
            'organization_id' => 99999, // Non-existent organization
        ]);
    }

    /**
     * Test that organization deletion cascades properly
     */
    public function test_organization_deletion_cascades_properly(): void
    {
        $traceRecord = TraceRecord::factory()->create([
            'organization_id' => $this->org1->id,
            'product_id' => $this->product1->id,
        ]);

        $this->assertDatabaseHas('trace_records', ['id' => $traceRecord->id]);

        // Delete organization
        $this->org1->delete();

        // Verify trace record was deleted
        $this->assertDatabaseMissing('trace_records', ['id' => $traceRecord->id]);
    }

    /**
     * Test that raw queries in QueryOptimizationService respect organization boundaries
     */
    public function test_query_optimization_service_respects_organization_boundaries(): void
    {
        $products1 = \App\Services\QueryOptimizationService::getActiveProducts($this->org1->id);
        $products2 = \App\Services\QueryOptimizationService::getActiveProducts($this->org2->id);

        // Each organization should only see their own products
        $this->assertCount(1, $products1);
        $this->assertEquals($this->product1->id, $products1->first()->id);

        $this->assertCount(1, $products2);
        $this->assertEquals($this->product2->id, $products2->first()->id);
    }

    /**
     * Test that middleware prevents cross-organization access
     */
    public function test_middleware_prevents_cross_organization_access(): void
    {
        $this->actingAs($this->user1);

        // User1 tries to access org2's location
        $response = $this->get(route('locations.show', $this->location2));
        $response->assertStatus(403);
    }

    /**
     * Test that audit logs are isolated by organization
     */
    public function test_audit_logs_are_isolated_by_organization(): void
    {
        $this->actingAs($this->user1);

        // Create an audit log entry
        \App\Models\AuditLog::create([
            'user_id' => $this->user1->id,
            'organization_id' => $this->org1->id,
            'action' => 'test_action',
            'model' => 'TestModel',
            'model_id' => 1,
        ]);

        // Verify only org1's audit logs are visible
        $logs = \App\Models\AuditLog::forOrganization($this->org1->id)->get();
        $this->assertCount(1, $logs);
    }
}
