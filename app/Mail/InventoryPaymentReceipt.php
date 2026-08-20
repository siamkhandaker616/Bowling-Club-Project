<?php

namespace App\Mail;

use App\Models\Club;
use App\Models\InventoryPurchase;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InventoryPaymentReceipt extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public InventoryPurchase $purchase,
        public ?string $receiptTo = null,
    ) {
        $this->receiptTo ??= Club::first()?->email;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Inventory Payment Confirmed — '.$this->purchase->item_name.' — ৳'.number_format((float) $this->purchase->total, 2),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.inventory-payment-receipt',
        );
    }
}
