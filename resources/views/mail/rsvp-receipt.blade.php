<div style="font-family:Arial,Helvetica,sans-serif;max-width:600px;margin:0 auto;padding:24px;background:#f8f6f0;border:2px solid #1a2a3a;border-radius:10px;color:#1a2a3a;">
    <div style="font-family:Georgia,serif;font-size:22px;text-transform:uppercase;color:#1a2a3a;margin-bottom:4px;">The Tenth Frame</div>
    <div style="font-size:13px;color:#6b7a8d;margin-bottom:20px;">RSVP Confirmation &mdash; {{ $rsvp->visitor_name }}</div>

    <p style="font-size:15px;line-height:1.6;">
        You're on the list for <strong>{{ $rsvp->event->title }}</strong>. See you on the lanes!
    </p>

    <table style="width:100%;border-collapse:collapse;font-size:14px;margin-top:12px;">
        <tr>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;background:#eef3f7;width:40%;">Event</td>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;font-weight:bold;">{{ $rsvp->event->title }}</td>
        </tr>
        <tr>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;background:#eef3f7;">When</td>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;">{{ $rsvp->event->date->format('l, M j, Y') }} at {{ $rsvp->event->time?->format('g:i A') }}</td>
        </tr>
        <tr>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;background:#eef3f7;">Venue</td>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;">{{ $rsvp->event->venue ?: '—' }}</td>
        </tr>
        <tr>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;background:#eef3f7;">Guest</td>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;">{{ $rsvp->visitor_name }}</td>
        </tr>
        <tr>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;background:#eef3f7;">Status</td>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;text-transform:uppercase;">{{ $rsvp->isConfirmed() ? 'You\'re in!' : ($rsvp->status === 'pending' ? 'Booking pending' : 'Cancelled') }}</td>
        </tr>
        @if($rsvp->payment && (float) $rsvp->payment->amount > 0)
        <tr>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;background:#eef3f7;">Paid</td>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;font-weight:bold;">৳ {{ number_format((float) $rsvp->payment->amount, 0) }}</td>
        </tr>
        <tr>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;background:#eef3f7;">Receipt No.</td>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;">{{ $rsvp->payment->transaction_id }}</td>
        </tr>
        @endif
    </table>

    <p style="font-size:13px;color:#6b7a8d;margin-top:20px;line-height:1.6;">
        Filled {{ $rsvp->event->current_rsvps }} of {{ $rsvp->event->max_capacity }} spots. Questions? Email the club secretary at {{ $contactEmail ?: 'the club desk' }}.
    </p>
</div>
