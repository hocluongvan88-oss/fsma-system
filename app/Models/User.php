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
        'preferred_language',
        'password',
        'full_name',
        'is_active',
        'last_login',
        'subscription_status',
        'subscription_ends_at',
        'organization_id',
        'role',
        'is_system_admin',
    ];

    protected $guarded = [
        'id',
        'remember_token',
        'email_verified_at',
        'created_at',
        'updated_at',
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
            'is_system_admin' => 'boolean',
            'subscription_ends_at' => 'datetime',
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

    public function notificationPreferences()
    {
        return $this->hasMany(NotificationPreference::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
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

    public function scopeForCurrentOrganization($query)
    {
        $user = auth()->user();
        
        // Admin sees all users
        if ($user && $user->isAdmin()) {
            return $query;
        }
        
        // Regular users only see their organization
        if ($user && $user->organization_id) {
            return $query->where('organization_id', $user->organization_id);
        }
        
        // If no organization, only see self
        return $query->where('id', $user?->id);
    }

    public function scopeForOrganization($query, $organizationId = null)
    {
        $organizationId = $organizationId ?? auth()->user()?->organization_id;
        return $query->where('organization_id', $organizationId);
    }

    // Helper methods
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isSystemAdmin(): bool
    {
        return $this->is_system_admin === true;
    }

    public function isOrganizationAdmin(): bool
    {
        return $this->isAdmin() && !$this->isSystemAdmin();
    }

    public function isManager(): bool
    {
        return in_array($this->role, ['admin', 'manager']);
    }

    public function canSign(): bool
    {
        return $this->is_active && in_array($this->role, ['admin', 'manager']);
    }

    public function canManageUser(User $targetUser): bool
    {
        if ($this->isSystemAdmin()) {
            return true;
        }

        // Admin can manage anyone
        if ($this->isAdmin()) {
            return true;
        }

        // Cannot manage admin
        if ($targetUser->isAdmin()) {
            return false;
        }

        // Must be in same organization
        if ($this->organization_id !== $targetUser->organization_id) {
            return false;
        }

        // Manager can manage operators only
        if ($this->isManager() && $targetUser->role === 'operator') {
            return true;
        }

        return false;
    }

    public function canViewUser(User $targetUser): bool
    {
        if ($this->isSystemAdmin()) {
            return true;
        }

        // Admin can view anyone
        if ($this->isAdmin()) {
            return true;
        }

        // Can always view self
        if ($this->id === $targetUser->id) {
            return true;
        }

        // Cannot view admin
        if ($targetUser->isAdmin()) {
            return false;
        }

        // Must be in same organization
        return $this->organization_id === $targetUser->organization_id;
    }

    // Package management methods
    public function package()
    {
        return $this->organization->package() ?? Package::where('id', 'free')->first();
    }

    public function getPackage()
    {
        if (!$this->organization) {
            return Package::where('id', 'free')->first();
        }
        return $this->organization->getPackage();
    }

    public function isFree(): bool
    {
        return $this->getPackage()?->id === 'free';
    }

    public function isBasic(): bool
    {
        return $this->getPackage()?->id === 'basic';
    }

    public function isPremium(): bool
    {
        return $this->getPackage()?->id === 'premium';
    }

    public function isEnterprise(): bool
    {
        return $this->getPackage()?->id === 'enterprise';
    }

    public function hasFeature(string $feature): bool
    {
        if ($this->isSystemAdmin()) {
            return true;
        }

        $package = $this->getPackage();
        return $package ? $package->hasFeature($feature) : false;
    }

    public function getCteUsageThisMonth(): int
    {
        if (!$this->organization) {
            return 0;
        }

        return CTEEvent::where('organization_id', $this->organization->id)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();
    }

    public function getCteUsagePercentage(): float
    {
        if (!$this->organization) {
            return 0;
        }

        return $this->organization->getQuotaUsagePercentage('cte_records_monthly');
    }

    public function canCreateCteRecord(): bool
    {
        if ($this->isSystemAdmin()) {
            return true;
        }

        if (!$this->organization) {
            return false;
        }

        return $this->organization->canPerformAction('cte_records_monthly');
    }

    public function getDocumentCount(): int
    {
        if (!$this->organization) {
            return 0;
        }

        $query = \DB::table('documents');
        
        if (Schema::hasColumn('documents', 'organization_id')) {
            $query->where('organization_id', $this->organization->id);
        }
        
        return $query->count();
    }

    public function canUploadDocument(): bool
    {
        if ($this->isSystemAdmin()) {
            return true;
        }

        if (!$this->organization) {
            return false;
        }

        return $this->organization->canPerformAction('documents');
    }

    public function getActiveUserCount(): int
    {
        if (!$this->organization) {
            return 0;
        }

        return User::where('organization_id', $this->organization->id)
            ->where('is_active', true)
            ->where('email', '!=', 'admin@fsma204.com')
            ->count();
    }

    public function canCreateUser(): bool
    {
        if ($this->isSystemAdmin()) {
            return true;
        }

        if (!$this->organization) {
            return false;
        }

        return $this->organization->canPerformAction('users');
    }

    public function setRole(string $role): void
    {
        $currentUser = auth()->user();
        
        if (!$currentUser || !$currentUser->isAdmin()) {
            throw new \Exception('Only administrators can change user roles');
        }

        // Cannot change admin role
        if ($this->isAdmin()) {
            throw new \Exception('Cannot change administrator role');
        }

        // Only admins can create new admins
        if ($role === 'admin' && !$currentUser->isAdmin()) {
            throw new \Exception('Only administrator can create admin users');
        }
        
        $this->role = $role;
        $this->save();

        // Log the role change
        \Log::info('User role changed', [
            'changed_by' => $currentUser->id,
            'user_id' => $this->id,
            'old_role' => $this->getOriginal('role'),
            'new_role' => $role,
        ]);
    }

    public function setOrganization(int $organizationId): void
    {
        $currentUser = auth()->user();
        
        // Only admin can change user organization
        if (!$currentUser || !$currentUser->isAdmin()) {
            throw new \Exception('Only administrator can change user organization');
        }

        if ($this->isSystemAdmin()) {
            throw new \Exception('Cannot change system administrator organization');
        }

        // Cannot change admin organization
        if ($this->isAdmin()) {
            throw new \Exception('Cannot change administrator organization');
        }
        
        $this->organization_id = $organizationId;
        $this->save();

        // Log the organization change
        \Log::info('User organization changed', [
            'changed_by' => $currentUser->id,
            'user_id' => $this->id,
            'old_organization_id' => $this->getOriginal('organization_id'),
            'new_organization_id' => $organizationId,
        ]);
    }

    public function setPackage(string $packageId): void
    {
        $currentUser = auth()->user();
        
        if (!$currentUser || !$currentUser->isAdmin()) {
            throw new \Exception('Only administrators can change package');
        }

        if (!$this->organization) {
            throw new \Exception('User must belong to an organization');
        }

        // Non-admin users can only change packages in their organization
        if (!$currentUser->isAdmin() && $this->organization_id !== $currentUser->organization_id) {
            throw new \Exception('You can only change packages for users in your organization');
        }

        $oldPackage = $this->organization->package_id;
        $this->organization->package_id = $packageId;
        $this->organization->save();

        // Log the package change
        \Log::info('Organization package changed', [
            'changed_by' => $currentUser->id,
            'organization_id' => $this->organization->id,
            'old_package' => $oldPackage,
            'new_package' => $packageId,
        ]);
    }

    // Trial management methods
    /**
     * Check if user is currently on trial
     */
    public function isOnTrial(): bool
    {
        // User is on trial if subscription_status is 'active' and subscription_ends_at is in the future
        if ($this->subscription_status === 'active' && $this->subscription_ends_at) {
            return now()->isBefore($this->subscription_ends_at);
        }
        return false;
    }

    /**
     * Get the number of days remaining in the trial period
     */
    public function getTrialDaysRemaining(): int
    {
        if (!$this->isOnTrial()) {
            return 0;
        }
        
        return max(0, now()->diffInDays($this->subscription_ends_at, false));
    }
}
