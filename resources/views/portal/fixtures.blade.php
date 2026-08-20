<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fixtures & Results — The Tenth Frame</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body style="min-height:100vh;">

    @component('site.partials.core-header', ['activeRoute' => 'public.fixtures'])
    @endcomponent

    <main style="padding:6rem 2rem 4rem;max-width:1100px;margin:0 auto;">

        <div style="text-align:center;margin-bottom:2.5rem;">
            <h1 style="font-family:var(--font-display);font-size:2.2rem;text-transform:uppercase;color:var(--navy);margin-bottom:0.25rem;">Fixtures & Results</h1>
            <p style="font-family:var(--font-sub);color:var(--slate);font-size:1rem;">Every frame, every score — the schedule, results, and standings straight off the lanes.</p>
            <div class="lane-stripe" style="margin:1.5rem auto 0;max-width:400px;"></div>
        </div>

        @if($nextMatch)
        <div class="pub-fixture-reveal" style="background:var(--navy);border-radius:12px;padding:1.75rem 2rem;margin-bottom:2.5rem;position:relative;overflow:hidden;">
            <div style="position:absolute;top:0;right:0;width:180px;height:180px;background:radial-gradient(circle,rgba(212,168,76,0.15) 0%,transparent 70%);pointer-events:none;"></div>
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:1rem;">
                <span style="width:10px;height:10px;border-radius:50%;background:var(--gold);animation:pub-pulse 1.5s ease-in-out infinite;"></span>
                <span style="font-family:var(--font-mono);font-size:0.7rem;color:var(--gold);text-transform:uppercase;letter-spacing:2px;">Next Match</span>
            </div>
            <div style="display:flex;align-items:center;justify-content:space-between;gap:2rem;flex-wrap:wrap;">
                <div>
                    <div style="font-family:var(--font-header);font-size:1.3rem;color:var(--pin-white);margin-bottom:0.25rem;">{{ $nextMatch->homeTeam->name }} vs {{ $nextMatch->awayTeam->name }}</div>
                    <div style="font-family:var(--font-sub);font-size:0.85rem;color:var(--fog);">{{ $nextMatch->league->name }} &bull; {{ $nextMatch->date->format('l, M j, Y') }} at {{ \Carbon\Carbon::parse($nextMatch->time)->format('g:i A') }}</div>
                    @if($nextMatch->lane)
                        <div style="font-family:var(--font-mono);font-size:0.7rem;color:var(--fog);margin-top:0.25rem;">Lane {{ $nextMatch->lane->lane_number }}</div>
                    @endif
                </div>
                <div style="text-align:right;">
                    <div style="font-family:var(--font-mono);font-size:0.65rem;color:var(--fog);text-transform:uppercase;letter-spacing:1px;">Starts in</div>
                    <div id="pub-next-countdown" style="font-family:var(--font-mono);font-size:1.5rem;color:var(--gold);font-weight:700;"></div>
                </div>
            </div>
        </div>
        @endif

        <div class="pub-filter-bar" style="display:flex;flex-wrap:nowrap;align-items:center;gap:6px;margin-bottom:1.5rem;overflow-x:auto;">
            @php $activeStatus = request('status', 'all'); @endphp
            <div id="pub-filter-tabs" style="display:flex;gap:4px;flex-wrap:wrap;">
                <button class="pub-tab {{ $activeStatus === 'all' ? 'active' : '' }}" data-filter="all" onclick="pubFilterFixtures('all', this)">All</button>
                <button class="pub-tab {{ $activeStatus === 'upcoming' ? 'active' : '' }}" data-filter="upcoming" onclick="pubFilterFixtures('upcoming', this)">Upcoming</button>
                <button class="pub-tab {{ $activeStatus === 'live' ? 'active' : '' }}" data-filter="live" onclick="pubFilterFixtures('live', this)">Live</button>
                <button class="pub-tab {{ $activeStatus === 'completed' ? 'active' : '' }}" data-filter="completed" onclick="pubFilterFixtures('completed', this)">Completed</button>
            </div>
            <span style="width:1px;height:20px;background:var(--navy);opacity:.25;flex-shrink:0;"></span>
            @php
                $fromRaw = request('date_from');
                $toRaw = request('date_to');
                try { $fromDate = $fromRaw ? \Carbon\Carbon::parse($fromRaw) : now(); } catch (\Throwable $e) { $fromRaw = null; $fromDate = now(); }
                try { $toDate = $toRaw ? \Carbon\Carbon::parse($toRaw) : now(); } catch (\Throwable $e) { $toRaw = null; $toDate = now(); }
            @endphp
            <select id="pub-league-filter" class="fold-select" onchange="pubApplyFilters()">
                <option value="" {{ request('league_id') === null ? 'selected' : '' }}>All Leagues</option>
                @foreach($leagues as $league)
                    <option value="{{ $league->id }}" {{ (string) request('league_id') === (string) $league->id ? 'selected' : '' }}>{{ $league->name }}</option>
                @endforeach
            </select>
            <select id="pub-team-filter" class="fold-select" onchange="pubApplyFilters()">
                <option value="" {{ request('team_id') === null ? 'selected' : '' }}>All Teams</option>
                @foreach($teams as $team)
                    <option value="{{ $team->id }}" {{ (string) request('team_id') === (string) $team->id ? 'selected' : '' }}>{{ $team->name }}</option>
                @endforeach
            </select>
            <input type="date" id="pub-date-from" class="pub-pill" data-datepicker placeholder="From" value="{{ $fromRaw }}" onchange="pubApplyFilters()">
            <input type="date" id="pub-date-to" class="pub-pill" data-datepicker placeholder="To" value="{{ $toRaw }}" onchange="pubApplyFilters()">
            <button onclick="pubResetFilters()" class="pub-pill" style="cursor:pointer;">Reset</button>
        </div>

        <div id="pub-fixtures-list" style="display:flex;flex-direction:column;gap:1rem;">
            @forelse($fixtures as $fixture)
                @php
                    $hasScores = $fixture->home_score !== null && $fixture->away_score !== null;
                    $homeWin = $fixture->status === 'completed' && $hasScores && $fixture->home_score > $fixture->away_score;
                    $awayWin = $fixture->status === 'completed' && $hasScores && $fixture->away_score > $fixture->home_score;
                    $draw = $fixture->status === 'completed' && $hasScores && (int) $fixture->home_score === (int) $fixture->away_score;
                @endphp
                <div class="pub-fixture-card pub-fixture-reveal"
                     data-status="{{ $fixture->status }}"
                     data-league="{{ $fixture->league_id }}"
                     data-home="{{ $fixture->home_team_id }}"
                     data-away="{{ $fixture->away_team_id }}"
                     data-date="{{ $fixture->date->format('Y-m-d') }}"
                     style="background:var(--pin-white);border:2px solid var(--navy);border-radius:10px;padding:1.25rem 1.5rem;display:grid;grid-template-columns:1fr auto 1fr;align-items:center;gap:1rem;transition:transform 0.15s,box-shadow 0.15s;"
                     onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='var(--shadow-lg)'"
                     onmouseout="this.style.transform='';this.style.boxShadow=''">

                    <div style="text-align:right;">
                        <div style="font-family:var(--font-header);font-size:1rem;color:var(--navy);{{ $homeWin ? 'color:var(--gold);' : ($draw ? '' : '') }}">
                            {{ $fixture->homeTeam->name }}
                        </div>
                        <div style="font-family:var(--font-sub);font-size:0.7rem;color:var(--slate);">{{ $fixture->homeTeam->league->name }}</div>
                        @if($fixture->status === 'completed' && $hasScores)
                            <div style="margin-top:0.35rem;">
                                @if($homeWin)
                                    <span style="font-family:var(--font-mono);font-size:0.65rem;padding:2px 10px;background:var(--gold-light);color:var(--navy);border-radius:50px;border:1.5px solid var(--gold);">W</span>
                                @elseif($draw)
                                    <span style="font-family:var(--font-mono);font-size:0.65rem;padding:2px 10px;background:var(--fog);color:var(--navy);border-radius:50px;border:1.5px solid var(--slate);">D</span>
                                @else
                                    <span style="font-family:var(--font-mono);font-size:0.65rem;padding:2px 10px;background:var(--coral-light);color:var(--navy);border-radius:50px;border:1.5px solid var(--coral);">L</span>
                                @endif
                            </div>
                        @elseif($fixture->status === 'completed')
                            <div style="margin-top:0.35rem;">
                                <span style="font-family:var(--font-mono);font-size:0.65rem;padding:2px 10px;background:var(--mist);color:var(--slate);border-radius:50px;border:1.5px solid var(--slate);">&mdash;</span>
                            </div>
                        @endif
                    </div>

                    <div style="text-align:center;min-width:120px;">
                        @if($fixture->status === 'completed')
                            <div style="font-family:var(--font-mono);font-size:1.8rem;font-weight:700;color:var(--navy);line-height:1;">
                                {{ $fixture->home_score ?? '—' }}&mdash;{{ $fixture->away_score ?? '—' }}
                            </div>
                            <div style="font-family:var(--font-mono);font-size:0.6rem;text-transform:uppercase;letter-spacing:1px;color:var(--slate);margin-top:0.25rem;">Final</div>
                        @elseif($fixture->status === 'live')
                            <div style="font-family:var(--font-mono);font-size:1.8rem;font-weight:700;color:var(--coral);line-height:1;">
                                {{ $fixture->home_score ?? 0 }}&mdash;{{ $fixture->away_score ?? 0 }}
                            </div>
                            <div style="display:flex;align-items:center;justify-content:center;gap:6px;margin-top:0.25rem;">
                                <span style="width:8px;height:8px;border-radius:50%;background:var(--coral);animation:pub-pulse 1s ease-in-out infinite;"></span>
                                <span style="font-family:var(--font-mono);font-size:0.6rem;text-transform:uppercase;letter-spacing:1px;color:var(--coral);">Pins Falling</span>
                            </div>
                        @else
                            <div style="font-family:var(--font-mono);font-size:0.8rem;color:var(--slate);">{{ $fixture->date->format('M j, Y') }}</div>
                            <div style="font-family:var(--font-mono);font-size:0.95rem;color:var(--navy);font-weight:600;">{{ \Carbon\Carbon::parse($fixture->time)->format('g:i A') }}</div>
                            @if($fixture->lane)
                                <div style="font-family:var(--font-mono);font-size:0.6rem;color:var(--fog);margin-top:0.15rem;">Lane {{ $fixture->lane->lane_number }}</div>
                            @endif
                        @endif
                    </div>

                    <div>
                        <div style="font-family:var(--font-header);font-size:1rem;color:var(--navy);{{ $awayWin ? 'color:var(--gold);' : '' }}">
                            {{ $fixture->awayTeam->name }}
                        </div>
                        <div style="font-family:var(--font-sub);font-size:0.7rem;color:var(--slate);">{{ $fixture->awayTeam->league->name }}</div>
                        @if($fixture->status === 'completed')
                            <div style="margin-top:0.35rem;">
                                @if($awayWin)
                                    <span style="font-family:var(--font-mono);font-size:0.65rem;padding:2px 10px;background:var(--gold-light);color:var(--navy);border-radius:50px;border:1.5px solid var(--gold);">W</span>
                                @elseif($draw)
                                    <span style="font-family:var(--font-mono);font-size:0.65rem;padding:2px 10px;background:var(--fog);color:var(--navy);border-radius:50px;border:1.5px solid var(--slate);">D</span>
                                @else
                                    <span style="font-family:var(--font-mono);font-size:0.65rem;padding:2px 10px;background:var(--coral-light);color:var(--navy);border-radius:50px;border:1.5px solid var(--coral);">L</span>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div style="text-align:center;padding:4rem 2rem;background:var(--pin-white);border:2px solid var(--navy);border-radius:12px;">
                    <div style="font-size:3rem;margin-bottom:1rem;">🎳</div>
                    <h3 style="font-family:var(--font-header);color:var(--navy);margin-bottom:0.5rem;">The Lanes Are Quiet</h3>
                    <p style="font-family:var(--font-sub);color:var(--slate);">No matches on the board yet — check back once the leagues roll into town.</p>
                </div>
            @endforelse
        </div>

        @if($fixtures->count() > 0)
        <div style="margin-top:2.5rem;">
            <div class="lane-stripe" style="margin-bottom:1.5rem;"></div>
            <h2 style="font-family:var(--font-header);font-size:1.2rem;text-transform:uppercase;color:var(--navy);margin-bottom:1rem;">The Scoreboard</h2>
            <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(300px, 1fr));gap:1rem;">
                @foreach($leagues as $league)
                    <div style="background:var(--pin-white);border:2px solid var(--navy);border-radius:10px;overflow:hidden;">
                        <div style="background:var(--navy);padding:0.6rem 1rem;">
                            <span style="font-family:var(--font-header);font-size:0.75rem;color:var(--pin-white);text-transform:uppercase;letter-spacing:1px;">{{ $league->name }}</span>
                            <span style="font-family:var(--font-mono);font-size:0.6rem;color:var(--fog);margin-left:0.5rem;">{{ $league->season }}</span>
                        </div>
                        <table style="width:100%;border-collapse:collapse;">
                            <thead>
                                <tr style="border-bottom:2px solid var(--navy);">
                                    <th style="font-family:var(--font-mono);font-size:0.6rem;text-transform:uppercase;letter-spacing:1px;color:var(--slate);padding:0.5rem 1rem;text-align:left;">Team</th>
                                    <th style="font-family:var(--font-mono);font-size:0.6rem;text-transform:uppercase;letter-spacing:1px;color:var(--slate);padding:0.5rem;text-align:center;">W</th>
                                    <th style="font-family:var(--font-mono);font-size:0.6rem;text-transform:uppercase;letter-spacing:1px;color:var(--slate);padding:0.5rem;text-align:center;">L</th>
                                    <th style="font-family:var(--font-mono);font-size:0.6rem;text-transform:uppercase;letter-spacing:1px;color:var(--slate);padding:0.5rem;text-align:center;">D</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($league->teams->sortByDesc(['wins', 'draws']) as $team)
                                    <tr style="border-bottom:1px solid var(--fog);" onmouseover="this.style.background='var(--mist)'" onmouseout="this.style.background=''">
                                        <td style="font-family:var(--font-sub);font-size:0.85rem;padding:0.5rem 1rem;color:var(--navy);">{{ $team->name }}</td>
                                        <td style="font-family:var(--font-mono);font-size:0.85rem;text-align:center;color:var(--gold);font-weight:700;">{{ $team->wins }}</td>
                                        <td style="font-family:var(--font-mono);font-size:0.85rem;text-align:center;color:var(--coral);">{{ $team->losses }}</td>
                                        <td style="font-family:var(--font-mono);font-size:0.85rem;text-align:center;color:var(--slate);">{{ $team->draws }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

    </main>

    @include('site.partials.core-footer')

    <script src="/js/datepicker.js"></script>
    <script>
    (function() {
        @if($nextMatch)
        var nextDate = new Date('{{ \Carbon\Carbon::parse($nextMatch->date->format('Y-m-d') . ' ' . $nextMatch->time->format('H:i'))->toIso8601String() }}');
        function pubUpdateCountdown() {
            var now = new Date();
            var diff = nextDate - now;
            if (diff <= 0) {
                document.getElementById('pub-next-countdown').textContent = 'NOW';
                return;
            }
            var d = Math.floor(diff / 86400000);
            var h = Math.floor((diff % 86400000) / 3600000);
            var m = Math.floor((diff % 3600000) / 60000);
            var s = Math.floor((diff % 60000) / 1000);
            var parts = [];
            if (d > 0) parts.push(d + 'd');
            parts.push(h + 'h');
            parts.push(m + 'm');
            parts.push(s + 's');
            document.getElementById('pub-next-countdown').textContent = parts.join(' ');
        }
        pubUpdateCountdown();
        setInterval(pubUpdateCountdown, 1000);
        @endif

        var currentTab = 'all';

        window.pubFilterFixtures = function(status, btn) {
            currentTab = status;
            document.querySelectorAll('.pub-tab').forEach(function(t) { t.classList.remove('active'); });
            btn.classList.add('active');
            pubApplyFilters();
        };

        window.pubApplyFilters = function() {
            var leagueId = document.getElementById('pub-league-filter').value;
            var teamId = document.getElementById('pub-team-filter').value;
            var dateFrom = document.getElementById('pub-date-from').value;
            var dateTo = document.getElementById('pub-date-to').value;
            var cards = document.querySelectorAll('.pub-fixture-card');

            cards.forEach(function(card, i) {
                var show = true;
                var cardStatus = card.dataset.status;
                var cardLeague = card.dataset.league;
                var cardHome = card.dataset.home;
                var cardAway = card.dataset.away;
                var cardDate = card.dataset.date;

                if (currentTab !== 'all' && cardStatus !== currentTab) show = false;
                if (leagueId && cardLeague !== leagueId) show = false;
                if (teamId && cardHome !== teamId && cardAway !== teamId) show = false;
                if (dateFrom && cardDate < dateFrom) show = false;
                if (dateTo && cardDate > dateTo) show = false;

                card.style.display = show ? '' : 'none';
                if (show) {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(12px)';
                    setTimeout(function() {
                        card.style.transition = 'opacity 0.3s ease-out, transform 0.3s ease-out';
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, i * 50);
                }
            });
        };

        window.pubResetFilters = function() {
            currentTab = 'all';
            document.querySelectorAll('.pub-tab').forEach(function(t) { t.classList.remove('active'); });
            var allTab = document.querySelector('.pub-tab[data-filter="all"]');
            if (allTab) allTab.classList.add('active');
            document.getElementById('pub-league-filter').value = '';
            document.getElementById('pub-team-filter').value = '';
            var fromPicker = typeof DatePicker !== 'undefined' && DatePicker.getInstance ? DatePicker.getInstance('pub-date-from') : null;
            var toPicker = typeof DatePicker !== 'undefined' && DatePicker.getInstance ? DatePicker.getInstance('pub-date-to') : null;
            if (fromPicker) { fromPicker.clear(); } else { var f = document.getElementById('pub-date-from'); if (f) f.value = ''; }
            if (toPicker) { toPicker.clear(); } else { var t = document.getElementById('pub-date-to'); if (t) t.value = ''; }
            pubApplyFilters();
        };

        var reveals = document.querySelectorAll('.pub-fixture-reveal');
        if (reveals.length && 'IntersectionObserver' in window) {
            var observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.05 });
            reveals.forEach(function(el, i) {
                el.style.opacity = '0';
                el.style.transform = 'translateY(20px)';
                el.style.transition = 'opacity 0.5s ease-out ' + (i * 0.08) + 's, transform 0.5s ease-out ' + (i * 0.08) + 's';
                observer.observe(el);
            });
        }
    })();
    </script>

    <style>
    .pub-tab {
        font-family: var(--font-mono);
        font-size: 0.65rem;
        padding: 5px 12px;
        border: 2px solid var(--navy);
        border-radius: 50px;
        background: var(--pin-white);
        color: var(--navy);
        cursor: pointer;
        transition: background 0.15s, color 0.15s, transform 0.15s;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        white-space: nowrap;
    }
    .pub-tab:hover { background: var(--mist); transform: translateY(-1px); }
    .pub-tab.active { background: var(--navy); color: var(--gold-light); }
    .pub-filter-bar { gap: 6px; }
    .pub-filter-bar .custom-select-trigger { border-radius: 50px; min-height: 0; padding: 0; }
    .pub-filter-bar .custom-select-trigger-inner { padding: 5px 10px; min-height: 0; gap: 6px; }
    .pub-filter-bar .custom-select-trigger-inner .label { font-family: var(--font-mono); font-size: 0.65rem; white-space: nowrap; }
    .pub-filter-bar .custom-select-trigger-inner .arrow { border-left-width: 4px; border-right-width: 4px; border-top-width: 5px; }
    .pub-filter-bar .custom-select-dropdown { min-width: 140px; }
    .pub-pill {
        font-family: var(--font-mono);
        font-size: 0.65rem;
        padding: 5px 12px;
        border: 2px solid var(--navy);
        border-radius: 50px;
        background: var(--pin-white);
        color: var(--navy);
        cursor: pointer;
        white-space: nowrap;
        outline: none;
    }
    .pub-pill:focus { border-color: var(--gold); }
    #pub-date-from + .dpicker-display,
    #pub-date-to + .dpicker-display { width: auto; min-width: 0; }
    .pub-filter-bar .dpicker-wrap { width: auto; flex: 0 0 auto; }
    @keyframes pub-pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }
    @media (max-width: 768px) {
        .pub-fixture-card { grid-template-columns: 1fr !important; text-align: center !important; }
        .pub-fixture-card > div { text-align: center !important; }
    }
    @media (prefers-reduced-motion: reduce) {
        .pub-fixture-reveal { opacity: 1 !important; transform: none !important; transition: none !important; }
        .pub-tab { transition: none !important; }
        @keyframes pub-pulse { 0%, 100% { opacity: 1; } }
    }
    </style>

    @include('sim.partials.fold-controls')
</body>
</html>
