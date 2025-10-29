<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('
            UPDATE organizations o
            SET package_id = (
                SELECT DISTINCT u.package_id
                FROM users u
                WHERE u.organization_id = o.id
                AND u.package_id IS NOT NULL
                LIMIT 1
            )
            WHERE EXISTS (
                SELECT 1 FROM users u
                WHERE u.organization_id = o.id
                AND u.package_id IS NOT NULL
            )
        ');

        DB::statement('
            INSERT INTO organization_quotas (organization_id, feature_name, used_count, limit_count, created_at, updated_at)
            SELECT 
                o.id,
                "cte_records_monthly",
                0,
                COALESCE(p.max_cte_records_monthly, 0),
                NOW(),
                NOW()
            FROM organizations o
            LEFT JOIN packages p ON o.package_id = p.id
            WHERE NOT EXISTS (
                SELECT 1 FROM organization_quotas oq
                WHERE oq.organization_id = o.id
                AND oq.feature_name = "cte_records_monthly"
            )
        ');

        DB::statement('
            INSERT INTO organization_quotas (organization_id, feature_name, used_count, limit_count, created_at, updated_at)
            SELECT 
                o.id,
                "documents",
                0,
                COALESCE(p.max_documents, 0),
                NOW(),
                NOW()
            FROM organizations o
            LEFT JOIN packages p ON o.package_id = p.id
            WHERE NOT EXISTS (
                SELECT 1 FROM organization_quotas oq
                WHERE oq.organization_id = o.id
                AND oq.feature_name = "documents"
            )
        ');

        DB::statement('
            INSERT INTO organization_quotas (organization_id, feature_name, used_count, limit_count, created_at, updated_at)
            SELECT 
                o.id,
                "users",
                (SELECT COUNT(*) FROM users u WHERE u.organization_id = o.id),
                COALESCE(p.max_users, 0),
                NOW(),
                NOW()
            FROM organizations o
            LEFT JOIN packages p ON o.package_id = p.id
            WHERE NOT EXISTS (
                SELECT 1 FROM organization_quotas oq
                WHERE oq.organization_id = o.id
                AND oq.feature_name = "users"
            )
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DELETE FROM organization_quotas');
    }
};
