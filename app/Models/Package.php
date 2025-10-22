<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'slug',
        'description',
        'max_cte_records_monthly',
        'max_documents',
        'max_users',
        'monthly_list_price',
        'monthly_selling_price',
        'yearly_list_price',
        'yearly_selling_price',
        'currency',
        'show_promotion',
        'promotion_text',
        'features',
        'is_popular',
        'is_highlighted',
        'is_visible',
        'is_selectable',
        'sort_order', // Đổi từ display_order về sort_order để khớp với database
        'stripe_product_id',
        'stripe_monthly_price_id',
        'stripe_yearly_price_id',
    ];

    protected $casts = [
        'features' => 'array',
        'show_promotion' => 'boolean',
        'is_popular' => 'boolean',
        'is_highlighted' => 'boolean',
        'is_visible' => 'boolean',
        'is_selectable' => 'boolean',
        'monthly_list_price' => 'decimal:2',
        'monthly_selling_price' => 'decimal:2',
        'yearly_list_price' => 'decimal:2',
        'yearly_selling_price' => 'decimal:2',
    ];

    // Relationships
    public function users()
    {
        return $this->hasMany(User::class, 'package_id', 'id');
    }

    // Scopes
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    public function scopeSelectable($query)
    {
        return $query->where('is_selectable', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    // Helper methods
    public function isFree(): bool
    {
        return $this->id === 'free';
    }

    public function hasUnlimitedCte(): bool
    {
        return $this->max_cte_records_monthly === 0 || $this->max_cte_records_monthly === null;
    }

    public function hasUnlimitedDocuments(): bool
    {
        return $this->max_documents === 0 || $this->max_documents === null || $this->max_documents >= 999999;
    }

    public function hasUnlimitedUsers(): bool
    {
        return $this->max_users === 0 || $this->max_users === null || $this->max_users >= 999999;
    }

    public function getMonthlyDiscount(): ?float
    {
        if (!$this->monthly_list_price || !$this->monthly_selling_price) {
            return null;
        }
        return (($this->monthly_list_price - $this->monthly_selling_price) / $this->monthly_list_price) * 100;
    }

    public function getYearlyDiscount(): ?float
    {
        if (!$this->yearly_list_price || !$this->yearly_selling_price) {
            return null;
        }
        return (($this->yearly_list_price - $this->yearly_selling_price) / $this->yearly_list_price) * 100;
    }

    public function getYearlySavings(): ?float
    {
        if (!$this->monthly_selling_price || !$this->yearly_selling_price) {
            return null;
        }
        $monthlyTotal = $this->monthly_selling_price * 12;
        if ($monthlyTotal == 0) {
            return null;
        }
        return (($monthlyTotal - $this->yearly_selling_price) / $monthlyTotal) * 100;
    }

    public function toViewArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'max_cte_records' => $this->max_cte_records_monthly,
            'max_documents' => $this->max_documents,
            'max_users' => $this->max_users,
            'monthly_list_price' => $this->monthly_list_price,
            'monthly_price' => $this->monthly_selling_price,
            'yearly_list_price' => $this->yearly_list_price,
            'yearly_price' => $this->yearly_selling_price,
            'currency' => $this->currency,
            'show_promotion' => $this->show_promotion,
            'promotion_text' => $this->promotion_text,
            'features' => $this->features ?? [],
            'is_popular' => $this->is_popular,
            'is_highlighted' => $this->is_highlighted,
        ];
    }
}
