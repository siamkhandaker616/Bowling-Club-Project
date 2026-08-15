{{-- Settings gear + dropdown — A1's design, placed in nav bar for admin users --}}
<div style="position:relative;">
    <svg id="settings-btn" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="22" height="22" style="cursor:pointer;opacity:0.7;transition:opacity 0.2s,transform 0.3s;" onmouseover="this.style.opacity='1';this.style.transform='rotate(60deg)'" onmouseout="this.style.opacity='0.7';this.style.transform=''">
        <g transform="translate(50, 50)">
            <g stroke="var(--navy)" stroke-width="4.5" stroke-linejoin="round" fill="var(--fog)">
                <rect x="-10" y="-45" width="20" height="90" rx="4"/>
                <rect x="-10" y="-45" width="20" height="90" rx="4" transform="rotate(60)"/>
                <rect x="-10" y="-45" width="20" height="90" rx="4" transform="rotate(120)"/>
            </g>
            <circle cx="0" cy="0" r="28" fill="var(--fog)" stroke="var(--navy)" stroke-width="4.5"/>
            <circle cx="0" cy="0" r="12" fill="var(--gold)" stroke="var(--navy)" stroke-width="4"/>
            <circle cx="0" cy="0" r="4" fill="var(--navy)"/>
        </g>
    </svg>
    <div id="settings-dropdown" style="position:absolute;top:30px;right:0;left:auto;z-index:210;background:var(--pin-white);border:2px solid var(--navy);box-shadow:var(--shadow-md);padding:14px 18px;white-space:nowrap;font-family:var(--font-sub);font-size:0.8rem;min-width:200px;transform:translateY(-10px) scale(0.9);opacity:0;pointer-events:none;transition:opacity 0.2s,transform 0.3s cubic-bezier(0.175,0.885,0.32,1.2);border-radius:10px;">
        <div style="font-family:var(--font-header);font-size:0.65rem;letter-spacing:2px;padding-bottom:8px;margin-bottom:10px;border-bottom:2px solid var(--fog);color:var(--navy);text-transform:uppercase;">⚙ Simulation</div>
        <div id="settings-day-info" style="margin-bottom:10px;font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);">Loading…</div>
        <form method="POST" action="{{ route('manager.day.advance') }}" style="margin-bottom:10px;">
            @csrf
            <button type="submit" style="width:100%;font-family:var(--font-sub);font-size:0.7rem;padding:8px 14px;background:var(--navy);color:var(--pin-white);border:2px solid var(--navy);border-radius:8px;cursor:pointer;text-transform:uppercase;letter-spacing:0.5px;transition:background 0.15s;" onmouseover="this.style.background='var(--sky-dark)'" onmouseout="this.style.background='var(--navy)'">▶ Advance Day</button>
        </form>
        <form method="POST" action="{{ route('manager.day.toggleBadDay') }}">
            @csrf
            <button id="settings-bad-day-btn" type="submit" style="width:100%;font-family:var(--font-sub);font-size:0.7rem;padding:8px 14px;background:var(--pin-white);color:var(--navy);border:2px solid var(--navy);border-radius:8px;cursor:pointer;text-transform:uppercase;letter-spacing:0.5px;transition:background 0.15s,color 0.15s;" onmouseover="this.style.opacity='0.85'" onmouseout="this.style.opacity='1'">⚠ Bad Day</button>
        </form>
    </div>
</div>

<script>
(function() {
    var btn = document.getElementById('settings-btn');
    var dd = document.getElementById('settings-dropdown');
    var dayInfo = document.getElementById('settings-day-info');
    var badDayBtn = document.getElementById('settings-bad-day-btn');

    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        var open = dd.style.opacity === '1';
        dd.style.opacity = open ? '0' : '1';
        dd.style.pointerEvents = open ? 'none' : 'auto';
        dd.style.transform = open ? 'translateY(-10px) scale(0.9)' : 'translateY(0) scale(1)';
        if (!open) fetchDayStats();
    });

    document.addEventListener('click', function(e) {
        if (!dd.contains(e.target) && e.target !== btn) {
            dd.style.opacity = '0';
            dd.style.pointerEvents = 'none';
            dd.style.transform = 'translateY(-10px) scale(0.9)';
        }
    });

    function fetchDayStats() {
        fetch('{{ route("manager.day.stats") }}', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            dayInfo.innerHTML = 'Day ' + d.current_day + (d.bad_day_mode ? ' · <span style="color:var(--coral);">⚠ BAD DAY</span>' : '');
            if (d.bad_day_mode) {
                badDayBtn.style.background = 'var(--coral)';
                badDayBtn.style.color = 'var(--pin-white)';
                badDayBtn.style.borderColor = 'var(--coral)';
            } else {
                badDayBtn.style.background = 'var(--pin-white)';
                badDayBtn.style.color = 'var(--navy)';
                badDayBtn.style.borderColor = 'var(--navy)';
            }
        });
    }
})();
</script>
