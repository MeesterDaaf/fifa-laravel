<?php

namespace App\Mail;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TournamentDeadlineMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<int, string>  $missing  Nog niet ingevulde onderdelen.
     */
    public function __construct(
        public User $user,
        public ?Carbon $deadline,
        public array $missing,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🏆 Toernooivoorspelling sluit bij de eerste wedstrijd',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.tournament-deadline',
        );
    }
}
