<?php

namespace App\Mail;

use App\Models\DefenseRequest;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class HonorariumFollowUp extends Mailable
{
    public function __construct(
        public DefenseRequest $defenseRequest,
        public string $senderName,
        public string $recipientRole,
        public string $notes,
        public string $trackingUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Honorarium follow-up for '.$this->defenseRequest->thesis_title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.honorarium-follow-up',
        );
    }
}
