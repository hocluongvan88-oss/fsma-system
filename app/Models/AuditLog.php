<?php

namespace App\Models;

use App\Traits\HasOrganizationScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory, HasOrganizationScope;

    public $timestamps = false;

    protected $fillable = [
        'organization_id',
        'user_id',
        'action',
        'table_name',
        'record_id',
        'integrity_hash',
    ];
    protected $guarded = ['*'];

    protected $appends = ['integrity_hash'];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->organization_id && auth()->check()) {
                $model->organization_id = auth()->user()->organization_id;
            }
            
            $model->integrity_hash = hash('sha256', json_encode([
                $model->user_id,
                $model->action,
                $model->table_name,
                $model->record_id,
                $model->created_at ?? now(),
            ]));
        });

        // Prevent updates to audit logs
        static::updating(function ($model) {
            throw new \Exception('Audit logs are immutable and cannot be modified');
        });

        // Prevent deletion of audit logs
        static::deleting(function ($model) {
            throw new \Exception('Audit logs are immutable and cannot be deleted');
        });
    }

    public static function createLog(array $data): self
    {
        $log = new self();
        $log->user_id = $data['user_id'] ?? null;
        $log->action = $data['action'];
        $log->table_name = $data['table_name'] ?? null;
        $log->record_id = $data['record_id'] ?? null;
        $log->organization_id = $data['organization_id'] ?? auth()->user()?->organization_id;
        $log->created_at = $data['created_at'] ?? now();
        $log->save();

        if (isset($data['old_values']) || isset($data['new_values']) || isset($data['ip_address']) || isset($data['user_agent'])) {
            \DB::table('audit_logs_details')->insert([
                'audit_log_id' => $log->id,
                'organization_id' => $log->organization_id,
                'ip_address' => $data['ip_address'] ?? null,
                'user_agent' => $data['user_agent'] ?? null,
                'old_values' => isset($data['old_values']) ? json_encode($data['old_values']) : null,
                'new_values' => isset($data['new_values']) ? json_encode($data['new_values']) : null,
            ]);
        }

        return $log;
    }

    public function verifyIntegrity(): bool
    {
        if (!$this->integrity_hash) {
            return false;
        }

        $expectedHash = hash('sha256', json_encode([
            $this->user_id,
            $this->action,
            $this->table_name,
            $this->record_id,
            $this->created_at,
        ]));

        return hash_equals($expectedHash, $this->integrity_hash);
    }

    public function details()
    {
        return $this->hasOne(\App\Models\AuditLogDetail::class, 'audit_log_id');
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByTable($query, $tableName)
    {
        return $query->where('table_name', $tableName);
    }

    public function scopeByRecord($query, $tableName, $recordId)
    {
        return $query->where('table_name', $tableName)
                     ->where('record_id', $recordId);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeForOrganization($query, $organizationId = null)
    {
        $organizationId = $organizationId ?? auth()->user()?->organization_id;
        
        return $query->whereHas('user', function ($q) use ($organizationId) {
            $q->where('organization_id', $organizationId);
        });
    }

    public function getChanges(): array
    {
        $changes = [];
        
        // Load details if not already loaded
        if (!$this->relationLoaded('details')) {
            $this->load('details');
        }
        
        if (!$this->details) {
            return $changes;
        }
        
        $oldValues = json_decode($this->details->old_values, true) ?? [];
        $newValues = json_decode($this->details->new_values, true) ?? [];
        
        if (empty($oldValues) || empty($newValues)) {
            return $changes;
        }

        foreach ($newValues as $key => $newValue) {
            $oldValue = $oldValues[$key] ?? null;
            
            if ($oldValue !== $newValue) {
                $changes[$key] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        return $changes;
    }
    
    public function getOldValuesAttribute()
    {
        if (!$this->relationLoaded('details')) {
            $this->load('details');
        }
        return $this->details ? json_decode($this->details->old_values, true) : null;
    }
    
    public function getNewValuesAttribute()
    {
        if (!$this->relationLoaded('details')) {
            $this->load('details');
        }
        return $this->details ? json_decode($this->details->new_values, true) : null;
    }
    
    public function getIpAddressAttribute()
    {
        if (!$this->relationLoaded('details')) {
            $this->load('details');
        }
        return $this->details?->ip_address;
    }
    
    public function getUserAgentAttribute()
    {
        if (!$this->relationLoaded('details')) {
            $this->load('details');
        }
        return $this->details?->user_agent;
    }
}
