<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountUpdateMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $updateType,
        public array $changes = []
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Account Update Notification',
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
            view: 'email.account-update',
            with: [
                'user' => $this->user,
                'updateType' => $this->updateType,
                'changes' => $this->changes,
            ],
        );
    }
}
