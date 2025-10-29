<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $types = ['documents', 'document_versions'];
        
        foreach ($types as $type) {
            $exists = DB::table('retention_policies')
                ->where('data_type', $type)
                ->exists();

            if (!$exists) {
                DB::table('retention_policies')->insert([
                    'policy_name' => ucfirst($type) . ' Retention',
                    'data_type' => $type,
                    'retention_months' => 0,
                    'backup_before_deletion' => false,
                    'is_active' => true,
                    'description' => 'FSMA 204 protected data',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('retention_policies')
            ->whereIn('data_type', ['documents', 'document_versions'])
            ->delete();
    }
};
