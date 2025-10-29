<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Document;
use App\Services\FileEncryptionService;

class EncryptExistingDocuments extends Migration
{
    public function up(): void
    {
        $encryptionService = app(FileEncryptionService::class);
        
        $documents = Document::where('is_encrypted', false)->get();
        
        foreach ($documents as $document) {
            try {
                $encryptionService->encryptDocumentFile($document);
                $encryptionService->encryptDocumentVersions($document);
            } catch (\Exception $e) {
                \Log::warning("Failed to encrypt document {$document->id}: " . $e->getMessage());
            }
        }
    }

    public function down(): void
    {
        // Note: Decryption during rollback is not recommended for security reasons
        // Manual intervention required if rollback is needed
    }
}
