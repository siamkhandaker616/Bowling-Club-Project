<?php

namespace App\Mail;

use App\Models\Rsvp;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RsvpConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Rsvp $rsvp)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New RSVP — '.$this->rsvp->event->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.rsvp-confirmation',
        );
    }
}
