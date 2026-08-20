<div style="font-family:Arial,Helvetica,sans-serif;max-width:600px;margin:0 auto;padding:24px;background:#f8f6f0;border:2px solid #1a2a3a;border-radius:10px;color:#1a2a3a;">
    <div style="font-family:Georgia,serif;font-size:22px;text-transform:uppercase;color:#1a2a3a;margin-bottom:4px;">The Tenth Frame</div>
    <div style="font-size:13px;color:#6b7a8d;margin-bottom:20px;">Inventory Purchase Payment Receipt</div>

    <p style="font-size:15px;line-height:1.6;">
        Payment for the following inventory purchase has been confirmed and charged to club expenses.
    </p>

    <table style="width:100%;border-collapse:collapse;font-size:14px;margin-top:12px;">
        <tr>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;background:#eef3f7;width:40%;">Item</td>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;font-weight:bold;">{{ $purchase->item_name }}</td>
        </tr>
        <tr>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;background:#eef3f7;">Quantity</td>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;">{{ $purchase->quantity }} units</td>
        </tr>
        <tr>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;background:#eef3f7;">Unit Cost</td>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;">&#2547; {{ number_format((float) $purchase->unit_cost, 2) }}</td>
        </tr>
        <tr>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;background:#eef3f7;">Total Charged</td>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;font-weight:bold;">&#2547; {{ number_format((float) $purchase->total, 2) }}</td>
        </tr>
        <tr>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;background:#eef3f7;">Status</td>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;text-transform:uppercase;">Approved &amp; Paid</td>
        </tr>
        @if($purchase->payment)
        <tr>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;background:#eef3f7;">Receipt No.</td>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;">{{ $purchase->payment->transaction_id }}</td>
        </tr>
        @endif
        @if($purchase->reviewedBy)
        <tr>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;background:#eef3f7;">Reviewed By</td>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;">{{ $purchase->reviewedBy->user->name ?? '—' }}</td>
        </tr>
        @endif
    </table>

    <p style="font-size:13px;color:#6b7a8d;margin-top:20px;line-height:1.6;">
        This charge has been added to the club's total expenses. For questions, contact the club secretary at {{ $receiptTo ?: 'the club desk' }}.
    </p>
</div>
