<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganizationQuota extends Model
{
    protected $table = 'organization_quotas';

    protected $fillable = [
        'organization_id',
        'feature_name',
        'used_count',
        'limit_count',
        'reset_date',
    ];

    protected $casts = [
        'used_count' => 'integer',
        'limit_count' => 'integer',
        'reset_date' => 'date',
    ];

    // Relationships
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    // Helper methods
    public function isUnlimited(): bool
    {
        return $this->limit_count === 0 || $this->limit_count === null;
    }

    public function getUsagePercentage(): float
    {
        if ($this->isUnlimited()) {
            return 0;
        }

        return ($this->used_count / $this->limit_count) * 100;
    }

    public function getRemainingCount(): int
    {
        if ($this->isUnlimited()) {
            return PHP_INT_MAX;
        }

        return max(0, $this->limit_count - $this->used_count);
    }

    public function isExceeded(): bool
    {
        if ($this->isUnlimited()) {
            return false;
        }

        return $this->used_count >= $this->limit_count;
    }

    public function isNearLimit(int $threshold = 80): bool
    {
        if ($this->isUnlimited()) {
            return false;
        }

        return $this->getUsagePercentage() >= $threshold;
    }

    public function needsReset(): bool
    {
        if (!$this->reset_date) {
            return false;
        }

        return now()->isAfter($this->reset_date);
    }

    public function reset(): void
    {
        $this->used_count = 0;
        $this->reset_date = now()->addMonth();
        $this->save();
    }
}
