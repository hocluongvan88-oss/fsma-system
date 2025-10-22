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

    /**
     * Create a new message instance.
     */
    public function __construct($userName, $planName, $newQuota, $dashboardUrl)
    {
        $this->userName = $userName;
        $this->planName = $planName;
        $this->newQuota = $newQuota;
        $this->dashboardUrl = $dashboardUrl;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🎉 Chúc mừng! Nâng cấp gói ' . $this->planName . ' thành công',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.upgrade-success',
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
