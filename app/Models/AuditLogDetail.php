<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasOrganizationScope;

class AuditLogDetail extends Model
{
    use HasFactory, HasOrganizationScope;

    protected $table = 'audit_logs_details';
    
    public $timestamps = false;

    protected $fillable = [
        'audit_log_id',
        'organization_id',
        'ip_address',
        'user_agent',
        'old_values',
        'new_values',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    // Relationships
    public function auditLog()
    {
        return $this->belongsTo(AuditLog::class, 'audit_log_id');
    }
    
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
