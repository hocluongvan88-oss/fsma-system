<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates archival tables for Document module (FSMA 204 compliance)
     */
    public function up(): void
    {
        Schema::create('archival_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('original_id')->index()->comment('Original ID from documents table');
            
            // Mirror all columns from documents table
            $table->string('doc_number')->unique();
            $table->string('title');
            $table->string('type');
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->string('file_name');
            $table->string('file_type');
            $table->bigInteger('file_size');
            $table->string('version')->default('1.0');
            $table->string('status');
            $table->date('effective_date')->nullable();
            $table->date('review_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->unsignedBigInteger('uploaded_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->json('metadata')->nullable();
            $table->unsignedBigInteger('organization_id');
            $table->string('file_hash')->nullable();
            $table->string('metadata_hash')->nullable();
            $table->boolean('is_encrypted')->default(false);
            $table->timestamp('encrypted_at')->nullable();
            
            // Archival metadata
            $table->timestamp('archived_at')->nullable()->index()->comment('When this record was moved to archival');
            $table->unsignedBigInteger('archived_by')->nullable()->comment('User who triggered archival');
            $table->boolean('has_signatures')->default(false)->comment('Whether document had signatures');
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes matching original table
            $table->index('doc_number');
            $table->index('type');
            $table->index('status');
            $table->index('organization_id');
            $table->index('uploaded_by');
            $table->index(['organization_id', 'status']);
            $table->index(['organization_id', 'type']);
        });

        Schema::create('archival_document_versions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('original_id')->index()->comment('Original ID from document_versions table');
            
            // Mirror all columns from document_versions table
            $table->unsignedBigInteger('document_id');
            $table->string('version');
            $table->string('file_path');
            $table->string('file_name');
            $table->bigInteger('file_size');
            $table->text('change_notes')->nullable();
            $table->unsignedBigInteger('created_by');
            
            // Archival metadata
            $table->timestamp('archived_at')->nullable()->index();
            $table->unsignedBigInteger('archived_by')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('document_id');
            $table->index('version');
            $table->index('created_by');
        });

        Schema::create('archival_e_signatures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('original_id')->index()->comment('Original ID from e_signatures table');
            
            // Mirror all columns from e_signatures table
            $table->string('record_type');
            $table->unsignedBigInteger('record_id');
            $table->unsignedBigInteger('user_id');
            $table->string('action');
            $table->text('meaning_of_signature');
            $table->text('reason')->nullable();
            $table->string('signature_hash');
            $table->text('timestamp_token')->nullable();
            $table->unsignedBigInteger('certificate_id')->nullable();
            $table->json('verification_report')->nullable();
            $table->timestamp('signed_at');
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_revoked')->default(false);
            $table->timestamp('revoked_at')->nullable();
            $table->unsignedBigInteger('revoked_by')->nullable();
            $table->text('revocation_reason')->nullable();
            
            // Archival metadata
            $table->timestamp('archived_at')->nullable()->index();
            $table->unsignedBigInteger('archived_by')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['record_type', 'record_id']);
            $table->index('user_id');
            $table->index('signed_at');
            $table->index('is_revoked');
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->timestamp('archived_at')->nullable()->after('deleted_at')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('archived_at');
        });
        
        Schema::dropIfExists('archival_e_signatures');
        Schema::dropIfExists('archival_document_versions');
        Schema::dropIfExists('archival_documents');
    }
};
