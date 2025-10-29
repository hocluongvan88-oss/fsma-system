<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasOrganizationScope;
use Illuminate\Support\Facades\Auth;

class Partner extends Model
{
    use HasFactory, HasOrganizationScope;

    protected $fillable = [
        'partner_name',
        'partner_type',
        'contact_person',
        'email',
        'phone',
        'address',
        'gln',
        'organization_id',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($partner) {
            if (empty($partner->organization_id) && Auth::check()) {
                $partner->organization_id = Auth::user()->organization_id;
            }
        });
    }

    // Relationships
    public function cteEvents()
    {
        return $this->hasMany(CTEEvent::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    // Scopes
    public function scopeSuppliers($query)
    {
        return $query->whereIn('partner_type', ['supplier', 'both']);
    }

    public function scopeCustomers($query)
    {
        return $query->whereIn('partner_type', ['customer', 'both']);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('partner_type', $type);
    }

    public function scopeForOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    // Helper methods
    public function isSupplier(): bool
    {
        return in_array($this->partner_type, ['supplier', 'both']);
    }

    public function isCustomer(): bool
    {
        return in_array($this->partner_type, ['customer', 'both']);
    }

    public function hasGLN(): bool
    {
        return !empty($this->gln);
    }
}
