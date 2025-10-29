<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasOrganizationScope;
use App\Events\NotificationCreated;
use App\Events\NotificationRead;

class Notification extends Model
{
    use HasFactory, HasOrganizationScope, SoftDeletes;

    protected $fillable = [
        'user_id',
        'organization_id',
        'type',
        'title',
        'message',
        'cta_text',
        'cta_url',
        'is_read',
        'is_blocking',
        'priority',
        'expires_at',
        'metadata',
        'notification_group',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'is_blocking' => 'boolean',
        'read_at' => 'datetime',
        'expires_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeBlocking($query)
    {
        return $query->where('is_blocking', true);
    }

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    public function scopeHighPriority($query)
    {
        return $query->where('priority', '>=', 1);
    }

    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
        
        broadcast(new NotificationRead($this))->toOthers();
        
        \App\Models\NotificationAuditLog::create([
            'user_id' => auth()->id(),
            'notification_id' => $this->id,
            'organization_id' => $this->organization_id,
            'action' => 'read',
            'status' => 'success',
            'ip_address' => request()->ip(),
        ]);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->organization_id && auth()->check()) {
                $model->organization_id = auth()->user()->organization_id;
            }
        });

        static::created(function ($model) {
            broadcast(new NotificationCreated($model));
            
            \App\Models\NotificationAuditLog::create([
                'notification_id' => $model->id,
                'organization_id' => $model->organization_id,
                'action' => 'created',
                'status' => 'success',
                'details' => json_encode([
                    'type' => $model->type,
                    'is_blocking' => $model->is_blocking,
                ]),
            ]);
        });

        static::deleting(function ($model) {
            \App\Models\NotificationAuditLog::create([
                'notification_id' => $model->id,
                'organization_id' => $model->organization_id,
                'action' => 'deleted',
                'status' => 'success',
                'details' => json_encode([
                    'type' => $model->type,
                    'is_blocking' => $model->is_blocking,
                    'deleted_at' => now(),
                ]),
            ]);
        });
    }
}
