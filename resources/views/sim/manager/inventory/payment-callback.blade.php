<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment {{ ucfirst($status) }} — The Tenth Frame</title>
</head>
<body style="min-height:100vh;display:flex;align-items:center;justify-content:center;background:var(--sky-light,var(--fog,#e8e4df));">
    <div style="max-width:420px;width:90%;text-align:center;padding:2rem;background:var(--pin-white,#fff);border:3px solid var(--navy,#1b2a4a);border-radius:14px;box-shadow:8px 8px 0 var(--navy,#1b2a4a);">
        @if ($status === 'success')
            <div style="font-size:2rem;margin-bottom:.5rem;">&#9989;</div>
            <h1 style="font-family:var(--font-header,serif);font-size:1.1rem;color:var(--navy,#1b2a4a);text-transform:uppercase;margin:0 0 .5rem;">Payment Successful</h1>
            <p style="font-family:var(--font-mono,monospace);font-size:.7rem;color:var(--slate,#6b7280);margin:0 0 1rem;">You can close this tab — your bill is settled.</p>
        @elseif ($status === 'failed')
            <div style="font-size:2rem;margin-bottom:.5rem;">&#10060;</div>
            <h1 style="font-family:var(--font-header,serif);font-size:1.1rem;color:var(--navy,#1b2a4a);text-transform:uppercase;margin:0 0 .5rem;">Payment Failed</h1>
            <p style="font-family:var(--font-mono,monospace);font-size:.7rem;color:var(--slate,#6b7280);margin:0 0 1rem;">Close this tab and try again from the bills page.</p>
        @elseif ($status === 'cancelled')
            <div style="font-size:2rem;margin-bottom:.5rem;">&#128683;</div>
            <h1 style="font-family:var(--font-header,serif);font-size:1.1rem;color:var(--navy,#1b2a4a);text-transform:uppercase;margin:0 0 .5rem;">Payment Cancelled</h1>
            <p style="font-family:var(--font-mono,monospace);font-size:.7rem;color:var(--slate,#6b7280);margin:0 0 1rem;">Close this tab — the bill is still pending on your dashboard.</p>
        @else
            <div style="font-size:2rem;margin-bottom:.5rem;">&#9203;</div>
            <h1 style="font-family:var(--font-header,serif);font-size:1.1rem;color:var(--navy,#1b2a4a);text-transform:uppercase;margin:0 0 .5rem;">Payment Processing</h1>
            <p style="font-family:var(--font-mono,monospace);font-size:.7rem;color:var(--slate,#6b7280);margin:0 0 1rem;">Close this tab — your dashboard will update automatically.</p>
        @endif
    </div>
    <script>setTimeout(function() { window.close(); }, 3000);</script>
</body>
</html>
