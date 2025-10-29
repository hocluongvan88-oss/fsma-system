<?php

namespace App\Http\Controllers;

use App\Mail\DataRetentionCompletedMail;
use App\Mail\DocumentExpiryWarningMail;
use App\Mail\ErrorNotificationMail;
use App\Mail\QuotaReachedMail;
use App\Mail\QuotaWarningMail;
use App\Mail\SuspiciousActivityAlert;
use App\Mail\UpgradeSuccessMail;
use App\Mail\TwoFactorCodeMail;
use App\Mail\AccountUpdateMail;
use App\Models\ErrorLog;
use App\Models\User;

class EmailPreviewController extends BaseController
{
    /**
     * Display list of available email templates for preview
     */
    public function index()
    {
        $emailTypes = [
            'data-retention-completed' => 'Data Retention Completed',
            'document-expiry-warning' => 'Document Expiry Warning',
            'error-notification' => 'Error Notification',
            'quota-reached' => 'Quota Reached',
            'quota-warning' => 'Quota Warning',
            'suspicious-activity-alert' => 'Suspicious Activity Alert',
            'upgrade-success' => 'Upgrade Success',
            'two-factor-code' => 'Two-Factor Authentication Code',
            'account-update' => 'Account Update Notification',
        ];

        return view('email.preview-index', ['emails' => $emailTypes]);
    }

    /**
     * Preview a specific email template
     */
    public function preview($emailType, $locale = null)
    {
        // Set locale if provided
        if ($locale) {
            app()->setLocale($locale);
        }

        try {
            $mailable = $this->getMailable($emailType);
            
            if (!$mailable) {
                return response()->view('errors.404', [], 404);
            }

            $subject = $mailable->envelope()->subject ?? 'Email Subject';
            $htmlContent = $mailable->render();

            return view('email.preview-show', [
                'subject' => $subject,
                'htmlContent' => $htmlContent,
                'emailType' => $emailType,
                'locale' => $locale ?? app()->getLocale(),
            ]);
        } catch (\Exception $e) {
            return response()->view('errors.500', ['exception' => $e], 500);
        }
    }

    /**
     * Get the appropriate mailable instance based on email type
     */
    private function getMailable($emailType)
    {
        $user = $this->getOrCreateUser();
        
        switch ($emailType) {
            case 'data-retention-completed':
                return new DataRetentionCompletedMail([
                    'trace_records' => 150,
                    'cte_events' => 45,
                    'audit_logs' => 320,
                    'e_signatures' => 12,
                    'error_logs' => 8,
                    'notifications' => 25,
                ]);
            
            case 'document-expiry-warning':
                return new DocumentExpiryWarningMail(
                    'John Doe',
                    'Sample Document',
                    'DOC-001',
                    now()->addDays(7),
                    7,
                    route('documents.show', 1),
                    $user->email_token
                );
            
            case 'error-notification':
                return new ErrorNotificationMail(
                    $this->getOrCreateErrorLog()
                );
            
            case 'quota-reached':
                return new QuotaReachedMail(
                    'John Doe',
                    85,
                    100,
                    route('pricing'),
                    $user->email_token
                );
            
            case 'quota-warning':
                return new QuotaWarningMail(
                    'John Doe',
                    85,
                    100,
                    85,
                    route('pricing'),
                    $user->email_token
                );
            
            case 'suspicious-activity-alert':
                return new SuspiciousActivityAlert(
                    $user,
                    'Multiple failed login attempts',
                    15
                );
            
            case 'upgrade-success':
                return new UpgradeSuccessMail(
                    'John Doe',
                    'Premium Plan',
                    500,
                    route('dashboard'),
                    $user->email_token
                );
            
            case 'two-factor-code':
                return new TwoFactorCodeMail(
                    $user,
                    '123456',
                    10
                );
            
            case 'account-update':
                return new AccountUpdateMail(
                    $user,
                    'security_settings',
                    [
                        'two_fa_enabled' => true,
                        'email' => $user->email,
                        'password' => 'changed',
                    ]
                );
            
            default:
                return null;
        }
    }

    /**
     * Get or create a sample user for preview
     */
    private function getOrCreateUser(): User
    {
        return User::firstOrCreate(
            ['email' => 'preview@example.com'],
            [
                'username' => 'preview_user',
                'full_name' => 'Preview User', // changed 'name' to 'full_name' to match database schema
                'password' => bcrypt('password'),
                'email_token' => \Illuminate\Support\Str::random(32),
            ]
        );
    }

    /**
     * Get or create a sample error log for preview
     */
    private function getOrCreateErrorLog(): ErrorLog
    {
        return ErrorLog::firstOrCreate(
            ['error_code' => 'PREVIEW_ERROR'],
            [
                'error_type' => 'Exception',
                'error_message' => 'This is a preview error for email template testing',
                'error_code' => 'PREVIEW_ERROR',
                'file_path' => 'app/Http/Controllers/EmailPreviewController.php',
                'line_number' => 1,
                'stack_trace' => 'Preview stack trace',
                'user_id' => $this->getOrCreateUser()->id,
                'status' => 'unresolved',
            ]
        );
    }
}
