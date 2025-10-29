<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            // Add missing file metadata columns
            if (!Schema::hasColumn('documents', 'file_name')) {
                $table->string('file_name')->nullable()->after('file_path');
            }
            if (!Schema::hasColumn('documents', 'file_type')) {
                $table->string('file_type')->nullable()->after('file_name');
            }
            if (!Schema::hasColumn('documents', 'file_size')) {
                $table->unsignedBigInteger('file_size')->nullable()->after('file_type');
            }
            
            // Add missing document metadata columns
            if (!Schema::hasColumn('documents', 'description')) {
                $table->text('description')->nullable()->after('title');
            }
            if (!Schema::hasColumn('documents', 'review_date')) {
                $table->date('review_date')->nullable()->after('expiry_date');
            }
            if (!Schema::hasColumn('documents', 'uploaded_by')) {
                $table->foreignId('uploaded_by')->nullable()->after('version')
                    ->constrained('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('documents', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
            if (!Schema::hasColumn('documents', 'metadata')) {
                $table->json('metadata')->nullable()->after('approved_at');
            }
            
            // Add soft deletes for archival
            if (!Schema::hasColumn('documents', 'deleted_at')) {
                $table->softDeletes();
            }
        });
        
        // Fix document type enum to match controller
        DB::statement("ALTER TABLE documents MODIFY COLUMN type ENUM('traceability_plan', 'sop', 'fda_correspondence', 'training_material', 'audit_report', 'other') NOT NULL");
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn([
                'file_name',
                'file_type', 
                'file_size',
                'description',
                'review_date',
                'uploaded_by',
                'approved_at',
                'metadata',
                'deleted_at'
            ]);
        });
        
        // Revert type enum
        DB::statement("ALTER TABLE documents MODIFY COLUMN type ENUM('traceability_plan', 'sop', 'fda_correspondence', 'training', 'other') NOT NULL");
    }
};
