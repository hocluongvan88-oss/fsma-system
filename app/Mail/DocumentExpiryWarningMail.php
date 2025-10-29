<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class DocumentExpiryWarningMail extends Mailable
{
    use Queueable, SerializesModels;

    public $userName;
    public $documentTitle;
    public $docNumber;
    public $expiryDate;
    public $daysUntilExpiry;
    public $documentUrl;
    public $userEmailToken;

    public function __construct(
        string $userName,
        string $documentTitle,
        string $docNumber,
        $expiryDate,
        int $daysUntilExpiry,
        string $documentUrl,
        string $userEmailToken = ''
    ) {
        $this->userName = $userName;
        $this->documentTitle = $documentTitle;
        $this->docNumber = $docNumber;
        $this->expiryDate = $expiryDate instanceof Carbon ? $expiryDate : Carbon::parse($expiryDate);
        $this->daysUntilExpiry = $daysUntilExpiry;
        $this->documentUrl = $documentUrl;
        $this->userEmailToken = $userEmailToken;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('messages.document_expiry_warning') . ' - ' . $this->docNumber,
        );
    }

    public function headers(): array
    {
        return [
            'List-Unsubscribe' => '<' . route('email.unsubscribe', ['token' => $this->userEmailToken]) . '>',
        ];
    }

    public function content(): Content
    {
        return new Content(
            view: 'email.document-expiry-warning',
        );
    }
}
