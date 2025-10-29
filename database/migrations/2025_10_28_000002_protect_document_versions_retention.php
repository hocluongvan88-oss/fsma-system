<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

class ProtectDocumentVersionsRetention extends Migration
{
    public function up()
    {
        $organizations = DB::table('organizations')->whereNull('deleted_at')->get();
        
        foreach ($organizations as $org) {
            $exists = DB::table('retention_policies')
                ->where('organization_id', $org->id)
                ->where('data_type', 'document_versions')
                ->exists();
            
            if (!$exists) {
                DB::table('retention_policies')->insert([
                    'organization_id' => $org->id,
                    'policy_name' => "Document Versions Retention - {$org->name} (ID: {$org->id})",
                    'data_type' => 'document_versions',
                    'retention_months' => 0,
                    'backup_before_deletion' => 0,
                    'is_active' => 1,
                    'description' => 'FSMA 204 protected data - indefinite retention required',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down()
    {
        DB::table('retention_policies')
            ->where('data_type', 'document_versions')
            ->delete();
    }
}
