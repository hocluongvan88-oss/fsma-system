<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasOrganizationScope;

class TwoFALog extends Model
{
    use HasFactory, HasOrganizationScope;

    protected $table = 'two_fa_logs';

    protected $fillable = [
        'user_id',
        'method',
        'success',
        'ip_address',
        'user_agent',
        'failure_reason',
        'attempted_at',
        'organization_id',
    ];

    protected function casts(): array
    {
        return [
            'success' => 'boolean',
            'attempted_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
