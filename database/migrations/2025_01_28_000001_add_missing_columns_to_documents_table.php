<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            if (!Schema::hasColumn('documents', 'description')) {
                $table->text('description')->nullable()->after('type');
            }
            
            if (!Schema::hasColumn('documents', 'file_name')) {
                $table->string('file_name')->nullable()->after('file_path');
            }
            
            if (!Schema::hasColumn('documents', 'file_type')) {
                $table->string('file_type')->nullable()->after('file_name');
            }
            
            if (!Schema::hasColumn('documents', 'file_size')) {
                $table->integer('file_size')->nullable()->after('file_type');
            }
            
            if (!Schema::hasColumn('documents', 'review_date')) {
                $table->date('review_date')->nullable()->after('effective_date');
            }
            
            if (!Schema::hasColumn('documents', 'uploaded_by')) {
                $table->foreignId('uploaded_by')->nullable()->after('approved_by')->constrained('users')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('documents', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $dropColumns = [];
            
            if (Schema::hasColumn('documents', 'description')) {
                $dropColumns[] = 'description';
            }
            if (Schema::hasColumn('documents', 'file_name')) {
                $dropColumns[] = 'file_name';
            }
            if (Schema::hasColumn('documents', 'file_type')) {
                $dropColumns[] = 'file_type';
            }
            if (Schema::hasColumn('documents', 'file_size')) {
                $dropColumns[] = 'file_size';
            }
            if (Schema::hasColumn('documents', 'review_date')) {
                $dropColumns[] = 'review_date';
            }
            if (Schema::hasColumn('documents', 'uploaded_by')) {
                $dropColumns[] = 'uploaded_by';
            }
            if (Schema::hasColumn('documents', 'deleted_at')) {
                $dropColumns[] = 'deleted_at';
            }
            
            if (!empty($dropColumns)) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};
