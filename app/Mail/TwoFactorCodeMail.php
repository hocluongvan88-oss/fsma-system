<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TwoFactorCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $code,
        public int $expiryMinutes = 10
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Two-Factor Authentication Code',
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
            view: 'email.two-factor-code',
            with: [
                'user' => $this->user,
                'code' => $this->code,
                'expiryMinutes' => $this->expiryMinutes,
            ],
        );
    }
}
