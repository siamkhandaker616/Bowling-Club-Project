<div style="font-family:Arial,Helvetica,sans-serif;max-width:600px;margin:0 auto;padding:24px;background:#f8f6f0;border:2px solid #1a2a3a;border-radius:10px;color:#1a2a3a;">
    <div style="font-family:Georgia,serif;font-size:22px;text-transform:uppercase;color:#1a2a3a;margin-bottom:4px;">The Tenth Frame</div>
    <div style="font-size:13px;color:#6b7a8d;margin-bottom:20px;">Touring Team Welcome Portal</div>

    <p style="font-size:15px;line-height:1.6;">A touring team has booked a visit. Details below.</p>

    <table style="width:100%;border-collapse:collapse;font-size:14px;margin-top:12px;">
        <tr>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;background:#eef3f7;width:40%;">Team</td>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;font-weight:bold;">{{ $touring->team_name }}</td>
        </tr>
        <tr>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;background:#eef3f7;">Home Club</td>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;">{{ $touring->home_club ?: '—' }}</td>
        </tr>
        <tr>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;background:#eef3f7;">Arrival Date</td>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;">{{ $touring->arrival_date->format('l, M j, Y') }}</td>
        </tr>
        <tr>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;background:#eef3f7;">Players</td>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;">{{ $touring->player_count }}</td>
        </tr>
        @if($touring->message)
        <tr>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;background:#eef3f7;vertical-align:top;">Message</td>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;">{{ $touring->message }}</td>
        </tr>
        @endif
    </table>

    <p style="font-size:13px;color:#6b7a8d;margin-top:20px;line-height:1.6;">
        {{ $club->name }} &bull; {{ $club->address }}<br>
        {{ $club->phone }} &bull; {{ $club->email }}
    </p>
</div>
