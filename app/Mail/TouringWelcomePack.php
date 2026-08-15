<?php

namespace App\Mail;

use App\Models\Club;
use App\Models\TouringRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TouringWelcomePack extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public TouringRequest $touring,
        public Club $club,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to The Tenth Frame — '.$this->touring->team_name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.touring-welcome-pack',
        );
    }
}
