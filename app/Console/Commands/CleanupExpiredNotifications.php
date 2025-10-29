<?php

namespace App\Console\Commands;

use App\Models\Notification;
use Illuminate\Console\Command;

class CleanupExpiredNotifications extends Command
{
    protected $signature = 'notifications:cleanup-expired {--dry-run}';
    protected $description = 'Delete expired notifications based on expires_at field';

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        $expiredNotifications = Notification::where('expires_at', '<', now())
            ->count();

        $this->info("Found {$expiredNotifications} expired notifications");

        if ($dryRun) {
            $this->info('DRY RUN: No notifications were deleted');
            return 0;
        }

        $deleted = Notification::where('expires_at', '<', now())
            ->delete();

        $this->info("Successfully deleted {$deleted} expired notifications");

        return 0;
    }
}
