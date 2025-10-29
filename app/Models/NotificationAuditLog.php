<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationAuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'notification_id',
        'organization_id',
        'action',
        'status',
        'details',
        'ip_address',
    ];

    protected $casts = [
        'details' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function notification()
    {
        return $this->belongsTo(Notification::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
