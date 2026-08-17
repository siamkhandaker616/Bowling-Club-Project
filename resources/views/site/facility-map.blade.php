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
        .pub-fz-label{font-family:var(--font-header);text-transform:uppercase;letter-spacing:1px;fill:var(--navy);pointer-events:none;}
        .pub-fz-emoji{pointer-events:none;}
        .pub-facility-deco{pointer-events:none;}
        @keyframes pubFacilityIn{from{transform:translateY(10px)}to{transform:translateY(0)}}

        .pub-facility-pulse{animation:pubFacilityPulse 2.2s ease-out infinite;transform-box:fill-box;transform-origin:center;}
        @keyframes pubFacilityPulse{0%{transform:scale(.5);opacity:.9}100%{transform:scale(1.8);opacity:0}}

        .pub-facility-hovercard{position:absolute;top:0;left:0;z-index:30;width:min(300px,calc(100% - 24px));background:var(--pin-white);border:3px solid var(--navy);border-radius:12px;box-shadow:var(--shadow-lg);padding:1rem 1.1rem;opacity:0;visibility:hidden;transform:translateY(6px);transition:opacity .13s ease,transform .13s ease,visibility .13s;pointer-events:none;}
        .pub-facility-hovercard.is-visible{opacity:1;visibility:visible;transform:translateY(0);pointer-events:auto;}
        .pub-hc-head{display:flex;align-items:flex-start;gap:.75rem;margin-bottom:.6rem;}
        .pub-hc-emoji{font-size:1.7rem;line-height:1;flex:none;}
        .pub-hc-name{font-family:var(--font-header);font-size:.95rem;text-transform:uppercase;letter-spacing:1px;color:var(--navy);line-height:1.25;margin-bottom:.3rem;}
        .pub-hc-meta{display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;}
        .pub-hc-badge{font-family:var(--font-header);font-size:.6rem;text-transform:uppercase;letter-spacing:1px;padding:.18rem .55rem;border-radius:50px;}
        .pub-hc-badge.open{background:var(--gold);color:var(--navy);}
        .pub-hc-badge.closed{background:var(--slate);color:var(--pin-white);}
        .pub-hc-badge.occupied{background:var(--coral-light);color:var(--coral);}
        .pub-hc-badge.maintenance{background:var(--mist);color:var(--slate);}
        .pub-hc-badge.reserved{background:var(--gold-light);color:var(--gold-dust);}
        .pub-hc-hours{font-family:var(--font-mono);font-size:.68rem;color:var(--slate);}
        .pub-hc-desc{font-family:var(--font-body);font-size:.86rem;color:var(--navy);line-height:1.55;margin:0 0 .75rem;}
        .pub-hc-facilities{margin-bottom:.6rem;}
        .pub-hc-oil{margin-bottom:.4rem;}
        .pub-hc-oil h3,.pub-hc-facilities h3{font-family:var(--font-sub);font-size:.7rem;text-transform:uppercase;letter-spacing:1px;color:var(--slate);margin:0 0 .55rem;}
        .pub-facility-status{width:8px;height:8px;border-radius:50%;display:inline-block;flex:none;}
        .pub-facility-status.open{background:var(--gold);}
        .pub-facility-status.closed{background:var(--slate);}
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

        .pub-lz{cursor:pointer;outline:none;pointer-events:auto;}
        .pub-lz .pub-lane-row{transition:fill .2s,stroke .2s;}
        .pub-lz:hover .pub-lane-row,.pub-lz:focus-visible .pub-lane-row,.pub-lz.is-hovered .pub-lane-row{fill:var(--gold-light);stroke:var(--gold-dust);stroke-width:2.5;}
        .pub-lz.is-active .pub-lane-row{fill:var(--gold-light);stroke:var(--gold-dust);stroke-width:3;}
        .pub-lz:focus-visible{outline:3px solid var(--coral);outline-offset:3px;}
        .pub-pinbox-open{fill:var(--pin-white);}
        .pub-pinbox-occupied{fill:var(--coral-light);}
        .pub-pinbox-maintenance{fill:var(--mist);}
        .pub-pinbox-reserved{fill:var(--gold-light);}
        .pub-fz[data-key="lanes"]:hover .pub-fz-body,.pub-fz[data-key="lanes"]:focus-visible .pub-fz-body,.pub-fz[data-key="lanes"].is-hovered .pub-fz-body{fill:url(#pub-pattern-lanes);stroke:var(--navy);stroke-width:2.5;}

        .pub-lane-board{margin-top:2rem;}
        .pub-lane-board-title{font-family:var(--font-header);text-transform:uppercase;letter-spacing:1px;font-size:.9rem;color:var(--navy);text-align:center;margin:0 0 .25rem;}
        .pub-lane-board-sub{font-family:var(--font-mono);font-size:.65rem;color:var(--slate);text-align:center;margin-bottom:.9rem;}
        .pub-lane-strip{display:flex;flex-wrap:wrap;gap:.5rem;justify-content:center;}
        .pub-lane-strip-chip{display:inline-flex;align-items:center;gap:.45rem;padding:.4rem .7rem;background:var(--pin-white);border:2px solid var(--navy);border-radius:8px;cursor:pointer;font-family:var(--font-mono);font-size:.68rem;color:var(--navy);transition:transform .15s,box-shadow .15s,background .15s;}
        .pub-lane-strip-chip:hover{transform:translateY(-2px);box-shadow:var(--shadow-md);}
        .pub-lane-strip-chip.is-active{background:var(--gold-light);}
        .pub-lane-dot-open{background:var(--sky);border:1.5px solid var(--sky-dark);}
        .pub-lane-dot-occupied{background:var(--coral-light);border:1.5px solid var(--coral);}
        .pub-lane-dot-maintenance{background:var(--mist);border:1.5px solid var(--slate);}
        .pub-lane-dot-reserved{background:var(--gold-light);border:1.5px solid var(--gold-dust);}

        .pub-oil-track{height:10px;border-radius:50px;background:var(--mist);border:2px solid var(--navy);overflow:hidden;margin-bottom:.4rem;}
        .pub-oil-fill{height:100%;border-radius:50px;background:linear-gradient(90deg,var(--gold-light),var(--gold));transition:width .3s;}
        .pub-lane-sub{font-family:var(--font-mono);font-size:.7rem;color:var(--slate);margin-bottom:1.1rem;}
        .pub-lane-actions{display:flex;flex-direction:column;gap:.6rem;border-top:2px dashed var(--fog);padding-top:1rem;margin-top:.4rem;}
        .pub-zone-action{display:block;text-align:center;background:var(--navy);color:var(--pin-white);font-family:var(--font-header);font-size:.7rem;text-transform:uppercase;letter-spacing:1px;padding:.7rem;border-radius:8px;text-decoration:none;transition:background .15s,transform .15s;}
        .pub-zone-action:hover{background:var(--sky-dark);transform:translateY(-1px);}
        .pub-zone-action.secondary{background:var(--pin-white);color:var(--navy);border:2px solid var(--navy);}
        .pub-zone-action.secondary:hover{background:var(--gold-light);}
        .pub-lane-actions form{display:flex;gap:.5rem;}
        .pub-lane-actions form button{flex:1;cursor:pointer;font-family:var(--font-header);font-size:.62rem;text-transform:uppercase;letter-spacing:.5px;padding:.6rem .4rem;border-radius:6px;border:2px solid var(--navy);background:var(--pin-white);color:var(--navy);transition:background .15s;}
        .pub-lane-actions form button:hover{background:var(--gold-light);}
        .pub-lane-actions form button.maint-toggle{background:var(--mist);}

        @media(max-width:760px){
            .pub-facility-hero h1{font-size:1.6rem;}
            .pub-facility-hovercard{width:calc(100% - 20px);}
        }
        @media (prefers-reduced-motion: reduce) {
            .pub-reveal { opacity: 1 !important; transform: none !important; transition: none !important; }
            .pub-lz { animation: none !important; }
        }
    </style>
</head>
<body class="pub-facility-page" style="min-height:100vh;">

    @component('site.partials.core-header')
        <a href="/" class="btn btn-ghost" style="padding:8px 20px;font-size:0.8rem;">Home</a>
        <a href="{{ route('site.facility-map') }}" class="btn btn-coral" style="padding:8px 20px;font-size:0.8rem;">Facility Map</a>
        @if(Auth::check() && Auth::user()->role === 'admin')
            <a href="{{ route('site.announcements.index') }}" class="btn btn-ghost" style="padding:8px 20px;font-size:0.8rem;">Manage Announcements</a>
        @endif
        @if(Auth::check() && Auth::user()->role === 'customer')
            <a href="{{ route('public.proshop.cart') }}" class="btn btn-ghost" style="padding:8px 20px;font-size:0.8rem;position:relative;">Bag
                @if(($bagCount ?? 0) > 0)<span style="position:absolute;top:-8px;right:-8px;min-width:18px;height:18px;border-radius:50%;background:var(--gold);color:var(--navy);font-family:var(--font-mono);font-size:.6rem;display:flex;align-items:center;justify-content:center;padding:0 4px;font-weight:700;">{{ $bagCount }}</span>@endif
            </a>
        @endif
        @if(!Auth::check())
            <a href="{{ route('public.fixtures') }}" class="btn btn-ghost" style="padding:8px 20px;font-size:0.8rem;">Fixtures</a>
        @endif
    @endcomponent

    <section class="pub-facility-hero">
        <div class="pub-facility-crumbs"><a href="/">Home</a> / Facility Map</div>
        <h1>Find Your Way Around</h1>
        <p>Every lane leads somewhere. Hover a zone for a quick look, click it for the full tour &mdash; and know exactly where to go before your ball's even out of the rack.</p>
        <span class="pub-facility-count" id="pub-facility-count">Checking the lights...</span>
        <div class="pub-facility-hint">Hover any zone or lane for its rundown &middot; Click to pin it open &middot; Your keyboard works too</div>
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
                        @foreach($lanes as $i => $lane)
                            @php
                                $ly = 138 + $i * 40;
                                $ln = $lane->lane_number;
                                $st = $lane->status;
                            @endphp
                            <g class="pub-lz" data-lane="{{ $ln }}" data-status="{{ $st }}" tabindex="0" role="button" aria-label="Lane {{ $ln }}, {{ $st }}">
                                <g transform="translate(100, {{ $ly }})">
                                    <rect x="0" y="0" width="6" height="26" fill="var(--rubber)"/>
                                    <rect class="pub-lane-row" x="6" y="0" width="428" height="26" fill="url(#pub-lane-wood)" stroke="rgba(26,42,58,0.35)" stroke-width="1"/>
                                    <rect x="434" y="0" width="6" height="26" fill="var(--rubber)"/>
                                    <polygon points="108,10 114,16 108,22" fill="rgba(26,42,58,0.4)"/>
                                    <polygon points="120,10 114,16 120,22" fill="rgba(26,42,58,0.4)"/>
                                    <polygon points="228,10 234,16 228,22" fill="rgba(26,42,58,0.4)"/>
                                    <polygon points="240,10 234,16 240,22" fill="rgba(26,42,58,0.4)"/>
                                    <rect class="pub-pinbox pub-pinbox-{{ $st }}" x="398" y="0" width="36" height="26" stroke="var(--navy)" stroke-width="1.5"/>
                                    @foreach($pins as $pin)
                                        <circle cx="{{ $pin[0] }}" cy="{{ $pin[1] }}" r="2.3" fill="var(--pin-white)" stroke="var(--navy)" stroke-width="1"/>
                                    @endforeach
                                </g>
                            </g>
                        @endforeach
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

            <div class="pub-facility-hovercard" id="pub-facility-hovercard" role="tooltip" aria-live="polite">
                <div class="pub-hc-head">
                    <span class="pub-hc-emoji" id="pub-hc-emoji"></span>
                    <div>
                        <div class="pub-hc-name" id="pub-hc-name"></div>
                        <div class="pub-hc-meta">
                            <span class="pub-facility-status" id="pub-hc-dot"></span>
                            <span class="pub-hc-badge" id="pub-hc-status"></span>
                            <span class="pub-hc-hours" id="pub-hc-hours"></span>
                        </div>
                    </div>
                </div>
                <p class="pub-hc-desc" id="pub-hc-desc"></p>
                <div class="pub-hc-facilities" id="pub-hc-facilities">
                    <h3>What's in here</h3>
                    <ul class="pub-facility-list" id="pub-hc-list"></ul>
                </div>
                <div class="pub-hc-oil" id="pub-hc-oil">
                    <h3>Oil level</h3>
                    <div class="pub-oil-track"><div class="pub-oil-fill" id="pub-hc-oil-fill" style="width:0%;"></div></div>
                    <div class="pub-lane-sub" id="pub-hc-lane-sub"></div>
                </div>
                <div class="pub-lane-actions" id="pub-hc-actions"></div>
            </div>
        </div>

        <div class="pub-facility-legend" id="pub-facility-legend" role="list"></div>

        <div class="pub-lane-board">
            <h2 class="pub-lane-board-title">Lane Board</h2>
            <p class="pub-lane-board-sub">Hover a lane for its rundown &middot; Pick a free one to book</p>
            <div class="pub-lane-strip" id="pub-lane-strip" role="list" aria-label="Lane availability board">
                @foreach($lanes as $lane)
                    <button type="button" class="pub-lane-strip-chip" data-lane="{{ $lane->lane_number }}" role="listitem" title="Lane {{ $lane->lane_number }} - {{ ucfirst($lane->status) }}">
                        <span class="pub-facility-status pub-lane-dot-{{ $lane->status }}"></span>
                        Lane {{ str_pad($lane->lane_number, 2, '0', STR_PAD_LEFT) }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    @include('site.partials.core-footer', ['noMarginTop' => true])

    @php
        $userRole = auth()->check() ? auth()->user()->role : null;
        $gameUrl = \Illuminate\Support\Facades\Route::has('game.index') ? route('game.index') : null;
        $maintainUrl = \Illuminate\Support\Facades\Route::has('caretaker.lanes.maintain')
            ? route('caretaker.lanes.maintain', ['lane' => '__ID__'])
            : null;
        $bookUrl = auth()->check() && $userRole === 'customer' ? route('visitor.bookings.create') : null;
        $loginUrl = route('login');
        $complaintsUrl = auth()->check() && $userRole === 'customer' ? route('visitor.complaints.index') : null;
        $proshopUrl = \Illuminate\Support\Facades\Route::has('public.proshop.index') ? route('public.proshop.index') : null;
        $snackbarUrl = \Illuminate\Support\Facades\Route::has('site.snackbar') ? route('site.snackbar') : null;
    @endphp

    <x-toast />

    <script>
    window.FACILITY_MAP_CONFIG = {
        zones: @json($zones),
        lanes: @json($lanes ?: []),
        role: @json($userRole),
        gameUrl: @json($gameUrl),
        maintainUrl: @json($maintainUrl),
        bookUrl: @json($bookUrl),
        loginUrl: @json($loginUrl),
        complaintsUrl: @json($complaintsUrl),
        proshopUrl: @json($proshopUrl),
        snackbarUrl: @json($snackbarUrl),
        csrf: @json(csrf_token())
    };
    </script>
    <script src="/js/facility-map.js"></script>
</body>
</html>
