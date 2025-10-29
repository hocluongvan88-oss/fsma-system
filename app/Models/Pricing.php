<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pricing extends Model
{

    protected $table = 'pricing';
    
    protected $fillable = [
        'package_id',
        'package_name',
        'price_monthly',
        'price_yearly',
        'list_price_monthly',
        'list_price_yearly',
        'max_cte_records_monthly',
        'max_documents',
        'max_users',
        'is_active',
    ];

    protected $casts = [
        'price_monthly' => 'decimal:2',
        'price_yearly' => 'decimal:2',
        'list_price_monthly' => 'decimal:2',
        'list_price_yearly' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function getDiscountMonthly(): float
    {
        if ($this->list_price_monthly == 0) return 0;
        return (($this->list_price_monthly - $this->price_monthly) / $this->list_price_monthly) * 100;
    }

    public function getDiscountYearly(): float
    {
        if ($this->list_price_yearly == 0) return 0;
        return (($this->list_price_yearly - $this->price_yearly) / $this->list_price_yearly) * 100;
    }
}
