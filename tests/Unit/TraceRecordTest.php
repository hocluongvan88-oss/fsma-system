<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\TraceRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TraceRecordTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_check_if_quantity_is_available()
    {
        $record = TraceRecord::factory()->create([
            'quantity' => 1000,
            'available_quantity' => 500,
            'consumed_quantity' => 500,
        ]);
        
        $this->assertTrue($record->canConsume(100));
        $this->assertTrue($record->canConsume(500));
        $this->assertFalse($record->canConsume(501));
        $this->assertFalse($record->canConsume(1000));
    }

    /** @test */
    public function it_correctly_consumes_quantity()
    {
        $record = TraceRecord::factory()->create([
            'quantity' => 1000,
            'available_quantity' => 1000,
            'consumed_quantity' => 0,
            'status' => 'active'
        ]);
        
        $result = $record->consume(300);
        
        $this->assertTrue($result);
        $this->assertEquals(700, $record->available_quantity);
        $this->assertEquals(300, $record->consumed_quantity);
        $this->assertEquals('active', $record->status);
    }

    /** @test */
    public function it_marks_as_consumed_when_fully_used()
    {
        $record = TraceRecord::factory()->create([
            'quantity' => 100,
            'available_quantity' => 100,
            'consumed_quantity' => 0,
            'status' => 'active'
        ]);
        
        $result = $record->consume(100);
        
        $this->assertTrue($result);
        $this->assertEquals(0, $record->available_quantity);
        $this->assertEquals(100, $record->consumed_quantity);
        $this->assertEquals('consumed', $record->status);
    }

    /** @test */
    public function it_prevents_over_consumption()
    {
        $record = TraceRecord::factory()->create([
            'quantity' => 100,
            'available_quantity' => 50,
            'consumed_quantity' => 50,
            'status' => 'active'
        ]);
        
        $result = $record->consume(100);
        
        $this->assertFalse($result);
        $this->assertEquals(50, $record->available_quantity);
        $this->assertEquals(50, $record->consumed_quantity);
    }

    /** @test */
    public function it_returns_correct_remaining_quantity()
    {
        $record = TraceRecord::factory()->create([
            'quantity' => 1000,
            'available_quantity' => 750,
            'consumed_quantity' => 250,
        ]);
        
        $this->assertEquals(750, $record->getRemainingQuantity());
    }
}
