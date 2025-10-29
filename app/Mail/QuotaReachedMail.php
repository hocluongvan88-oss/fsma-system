<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuotaReachedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $userName;
    public $usedQuota;
    public $totalQuota;
    public $upgradeUrl;
    public $userEmailToken;

    public function __construct($userName, $usedQuota, $totalQuota, $upgradeUrl, $userEmailToken = '')
    {
        $this->userName = $userName;
        $this->usedQuota = $usedQuota;
        $this->totalQuota = $totalQuota;
        $this->upgradeUrl = $upgradeUrl;
        $this->userEmailToken = $userEmailToken;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('messages.quota_reached'),
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
            view: 'email.quota-reached',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
