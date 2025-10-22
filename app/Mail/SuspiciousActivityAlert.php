<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SuspiciousActivityAlert extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $action,
        public int $lockoutMinutes
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Security Alert: Suspicious Activity Detected',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.suspicious-activity-alert',
            with: [
                'user' => $this->user,
                'action' => $this->action,
                'lockoutMinutes' => $this->lockoutMinutes,
            ],
        );
    }
}
