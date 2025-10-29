<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'type',
        'language',
        'title',
        'message',
        'cta_text',
        'cta_url',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public static function getTemplate(string $type, string $language = 'en', ?int $organizationId = null)
    {
        $query = self::where('type', $type)
            ->where('language', $language)
            ->where('is_active', true);

        if ($organizationId) {
            $query->where(function ($q) use ($organizationId) {
                $q->where('organization_id', $organizationId)
                  ->orWhereNull('organization_id'); // Fall back to global template
            });
        }

        return $query->first();
    }
}
