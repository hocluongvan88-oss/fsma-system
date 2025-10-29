<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UpgradeSuccessMail extends Mailable
{
    use Queueable, SerializesModels;

    public $userName;
    public $planName;
    public $newQuota;
    public $dashboardUrl;
    public $userEmailToken;

    public function __construct($userName, $planName, $newQuota, $dashboardUrl, $userEmailToken = '')
    {
        $this->userName = $userName;
        $this->planName = $planName;
        $this->newQuota = $newQuota;
        $this->dashboardUrl = $dashboardUrl;
        $this->userEmailToken = $userEmailToken;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('messages.upgrade_successful'),
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
            view: 'email.upgrade-success',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
