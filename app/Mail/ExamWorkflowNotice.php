<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ExamWorkflowNotice extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $noticeTitle,
        public string $noticeBody,
        public string $link,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->noticeTitle);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.exam-workflow-notice');
    }
}
