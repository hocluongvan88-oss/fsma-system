<?php

namespace App\Console\Commands;

use App\Models\Notification;
use Illuminate\Console\Command;

class RestoreNotifications extends Command
{
    protected $signature = 'notifications:restore {notification_id? : Specific notification ID to restore}';
    protected $description = 'Restore soft-deleted notifications';

    public function handle()
    {
        $notificationId = $this->argument('notification_id');

        if ($notificationId) {
            $notification = Notification::onlyTrashed()
                ->find($notificationId);

            if (!$notification) {
                $this->error("Notification {$notificationId} not found in trash");
                return 1;
            }

            $notification->restore();
            $this->info("Successfully restored notification {$notificationId}");
        } else {
            $count = Notification::onlyTrashed()->restore();
            $this->info("Successfully restored {$count} notifications");
        }

        return 0;
    }
}
