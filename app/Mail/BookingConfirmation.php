<?php

namespace App\Mail;

use App\Models\Club;
use App\Models\LaneBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public LaneBooking $booking, public ?string $contactEmail = null)
    {
        $this->contactEmail ??= Club::first()?->email;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Lane Booked — '.\App\Helpers\Label::timeSlotFull($this->booking->time_slot).' on '.$this->booking->date->format('M j, Y'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.booking-confirmation',
        );
    }
}
