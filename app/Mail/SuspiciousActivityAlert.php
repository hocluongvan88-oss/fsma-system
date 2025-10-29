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
            subject: __('messages.security_alert'),
        );
    }

    public function headers(): array
    {
        return [
            'List-Unsubscribe' => '<' . route('email.unsubscribe', ['token' => $this->user->email_token ?? '']) . '>',
        ];
    }

    public function content(): Content
    {
        return new Content(
            view: 'email.suspicious-activity-alert',
            with: [
                'user' => $this->user,
                'action' => $this->action,
                'lockoutMinutes' => $this->lockoutMinutes,
            ],
        );
    }
}
