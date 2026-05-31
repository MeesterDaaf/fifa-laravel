<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Collection;
use Illuminate\Queue\SerializesModels;

class AwaitingResultsMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $admin,
        public Collection $matches,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '⚠️ Uitslagen invoeren — speelronde voorbij',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.awaiting-results',
        );
    }
}
