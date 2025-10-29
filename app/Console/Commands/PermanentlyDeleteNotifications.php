<?php

namespace App\Console\Commands;

use App\Models\Notification;
use Illuminate\Console\Command;

class PermanentlyDeleteNotifications extends Command
{
    protected $signature = 'notifications:permanently-delete {--days=180 : Number of days to keep soft-deleted}';
    protected $description = 'Permanently delete soft-deleted notifications older than specified days';

    public function handle()
    {
        $days = $this->option('days');
        $cutoffDate = now()->subDays($days);

        $permanentlyDeleted = Notification::onlyTrashed()
            ->where('deleted_at', '<', $cutoffDate)
            ->count();

        $this->info("Found {$permanentlyDeleted} soft-deleted notifications older than {$days} days");

        $deleted = Notification::onlyTrashed()
            ->where('deleted_at', '<', $cutoffDate)
            ->forceDelete();

        $this->info("Successfully permanently deleted {$deleted} notifications");

        return 0;
    }
}
