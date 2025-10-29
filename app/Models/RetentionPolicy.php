<?php

namespace App\Models;

use App\Traits\HasOrganizationScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RetentionPolicy extends Model
{
    use HasFactory, SoftDeletes, HasOrganizationScope;

    protected $fillable = [
        'organization_id',
        'policy_name',
        'data_type',
        'retention_months',
        'backup_before_deletion',
        'is_active',
        'description',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'backup_before_deletion' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function retentionLogs()
    {
        return $this->hasMany(RetentionLog::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByDataType($query, string $dataType)
    {
        return $query->where('data_type', $dataType);
    }

    public static function getDefaultPolicies(): array
    {
        return [
            // FSMA 204 Protected Data - NEVER delete (0 = indefinite retention)
            'trace_records' => 0,
            'cte_events' => 0,
            'trace_relationships' => 0,
            'audit_logs' => 0,
            'e_signatures' => 0,
            'documents' => 0,
            'document_versions' => 0,
            
            // Non-critical operational data - Can be deleted
            'error_logs' => 6,
            'notifications' => 3,
        ];
    }

    public static function isProtectedDataType(string $dataType): bool
    {
        $protectedTypes = [
            'trace_records',
            'cte_events',
            'trace_relationships',
            'audit_logs',
            'e_signatures',
            'documents',
            'document_versions',
        ];
        
        return in_array($dataType, $protectedTypes);
    }

    public static function getProtectionReason(string $dataType): ?string
    {
        $reasons = [
            'trace_records' => 'Core traceability data required for FSMA 204 compliance',
            'cte_events' => 'Immutable Critical Tracking Events per FSMA 204 Section 204.6',
            'trace_relationships' => 'Audit trail required for traceability chain integrity',
            'audit_logs' => 'Compliance and regulatory audit requirement',
            'e_signatures' => 'Legal requirement per 21 CFR Part 11 (Electronic Records)',
            'documents' => 'Business critical documents must be retained indefinitely',
            'document_versions' => 'Document version history required for compliance',
        ];
        
        return $reasons[$dataType] ?? null;
    }
}
