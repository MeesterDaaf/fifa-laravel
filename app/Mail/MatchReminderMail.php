<?php

namespace App\Mail;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Collection;
use Illuminate\Queue\SerializesModels;

class MatchReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public Collection $matches,
        public Carbon $matchDate,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '⚽ Vergeet je voorspelling niet!',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.match-reminder',
        );
    }
}
