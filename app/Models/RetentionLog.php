<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RetentionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'retention_policy_id',
        'data_type',
        'records_deleted',
        'records_backed_up',
        'backup_file_path',
        'executed_at',
        'executed_by',
        'status',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'executed_at' => 'datetime',
        ];
    }

    public function retentionPolicy()
    {
        return $this->belongsTo(RetentionPolicy::class);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('executed_at', '>=', now()->subDays($days));
    }
}
