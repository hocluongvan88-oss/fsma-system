<?php

namespace App\Notifications;

use App\Models\Package;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PackageChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $oldPackageId;
    protected $newPackageId;
    protected $newPackage;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $oldPackageId, string $newPackageId, Package $newPackage)
    {
        $this->oldPackageId = $oldPackageId;
        $this->newPackageId = $newPackageId;
        $this->newPackage = $newPackage;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Package Has Been Updated')
            ->line("Your package has been changed from {$this->oldPackageId} to {$this->newPackageId}.")
            ->line('New package limits:')
            ->line("- CTE Records: " . ($this->newPackage->max_cte_records_monthly == 0 ? 'Unlimited' : $this->newPackage->max_cte_records_monthly . ' per month'))
            ->line("- Documents: " . ($this->newPackage->max_documents == 0 ? 'Unlimited' : $this->newPackage->max_documents))
            ->line("- Users: " . ($this->newPackage->max_users == 0 ? 'Unlimited' : $this->newPackage->max_users))
            ->action('View Dashboard', url('/dashboard'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'package_changed',
            'old_package' => $this->oldPackageId,
            'new_package' => $this->newPackageId,
            'new_limits' => [
                'max_cte_records_monthly' => $this->newPackage->max_cte_records_monthly,
                'max_documents' => $this->newPackage->max_documents,
                'max_users' => $this->newPackage->max_users,
            ],
            'changed_at' => now(),
        ];
    }
}
