<?php

namespace App\Mail;

use App\Models\Club;
use App\Models\TouringRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TouringWelcome extends Mailable
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
            subject: 'New Touring Request — '.$this->touring->team_name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.touring-welcome',
        );
    }
}
