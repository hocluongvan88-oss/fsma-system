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

    /**
     * Create a new message instance.
     */
    public function __construct($userName, $usedQuota, $totalQuota, $percentage, $upgradeUrl)
    {
        $this->userName = $userName;
        $this->usedQuota = $usedQuota;
        $this->totalQuota = $totalQuota;
        $this->percentage = $percentage;
        $this->upgradeUrl = $upgradeUrl;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '⚠️ Cảnh báo: Bạn đã sử dụng ' . $this->percentage . '% dung lượng',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.quota-warning',
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
