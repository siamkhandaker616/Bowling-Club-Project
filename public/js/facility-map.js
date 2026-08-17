(function () {
    var C = window.FACILITY_MAP_CONFIG;
    if (!C) return;
    var ZONES = C.zones;
    if (!ZONES || !ZONES.length) return;

    var LANES = C.lanes || [];
    var ROLE = C.role;
    var GAME_URL = C.gameUrl;
    var MAINTAIN_URL = C.maintainUrl;
    var BOOK_URL = C.bookUrl;
    var LOGIN_URL = C.loginUrl;
    var COMPLAINTS_URL = C.complaintsUrl;
    var PROSHOP_URL = C.proshopUrl;
    var SNACKBAR_URL = C.snackbarUrl;
    var CSRF = C.csrf;

    var EMOJI = {'lanes':'\u{1F3B3}','snack-bar':'\u{1F964}','arcade':'\u{1F579}','lounge':'\u{1F6CB}','restaurant':'\u{1F37D}','pro-shop':'\u{1F3EA}','washrooms':'\u{1F6BD}','parking':'\u{1F697}'};

    var byKey = {};
    ZONES.forEach(function (z) {
        z.emoji = EMOJI[z.map_key] || '';
        byKey[z.map_key] = z;
    });

    var laneByNum = {};
    LANES.forEach(function (l) { laneByNum[l.lane_number] = l; });

    var STATUS_LABEL = {open:'OPEN', occupied:'IN PLAY', maintenance:'MAINTENANCE', reserved:'RESERVED'};
    var STATUS_NOTE = {
        open:'This lane is open and ready to roll.',
        occupied:'This lane is currently in play - it should free up after the current frame.',
        maintenance:'This lane is under maintenance and off the board right now.',
        reserved:'This lane is reserved for an upcoming booking.'
    };

    var stage = document.getElementById('pub-facility-stage');
    var svgMap = document.getElementById('pub-facility-map');
    var card = document.getElementById('pub-facility-hovercard');
    var hcEmoji = document.getElementById('pub-hc-emoji');
    var hcName = document.getElementById('pub-hc-name');
    var hcDot = document.getElementById('pub-hc-dot');
    var hcStatus = document.getElementById('pub-hc-status');
    var hcHours = document.getElementById('pub-hc-hours');
    var hcDesc = document.getElementById('pub-hc-desc');
    var hcFacilities = document.getElementById('pub-hc-facilities');
    var hcList = document.getElementById('pub-hc-list');
    var hcOil = document.getElementById('pub-hc-oil');
    var hcOilFill = document.getElementById('pub-hc-oil-fill');
    var hcLaneSub = document.getElementById('pub-hc-lane-sub');
    var hcActions = document.getElementById('pub-hc-actions');
    var legend = document.getElementById('pub-facility-legend');
    var countEl = document.getElementById('pub-facility-count');

    var hideTimer = null;
    var pinned = false;
    var pinKind = null;
    var pinKey = null;

    function pad(n) { return (n < 10 ? '0' : '') + n; }

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

    function openLanesCount() {
        var c = 0;
        LANES.forEach(function (l) { if (l.status === 'open') c++; });
        return c;
    }

    function updateCount() {
        if (!countEl) return;
        var open = ZONES.filter(isOpen).length;
        countEl.textContent = open + ' of ' + ZONES.length + ' zones open \u00b7 ' + openLanesCount() + ' of ' + LANES.length + ' lanes free';
    }

    function zoneEl(key) {
        return document.querySelector('.pub-fz[data-key="' + key + '"]');
    }

    function setZoneHover(key, on) {
        var el = zoneEl(key);
        if (el) el.classList.toggle('is-hovered', on);
    }

    function laneEl(n) {
        return document.querySelector('.pub-lz[data-lane="' + n + '"]');
    }

    function setLaneHover(n, on) {
        var el = laneEl(n);
        if (el) el.classList.toggle('is-hovered', on);
    }

    function setHover(kind, key, on) {
        if (kind === 'zone') setZoneHover(key, on); else setLaneHover(key, on);
    }

    function clearActive() {
        document.querySelectorAll('.pub-fz.is-active').forEach(function (e) { e.classList.remove('is-active'); });
        document.querySelectorAll('.pub-facility-chip.is-active').forEach(function (c) { c.classList.remove('is-active'); });
        document.querySelectorAll('.pub-lz.is-active').forEach(function (e) { e.classList.remove('is-active'); });
        document.querySelectorAll('.pub-lane-strip-chip.is-active').forEach(function (c) { c.classList.remove('is-active'); });
    }

    function actionHtml(l) {
        var n = l.lane_number;
        var h = '';
        if (ROLE === 'customer') {
            if (BOOK_URL) h += '<a class="pub-zone-action" href="' + BOOK_URL + '?lane=' + n + '">Book Lane ' + pad(n) + ' \u2192</a>';
            if (COMPLAINTS_URL) h += '<a class="pub-zone-action secondary" href="' + COMPLAINTS_URL + '">Report a problem with this lane</a>';
        } else if (ROLE === 'caretaker' || ROLE === 'admin') {
            if (MAINTAIN_URL) {
                var url = MAINTAIN_URL.replace('__ID__', l.id);
                var maintLabel = l.status === 'maintenance' ? 'End maintenance' : 'Start maintenance';
                h += '<form method="POST" action="' + url + '">'
                    + '<input type="hidden" name="_token" value="' + CSRF + '">'
                    + '<button type="submit" name="action" value="oiled">Apply oil</button>'
                    + '<button type="submit" name="action" value="cleaned">Clean lanes</button>'
                    + '<button type="submit" name="action" value="toggle_maint" class="maint-toggle">' + maintLabel + '</button>'
                    + '</form>';
            }
            if (COMPLAINTS_URL) h += '<a class="pub-zone-action secondary" href="' + COMPLAINTS_URL + '">Report a problem with this lane</a>';
        } else if (!ROLE) {
            h += '<a class="pub-zone-action" href="' + LOGIN_URL + '">Sign in to book this lane \u2192</a>';
        }
        return h;
    }

    function zoneCard(z) {
        var open = isOpen(z);
        hcEmoji.textContent = z.emoji;
        hcName.textContent = z.name;
        hcDot.className = 'pub-facility-status ' + (open ? 'open' : 'closed');
        hcStatus.textContent = open ? 'OPEN' : 'CLOSED';
        hcStatus.className = 'pub-hc-badge ' + (open ? 'open' : 'closed');
        hcHours.textContent = hours(z);
        hcDesc.textContent = z.description || '';
        hcList.innerHTML = '';
        (z.facilities || []).forEach(function (f) {
            var li = document.createElement('li');
            li.textContent = f;
            hcList.appendChild(li);
        });
        hcFacilities.style.display = (z.facilities && z.facilities.length) ? '' : 'none';
        hcOil.style.display = 'none';
        hcActions.innerHTML = '';
        if (z.map_key === 'arcade' && GAME_URL) {
            var a = document.createElement('a');
            a.className = 'pub-zone-action';
            a.href = GAME_URL;
            a.textContent = 'Launch Virtual Bowling \u2192';
            hcActions.appendChild(a);
        }
        if (z.map_key === 'pro-shop' && PROSHOP_URL) {
            var a = document.createElement('a');
            a.className = 'pub-zone-action';
            a.href = PROSHOP_URL;
            a.textContent = 'Visit the Pro Shop \u2192';
            hcActions.appendChild(a);
        }
        if (z.map_key === 'snack-bar' && SNACKBAR_URL) {
            var a = document.createElement('a');
            a.className = 'pub-zone-action';
            a.href = SNACKBAR_URL;
            a.textContent = 'See the Snack Bar menu \u2192';
            hcActions.appendChild(a);
        }
        hcActions.style.display = hcActions.children.length ? '' : 'none';
    }

    function laneCard(l) {
        var n = l.lane_number;
        hcEmoji.textContent = '\u{1F3B3}';
        hcName.textContent = 'Lane ' + pad(n);
        hcDot.className = 'pub-facility-status pub-lane-dot-' + l.status;
        hcStatus.textContent = STATUS_LABEL[l.status] || l.status;
        hcStatus.className = 'pub-hc-badge ' + l.status;
        var zl = byKey['lanes'];
        hcHours.textContent = zl ? hours(zl) : '';
        hcDesc.textContent = STATUS_NOTE[l.status] || '';
        hcFacilities.style.display = 'none';
        hcOil.style.display = '';
        hcOilFill.style.width = Math.max(0, Math.min(100, l.oil_level)) + '%';
        var lm = l.last_maintained_at ? new Date(l.last_maintained_at).toLocaleDateString(undefined, {month: 'short', day: 'numeric'}) : '';
        hcLaneSub.textContent = l.oil_level + '% oil' + (lm ? ' \u00b7 last serviced ' + lm : ' \u00b7 never serviced');
        hcActions.innerHTML = actionHtml(l);
        hcActions.style.display = hcActions.children.length ? '' : 'none';
    }

    function anchorEl() {
        return pinKind === 'zone' ? zoneEl(pinKey) : laneEl(pinKey);
    }

    function placeCard(e) {
        var r = stage.getBoundingClientRect();
        var x = e.clientX - r.left;
        var y = e.clientY - r.top;
        var w = card.offsetWidth, h = card.offsetHeight;
        var left = x + 18;
        if (left + w > r.width - 8) left = x - w - 18;
        left = Math.max(8, Math.min(left, r.width - w - 8));
        var top = y + 14;
        if (top + h > r.height - 8) top = r.height - h - 8;
        top = Math.max(8, top);
        card.style.left = left + 'px';
        card.style.top = top + 'px';
    }

    function placeCardNear(el) {
        if (!el) return;
        var r = stage.getBoundingClientRect();
        var er = el.getBoundingClientRect();
        var w = card.offsetWidth, h = card.offsetHeight;
        var left = (er.left - r.left) + er.width / 2 - w / 2;
        left = Math.max(8, Math.min(left, r.width - w - 8));
        var top = (er.bottom - r.top) + 10;
        if (top + h > r.height - 8) top = (er.top - r.top) - h - 10;
        top = Math.max(8, top);
        card.style.left = left + 'px';
        card.style.top = top + 'px';
    }

    function showCard(kind, key, e) {
        if (kind === 'zone') {
            var z = byKey[key];
            if (!z) return;
            zoneCard(z);
        } else {
            var l = laneByNum[key];
            if (!l) return;
            laneCard(l);
        }
        pinKind = kind;
        pinKey = key;
        card.classList.add('is-visible');
        if (e) placeCard(e); else placeCardNear(anchorEl());
    }

    function hideCard() {
        clearTimeout(hideTimer);
        if (pinned) return;
        card.classList.remove('is-visible');
    }

    function scheduleHide() {
        if (pinned) return;
        clearTimeout(hideTimer);
        hideTimer = setTimeout(hideCard, 120);
    }

    function setActive(kind, key, on) {
        if (kind === 'zone') {
            var el = zoneEl(key), chip = document.querySelector('.pub-facility-chip[data-key="' + key + '"]');
            if (el) el.classList.toggle('is-active', on);
            if (chip) chip.classList.toggle('is-active', on);
        } else {
            var le = laneEl(key), c = document.querySelector('.pub-lane-strip-chip[data-lane="' + key + '"]');
            if (le) le.classList.toggle('is-active', on);
            if (c) c.classList.toggle('is-active', on);
        }
    }

    function pinCard(kind, key) {
        if (pinned && pinKind === kind && pinKey === key) { unpinCard(); return; }
        clearActive();
        pinned = true;
        showCard(kind, key, null);
        setActive(kind, key, true);
    }

    function unpinCard() {
        pinned = false;
        hideCard();
        clearActive();
    }

    function bindHover(el, kind, key) {
        el.addEventListener('mouseenter', function (e) {
            setHover(kind, key, true);
            if (pinned || isSmallScreen()) return;
            clearTimeout(hideTimer);
            showCard(kind, key, e);
        });
        el.addEventListener('mousemove', function (e) {
            if (!pinned && !isSmallScreen() && card.classList.contains('is-visible')) placeCard(e);
        });
        el.addEventListener('mouseleave', function (e) {
            setHover(kind, key, false);
            if (e.relatedTarget && card.contains(e.relatedTarget)) return;
            scheduleHide();
        });
        el.addEventListener('focus', function () {
            if (pinned || isSmallScreen()) return;
            clearTimeout(hideTimer);
            showCard(kind, key, null);
        });
        el.addEventListener('blur', function () { scheduleHide(); });
        el.addEventListener('click', function (e) {
            e.stopPropagation();
            pinCard(kind, key);
        });
        el.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                e.stopPropagation();
                pinCard(kind, key);
            }
        });
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
            bindHover(chip, 'zone', z.map_key);
            legend.appendChild(chip);
        });
    }

    document.querySelectorAll('.pub-lane-strip-chip').forEach(function (chip) {
        bindHover(chip, 'lane', +chip.getAttribute('data-lane'));
    });

    document.querySelectorAll('.pub-lz').forEach(function (el) {
        bindHover(el, 'lane', +el.getAttribute('data-lane'));
    });

    document.querySelectorAll('.pub-fz').forEach(function (el) {
        bindHover(el, 'zone', el.getAttribute('data-key'));
    });

    svgMap.addEventListener('click', function (e) {
        if (!e.target.closest('.pub-fz') && !e.target.closest('.pub-lz') && !card.contains(e.target)) unpinCard();
    });

    card.addEventListener('mouseenter', function () { clearTimeout(hideTimer); });
    card.addEventListener('mouseleave', function () { scheduleHide(); });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') unpinCard();
    });

    buildLegend();
    updateCount();
    setInterval(updateCount, 30000);
})();
