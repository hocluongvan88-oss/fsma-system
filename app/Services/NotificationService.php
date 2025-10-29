<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\NotificationPreference;
use App\Models\NotificationTemplate;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\QuotaWarningMail;
use App\Mail\QuotaReachedMail;
use App\Mail\UpgradeSuccessMail;

class NotificationService
{
    private function checkDuplicate(int $userId, string $type, ?string $group = null): bool
    {
        if (!$group) {
            return false;
        }

        $existing = Notification::where('user_id', $userId)
            ->where('type', $type)
            ->where('notification_group', $group)
            ->where('created_at', '>', now()->subHours(24))
            ->exists();

        return $existing;
    }

    private function shouldSendNotification(int $userId, string $notificationType): bool
    {
        return NotificationPreference::isEnabled($userId, $notificationType);
    }

    public function sendQuotaWarning(User $user, string $resourceType, float $percentage)
    {
        if (!$this->shouldSendNotification($user->id, 'quota_warning')) {
            \Log::info('Quota warning notification skipped - user disabled', ['user_id' => $user->id]);
            return null;
        }

        $group = "quota_warning_{$resourceType}_{$user->id}";
        if ($this->checkDuplicate($user->id, 'quota_warning', $group)) {
            \Log::info('Quota warning notification skipped - duplicate', ['user_id' => $user->id, 'group' => $group]);
            return null;
        }

        $messages = [
            'cte' => [
                'title' => 'Sắp đạt giới hạn Bản ghi',
                'message' => "Bạn đã sử dụng {$percentage}% ({$user->getCteUsageThisMonth()}/{$user->max_cte_records_monthly}) Bản ghi tháng này. Hãy nâng cấp lên gói cao hơn để tiếp tục công việc không giới hạn!",
                'cta_text' => 'Xem Bảng Giá',
                'cta_url' => route('pricing'),
            ],
            'document' => [
                'title' => 'Sắp đạt giới hạn Tài liệu',
                'message' => "Bạn đã sử dụng {$user->getDocumentCount()}/{$user->max_documents} tài liệu. Nâng cấp để lưu trữ không giới hạn!",
                'cta_text' => 'Nâng cấp ngay',
                'cta_url' => route('pricing'),
            ],
        ];

        $data = $messages[$resourceType] ?? $messages['cte'];

        $notification = Notification::create([
            'user_id' => $user->id,
            'organization_id' => $user->organization_id,
            'type' => 'quota_warning',
            'title' => $data['title'],
            'message' => $data['message'],
            'cta_text' => $data['cta_text'],
            'cta_url' => $data['cta_url'],
            'is_blocking' => false,
            'priority' => 1,
            'notification_group' => $group,
            'metadata' => [
                'resource_type' => $resourceType,
                'percentage' => $percentage,
            ],
        ]);

        $this->sendQuotaWarningEmail($user, $resourceType, $percentage);

        return $notification;
    }

    public function sendQuotaReached(User $user, string $resourceType)
    {
        if (!$this->shouldSendNotification($user->id, 'quota_reached')) {
            \Log::info('Quota reached notification skipped - user disabled', ['user_id' => $user->id]);
            return null;
        }

        $group = "quota_reached_{$resourceType}_{$user->id}";
        if ($this->checkDuplicate($user->id, 'quota_reached', $group)) {
            \Log::info('Quota reached notification skipped - duplicate', ['user_id' => $user->id, 'group' => $group]);
            return null;
        }

        $messages = [
            'cte' => [
                'title' => 'Đã đạt giới hạn Bản ghi',
                'message' => "Bạn đã đạt giới hạn {$user->max_cte_records_monthly} Bản ghi. Vui lòng nâng cấp để tiếp tục sử dụng.",
                'cta_text' => 'Nâng cấp ngay',
                'cta_url' => route('pricing'),
            ],
            'document' => [
                'title' => 'Đã đạt giới hạn Tài liệu',
                'message' => "Bạn đã đạt giới hạn {$user->max_documents} tài liệu. Nâng cấp để tiếp tục upload.",
                'cta_text' => 'Nâng cấp ngay',
                'cta_url' => route('pricing'),
            ],
            'user' => [
                'title' => 'Đã đạt giới hạn Người dùng',
                'message' => "Bạn đã đạt giới hạn {$user->max_users} người dùng. Nâng cấp để thêm thành viên.",
                'cta_text' => 'Nâng cấp ngay',
                'cta_url' => route('pricing'),
            ],
        ];

        $data = $messages[$resourceType] ?? $messages['cte'];

        $notification = Notification::create([
            'user_id' => $user->id,
            'organization_id' => $user->organization_id,
            'type' => 'quota_reached',
            'title' => $data['title'],
            'message' => $data['message'],
            'cta_text' => $data['cta_text'],
            'cta_url' => $data['cta_url'],
            'is_blocking' => true,
            'priority' => 2,
            'notification_group' => $group,
            'metadata' => [
                'resource_type' => $resourceType,
            ],
        ]);

        $this->sendQuotaReachedEmail($user, $resourceType);

        return $notification;
    }

    public function sendUpgradeSuccess(User $user, string $oldPackage, string $newPackage)
    {
        if (!$this->shouldSendNotification($user->id, 'upgrade_success')) {
            \Log::info('Upgrade success notification skipped - user disabled', ['user_id' => $user->id]);
            return null;
        }

        $packageNames = [
            'free' => 'Free Tier',
            'lite' => 'Basic',
            'pro' => 'Premium',
            'enterprise' => 'Enterprise',
        ];

        $notification = Notification::create([
            'user_id' => $user->id,
            'organization_id' => $user->organization_id,
            'type' => 'upgrade_success',
            'title' => 'Nâng cấp thành công!',
            'message' => "Chúc mừng! Tài khoản của bạn đã được nâng cấp từ {$packageNames[$oldPackage]} lên {$packageNames[$newPackage]}.",
            'cta_text' => 'Xem Dashboard',
            'cta_url' => route('dashboard'),
            'is_blocking' => false,
            'priority' => 1,
            'metadata' => [
                'old_package' => $oldPackage,
                'new_package' => $newPackage,
            ],
        ]);

        $this->sendUpgradeSuccessEmail($user, $packageNames[$newPackage]);

        return $notification;
    }

    public function sendFeatureLocked(User $user, string $featureName)
    {
        if (!$this->shouldSendNotification($user->id, 'feature_locked')) {
            \Log::info('Feature locked notification skipped - user disabled', ['user_id' => $user->id]);
            return null;
        }

        return Notification::create([
            'user_id' => $user->id,
            'organization_id' => $user->organization_id,
            'type' => 'feature_locked',
            'title' => 'Tính năng bị khóa',
            'message' => "Tính năng {$featureName} chỉ khả dụng cho gói Premium trở lên. Nâng cấp để sử dụng!",
            'cta_text' => 'Xem Bảng Giá',
            'cta_url' => route('pricing'),
            'is_blocking' => false,
            'priority' => 0,
            'metadata' => [
                'feature_name' => $featureName,
            ],
        ]);
    }

    public function sendErrorNotification(User $user, string $errorMessage, string $errorCode = null)
    {
        if (!$this->shouldSendNotification($user->id, 'error_alert')) {
            return null;
        }

        return Notification::create([
            'user_id' => $user->id,
            'organization_id' => $user->organization_id,
            'type' => 'error_alert',
            'title' => 'Lỗi hệ thống',
            'message' => $errorMessage,
            'cta_text' => 'Liên hệ hỗ trợ',
            'cta_url' => route('support'),
            'is_blocking' => false,
            'priority' => 2,
            'metadata' => [
                'error_code' => $errorCode,
            ],
        ]);
    }

    public function sendDataRetentionCompleted(User $user, string $dataType, int $recordsDeleted)
    {
        if (!$this->shouldSendNotification($user->id, 'data_retention_completed')) {
            return null;
        }

        return Notification::create([
            'user_id' => $user->id,
            'organization_id' => $user->organization_id,
            'type' => 'data_retention_completed',
            'title' => 'Dữ liệu đã được xóa theo chính sách lưu giữ',
            'message' => "Đã xóa {$recordsDeleted} bản ghi {$dataType} theo chính sách lưu giữ dữ liệu.",
            'cta_text' => 'Xem chi tiết',
            'cta_url' => route('admin.retention.logs'),
            'is_blocking' => false,
            'priority' => 0,
            'metadata' => [
                'data_type' => $dataType,
                'records_deleted' => $recordsDeleted,
            ],
        ]);
    }

    private function sendQuotaWarningEmail(User $user, string $resourceType, float $percentage)
    {
        try {
            $usedQuota = $resourceType === 'cte' 
                ? $user->getCteUsageThisMonth() 
                : $user->getDocumentCount();
            
            $totalQuota = $resourceType === 'cte' 
                ? $user->max_cte_records_monthly 
                : $user->max_documents;

            Mail::to($user->email)->send(new QuotaWarningMail(
                $user->name,
                $usedQuota,
                $totalQuota,
                round($percentage),
                route('pricing')
            ));

            \Log::info('Quota warning email sent', [
                'user_id' => $user->id,
                'email' => $user->email,
                'resource_type' => $resourceType,
                'percentage' => $percentage
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send quota warning email: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function sendQuotaReachedEmail(User $user, string $resourceType)
    {
        try {
            $usedQuota = $resourceType === 'cte' 
                ? $user->getCteUsageThisMonth() 
                : ($resourceType === 'document' ? $user->getDocumentCount() : $user->users()->count());
            
            $totalQuota = $resourceType === 'cte' 
                ? $user->max_cte_records_monthly 
                : ($resourceType === 'document' ? $user->max_documents : $user->max_users);

            Mail::to($user->email)->send(new QuotaReachedMail(
                $user->name,
                $usedQuota,
                $totalQuota,
                route('pricing')
            ));

            \Log::info('Quota reached email sent', [
                'user_id' => $user->id,
                'email' => $user->email,
                'resource_type' => $resourceType
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send quota reached email: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function sendUpgradeSuccessEmail(User $user, string $planName)
    {
        try {
            $newQuota = $user->max_cte_records_monthly;

            Mail::to($user->email)->send(new UpgradeSuccessMail(
                $user->name,
                $planName,
                $newQuota,
                route('dashboard')
            ));

            \Log::info('Upgrade success email sent', [
                'user_id' => $user->id,
                'email' => $user->email,
                'plan' => $planName
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send upgrade success email: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * @deprecated Use specific email methods instead
     */
    private function sendEmail(User $user, string $subject, string $message)
    {
        // Simple email sending - can be enhanced with Mailable classes
        try {
            Mail::raw($message, function ($mail) use ($user, $subject) {
                $mail->to($user->email)
                     ->subject($subject);
            });
        } catch (\Exception $e) {
            \Log::error('Failed to send notification email: ' . $e->getMessage());
        }
    }
}
