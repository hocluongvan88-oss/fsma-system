<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\QuotaWarningMail;
use App\Mail\QuotaReachedMail;
use App\Mail\UpgradeSuccessMail;

class NotificationService
{
    public function sendQuotaWarning(User $user, string $resourceType, float $percentage)
    {
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

        // Create in-app notification
        $notification = Notification::create([
            'user_id' => $user->id,
            'type' => 'quota_warning',
            'title' => $data['title'],
            'message' => $data['message'],
            'cta_text' => $data['cta_text'],
            'cta_url' => $data['cta_url'],
            'is_blocking' => false,
        ]);

        $this->sendQuotaWarningEmail($user, $resourceType, $percentage);

        return $notification;
    }

    public function sendQuotaReached(User $user, string $resourceType)
    {
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

        // Create blocking notification
        $notification = Notification::create([
            'user_id' => $user->id,
            'type' => 'quota_reached',
            'title' => $data['title'],
            'message' => $data['message'],
            'cta_text' => $data['cta_text'],
            'cta_url' => $data['cta_url'],
            'is_blocking' => true,
        ]);

        $this->sendQuotaReachedEmail($user, $resourceType);

        return $notification;
    }

    public function sendUpgradeSuccess(User $user, string $oldPackage, string $newPackage)
    {
        $packageNames = [
            'free' => 'Free Tier',
            'lite' => 'Basic',
            'pro' => 'Premium',
            'enterprise' => 'Enterprise',
        ];

        $notification = Notification::create([
            'user_id' => $user->id,
            'type' => 'upgrade_success',
            'title' => 'Nâng cấp thành công!',
            'message' => "Chúc mừng! Tài khoản của bạn đã được nâng cấp từ {$packageNames[$oldPackage]} lên {$packageNames[$newPackage]}.",
            'cta_text' => 'Xem Dashboard',
            'cta_url' => route('dashboard'),
            'is_blocking' => false,
        ]);

        $this->sendUpgradeSuccessEmail($user, $packageNames[$newPackage]);

        return $notification;
    }

    public function sendFeatureLocked(User $user, string $featureName)
    {
        return Notification::create([
            'user_id' => $user->id,
            'type' => 'feature_locked',
            'title' => 'T��nh năng bị khóa',
            'message' => "Tính năng {$featureName} chỉ khả dụng cho gói Premium trở lên. Nâng cấp để sử dụng!",
            'cta_text' => 'Xem Bảng Giá',
            'cta_url' => route('pricing'),
            'is_blocking' => false,
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
