<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\CrmIntegrationService;
use App\Traits\HasOrganizationScope;

class Lead extends Model
{
    use HasOrganizationScope;

    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'company_name',
        'industry',
        'message',
        'status',
        'source',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_content',
        'ip_address',
        'user_agent',
        'contacted_at',
        'notes',
        'organization_id', // Added organization_id for multi-tenancy
    ];

    protected $casts = [
        'contacted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::created(function (Lead $lead) {
            // Send to CRM systems asynchronously
            if (config('services.crm.auto_sync')) {
                CrmIntegrationService::sendToAllCrms($lead);
            }
        });

        static::updated(function (Lead $lead) {
            // Sync status changes to CRM
            if ($lead->isDirty('status') && config('services.crm.auto_sync')) {
                CrmIntegrationService::sendToAllCrms($lead);
            }
        });
    }

    // Scopes
    public function scopeNew($query)
    {
        return $query->where('status', 'new');
    }

    public function scopeContacted($query)
    {
        return $query->where('status', 'contacted');
    }

    public function scopeQualified($query)
    {
        return $query->where('status', 'qualified');
    }

    public function scopeConverted($query)
    {
        return $query->where('status', 'converted');
    }

    public function scopeFromLandingPage($query)
    {
        return $query->where('source', 'landing_page');
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
                     ->whereYear('created_at', now()->year);
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek(),
        ]);
    }

    // Accessors
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'new' => 'Mới',
            'contacted' => 'Đã liên hệ',
            'qualified' => 'Đủ điều kiện',
            'converted' => 'Đã chuyển đổi',
            'rejected' => 'Từ chối',
            default => $this->status,
        };
    }

    public function getSourceLabelAttribute()
    {
        return match($this->source) {
            'landing_page' => 'Landing Page',
            'referral' => 'Giới thiệu',
            'organic' => 'Tìm kiếm',
            'paid_ads' => 'Quảng cáo trả phí',
            default => $this->source,
        };
    }
}
