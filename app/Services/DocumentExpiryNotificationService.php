<?php

namespace App\Services;

use App\Models\Document;
use App\Models\Notification;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Mail;
use App\Mail\DocumentExpiryWarningMail;

class DocumentExpiryNotificationService
{
    protected $auditLogService;
    protected $notificationService;

    public function __construct(AuditLogService $auditLogService, NotificationService $notificationService)
    {
        $this->auditLogService = $auditLogService;
        $this->notificationService = $notificationService;
    }

    /**
     * Check all documents expiring within specified days and send notifications
     */
    public function checkExpiringDocuments(int $daysThreshold = 30): array
    {
        $expiringDocuments = Document::expiringWithin($daysThreshold)
            ->with(['uploader', 'approver', 'organization'])
            ->get();

        $notificationsSent = 0;
        $errors = [];

        foreach ($expiringDocuments as $document) {
            try {
                $this->sendExpiryWarning($document);
                $notificationsSent++;
            } catch (\Exception $e) {
                $errors[] = [
                    'document_id' => $document->id,
                    'error' => $e->getMessage(),
                ];
                \Log::error('Failed to send document expiry notification', [
                    'document_id' => $document->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Audit log
        $this->auditLogService->log(
            'DOCUMENT_EXPIRY_CHECK',
            'documents',
            null,
            null,
            [
                'total_expiring' => $expiringDocuments->count(),
                'notifications_sent' => $notificationsSent,
                'errors' => count($errors),
                'days_threshold' => $daysThreshold,
            ]
        );

        return [
            'total_expiring' => $expiringDocuments->count(),
            'notifications_sent' => $notificationsSent,
            'errors' => $errors,
        ];
    }

    /**
     * Send expiry warning for a specific document
     */
    public function sendExpiryWarning(Document $document): void
    {
        if (!$document->expiry_date) {
            return;
        }

        $daysUntilExpiry = now()->diffInDays($document->expiry_date, false);

        if ($daysUntilExpiry < 0) {
            $this->sendExpiredNotification($document);
            return;
        }

        // Send to document uploader
        if ($document->uploader) {
            $this->createNotification($document, $document->uploader, $daysUntilExpiry);
            $this->sendEmail($document, $document->uploader, $daysUntilExpiry);
        }

        // Send to document approver
        if ($document->approver && $document->approver->id !== $document->uploader?->id) {
            $this->createNotification($document, $document->approver, $daysUntilExpiry);
            $this->sendEmail($document, $document->approver, $daysUntilExpiry);
        }

        // Send to organization admins
        $admins = $document->organization->users()
            ->where('role', 'admin')
            ->where('id', '!=', $document->uploader?->id)
            ->where('id', '!=', $document->approver?->id)
            ->get();

        foreach ($admins as $admin) {
            $this->createNotification($document, $admin, $daysUntilExpiry);
            $this->sendEmail($document, $admin, $daysUntilExpiry);
        }

        // Audit log
        $this->auditLogService->log(
            'DOCUMENT_EXPIRY_WARNING_SENT',
            'documents',
            $document->id,
            null,
            [
                'doc_number' => $document->doc_number,
                'expiry_date' => $document->expiry_date,
                'days_until_expiry' => $daysUntilExpiry,
            ]
        );
    }

    /**
     * Send notification for already expired document
     */
    protected function sendExpiredNotification(Document $document): void
    {
        $daysExpired = abs(now()->diffInDays($document->expiry_date, false));

        // Send to document uploader
        if ($document->uploader) {
            $this->createExpiredNotification($document, $document->uploader, $daysExpired);
        }

        // Send to document approver
        if ($document->approver && $document->approver->id !== $document->uploader?->id) {
            $this->createExpiredNotification($document, $document->approver, $daysExpired);
        }

        // Audit log
        $this->auditLogService->log(
            'DOCUMENT_EXPIRED_NOTIFICATION_SENT',
            'documents',
            $document->id,
            null,
            [
                'doc_number' => $document->doc_number,
                'expiry_date' => $document->expiry_date,
                'days_expired' => $daysExpired,
            ]
        );
    }

    /**
     * Create in-app notification for expiring document
     */
    protected function createNotification(Document $document, User $user, int $daysUntilExpiry): void
    {
        $priority = $this->calculatePriority($daysUntilExpiry);
        $isBlocking = $daysUntilExpiry <= 7;

        Notification::create([
            'user_id' => $user->id,
            'organization_id' => $document->organization_id,
            'type' => 'document_expiry_warning',
            'title' => 'Document Expiring Soon',
            'message' => "Document '{$document->title}' (#{$document->doc_number}) will expire in {$daysUntilExpiry} days on {$document->expiry_date->format('Y-m-d')}. Please review and update if necessary.",
            'cta_text' => 'View Document',
            'cta_url' => route('documents.show', $document->id),
            'is_blocking' => $isBlocking,
            'priority' => $priority,
            'metadata' => [
                'document_id' => $document->id,
                'doc_number' => $document->doc_number,
                'expiry_date' => $document->expiry_date,
                'days_until_expiry' => $daysUntilExpiry,
            ],
        ]);
    }

    /**
     * Create in-app notification for expired document
     */
    protected function createExpiredNotification(Document $document, User $user, int $daysExpired): void
    {
        Notification::create([
            'user_id' => $user->id,
            'organization_id' => $document->organization_id,
            'type' => 'document_expired',
            'title' => 'Document Expired',
            'message' => "Document '{$document->title}' (#{$document->doc_number}) expired {$daysExpired} days ago on {$document->expiry_date->format('Y-m-d')}. Immediate action required.",
            'cta_text' => 'View Document',
            'cta_url' => route('documents.show', $document->id),
            'is_blocking' => true,
            'priority' => 2,
            'metadata' => [
                'document_id' => $document->id,
                'doc_number' => $document->doc_number,
                'expiry_date' => $document->expiry_date,
                'days_expired' => $daysExpired,
            ],
        ]);
    }

    /**
     * Send email notification
     */
    protected function sendEmail(Document $document, User $user, int $daysUntilExpiry): void
    {
        try {
            Mail::to($user->email)->send(new DocumentExpiryWarningMail(
                $user->name,
                $document->title,
                $document->doc_number,
                $document->expiry_date,
                $daysUntilExpiry,
                route('documents.show', $document->id)
            ));

            \Log::info('Document expiry warning email sent', [
                'user_id' => $user->id,
                'document_id' => $document->id,
                'days_until_expiry' => $daysUntilExpiry,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send document expiry email', [
                'user_id' => $user->id,
                'document_id' => $document->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Calculate notification priority based on days until expiry
     */
    protected function calculatePriority(int $daysUntilExpiry): int
    {
        if ($daysUntilExpiry <= 7) {
            return 2; // High priority
        } elseif ($daysUntilExpiry <= 14) {
            return 1; // Medium priority
        }
        return 0; // Low priority
    }

    /**
     * Get expiry status for a document
     */
    public function getExpiryStatus(Document $document): array
    {
        if (!$document->expiry_date) {
            return [
                'status' => 'no_expiry',
                'message' => 'No expiry date set',
                'days' => null,
            ];
        }

        $daysUntilExpiry = now()->diffInDays($document->expiry_date, false);

        if ($daysUntilExpiry < 0) {
            return [
                'status' => 'expired',
                'message' => 'Document expired ' . abs($daysUntilExpiry) . ' days ago',
                'days' => $daysUntilExpiry,
            ];
        } elseif ($daysUntilExpiry <= 7) {
            return [
                'status' => 'critical',
                'message' => 'Expires in ' . $daysUntilExpiry . ' days',
                'days' => $daysUntilExpiry,
            ];
        } elseif ($daysUntilExpiry <= 30) {
            return [
                'status' => 'warning',
                'message' => 'Expires in ' . $daysUntilExpiry . ' days',
                'days' => $daysUntilExpiry,
            ];
        }

        return [
            'status' => 'valid',
            'message' => 'Expires in ' . $daysUntilExpiry . ' days',
            'days' => $daysUntilExpiry,
        ];
    }
}
