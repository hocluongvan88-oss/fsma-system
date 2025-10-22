<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Data Retention: Delete non-critical data daily at 2 AM
        $schedule->command('retention:cleanup')->dailyAt('02:00');
        
        // Data Archival: Move old CTE data to cold storage monthly at 3 AM on 1st
        $schedule->command('archival:execute')->cron(config('archival.schedule', '0 3 1 * *'));
        
        // Cleanup old audit logs (older than 2 years)
        $schedule->call(function () {
            \App\Models\AuditLog::where('created_at', '<', now()->subYears(2))->delete();
        })->monthly();
        
        // Cleanup old resolved errors (older than 30 days)
        $schedule->command('errors:cleanup --days=30')->daily();
        
        // Cleanup expired sessions
        $schedule->command('session:gc')->daily();

        $schedule->call(function () {
            $certificates = \App\Models\DigitalCertificate::active()->get();
            foreach ($certificates as $cert) {
                if ($cert->crl_url) {
                    app(\App\Services\DigitalCertificateService::class)->verifyCRLStatus($cert);
                }
                if ($cert->ocsp_url) {
                    app(\App\Services\DigitalCertificateService::class)->verifyOCSPStatus($cert);
                }
            }
        })->daily();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
