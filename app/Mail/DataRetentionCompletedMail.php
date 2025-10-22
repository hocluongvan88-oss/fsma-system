<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
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
            subject: 'FSMA 204 - Data Retention Cleanup Completed',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.data-retention-completed',
            with: [
                'stats' => $this->stats,
                'totalDeleted' => array_sum($this->stats),
            ],
        );
    }
}
