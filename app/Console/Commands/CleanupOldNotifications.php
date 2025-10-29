<?php

namespace App\Console\Commands;

use App\Models\Notification;
use Illuminate\Console\Command;

class CleanupOldNotifications extends Command
{
    protected $signature = 'notifications:cleanup-old {--days=90 : Number of days to keep}';
    protected $description = 'Soft delete old read notifications';

    public function handle()
    {
        $days = $this->option('days');
        $cutoffDate = now()->subDays($days);

        $oldNotifications = Notification::where('is_read', true)
            ->where('created_at', '<', $cutoffDate)
            ->count();

        $this->info("Found {$oldNotifications} old read notifications (older than {$days} days)");

        $deleted = Notification::where('is_read', true)
            ->where('created_at', '<', $cutoffDate)
            ->delete();

        $this->info("Successfully soft-deleted {$deleted} old notifications");

        return 0;
    }
}
