<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DocumentExpiryNotificationService;

class CheckDocumentExpiry extends Command
{
    protected $signature = 'documents:check-expiry {--days=30 : Days threshold for expiry warning}';
    protected $description = 'Check for expiring documents and send notifications';

    protected $expiryService;

    public function __construct(DocumentExpiryNotificationService $expiryService)
    {
        parent::__construct();
        $this->expiryService = $expiryService;
    }

    public function handle()
    {
        $daysThreshold = (int) $this->option('days');
        
        $this->info("Checking for documents expiring within {$daysThreshold} days...");
        
        $result = $this->expiryService->checkExpiringDocuments($daysThreshold);
        
        $this->info("Total expiring documents: {$result['total_expiring']}");
        $this->info("Notifications sent: {$result['notifications_sent']}");
        
        if (count($result['errors']) > 0) {
            $this->warn("Errors encountered: " . count($result['errors']));
            foreach ($result['errors'] as $error) {
                $this->error("Document ID {$error['document_id']}: {$error['error']}");
            }
        }
        
        $this->info('Document expiry check completed.');
        
        return 0;
    }
}
