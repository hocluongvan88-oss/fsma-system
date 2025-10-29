<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'is_enabled',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function isEnabled(int $userId, string $notificationType): bool
    {
        $preference = self::where('user_id', $userId)
            ->where('type', $notificationType)
            ->first();

        // Default to enabled if no preference exists
        return $preference ? $preference->is_enabled : true;
    }

    public static function getFrequency(int $userId, string $notificationType): string
    {
        $preference = self::where('user_id', $userId)
            ->where('type', $notificationType)
            ->first();

        return $preference ? $preference->frequency : 'real-time';
    }
}
