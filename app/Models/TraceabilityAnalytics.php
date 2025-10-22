<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TraceabilityAnalytics extends Model
{
    protected $fillable = [
        'trace_record_id',
        'query_type',
        'direction',
        'user_id',
        'ip_address',
        'user_agent',
    ];

    public function traceRecord()
    {
        return $this->belongsTo(TraceRecord::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
