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

    /**
     * Create a new message instance.
     */
    public function __construct($userName, $usedQuota, $totalQuota, $upgradeUrl)
    {
        $this->userName = $userName;
        $this->usedQuota = $usedQuota;
        $this->totalQuota = $totalQuota;
        $this->upgradeUrl = $upgradeUrl;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🚨 Khẩn cấp: Bạn đã hết dung lượng!',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.quota-reached',
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
