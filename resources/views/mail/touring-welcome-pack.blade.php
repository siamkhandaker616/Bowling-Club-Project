<div style="font-family:Arial,Helvetica,sans-serif;max-width:600px;margin:0 auto;padding:24px;background:#f8f6f0;border:2px solid #1a2a3a;border-radius:10px;color:#1a2a3a;">
    <div style="font-family:Georgia,serif;font-size:22px;text-transform:uppercase;color:#1a2a3a;margin-bottom:4px;">The Tenth Frame</div>
    <div style="font-size:13px;color:#6b7a8d;margin-bottom:20px;">Touring Team Welcome Pack</div>

    <p style="font-size:15px;line-height:1.6;">Hello {{ $touring->team_name }}! Your visit to {{ $club->name }} is booked. Here's what you can expect.</p>

    <table style="width:100%;border-collapse:collapse;font-size:14px;margin-top:12px;">
        <tr>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;background:#eef3f7;width:40%;">Team</td>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;font-weight:bold;">{{ $touring->team_name }}</td>
        </tr>
        <tr>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;background:#eef3f7;">Home Club</td>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;">{{ $touring->home_club ?: '&mdash;' }}</td>
        </tr>
        <tr>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;background:#eef3f7;">Arrival Date</td>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;">{{ $touring->arrival_date->format('l, M j, Y') }}</td>
        </tr>
        <tr>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;background:#eef3f7;">Players</td>
            <td style="padding:8px 10px;border:1px solid #1a2a3a;">{{ $touring->player_count }}</td>
        </tr>
    </table>

    <div style="background:#1a2a3a;border-radius:8px;padding:14px 18px;margin-top:18px;color:#f8f6f0;">
        <div style="font-family:Georgia,serif;font-size:14px;text-transform:uppercase;color:#d9a441;margin-bottom:8px;">Your Visit Includes</div>
        <ul style="list-style:none;padding:0;margin:0;font-size:13px;line-height:1.9;">
            <li>&#9917; Reserved lane block on arrival</li>
            <li>&#9917; Complimentary welcome drinks</li>
            <li>&#9917; Practice session with our pro coach</li>
            <li>&#9917; Printable welcome pack + directions</li>
        </ul>
    </div>

    <p style="font-size:13px;color:#6b7a8d;margin-top:20px;line-height:1.6;">
        Questions? Reach {{ $club->name }} at {{ $club->email }} or {{ $club->phone }}.<br>
        {{ $club->address }}
    </p>
</div>
