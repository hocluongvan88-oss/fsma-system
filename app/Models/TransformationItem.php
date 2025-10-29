<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasOrganizationScope;

class TransformationItem extends Model
{
    use HasFactory, HasOrganizationScope;

    protected $fillable = [
        'transformation_event_id',
        'input_trace_record_id',
        'quantity_used',
        'unit',
    ];

    protected function casts(): array
    {
        return [
            'quantity_used' => 'decimal:2',
        ];
    }

    public function transformationEvent(): BelongsTo
    {
        return $this->belongsTo(CTEEvent::class, 'transformation_event_id');
    }

    public function inputTraceRecord(): BelongsTo
    {
        return $this->belongsTo(TraceRecord::class, 'input_trace_record_id');
    }
}
