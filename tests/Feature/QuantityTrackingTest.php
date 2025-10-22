<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Location;
use App\Models\Partner;
use App\Models\TraceRecord;
use App\Models\CTEEvent;
use App\Models\TransformationItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

class QuantityTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $product;
    protected $location;
    protected $partner;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'role' => 'admin',
            'organization_id' => 1
        ]);
        
        $this->product = Product::factory()->create([
            'organization_id' => 1
        ]);
        
        $this->location = Location::factory()->create([
            'organization_id' => 1
        ]);
        
        $this->partner = Partner::factory()->create([
            'organization_id' => 1,
            'partner_type' => 'supplier'
        ]);
    }

    /** @test */
    public function it_initializes_available_quantity_on_receiving()
    {
        $this->actingAs($this->user);
        
        $response = $this->post(route('cte.receiving'), [
            'tlc' => 'TEST-RCV-001',
            'product_id' => $this->product->id,
            'quantity_received' => 1000,
            'quantity_unit' => 'kg',
            'location_id' => $this->location->id,
            'partner_id' => $this->partner->id,
            'event_date' => now()->format('Y-m-d\TH:i'),
        ]);
        
        $response->assertRedirect();
        
        $traceRecord = TraceRecord::where('tlc', 'TEST-RCV-001')->first();
        
        $this->assertNotNull($traceRecord);
        $this->assertEquals(1000, $traceRecord->quantity);
        $this->assertEquals(1000, $traceRecord->available_quantity);
        $this->assertEquals(0, $traceRecord->consumed_quantity);
        $this->assertEquals('active', $traceRecord->status);
    }

    /** @test */
    public function it_prevents_transformation_when_insufficient_quantity()
    {
        $this->actingAs($this->user);
        
        // Create input TLC with 100kg
        $inputTLC = TraceRecord::factory()->create([
            'organization_id' => 1,
            'quantity' => 100,
            'available_quantity' => 100,
            'consumed_quantity' => 0,
            'unit' => 'kg',
            'status' => 'active'
        ]);
        
        // Try to transform 150kg (more than available)
        $response = $this->post(route('cte.transformation'), [
            'output_tlc' => 'TEST-TRF-001',
            'product_id' => $this->product->id,
            'quantity' => 150,
            'quantity_unit' => 'kg',
            'location_id' => $this->location->id,
            'input_tlcs' => [$inputTLC->id],
            'event_date' => now()->format('Y-m-d\TH:i'),
        ]);
        
        $response->assertSessionHasErrors('quantity');
        
        // Verify input TLC was not consumed
        $inputTLC->refresh();
        $this->assertEquals(100, $inputTLC->available_quantity);
        $this->assertEquals(0, $inputTLC->consumed_quantity);
    }

    /** @test */
    public function it_correctly_consumes_quantity_during_transformation()
    {
        $this->actingAs($this->user);
        
        // Create input TLC with 1000kg
        $inputTLC = TraceRecord::factory()->create([
            'organization_id' => 1,
            'quantity' => 1000,
            'available_quantity' => 1000,
            'consumed_quantity' => 0,
            'unit' => 'kg',
            'status' => 'active'
        ]);
        
        // Transform 200kg
        $response = $this->post(route('cte.transformation'), [
            'output_tlc' => 'TEST-TRF-001',
            'product_id' => $this->product->id,
            'quantity' => 200,
            'quantity_unit' => 'kg',
            'location_id' => $this->location->id,
            'input_tlcs' => [$inputTLC->id],
            'event_date' => now()->format('Y-m-d\TH:i'),
        ]);
        
        $response->assertRedirect();
        
        // Verify input TLC quantities
        $inputTLC->refresh();
        $this->assertEquals(800, $inputTLC->available_quantity);
        $this->assertEquals(200, $inputTLC->consumed_quantity);
        $this->assertEquals('active', $inputTLC->status); // Still active because not fully consumed
        
        // Verify transformation item was created
        $transformationItem = TransformationItem::where('input_trace_record_id', $inputTLC->id)->first();
        $this->assertNotNull($transformationItem);
        $this->assertEquals(200, $transformationItem->quantity_used);
    }

    /** @test */
    public function it_marks_tlc_as_consumed_when_fully_used()
    {
        $this->actingAs($this->user);
        
        // Create input TLC with 100kg
        $inputTLC = TraceRecord::factory()->create([
            'organization_id' => 1,
            'quantity' => 100,
            'available_quantity' => 100,
            'consumed_quantity' => 0,
            'unit' => 'kg',
            'status' => 'active'
        ]);
        
        // Transform all 100kg
        $response = $this->post(route('cte.transformation'), [
            'output_tlc' => 'TEST-TRF-001',
            'product_id' => $this->product->id,
            'quantity' => 100,
            'quantity_unit' => 'kg',
            'location_id' => $this->location->id,
            'input_tlcs' => [$inputTLC->id],
            'event_date' => now()->format('Y-m-d\TH:i'),
        ]);
        
        $response->assertRedirect();
        
        // Verify TLC is marked as consumed
        $inputTLC->refresh();
        $this->assertEquals(0, $inputTLC->available_quantity);
        $this->assertEquals(100, $inputTLC->consumed_quantity);
        $this->assertEquals('consumed', $inputTLC->status);
    }

    /** @test */
    public function it_allows_multiple_transformations_from_same_input()
    {
        $this->actingAs($this->user);
        
        // Create input TLC with 1000kg
        $inputTLC = TraceRecord::factory()->create([
            'organization_id' => 1,
            'quantity' => 1000,
            'available_quantity' => 1000,
            'consumed_quantity' => 0,
            'unit' => 'kg',
            'status' => 'active'
        ]);
        
        // First transformation: 200kg
        $this->post(route('cte.transformation'), [
            'output_tlc' => 'TEST-TRF-001',
            'product_id' => $this->product->id,
            'quantity' => 200,
            'quantity_unit' => 'kg',
            'location_id' => $this->location->id,
            'input_tlcs' => [$inputTLC->id],
            'event_date' => now()->format('Y-m-d\TH:i'),
        ]);
        
        $inputTLC->refresh();
        $this->assertEquals(800, $inputTLC->available_quantity);
        
        // Second transformation: 300kg
        $this->post(route('cte.transformation'), [
            'output_tlc' => 'TEST-TRF-002',
            'product_id' => $this->product->id,
            'quantity' => 300,
            'quantity_unit' => 'kg',
            'location_id' => $this->location->id,
            'input_tlcs' => [$inputTLC->id],
            'event_date' => now()->format('Y-m-d\TH:i'),
        ]);
        
        $inputTLC->refresh();
        $this->assertEquals(500, $inputTLC->available_quantity);
        $this->assertEquals(500, $inputTLC->consumed_quantity);
        
        // Verify both transformation items exist
        $items = TransformationItem::where('input_trace_record_id', $inputTLC->id)->get();
        $this->assertCount(2, $items);
        $this->assertEquals(500, $items->sum('quantity_used'));
    }

    /** @test */
    public function it_distributes_consumption_across_multiple_inputs()
    {
        $this->actingAs($this->user);
        
        // Create two input TLCs
        $input1 = TraceRecord::factory()->create([
            'organization_id' => 1,
            'quantity' => 100,
            'available_quantity' => 100,
            'consumed_quantity' => 0,
            'unit' => 'kg',
            'status' => 'active'
        ]);
        
        $input2 = TraceRecord::factory()->create([
            'organization_id' => 1,
            'quantity' => 200,
            'available_quantity' => 200,
            'consumed_quantity' => 0,
            'unit' => 'kg',
            'status' => 'active'
        ]);
        
        // Transform 250kg (should consume all of input1 and 150kg of input2)
        $response = $this->post(route('cte.transformation'), [
            'output_tlc' => 'TEST-TRF-001',
            'product_id' => $this->product->id,
            'quantity' => 250,
            'quantity_unit' => 'kg',
            'location_id' => $this->location->id,
            'input_tlcs' => [$input1->id, $input2->id],
            'event_date' => now()->format('Y-m-d\TH:i'),
        ]);
        
        $response->assertRedirect();
        
        // Verify input1 is fully consumed
        $input1->refresh();
        $this->assertEquals(0, $input1->available_quantity);
        $this->assertEquals(100, $input1->consumed_quantity);
        $this->assertEquals('consumed', $input1->status);
        
        // Verify input2 is partially consumed
        $input2->refresh();
        $this->assertEquals(50, $input2->available_quantity);
        $this->assertEquals(150, $input2->consumed_quantity);
        $this->assertEquals('active', $input2->status);
    }

    /** @test */
    public function it_tracks_organization_isolation_in_quantity_tracking()
    {
        // Create user in different organization
        $otherUser = User::factory()->create([
            'role' => 'admin',
            'organization_id' => 2
        ]);
        
        // Create TLC in organization 1
        $tlc1 = TraceRecord::factory()->create([
            'organization_id' => 1,
            'quantity' => 1000,
            'available_quantity' => 1000,
            'status' => 'active'
        ]);
        
        // User from organization 2 should not see TLC from organization 1
        $this->actingAs($otherUser);
        
        $response = $this->get(route('cte.transformation'));
        
        // The TLC from org 1 should not be in the available TLCs list
        $response->assertDontSee($tlc1->tlc);
    }
}
