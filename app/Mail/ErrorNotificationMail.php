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
            subject: '[CRITICAL] Error in FSMA 204 System - ' . $this->errorLog->error_type,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.error-notification',
            with: [
                'errorLog' => $this->errorLog,
                'dashboardUrl' => route('admin.errors.show', $this->errorLog->id),
            ],
        );
    }
}
