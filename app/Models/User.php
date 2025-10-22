<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'username',
        'email',
        'preferred_language', // Added preferred_language to fillable array to allow mass assignment
        'password',
        'full_name',
        'role',
        'package_id',
        'max_cte_records_monthly',
        'max_documents',
        'max_users',
        'is_active',
        'last_login',
        'organization_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    // Relationships
    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    public function eSignatures()
    {
        return $this->hasMany(ESignature::class);
    }

    public function digitalCertificate()
    {
        return $this->hasOne(DigitalCertificate::class);
    }

    public function digitalCertificates()
    {
        return $this->hasMany(DigitalCertificate::class);
    }

    public function twoFALogs()
    {
        return $this->hasMany(TwoFALog::class);
    }

    public function cteEvents()
    {
        return $this->hasMany(CTEEvent::class, 'created_by');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    // Helper methods
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isManager(): bool
    {
        return in_array($this->role, ['admin', 'manager']);
    }

    public function canSign(): bool
    {
        return $this->is_active && in_array($this->role, ['admin', 'manager']);
    }

    // Package management methods
    public function isFree(): bool
    {
        return $this->package_id === 'free';
    }

    public function isBasic(): bool
    {
        return $this->package_id === 'basic';
    }

    public function isPremium(): bool
    {
        return $this->package_id === 'premium';
    }

    public function isEnterprise(): bool
    {
        return $this->package_id === 'enterprise';
    }

    public function hasFeature(string $feature): bool
    {
        $features = [
            'free' => ['basic_traceability'],
            'basic' => ['basic_traceability', 'export'],
            'premium' => ['basic_traceability', 'export', 'advanced_reports', 'api_access'],
            'enterprise' => ['basic_traceability', 'export', 'advanced_reports', 'api_access', 'e_signatures', 'compliance_reports'],
        ];

        return in_array($feature, $features[$this->package_id] ?? []);
    }

    public function getCteUsageThisMonth(): int
    {
        return $this->cteEvents()
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();
    }

    public function getCteUsagePercentage(): float
    {
        // Handle unlimited packages
        if ($this->max_cte_records_monthly === 0 || $this->max_cte_records_monthly === null) {
            return 0;
        }
        
        $usage = $this->getCteUsageThisMonth();
        return ($usage / $this->max_cte_records_monthly) * 100;
    }

    public function canCreateCteRecord(): bool
    {
        // Unlimited package check
        if ($this->max_cte_records_monthly === 0 || $this->max_cte_records_monthly === null) {
            return true;
        }
        
        $usage = $this->getCteUsageThisMonth();
        
        // Simple quota check without triggering notifications here
        // Notifications will be handled by the controller/service layer
        return $usage < $this->max_cte_records_monthly;
    }

    public function getDocumentCount(): int
    {
        $query = \DB::table('documents');
        
        if (Schema::hasColumn('documents', 'organization_id')) {
            $query->where('organization_id', $this->organization_id);
        }
        
        return $query->count();
    }

    public function canUploadDocument(): bool
    {
        // Unlimited documents check
        if ($this->max_documents === 0 || $this->max_documents === null || $this->max_documents >= 999999) {
            return true;
        }
        
        return $this->getDocumentCount() < $this->max_documents;
    }

    public function getActiveUserCount(): int
    {
        $query = \DB::table('users')->where('is_active', true);
        
        if (Schema::hasColumn('users', 'organization_id') && $this->organization_id) {
            $query->where('organization_id', $this->organization_id);
        }
        
        $query->where('email', '!=', 'admin@fsma204.com');
        
        return $query->count();
    }

    public function canCreateUser(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }
        
        // Enterprise has unlimited users
        if ($this->package_id === 'enterprise' || $this->max_users >= 999999) {
            return true;
        }
        
        return $this->getActiveUserCount() < $this->max_users;
    }

    public function scopeForOrganization($query, $organizationId = null)
    {
        $organizationId = $organizationId ?? auth()->user()?->organization_id;
        return $query->where('organization_id', $organizationId);
    }
}
