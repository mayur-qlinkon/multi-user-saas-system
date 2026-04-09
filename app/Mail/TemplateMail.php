<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Generic Mailable used by EmailService to send HTML email from a stored template.
 * From address/name are controlled by runtime config set in EmailService — not here.
 */
class TemplateMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        private readonly string $mailSubject,
        private readonly string $mailBody,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->mailSubject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.template',
            with: ['mailBody' => html_entity_decode($this->mailBody)],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
