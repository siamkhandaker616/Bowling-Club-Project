<div style="font-family:Arial,Helvetica,sans-serif;max-width:600px;margin:0 auto;padding:24px;background:#f8f6f0;border:2px solid #1a2a3a;border-radius:10px;color:#1a2a3a;">
    <div style="font-family:Georgia,serif;font-size:22px;text-transform:uppercase;color:#1a2a3a;margin-bottom:4px;">The Tenth Frame</div>
    <div style="font-size:13px;color:#6b7a8d;margin-bottom:20px;">Lane Booking Confirmation</div>

    <p style="font-size:15px;line-height:1.6;">
        Your lane is booked. See you on the lanes!
    </p>

    <table style="width:100%;border-collapse:collapse;font-size:14px;margin-top:12px;">
        <tr>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;background:#eef3f7;width:40%;">Lane</td>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;font-weight:bold;">Lane {{ $booking->lane->lane_number ?? '—' }}</td>
        </tr>
        <tr>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;background:#eef3f7;">Date</td>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;">{{ $booking->date->format('l, M j, Y') }}</td>
        </tr>
        <tr>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;background:#eef3f7;">Time Slot</td>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;">{{ \App\Helpers\Label::timeSlotFull($booking->time_slot) }}</td>
        </tr>
        <tr>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;background:#eef3f7;">Guest</td>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;">{{ $booking->visitor->name ?? '—' }}</td>
        </tr>
        @if($booking->payment && (float) $booking->payment->amount > 0)
        <tr>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;background:#eef3f7;">Amount Paid</td>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;font-weight:bold;">&#2547; {{ number_format((float) $booking->payment->amount, 2) }}</td>
        </tr>
        <tr>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;background:#eef3f7;">Receipt No.</td>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;">{{ $booking->payment->transaction_id }}</td>
        </tr>
        @endif
    </table>

    <p style="font-size:13px;color:#6b7a8d;margin-top:20px;line-height:1.6;">
        If you need to cancel or reschedule, visit your bookings page. Questions? Email us at {{ $contactEmail ?: 'the club desk' }}.
    </p>
</div>
