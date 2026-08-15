<div style="font-family:Arial,Helvetica,sans-serif;max-width:600px;margin:0 auto;padding:24px;background:#f8f6f0;border:2px solid #1a2a3a;border-radius:10px;color:#1a2a3a;">
    <div style="font-family:Georgia,serif;font-size:22px;text-transform:uppercase;color:#1a2a3a;margin-bottom:4px;">The Tenth Frame</div>
    <div style="font-size:13px;color:#6b7a8d;margin-bottom:20px;">Pro Shop Order Receipt &mdash; Order #{{ $order->id }}</div>

    <p style="font-size:15px;line-height:1.6;">
        Payment received! Your gear is held at the front desk &mdash; show this receipt to collect it.
    </p>

    <table style="width:100%;border-collapse:collapse;font-size:14px;margin-top:12px;">
        <tr style="background:#eef3f7;">
            <th style="padding:8px 10px;border:1px solid #1a2a3a;text-align:left;">Item</th>
            <th style="padding:8px 10px;border:1px solid #1a2a3a;text-align:right;">Qty</th>
            <th style="padding:8px 10px;border:1px solid #1a2a3a;text-align:right;">Price</th>
        </tr>
        @foreach($order->items as $line)
        <tr>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;">{{ $line->product?->name ?? 'Item' }}</td>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;text-align:right;">{{ $line->quantity }}</td>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;text-align:right;">BDT {{ number_format((float) $line->unit_price * $line->quantity, 0) }}</td>
        </tr>
        @endforeach
        <tr>
            <td colspan="2" style="padding:8px 10px;border:1px solid #1a2a3a;background:#eef3f7;font-weight:bold;">Total</td>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;text-align:right;font-weight:bold;">BDT {{ number_format((float) $order->total(), 0) }}</td>
        </tr>
    </table>

    <table style="width:100%;border-collapse:collapse;font-size:14px;margin-top:12px;">
        <tr>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;background:#eef3f7;width:40%;">Transaction</td>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;">{{ $order->payment?->transaction_id }}</td>
        </tr>
        <tr>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;background:#eef3f7;">Status</td>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;text-transform:uppercase;">{{ $order->payment?->status }}</td>
        </tr>
    </table>

    <p style="font-size:13px;color:#6b7a8d;margin-top:20px;line-height:1.6;">
        Collect your order from the Pro Shop desk. Strike fast, roll loud!
    </p>
</div>
