<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('packages')->where('id', 'free')->update([
            'name' => 'Free Tier',
            'description' => 'Dùng thử tất cả tính năng trong 15 ngày',
            'has_traceability' => true,
            'has_document_management' => true,
            'has_e_signatures' => true,
            'has_certificates' => true,
            'has_data_retention' => true,
            'has_archival' => true,
            'has_compliance_report' => true,
            'support_level' => 'email',
            'features' => json_encode([
                'Dùng thử 15 ngày tất cả tính năng',
                'Truy xuất nguồn gốc (Traceability)',
                'Quản lý Tài liệu',
                'Chữ ký điện tử (E-Signatures)',
                'Quản lý Chứng chỉ (Certificates)',
                'Lưu trữ Dữ liệu (Data Retention)',
                'Lưu trữ cho Admin (Archival)',
                'Báo cáo Tuân thủ (Compliance Report)',
                'Hỗ trợ Email'
            ]),
        ]);

        DB::table('packages')->where('id', 'basic')->update([
            'name' => 'Basic',
            'description' => 'Tính năng cơ bản cho tuân thủ',
            'has_traceability' => true,
            'has_document_management' => true,
            'has_e_signatures' => false,
            'has_certificates' => false,
            'has_data_retention' => false,
            'has_archival' => false,
            'has_compliance_report' => true,
            'support_level' => 'email',
            'features' => json_encode([
                '500 CTE records/tháng',
                '10 tài liệu',
                '1 người dùng',
                'Truy xuất nguồn gốc (Traceability)',
                'Quản lý Tài liệu cơ bản',
                'Báo cáo Tuân thủ (Compliance Report)',
                'Hỗ trợ Email'
            ]),
        ]);

        DB::table('packages')->where('id', 'premium')->update([
            'name' => 'Premium',
            'description' => 'Tính năng nâng cao cho doanh nghiệp',
            'has_traceability' => true,
            'has_document_management' => true,
            'has_e_signatures' => true,
            'has_certificates' => true,
            'has_data_retention' => false,
            'has_archival' => false,
            'has_compliance_report' => true,
            'support_level' => 'email_chat',
            'features' => json_encode([
                '2,500 CTE records/tháng',
                'Tài liệu không giới hạn',
                '3 người dùng',
                'Truy xuất nguồn gốc (Traceability)',
                'Quản lý Tài liệu',
                'Chữ ký điện tử (E-Signatures)',
                'Quản lý Chứng chỉ (Certificates)',
                'Báo cáo Tuân thủ (Compliance Report)',
                'Hỗ trợ Email & Chat'
            ]),
        ]);

        DB::table('packages')->where('id', 'enterprise')->update([
            'name' => 'Enterprise',
            'description' => 'Tất cả tính năng cho t��� chức lớn',
            'has_traceability' => true,
            'has_document_management' => true,
            'has_e_signatures' => true,
            'has_certificates' => true,
            'has_data_retention' => true,
            'has_archival' => true,
            'has_compliance_report' => true,
            'support_level' => 'dedicated',
            'features' => json_encode([
                '5,000+ CTE records/tháng',
                'Tài liệu không giới hạn',
                '5+ người dùng',
                'Truy xuất nguồn gốc (Traceability)',
                'Quản lý Tài liệu',
                'Chữ ký điện tử (E-Signatures)',
                'Quản lý Chứng chỉ (Certificates)',
                'Lưu trữ Dữ liệu (Data Retention)',
                'Lưu trữ cho Admin (Archival)',
                'Báo cáo Tuân thủ (Compliance Report)',
                'Quản lý tài khoản riêng',
                'Hỗ trợ ưu tiên 24/7'
            ]),
        ]);
    }

    public function down(): void
    {
        // Revert to original features
        DB::table('packages')->where('id', 'free')->update([
            'features' => json_encode([
                '50 CTE records/month (permanent)',
                '1 document',
                '1 user',
                'Basic traceability',
                'Community support'
            ]),
        ]);
    }
};
