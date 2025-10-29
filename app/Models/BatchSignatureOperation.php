<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasOrganizationScope;

class BatchSignatureOperation extends Model
{
    use HasFactory, HasOrganizationScope;

    protected $table = 'batch_signature_operations';

    protected $fillable = [
        'batch_operation_id',
        'user_id',
        'operation_type',
        'total_signatures',
        'processed_count',
        'success_count',
        'failed_count',
        'status',
        'reason',
        'details',
        'error_log',
        'ip_address',
        'user_agent',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'error_log' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function signatures()
    {
        return ESignature::where('batch_operation_id', $this->batch_operation_id)->get();
    }

    public function scopeByOperationType($query, string $type)
    {
        return $query->where('operation_type', $type);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
