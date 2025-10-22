<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\DigitalCertificateService;
use App\Services\TwoFactorAuthService;
use Illuminate\Database\Seeder;

class FSMA204ComplianceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $certificateService = app(DigitalCertificateService::class);
        $twoFAService = app(TwoFactorAuthService::class);

        // Get all admin and manager users
        $users = User::whereIn('role', ['admin', 'manager'])->get();

        foreach ($users as $user) {
            // Generate digital certificate if not exists
            if (!$user->certificate_id) {
                try {
                    $certificate = $certificateService->generateCertificate($user, 2048, 365);
                    echo "Generated certificate for {$user->email}\n";
                } catch (\Exception $e) {
                    echo "Failed to generate certificate for {$user->email}: {$e->getMessage()}\n";
                }
            }

            // Enable 2FA if not already enabled
            if (!$user->two_fa_enabled) {
                try {
                    $secret = $twoFAService->generateSecret($user);
                    // For seeding, we'll use a test code (in production, user would scan QR)
                    // This is just to demonstrate the setup
                    echo "2FA secret generated for {$user->email} (manual setup required)\n";
                } catch (\Exception $e) {
                    echo "Failed to setup 2FA for {$user->email}: {$e->getMessage()}\n";
                }
            }
        }

        echo "FSMA 204 compliance seeding completed.\n";
    }
}
