<?php

namespace App\Mail;

use App\Models\ErrorLog;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ErrorNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ErrorLog $errorLog)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[CRITICAL] ' . __('messages.error_detected') . ' - ' . $this->errorLog->error_type,
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
            view: 'email.error-notification',
            with: [
                'errorLog' => $this->errorLog,
                'dashboardUrl' => route('admin.errors.show', $this->errorLog->id),
            ],
        );
    }
}
