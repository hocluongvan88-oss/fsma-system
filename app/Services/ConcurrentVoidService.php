<?php

namespace App\Services;

use App\Models\CTEEvent;
use App\Models\TraceRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Services\CacheService; // Ensure CacheService is imported

class ConcurrentVoidService
{
    /**
     * Handle VOID operation with optimistic locking and retry mechanism
     * Designed to handle 100+ concurrent VOID operations
     */
    public function voidEvent(int $eventId, string $reason, ?string $notes = null, int $maxRetries = 3): array
    {
        $attempt = 0;
        $lastException = null;

        while ($attempt < $maxRetries) {
            try {
                return $this->executeVoid($eventId, $reason, $notes);
            } catch (\Illuminate\Database\QueryException $e) {
                // Deadlock or lock timeout - retry
                if ($this->isDeadlockException($e)) {
                    $attempt++;
                    $lastException = $e;
                    
                    // Exponential backoff: 10ms, 20ms, 40ms
                    usleep(10000 * pow(2, $attempt - 1));
                    
                    Log::warning("VOID operation deadlock, retry {$attempt}/{$maxRetries}", [
                        'event_id' => $eventId,
                        'error' => $e->getMessage()
                    ]);
                    
                    continue;
                }
                
                // Other database errors - don't retry
                throw $e;
            }
        }

        // Max retries exceeded
        throw new \Exception("VOID operation failed after {$maxRetries} retries: " . $lastException->getMessage());
    }

    /**
     * Execute VOID operation with proper locking
     */
    protected function executeVoid(int $eventId, string $reason, ?string $notes): array
    {
        return DB::transaction(function () use ($eventId, $reason, $notes) {
            $startTime = microtime(true);

            $event = CTEEvent::where('id', $eventId)
                ->lockForUpdate()
                ->first();

            if (!$event) {
                throw new \Exception('Event not found');
            }

            if ($event->status === 'voided') {
                throw new \Exception('Event already voided');
            }

            if ($event->void_count >= 1) {
                throw new \Exception('Event has already been voided once');
            }

            // Mark original event as voided
            $event->update([
                'status' => 'voided',
                'voided_by' => auth()->id(),
                'voided_at' => now(),
                'void_count' => DB::raw('void_count + 1'),
            ]);

            // Create VOID event
            $voidEvent = CTEEvent::create([
                'event_type' => 'VOID',
                'status' => 'active',
                'trace_record_id' => $event->trace_record_id, // Reference the same trace record as original event
                'event_date' => now(), // Record the time when void action occurred
                'location_id' => $event->location_id, // Add location_id from original event
                'voids_event_id' => $event->id,
                'notes' => "VOID: {$reason}" . ($notes ? " - {$notes}" : ''),
                'created_by' => auth()->id(),
                'organization_id' => $event->organization_id,
            ]);

            // Reverse inventory changes based on event type
            $this->reverseInventory($event);

            $executionTime = round((microtime(true) - $startTime) * 1000, 2);

            // Clear relevant caches
            CacheService::forgetByTag('cte_events');
            CacheService::forgetByTag('trace_records');

            return [
                'success' => true,
                'void_event_id' => $voidEvent->id,
                'execution_time_ms' => $executionTime,
            ];
        });
    }

    /**
     * Reverse inventory changes for voided event
     */
    protected function reverseInventory(CTEEvent $event): void
    {
        switch ($event->event_type) {
            case 'receiving':
                $this->reverseReceiving($event);
                break;
            case 'transformation':
                $this->reverseTransformation($event);
                break;
            case 'shipping':
                $this->reverseShipping($event);
                break;
        }
    }

    /**
     * Reverse RECEIVING event
     */
    protected function reverseReceiving(CTEEvent $event): void
    {
        if ($event->trace_record_id) {
            $record = TraceRecord::lockForUpdate()->find($event->trace_record_id);
            
            if ($record) {
                $newAvailableQuantity = $record->available_quantity - $event->quantity_received;
                $newQuantity = $record->quantity - $event->quantity_received;
                
                $record->update([
                    'available_quantity' => $newAvailableQuantity,
                    'quantity' => $newQuantity,
                    'status' => 'voided',
                ]);
            }
        }
    }

    /**
     * Reverse TRANSFORMATION event
     */
    protected function reverseTransformation(CTEEvent $event): void
    {
        // Get transformation items
        $items = $event->transformationItems;
        
        foreach ($items as $item) {
            $inputRecord = TraceRecord::lockForUpdate()->find($item->input_trace_record_id);
            
            if ($inputRecord) {
                $newAvailableQuantity = $inputRecord->available_quantity + $item->quantity_used;
                $newConsumedQuantity = $inputRecord->consumed_quantity - $item->quantity_used;
                
                $inputRecord->update([
                    'available_quantity' => $newAvailableQuantity,
                    'consumed_quantity' => $newConsumedQuantity,
                    'status' => 'active',
                ]);
            }
        }

        // Mark output as voided
        if ($event->trace_record_id) {
            $outputRecord = TraceRecord::lockForUpdate()->find($event->trace_record_id);
            
            if ($outputRecord) {
                $outputRecord->update([
                    'status' => 'voided',
                    'available_quantity' => 0,
                ]);
            }
        }
    }

    /**
     * Reverse SHIPPING event
     */
    protected function reverseShipping(CTEEvent $event): void
    {
        // Get shipping relationships
        $relationships = DB::table('trace_relationships')
            ->where('cte_event_id', $event->id)
            ->where('relationship_type', 'INPUT')
            ->get();

        foreach ($relationships as $rel) {
            $record = TraceRecord::lockForUpdate()->find($rel->parent_id);
            
            if ($record) {
                // Get quantity from relationship metadata or event
                $quantity = 0;
                
                // Try to get quantity from relationship metadata first
                if (isset($rel->metadata)) {
                    $metadata = json_decode($rel->metadata, true);
                    $quantity = $metadata['quantity'] ?? 0;
                }
                
                // Fallback to event quantity_received (shipping uses this field)
                if ($quantity == 0) {
                    $quantity = $event->quantity_received ?? 0;
                }
                
                $newAvailableQuantity = $record->available_quantity + $quantity;
                $newConsumedQuantity = $record->consumed_quantity - $quantity;
                
                $record->update([
                    'available_quantity' => $newAvailableQuantity,
                    'consumed_quantity' => $newConsumedQuantity,
                    'status' => 'active',
                ]);
            }
        }
    }

    /**
     * Check if exception is a deadlock
     */
    protected function isDeadlockException(\Illuminate\Database\QueryException $e): bool
    {
        $errorCode = $e->getCode();
        $errorMessage = $e->getMessage();

        // MySQL deadlock codes
        return in_array($errorCode, [1213, 1205]) || 
               str_contains($errorMessage, 'Deadlock') ||
               str_contains($errorMessage, 'Lock wait timeout');
    }
}
