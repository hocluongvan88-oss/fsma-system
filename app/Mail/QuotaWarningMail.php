<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuotaWarningMail extends Mailable
{
    use Queueable, SerializesModels;

    public $userName;
    public $usedQuota;
    public $totalQuota;
    public $percentage;
    public $upgradeUrl;
    public $userEmailToken;

    public function __construct($userName, $usedQuota, $totalQuota, $percentage, $upgradeUrl, $userEmailToken = '')
    {
        $this->userName = $userName;
        $this->usedQuota = $usedQuota;
        $this->totalQuota = $totalQuota;
        $this->percentage = $percentage;
        $this->upgradeUrl = $upgradeUrl;
        $this->userEmailToken = $userEmailToken;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('messages.quota_warning') . ' - ' . $this->percentage . '%',
        );
    }

    public function headers(): array
    {
        return [
            'List-Unsubscribe' => '<' . route('email.unsubscribe', ['token' => $this->userEmailToken]) . '>',
        ];
    }

    public function content(): Content
    {
        return new Content(
            view: 'email.quota-warning',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
