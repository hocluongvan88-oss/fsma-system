<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DataRetentionCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $stats
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('messages.data_retention_completed'),
        );
    }

    public function headers(): array
    {
        return [
            'List-Unsubscribe' => '<' . route('email.unsubscribe', ['token' => auth()->user()->email_token ?? '']) . '>',
        ];
    }

    public function content(): Content
    {
        return new Content(
            view: 'email.data-retention-completed',
            with: [
                'stats' => $this->stats,
                'totalDeleted' => array_sum($this->stats),
            ],
        );
    }
}
