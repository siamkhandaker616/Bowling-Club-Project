<?php

namespace App\Mail;

use App\Models\Club;
use App\Models\Rsvp;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RsvpReceipt extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Rsvp $rsvp, public ?string $contactEmail = null)
    {
        $this->contactEmail ??= Club::first()?->email;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You\'re in! '.$this->rsvp->event->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.rsvp-receipt',
        );
    }
}
