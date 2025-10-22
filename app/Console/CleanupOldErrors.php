<?php

namespace App\Console\Commands;

use App\Models\ErrorLog;
use Illuminate\Console\Command;

class CleanupOldErrors extends Command
{
    protected $signature = 'errors:cleanup {--days=30 : Number of days to keep}';
    protected $description = 'Clean up old resolved errors from the database';

    public function handle()
    {
        $days = $this->option('days');
        $cutoffDate = now()->subDays($days);

        $deleted = ErrorLog::where('is_resolved', true)
            ->where('resolved_at', '<', $cutoffDate)
            ->delete();

        $this->info("Deleted $deleted old error logs.");
    }
}
