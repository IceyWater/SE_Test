<?php

namespace App\Mail;

<<<<<<< Updated upstream
use App\Models\DefenseRequest;
=======
use Illuminate\Bus\Queueable;
>>>>>>> Stashed changes
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class HonorariumFollowUp extends Mailable
{
<<<<<<< Updated upstream
    public function __construct(
        public DefenseRequest $defenseRequest,
        public string $senderName,
        public string $recipientRole,
        public string $notes,
        public string $trackingUrl,
=======
    use Queueable;

    public function __construct(
        public string $studentName,
        public string $program,
        public string $defenseType,
        public string $recipientRole,
        public string $notes,
        public int $requestId,
>>>>>>> Stashed changes
    ) {}

    public function envelope(): Envelope
    {
<<<<<<< Updated upstream
        return new Envelope(
            subject: 'Honorarium follow-up for '.$this->defenseRequest->thesis_title,
        );
=======
        return new Envelope(subject: 'Honorarium follow-up requested');
>>>>>>> Stashed changes
    }

    public function content(): Content
    {
<<<<<<< Updated upstream
        return new Content(
            view: 'emails.honorarium-follow-up',
        );
=======
        return new Content(view: 'emails.honorarium-follow-up');
>>>>>>> Stashed changes
    }
}
