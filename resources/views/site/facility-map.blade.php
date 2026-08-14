<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Facility Map - The Tenth Frame</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
        .pub-facility-page{min-height:100vh;}
        .pub-facility-hero{padding:7rem 2rem 1.5rem;text-align:center;}
        .pub-facility-crumbs{font-family:var(--font-mono);font-size:.65rem;color:var(--slate);letter-spacing:1px;text-transform:uppercase;margin-bottom:.75rem;}
        .pub-facility-crumbs a{color:var(--slate);text-decoration:none;}
        .pub-facility-crumbs a:hover{color:var(--coral);}
        .pub-facility-hero h1{font-family:var(--font-header);text-transform:uppercase;font-size:2.3rem;color:var(--navy);margin:0 0 .6rem;}
        .pub-facility-hero p{font-family:var(--font-sub);color:var(--slate);font-size:1rem;max-width:560px;margin:0 auto;}
        .pub-facility-count{font-family:var(--font-mono);font-size:.8rem;color:var(--gold-dust);display:inline-block;margin-top:.75rem;background:var(--pin-white);border:2px solid var(--navy);border-radius:50px;padding:.35rem .9rem;}
        .pub-facility-hint{font-family:var(--font-mono);font-size:.7rem;color:var(--slate);margin-top:.5rem;}

        .pub-facility-stage-wrap{max-width:1100px;margin:0 auto;padding:1.5rem 1.5rem 3rem;}
        .pub-facility-stage{position:relative;background:var(--pin-white);border:3px solid var(--navy);border-radius:16px;box-shadow:var(--shadow-lg);overflow:hidden;}
        .pub-facility-map{display:block;width:100%;height:auto;}

        .pub-fz{cursor:pointer;outline:none;opacity:1;transition:opacity .2s;animation:pubFacilityIn .5s ease-out;}
        .pub-fz .pub-fz-body{transition:fill .2s,stroke .2s;stroke:var(--navy);stroke-width:2.5;}
        .pub-fz:hover .pub-fz-body,.pub-fz:focus-visible .pub-fz-body,.pub-fz.is-hovered .pub-fz-body{fill:var(--gold-light);stroke:var(--gold-dust);stroke-width:3;}
        .pub-fz.is-active .pub-fz-body{fill:var(--gold-light);stroke:var(--gold-dust);stroke-width:3.5;filter:drop-shadow(0 2px 6px rgba(212,168,76,.5));}
        .pub-fz:focus-visible{outline:3px solid var(--coral);outline-offset:3px;}
        .pub-stage.dimmed .pub-fz:not(.is-active){opacity:.5;}
        .pub-fz-label{font-family:var(--font-header);text-transform:uppercase;letter-spacing:1px;fill:var(--navy);pointer-events:none;}
        .pub-fz-emoji{pointer-events:none;}
        .pub-facility-deco{pointer-events:none;}
        @keyframes pubFacilityIn{from{transform:translateY(10px)}to{transform:translateY(0)}}

        .pub-facility-pulse{animation:pubFacilityPulse 2.2s ease-out infinite;transform-box:fill-box;transform-origin:center;}
        @keyframes pubFacilityPulse{0%{transform:scale(.5);opacity:.9}100%{transform:scale(1.8);opacity:0}}

        .pub-facility-tooltip{position:absolute;pointer-events:none;background:var(--navy);color:var(--pin-white);border-radius:8px;padding:.55rem .8rem;box-shadow:var(--shadow-md);z-index:20;opacity:0;transition:opacity .12s;transform:translate(16px,-50%);max-width:230px;white-space:nowrap;}
        .pub-facility-tooltip.is-visible{opacity:1;}
        .pub-facility-tooltip-name{font-family:var(--font-sub);font-size:.85rem;color:var(--gold-light);margin-bottom:.15rem;}
        .pub-facility-tooltip-sub{font-family:var(--font-mono);font-size:.68rem;color:var(--fog);display:flex;align-items:center;gap:6px;}
        .pub-facility-status{width:8px;height:8px;border-radius:50%;display:inline-block;flex:none;}
        .pub-facility-status.open{background:var(--gold);}
        .pub-facility-status.closed{background:var(--slate);}

        .pub-facility-panel{position:absolute;top:0;right:0;bottom:0;width:min(340px,100%);background:var(--pin-white);border-left:3px solid var(--navy);box-shadow:var(--shadow-lg);transform:translateX(103%);transition:transform .25s ease;z-index:30;display:flex;flex-direction:column;overflow:auto;}
        .pub-facility-panel.is-open{transform:translateX(0);}
        .pub-facility-panel-head{background:var(--navy);color:var(--pin-white);padding:1rem 1.25rem;display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;border-bottom:3px solid var(--gold);}
        .pub-facility-panel-emoji{font-size:1.6rem;display:block;margin-bottom:.3rem;}
        .pub-facility-panel-title{font-family:var(--font-header);font-size:1rem;text-transform:uppercase;letter-spacing:1px;margin:0;line-height:1.2;}
        .pub-facility-panel-close{background:none;border:none;color:var(--fog);font-size:1.1rem;cursor:pointer;padding:.25rem;line-height:1;border-radius:4px;transition:color .15s,background .15s;}
        .pub-facility-panel-close:hover{color:var(--pin-white);background:rgba(255,255,255,.12);}
        .pub-facility-panel-body{padding:1.25rem;}
        .pub-facility-panel-meta{display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;margin-bottom:1rem;}
        .pub-facility-panel-status{font-family:var(--font-header);font-size:.7rem;text-transform:uppercase;letter-spacing:1px;padding:.25rem .7rem;border-radius:50px;}
        .pub-facility-panel-status.open{background:var(--gold);color:var(--navy);}
        .pub-facility-panel-status.closed{background:var(--slate);color:var(--pin-white);}
        .pub-facility-panel-hours{font-family:var(--font-mono);font-size:.75rem;color:var(--slate);}
        .pub-facility-panel-desc{font-family:var(--font-body);font-size:.9rem;color:var(--navy);line-height:1.6;margin:0 0 1.25rem;}
        .pub-facility-panel-body h3{font-family:var(--font-sub);font-size:.75rem;text-transform:uppercase;letter-spacing:1px;color:var(--slate);margin:0 0 .6rem;}
        .pub-facility-list{list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:.55rem;}
        .pub-facility-list li{font-family:var(--font-body);font-size:.88rem;color:var(--navy);position:relative;padding-left:1.4rem;}
        .pub-facility-list li::before{content:'';position:absolute;left:0;top:50%;width:10px;height:10px;border-radius:50%;background:var(--gold);border:2px solid var(--navy);transform:translateY(-50%);}

        .pub-facility-legend{display:flex;flex-wrap:wrap;gap:.7rem;justify-content:center;margin-top:1.25rem;}
        .pub-facility-chip{display:flex;align-items:center;gap:.55rem;padding:.45rem .85rem;background:var(--pin-white);border:2px solid var(--navy);border-radius:50px;cursor:pointer;font-family:var(--font-sub);font-size:.75rem;color:var(--navy);transition:transform .15s,box-shadow .15s,background .15s;animation:pubFacilityIn .5s ease-out both;}
        .pub-facility-chip:hover{transform:translateY(-2px);box-shadow:var(--shadow-md);}
        .pub-facility-chip.is-active{background:var(--gold-light);}
        .pub-facility-swatch{width:14px;height:14px;border-radius:50%;border:2px solid var(--navy);flex:none;}
        .pub-facility-chip-hours{font-family:var(--font-mono);font-size:.62rem;color:var(--slate);}
        .pub-facility-swatch-lanes{background:var(--lane-wood-light);}
        .pub-facility-swatch-snack-bar{background:var(--coral-light);}
        .pub-facility-swatch-arcade{background:var(--sky);}
        .pub-facility-swatch-lounge{background:var(--sky-light);}
        .pub-facility-swatch-restaurant{background:var(--gold-light);}
        .pub-facility-swatch-pro-shop{background:var(--gold-light);}
        .pub-facility-swatch-washrooms{background:var(--mist);}
        .pub-facility-swatch-parking{background:var(--fog);}

        @media(max-width:760px){
            .pub-facility-tooltip{display:none;}
            .pub-facility-panel{top:auto;right:0;bottom:0;left:0;width:100%;max-height:62vh;border-left:none;border-top:3px solid var(--navy);transform:translateY(103%);}
            .pub-facility-panel.is-open{transform:translateY(0);}
            .pub-facility-hero h1{font-size:1.6rem;}
        }
    </style>
</head>
<body class="pub-facility-page" style="min-height:100vh;">

    <header style="position:fixed;top:0;left:0;right:0;z-index:52;background:rgba(245,248,250,0.95);backdrop-filter:blur(8px);border-bottom:3px solid var(--navy);padding:0.75rem 2rem;display:flex;align-items:center;justify-content:space-between;">
        <a href="/" style="text-decoration:none;display:flex;align-items:center;gap:10px;">
            <div class="ball-accent" style="width:32px;height:32px;"></div>
            <span style="font-family:var(--font-display);font-size:1.3rem;color:var(--navy);text-transform:uppercase;">The Tenth Frame</span>
        </a>
        <nav style="display:flex;align-items:center;gap:1.25rem;">
            <a href="/" style="font-family:var(--font-sub);color:var(--slate);text-decoration:none;font-size:0.85rem;">Home</a>
            <a href="{{ route('site.facility-map') }}" style="font-family:var(--font-sub);color:var(--sky-dark);text-decoration:none;font-size:0.85rem;border-bottom:2px solid var(--coral);">Facility Map</a>
            @if (Route::has('login'))
                @auth
                    @if(Auth::user()->role === 'admin')
                        <a href="{{ route('site.announcements.index') }}" style="font-family:var(--font-sub);color:var(--slate);text-decoration:none;font-size:0.85rem;">Manage Announcements</a>
                    @endif
                    <a href="{{ url('/dashboard') }}" class="btn" style="padding:8px 24px;font-size:0.85rem;">Dashboard</a>
                @else
                    <a href="{{ route('public.fixtures') }}" style="font-family:var(--font-sub);color:var(--slate);text-decoration:none;font-size:0.85rem;">Fixtures</a>
                    <a href="{{ route('login') }}" style="font-family:var(--font-sub);color:var(--navy);text-decoration:none;padding:6px 14px;border-radius:50px;transition:background 0.15s,color 0.15s;" onmouseover="this.style.background='var(--mist)';this.style.color='var(--sky-dark)'" onmouseout="this.style.background='';this.style.color='var(--navy)'">Sign In</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn" style="padding:8px 20px;font-size:0.8rem;">Join the Club</a>
                    @endif
                @endauth
            @endif
        </nav>
    </header>

    <section class="pub-facility-hero">
        <div class="pub-facility-crumbs"><a href="/">Home</a> / Facility Map</div>
        <h1>Find Your Way Around</h1>
        <p>Every lane leads somewhere. Hover a zone for a quick look, click it for the full tour &mdash; and know exactly where to go before your ball's even out of the rack.</p>
        <span class="pub-facility-count" id="pub-facility-count">Checking the lights...</span>
        <div class="pub-facility-hint">Hover for a peek &middot; Click for the full rundown &middot; Your keyboard works too</div>
    </section>

    <div class="pub-facility-stage-wrap">
        <div class="pub-facility-stage" id="pub-facility-stage">
            @php
                $zoneKeys = collect($zones)->pluck('map_key');
                $pins = [];
                for ($r = 0; $r < 4; $r++) {
                    $cnt = 4 - $r;
                    for ($c = 0; $c < $cnt; $c++) {
                        $pins[] = [405 + $r * 7, 13 + ($c - ($cnt - 1) / 2) * 7];
                    }
                }
            @endphp
            <svg class="pub-facility-map" viewBox="0 0 1000 740" role="img" aria-label="Floor plan of The Tenth Frame Bowling Club" id="pub-facility-map">
                <title>Floor plan of The Tenth Frame Bowling Club</title>

                <defs>
                    <pattern id="pub-pattern-lanes" width="8" height="8" patternUnits="userSpaceOnUse">
                        <rect width="8" height="8" fill="var(--lane-wood-light)"/>
                        <rect y="5" width="8" height="2" fill="rgba(26,42,58,0.08)"/>
                    </pattern>
                    <pattern id="pub-lane-wood" width="8" height="8" patternUnits="userSpaceOnUse">
                        <rect width="8" height="8" fill="var(--lane-wood-light)"/>
                        <rect y="5" width="8" height="2" fill="rgba(26,42,58,0.08)"/>
                    </pattern>
                    <pattern id="pub-pattern-snack" width="10" height="10" patternUnits="userSpaceOnUse">
                        <rect width="10" height="10" fill="var(--coral-light)"/>
                        <circle cx="2" cy="2" r="1.3" fill="rgba(255,255,255,0.6)"/>
                    </pattern>
                    <pattern id="pub-pattern-arcade" width="12" height="12" patternUnits="userSpaceOnUse">
                        <rect width="12" height="12" fill="var(--sky)"/>
                        <path d="M12 0H0V12" fill="none" stroke="rgba(26,42,58,0.14)" stroke-width="1"/>
                    </pattern>
                    <pattern id="pub-pattern-lounge" width="10" height="10" patternUnits="userSpaceOnUse">
                        <rect width="10" height="10" fill="var(--sky-light)"/>
                        <circle cx="2" cy="2" r="1.3" fill="rgba(255,255,255,0.5)"/>
                    </pattern>
                    <pattern id="pub-pattern-restaurant" width="8" height="8" patternTransform="rotate(45)" patternUnits="userSpaceOnUse">
                        <rect width="8" height="8" fill="var(--gold-light)"/>
                        <rect width="3" height="8" fill="rgba(26,42,58,0.1)"/>
                    </pattern>
                    <pattern id="pub-pattern-shop" width="12" height="12" patternUnits="userSpaceOnUse">
                        <rect width="12" height="12" fill="var(--gold-light)"/>
                        <path d="M12 0H0V12" fill="none" stroke="rgba(26,42,58,0.12)" stroke-width="1"/>
                    </pattern>
                    <pattern id="pub-pattern-wash" width="10" height="10" patternUnits="userSpaceOnUse">
                        <rect width="10" height="10" fill="var(--mist)"/>
                        <circle cx="2" cy="2" r="1.3" fill="rgba(26,42,58,0.12)"/>
                    </pattern>
                    <pattern id="pub-pattern-parking" width="12" height="12" patternTransform="rotate(45)" patternUnits="userSpaceOnUse">
                        <rect width="12" height="12" fill="var(--fog)"/>
                        <rect width="4" height="12" fill="rgba(26,42,58,0.1)"/>
                    </pattern>
                    <pattern id="pub-floor-grid" width="20" height="20" patternUnits="userSpaceOnUse">
                        <path d="M20 0H0V20" fill="none" stroke="rgba(26,42,58,0.06)" stroke-width="1"/>
                    </pattern>
                </defs>

                <rect x="70" y="652" width="860" height="13" fill="var(--cloud)"/>
                <rect x="70" y="50" width="860" height="600" rx="16" fill="var(--mist)"/>
                <rect x="70" y="50" width="860" height="600" rx="16" fill="url(#pub-floor-grid)"/>

                @if($zoneKeys->contains('lanes'))
                <g class="pub-fz" data-key="lanes" tabindex="0" role="button" aria-label="Championship Lanes, open today">
                    <rect class="pub-fz-body" x="90" y="118" width="470" height="520" rx="12" fill="url(#pub-pattern-lanes)"/>
                    <g class="pub-facility-deco">
                        @for($i = 0; $i < 12; $i++)
                            @php $ly = 138 + $i * 40; @endphp
                            <g transform="translate(100, {{ $ly }})">
                                <rect x="0" y="0" width="6" height="26" fill="var(--rubber)"/>
                                <rect x="6" y="0" width="428" height="26" fill="url(#pub-lane-wood)" stroke="rgba(26,42,58,0.35)" stroke-width="1"/>
                                <rect x="434" y="0" width="6" height="26" fill="var(--rubber)"/>
                                <polygon points="108,10 114,16 108,22" fill="rgba(26,42,58,0.4)"/>
                                <polygon points="120,10 114,16 120,22" fill="rgba(26,42,58,0.4)"/>
                                <polygon points="228,10 234,16 228,22" fill="rgba(26,42,58,0.4)"/>
                                <polygon points="240,10 234,16 240,22" fill="rgba(26,42,58,0.4)"/>
                                <rect x="398" y="0" width="36" height="26" fill="var(--pin-white)" stroke="var(--navy)" stroke-width="1.5"/>
                                @foreach($pins as $pin)
                                    <circle cx="{{ $pin[0] }}" cy="{{ $pin[1] }}" r="2.3" fill="var(--pin-white)" stroke="var(--navy)" stroke-width="1"/>
                                @endforeach
                            </g>
                        @endfor
                    </g>
                    <text class="pub-fz-label pub-facility-deco" x="100" y="132" font-size="15">Championship Lanes</text>
                    <text class="pub-facility-deco" x="540" y="138" text-anchor="end" font-size="20">&#127944;</text>
                </g>
                @endif

                @if($zoneKeys->contains('snack-bar'))
                <g class="pub-fz" data-key="snack-bar" tabindex="0" role="button" aria-label="Snack Bar">
                    <rect class="pub-fz-body" x="580" y="118" width="320" height="150" rx="12" fill="url(#pub-pattern-snack)"/>
                    <text class="pub-fz-label pub-facility-deco" x="720" y="198" text-anchor="middle" font-size="15">Snack Bar</text>
                    <text class="pub-fz-emoji pub-facility-deco" x="888" y="142" text-anchor="end" font-size="24">&#129380;</text>
                </g>
                @endif

                @if($zoneKeys->contains('arcade'))
                <g class="pub-fz" data-key="arcade" tabindex="0" role="button" aria-label="Arcade">
                    <rect class="pub-fz-body" x="580" y="288" width="320" height="170" rx="12" fill="url(#pub-pattern-arcade)"/>
                    <text class="pub-fz-label pub-facility-deco" x="740" y="378" text-anchor="middle" font-size="15">Arcade</text>
                    <text class="pub-fz-emoji pub-facility-deco" x="888" y="312" text-anchor="end" font-size="24">&#128377;</text>
                </g>
                @endif

                @if($zoneKeys->contains('lounge'))
                <g class="pub-fz" data-key="lounge" tabindex="0" role="button" aria-label="Lounge">
                    <rect class="pub-fz-body" x="580" y="478" width="150" height="160" rx="12" fill="url(#pub-pattern-lounge)"/>
                    <text class="pub-fz-label pub-facility-deco" x="655" y="562" text-anchor="middle" font-size="13">Lounge</text>
                    <text class="pub-fz-emoji pub-facility-deco" x="716" y="502" text-anchor="end" font-size="20">&#129340;</text>
                </g>
                @endif

                @if($zoneKeys->contains('restaurant'))
                <g class="pub-fz" data-key="restaurant" tabindex="0" role="button" aria-label="Restaurant">
                    <rect class="pub-fz-body" x="750" y="478" width="150" height="160" rx="12" fill="url(#pub-pattern-restaurant)"/>
                    <text class="pub-fz-label pub-facility-deco" x="825" y="562" text-anchor="middle" font-size="13">Restaurant</text>
                    <text class="pub-fz-emoji pub-facility-deco" x="886" y="502" text-anchor="end" font-size="20">&#127869;</text>
                </g>
                @endif

                @if($zoneKeys->contains('pro-shop'))
                <g class="pub-fz" data-key="pro-shop" tabindex="0" role="button" aria-label="Pro Shop">
                    <rect class="pub-fz-body" x="90" y="62" width="340" height="44" rx="8" fill="url(#pub-pattern-shop)"/>
                    <text class="pub-fz-emoji pub-facility-deco" x="106" y="90" font-size="16">&#127982;</text>
                    <text class="pub-fz-label pub-facility-deco" x="130" y="90" font-size="12">Pro Shop</text>
                </g>
                @endif

                @if($zoneKeys->contains('washrooms'))
                <g class="pub-fz" data-key="washrooms" tabindex="0" role="button" aria-label="Washrooms">
                    <rect class="pub-fz-body" x="450" y="62" width="440" height="44" rx="8" fill="url(#pub-pattern-wash)"/>
                    <text class="pub-fz-emoji pub-facility-deco" x="466" y="90" font-size="16">&#128701;</text>
                    <text class="pub-fz-label pub-facility-deco" x="490" y="90" font-size="12">Washrooms</text>
                </g>
                @endif

                @if($zoneKeys->contains('parking'))
                <g class="pub-fz" data-key="parking" tabindex="0" role="button" aria-label="Parking">
                    <rect class="pub-fz-body" x="70" y="665" width="860" height="60" rx="12" fill="url(#pub-pattern-parking)"/>
                    <text class="pub-fz-label pub-facility-deco" x="500" y="699" text-anchor="middle" font-size="14">Parking</text>
                    <text class="pub-fz-emoji pub-facility-deco" x="100" y="702" font-size="18">&#128663;</text>
                </g>
                @endif

                <path class="pub-facility-deco" d="M70 50 H930 M930 50 V650 M930 650 H570 M430 650 H70 M70 650 V50" fill="none" stroke="var(--navy)" stroke-width="5" stroke-linecap="round"/>
                <text class="pub-facility-deco" x="500" y="643" text-anchor="middle" font-family="var(--font-mono)" font-size="10" fill="var(--slate)" letter-spacing="2">ENTRANCE</text>

                <g class="pub-facility-deco">
                    <circle cx="500" cy="660" r="7" fill="var(--coral)" stroke="var(--navy)" stroke-width="2"/>
                    <circle cx="500" cy="660" r="12" fill="none" stroke="var(--coral)" stroke-width="2" class="pub-facility-pulse"/>
                    <text x="514" y="656" font-family="var(--font-mono)" font-size="10" fill="var(--navy)" font-weight="700">You are here</text>
                </g>
            </svg>

            <div class="pub-facility-tooltip" id="pub-facility-tooltip">
                <div class="pub-facility-tooltip-name" id="pub-facility-tooltip-name"></div>
                <div class="pub-facility-tooltip-sub" id="pub-facility-tooltip-sub"></div>
            </div>

            <div class="pub-facility-panel" id="pub-facility-panel" aria-live="polite">
                <div class="pub-facility-panel-head">
                    <div>
                        <span class="pub-facility-panel-emoji" id="pub-facility-panel-emoji"></span>
                        <h2 class="pub-facility-panel-title" id="pub-facility-panel-title"></h2>
                    </div>
                    <button class="pub-facility-panel-close" id="pub-facility-panel-close" aria-label="Close panel">&times;</button>
                </div>
                <div class="pub-facility-panel-body">
                    <div class="pub-facility-panel-meta">
                        <span class="pub-facility-panel-status" id="pub-facility-panel-status"></span>
                        <span class="pub-facility-panel-hours" id="pub-facility-panel-hours"></span>
                    </div>
                    <p class="pub-facility-panel-desc" id="pub-facility-panel-desc"></p>
                    <h3>What's in here</h3>
                    <ul class="pub-facility-list" id="pub-facility-list"></ul>
                </div>
            </div>
        </div>

        <div class="pub-facility-legend" id="pub-facility-legend" role="list"></div>
    </div>

    <footer style="background:var(--navy);color:var(--fog);padding:3rem 2rem;text-align:center;">
        <div class="ball-accent" style="width:28px;height:28px;margin:0 auto 1rem;"></div>
        <p style="font-family:var(--font-display);font-size:1.2rem;color:var(--pin-white);margin-bottom:0.5rem;">The Tenth Frame</p>
        <p style="font-family:var(--font-sub);font-size:0.85rem;color:var(--fog);">The Tenth Frame Bowling Club &copy; {{ date('Y') }}</p>
    </footer>

    <script>
    (function () {
        var ZONES = @json($zones);
        if (!ZONES || !ZONES.length) return;

        var EMOJI = {'lanes':'\u{1F3B3}','snack-bar':'\u{1F964}','arcade':'\u{1F579}','lounge':'\u{1F6CB}','restaurant':'\u{1F37D}','pro-shop':'\u{1F3EA}','washrooms':'\u{1F6BD}','parking':'\u{1F697}'};

        var byKey = {};
        ZONES.forEach(function (z) {
            z.emoji = EMOJI[z.map_key] || '';
            byKey[z.map_key] = z;
        });

        var stage = document.getElementById('pub-facility-stage');
        var svgMap = document.getElementById('pub-facility-map');
        var tooltip = document.getElementById('pub-facility-tooltip');
        var ttName = document.getElementById('pub-facility-tooltip-name');
        var ttSub = document.getElementById('pub-facility-tooltip-sub');
        var panel = document.getElementById('pub-facility-panel');
        var pEmoji = document.getElementById('pub-facility-panel-emoji');
        var pTitle = document.getElementById('pub-facility-panel-title');
        var pStatus = document.getElementById('pub-facility-panel-status');
        var pHours = document.getElementById('pub-facility-panel-hours');
        var pDesc = document.getElementById('pub-facility-panel-desc');
        var pList = document.getElementById('pub-facility-list');
        var legend = document.getElementById('pub-facility-legend');
        var countEl = document.getElementById('pub-facility-count');

        function timeToSec(t) {
            if (!t) return 0;
            var p = String(t).split(':');
            return (+p[0] || 0) * 3600 + (+p[1] || 0) * 60 + (+p[2] || 0);
        }
        function fmtTime(t) {
            if (!t) return '';
            var p = String(t).split(':');
            var h = +p[0], m = (p[1] || '00');
            var ap = h >= 12 ? 'PM' : 'AM';
            h = h % 12 || 12;
            return h + ':' + m + ' ' + ap;
        }
        function isOpen(z) {
            var now = new Date();
            var s = now.getHours() * 3600 + now.getMinutes() * 60 + now.getSeconds();
            var o = timeToSec(z.open_time), c = timeToSec(z.close_time);
            if (o === c) return true;
            return o < c ? (s >= o && s < c) : (s >= o || s < c);
        }
        function hours(z) {
            if (timeToSec(z.open_time) === timeToSec(z.close_time)) return 'Open 24 hours';
            return fmtTime(z.open_time) + ' \u2013 ' + fmtTime(z.close_time);
        }
        function isSmallScreen() {
            return window.matchMedia('(max-width: 760px)').matches;
        }

        function updateCount() {
            if (!countEl) return;
            var open = ZONES.filter(isOpen).length;
            countEl.textContent = open + ' of ' + ZONES.length + ' zones open right now';
        }

        function zoneEl(key) {
            return document.querySelector('.pub-fz[data-key="' + key + '"]');
        }

        function setZoneHover(key, on) {
            var el = zoneEl(key);
            if (el) el.classList.toggle('is-hovered', on);
        }

        function showTooltip(el) {
            var z = byKey[el.getAttribute('data-key')];
            if (!z || isSmallScreen()) return;
            ttName.textContent = z.name;
            ttSub.innerHTML = '<span class="pub-facility-status ' + (isOpen(z) ? 'open' : 'closed') + '"></span> ' + (isOpen(z) ? 'Open' : 'Closed') + ' \u00b7 ' + hours(z);
            tooltip.classList.add('is-visible');
        }

        function hideTooltip() {
            tooltip.classList.remove('is-visible');
        }

        function moveTooltip(e) {
            var r = stage.getBoundingClientRect();
            var x = e.clientX - r.left;
            var y = e.clientY - r.top;
            tooltip.style.left = Math.min(x, r.width - 240) + 'px';
            tooltip.style.top = y + 'px';
        }

        function openZone(key) {
            var z = byKey[key];
            if (!z) return;
            pEmoji.textContent = z.emoji;
            pTitle.textContent = z.name;
            var open = isOpen(z);
            pStatus.textContent = open ? 'OPEN' : 'CLOSED';
            pStatus.className = 'pub-facility-panel-status ' + (open ? 'open' : 'closed');
            pHours.textContent = hours(z);
            pDesc.textContent = z.description || '';
            pList.innerHTML = '';
            (z.facilities || []).forEach(function (f) {
                var li = document.createElement('li');
                li.textContent = f;
                pList.appendChild(li);
            });
            panel.classList.add('is-open');
            stage.classList.add('dimmed');
            document.querySelectorAll('.pub-fz.is-active').forEach(function (e) { e.classList.remove('is-active'); });
            var el = zoneEl(key);
            if (el) el.classList.add('is-active');
            document.querySelectorAll('.pub-facility-chip.is-active').forEach(function (c) { c.classList.remove('is-active'); });
            var chip = document.querySelector('.pub-facility-chip[data-key="' + key + '"]');
            if (chip) chip.classList.add('is-active');
            hideTooltip();
        }

        function closePanel() {
            panel.classList.remove('is-open');
            stage.classList.remove('dimmed');
            document.querySelectorAll('.pub-fz.is-active').forEach(function (e) { e.classList.remove('is-active'); });
            document.querySelectorAll('.pub-facility-chip.is-active').forEach(function (c) { c.classList.remove('is-active'); });
        }

        function buildLegend() {
            ZONES.forEach(function (z, i) {
                var chip = document.createElement('button');
                chip.type = 'button';
                chip.className = 'pub-facility-chip';
                chip.setAttribute('data-key', z.map_key);
                chip.style.animationDelay = (0.5 + i * 0.06) + 's';
                chip.innerHTML =
                    '<span class="pub-facility-swatch pub-facility-swatch-' + z.map_key + '"></span>' +
                    '<span>' + z.name + '</span>' +
                    '<span class="pub-facility-chip-hours">' + hours(z) + '</span>' +
                    '<span class="pub-facility-status ' + (isOpen(z) ? 'open' : 'closed') + '" title="' + (isOpen(z) ? 'Open now' : 'Closed now') + '"></span>';
                chip.addEventListener('click', function () { openZone(z.map_key); });
                chip.addEventListener('mouseenter', function () { setZoneHover(z.map_key, true); });
                chip.addEventListener('mouseleave', function () { setZoneHover(z.map_key, false); });
                legend.appendChild(chip);
            });
        }

        document.querySelectorAll('.pub-fz').forEach(function (el) {
            el.addEventListener('mouseenter', function () { showTooltip(el); });
            el.addEventListener('mousemove', moveTooltip);
            el.addEventListener('mouseleave', hideTooltip);
            el.addEventListener('focus', function () { showTooltip(el); });
            el.addEventListener('blur', hideTooltip);
            el.addEventListener('click', function () { openZone(el.getAttribute('data-key')); });
            el.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    openZone(el.getAttribute('data-key'));
                }
            });
        });

        svgMap.addEventListener('click', function (e) {
            if (!e.target.closest('.pub-fz')) closePanel();
        });

        document.getElementById('pub-facility-panel-close').addEventListener('click', closePanel);
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closePanel();
        });

        buildLegend();
        updateCount();
        setInterval(updateCount, 30000);
    })();
    </script>
</body>
</html>
