<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ensure the role column exists and has a default value
        if (Schema::hasTable('users')) {
            if (!Schema::hasColumn('users', 'role')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->string('role')->default('operator')->after('email');
                });
            }

            // Set admin role for users with admin email or specific conditions
            $adminEmails = [
                'admin@fsma204.com',
                'admin@example.com',
            ];

            foreach ($adminEmails as $email) {
                DB::table('users')
                    ->where('email', $email)
                    ->update(['role' => 'admin']);
            }

            // Log the migration
            \Log::info('Admin role migration completed', [
                'timestamp' => now(),
                'admin_emails_updated' => $adminEmails,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration cannot be safely reversed as it modifies data
        \Log::warning('Admin role migration cannot be reversed');
    }
};
