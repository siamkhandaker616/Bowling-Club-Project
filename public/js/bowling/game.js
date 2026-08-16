/* === VIRTUAL BOWLING (Feature 13) — Layer C canvas engine === */
/* Left-drag to rotate aim, release to charge the meter, click to set power. Right-drag to move the ball, or arrow keys + space. */

(function () {
    'use strict';

    var cfg = window.GAME_CONFIG || {};
    var canvas = document.getElementById('game-canvas');
    if (!canvas) return;
    var ctx = canvas.getContext('2d');

    var W = canvas.width, H = canvas.height;
    var LANE_L = 96, LANE_R = 404, CENTER = 250;
    var GUTTER_LX = LANE_L - 13, GUTTER_RX = LANE_R + 13;
    var BALL_R = 14, PIN_R = 7, PIN_FALL = 18;
    var FOUL_Y = 560, BALL_START_Y = 590, PIT_Y = 118;
    var LANE_DAMP = 0.30;
    var MAX_ANGLE = 30;

    var METER = { x: 460, w: 16, top: 140, bottom: 560, period: 2.4, power: 0, active: false, t: 0, lockedTier: null, lockFlash: 0 };

    var TIERS = [
        { min: 0, time: null, speed: null, color: '#584a8c', weak: 1 },
        { min: 12, time: null, speed: null, color: '#5b5bd6', weak: 2 },
        { min: 28, time: null, speed: null, color: '#9b5de5', weak: 3 },
        { min: 44, time: 1.0, speed: 433, color: '#f15bb5' },
        { min: 58, time: 0.8, speed: 525, color: '#fb8500' },
        { min: 72, time: 0.6, speed: 680, color: '#ffd23f' },
        { min: 86, time: 0.4, speed: 992, color: '#ff4d3d' },
        { min: 94, time: 0.2, speed: 1928, color: '#ffe15a', max: true }
    ];

    function weakSpeedFor(pct, tier) {
        if (tier.weak === 1) return 40 + pct * 3.5;
        if (tier.weak === 2) return 330 + (pct - 12) * 10.6;
        return 520 + (pct - 28) * 7.5;
    }

    var PIN_SPOTS = [
        [0, 216],
        [-18, 186], [18, 186],
        [-36, 156], [0, 156], [36, 156],
        [-54, 126], [-18, 126], [18, 126], [54, 126]
    ];

    function cssVar(name) {
        var v = getComputedStyle(document.documentElement).getPropertyValue(name).trim();
        return v || '';
    }

    var palette = {
        laneLight: cssVar('--lane-wood-light') || '#c9a469',
        lane: cssVar('--lane-wood') || '#a5793f',
        laneDark: cssVar('--lane-wood-dark') || '#7f5b2b',
        rubber: cssVar('--rubber') || '#241d18',
        navy: cssVar('--navy') || '#2a1f16',
        slate: cssVar('--slate') || '#7a6a54',
        gold: cssVar('--gold') || '#c98f3d',
        goldLight: cssVar('--gold-light') || '#dfae62',
        coral: cssVar('--coral') || '#d4574f',
        coralLight: cssVar('--coral-light') || '#e88a83',
        pinWhite: cssVar('--pin-white') || '#f9f4e8',
        mist: cssVar('--mist') || '#efe6d4',
        fog: cssVar('--fog') || '#c0b098'
    };

    var BALL_COLORS = {
        coral: { main: '#d4574f', light: '#e88a83', dark: '#8f3a36' },
        gold: { main: '#c98f3d', light: '#e2b866', dark: '#8a6228' },
        violet: { main: '#8a5bc9', light: '#b28ce8', dark: '#5c3a8c' },
        teal: { main: '#2a9d8f', light: '#6fcfbe', dark: '#1d6f66' },
        blue: { main: '#3d6fd4', light: '#7f9fe8', dark: '#2a4a92' },
        emerald: { main: '#2f9e44', light: '#82c77f', dark: '#1f6b2f' },
        pink: { main: '#f15bb5', light: '#f7a1d3', dark: '#a63d7a' },
        black: { main: '#1f1f1f', light: '#4d4d4d', dark: '#000000' }
    };

    var ballColor = 'coral';
    try {
        var storedBallColor = localStorage.getItem('bowling-ball-color');
        if (storedBallColor && BALL_COLORS[storedBallColor]) ballColor = storedBallColor;
    } catch (err) {}

    var pins = [];
    var ball = { x: CENTER, y: BALL_START_Y, vx: 0, vy: 0, active: false, inGutter: false, age: 0, damp: LANE_DAMP, maxShot: false };
    var state = 'aiming';
    var aim = { angle: 0, mode: null, pressX: null, pressY: null, dragX: null, dragY: null };
    var rollStanding = 0;
    var lastTier = null;
    var burst = null;
    var commentary = null;
    var holdActive = false;
    var holdContinue = null;
    var angleErrOverride = null;
    var lastAngleErr = 0;
    var lastLaunchAngle = aim.angle;

    var game = {
        currentFrame: 1,
        ballInFrame: 1,
        frames: [],
        best: cfg.best || 0,
        lastTotal: 0
    };

    for (var i = 0; i < 10; i++) game.frames.push({ rolls: [], total: null });

    var els = {
        frame: document.getElementById('hud-frame'),
        ball: document.getElementById('hud-ball'),
        pins: document.getElementById('hud-pins'),
        best: document.getElementById('hud-best'),
        status: document.getElementById('game-status'),
        over: document.getElementById('game-over'),
        overScore: document.getElementById('over-score'),
        overBadge: document.getElementById('over-badge'),
        overMeta: document.getElementById('over-meta'),
        again: document.getElementById('over-again')
    };

    var LINES = {
        strike: ['STRIKE! The pins never stood a chance.', 'ALL TEN! That ball was a missile.', 'STRIKE! A frame for the highlight reel.'],
        maxStrike: ['MAX POWER STRIKE! The deck is smoking.', 'ALL TEN AT MAX! The frames are shook.'],
        spare: ['CLEAN SPARE! Textbook pickup.', 'SPARE! Pure precision.'],
        good: ['Nice rack work on that roll.', 'Solid roll. The pocket liked it.'],
        meh: ['Only a few fell. The pocket was cruel.', 'A bit light on that one.'],
        brick: ['One pin. The head pin barely flinched.', 'A love tap. The pins asked for more.'],
        gutter: ['Ouch. Even the gutter looked away.', 'Wide! The gutter ate that one.'],
        weak1: ['No tank. The lane swallowed the ball.'],
        weak2: ['That roll ran out of fuel halfway.'],
        weak3: ['So close \u2014 the ball died at the pins.', 'A graze and nothing more. The rack barely noticed.'],
        max: ['MAXIMUM POWER! The pins felt that.', 'FULL CHARGE! The deck is rattling.']
    };

    function standingCount() {
        var n = 0;
        for (var i = 0; i < pins.length; i++) if (!pins[i].down) n++;
        return n;
    }

    function resetPins() {
        pins = [];
        for (var i = 0; i < PIN_SPOTS.length; i++) {
            pins.push({
                baseX: CENTER + PIN_SPOTS[i][0],
                baseY: PIN_SPOTS[i][1],
                x: CENTER + PIN_SPOTS[i][0],
                y: PIN_SPOTS[i][1],
                vx: 0, vy: 0,
                down: false,
                dead: false,
                rot: 0,
                num: i + 1
            });
        }
    }

    function resetBall() {
        ball.y = BALL_START_Y;
        ball.vx = 0;
        ball.vy = 0;
        ball.active = false;
        ball.inGutter = false;
        ball.age = 0;
        ball.damp = LANE_DAMP;
        ball.maxShot = false;
    }

    resetPins();

    function computeScores(frames) {
        var flat = [];
        var i, j;
        for (i = 0; i < frames.length; i++) {
            for (j = 0; j < frames[i].rolls.length; j++) flat.push(frames[i].rolls[j]);
        }
        var totals = new Array(10).fill(null);
        var running = 0, cursor = 0;
        for (i = 0; i < 10; i++) {
            if (flat[cursor] === undefined) break;
            if (flat[cursor] === 10) {
                if (flat[cursor + 1] === undefined || flat[cursor + 2] === undefined) break;
                running += 10 + flat[cursor + 1] + flat[cursor + 2];
                cursor += 1;
            } else {
                if (flat[cursor + 1] === undefined) break;
                var sum = flat[cursor] + flat[cursor + 1];
                if (sum === 10) {
                    if (flat[cursor + 2] === undefined) break;
                    running += 10 + flat[cursor + 2];
                } else {
                    running += sum;
                }
                cursor += 2;
            }
            totals[i] = running;
        }
        return totals;
    }

    function renderScoreboard() {
        var totals = computeScores(game.frames);
        for (var f = 1; f <= 10; f++) {
            var frame = game.frames[f - 1];
            for (var s = 1; s <= 3; s++) {
                var cell = document.querySelector('.sc-roll[data-frame="' + f + '"][data-slot="' + s + '"]');
                if (!cell) continue;
                var r = frame.rolls[s - 1];
                cell.textContent = '';
                cell.classList.remove('strike', 'spare');
                if (r === undefined) continue;
                if (r === 10) {
                    cell.textContent = 'X';
                    cell.classList.add('strike');
                } else if (s === 2 && r !== undefined && frame.rolls[0] !== undefined && frame.rolls[0] !== 10 && frame.rolls[0] + r === 10) {
                    cell.textContent = '/';
                    cell.classList.add('spare');
                } else if (s === 3 && f === 10 && frame.rolls.length === 3 && r === 10) {
                    cell.textContent = 'X';
                    cell.classList.add('strike');
                } else {
                    cell.textContent = r === 0 ? '-' : String(r);
                }
            }
            var totalCell = document.querySelector('.sc-total[data-frame="' + f + '"]');
            if (totalCell) {
                frame.total = totals[f - 1];
                totalCell.textContent = totals[f - 1] === null ? '' : String(totals[f - 1]);
            }
        }
        if (els.best) els.best.textContent = String(Math.max(game.best, game.lastTotal));
        if (els.frame) els.frame.textContent = String(game.currentFrame);
        if (els.ball) els.ball.textContent = game.currentFrame === 10 ? String(game.frames[9].rolls.length + 1) : String(game.ballInFrame);
        if (els.pins) els.pins.textContent = String(standingCount());
    }

    function setStatus(text) {
        if (els.status) els.status.textContent = text;
    }

    function pickLine(arr) {
        return arr[Math.floor(Math.random() * arr.length)];
    }

    function sayCommentary(text) {
        commentary = { text: text, t: 0, life: 2.4 };
    }

    function say(knocked, spare) {
        var t = lastTier;
        if (t && t.weak) {
            var pool = t.weak === 1 ? LINES.weak1 : t.weak === 2 ? LINES.weak2 : LINES.weak3;
            sayCommentary(pickLine(pool));
            return;
        }
        if (knocked === 10) { sayCommentary(pickLine(t && t.max ? LINES.maxStrike : LINES.strike)); return; }
        if (spare) { sayCommentary(pickLine(LINES.spare)); return; }
        if (knocked >= 5) { sayCommentary(pickLine(t && t.max ? LINES.max : LINES.good)); return; }
        if (knocked >= 2) { sayCommentary(pickLine(LINES.meh)); return; }
        if (knocked === 1) { sayCommentary(pickLine(LINES.brick)); return; }
        sayCommentary(pickLine(LINES.gutter));
    }

    function recordRoll(knocked) {
        var f = game.currentFrame;
        if (f < 10) {
            if (game.ballInFrame === 1) {
                if (knocked === 10) {
                    game.frames[f - 1].rolls = [10];
                    say(knocked, false);
                    endFrame('Strike! Click to continue');
                } else {
                    game.frames[f - 1].rolls = [knocked];
                    game.ballInFrame = 2;
                    say(knocked, false);
                    setStatus('Roll again \u2014 ' + (10 - knocked) + ' pins left');
                    resetForNextRoll(false);
                }
            } else {
                var first = game.frames[f - 1].rolls[0];
                game.frames[f - 1].rolls = [first, knocked];
                var spare = first + knocked === 10;
                say(knocked, spare);
                if (spare) {
                    endFrame('Spare! Click to continue');
                } else {
                    endFrame('Open frame \u2014 ' + (10 - first - knocked) + ' pins. Click to continue');
                }
            }
        } else {
            var tenth = game.frames[9].rolls;
            tenth.push(knocked);
            var done = (tenth.length === 2 && tenth[0] + tenth[1] < 10) || tenth.length === 3;
            if (done) {
                var spare10 = tenth.length === 2 && tenth[0] + tenth[1] === 10;
                say(knocked, spare10);
                if (tenth[0] === 10) setStatus('Strike!');
                else if (spare10) setStatus('Spare!');
                else setStatus('Open frame');
                endFrame();
            } else {
                say(knocked, tenth[0] === 10 ? false : tenth[0] + knocked === 10);
                if (knocked === 10) {
                    holdFrameEnd(function () { resetForNextRoll(standingCount() === 0); }, 'Strike \u2014 two more rolls. Click to continue');
                } else {
                    setStatus(tenth[0] === 10 ? 'Strike \u2014 two more rolls' : 'Spare \u2014 one more roll');
                    resetForNextRoll(standingCount() === 0);
                }
            }
        }
    }

    function endFrame(msg) {
        var totals = computeScores(game.frames);
        game.lastTotal = totals[9] === null ? 0 : totals[9];
        renderScoreboard();

        if (game.currentFrame === 10) {
            state = 'over';
            setStatus('Game over');
            submitScore();
            return;
        }

        holdFrameEnd(advanceFrame, msg);
    }

    function advanceFrame() {
        game.currentFrame++;
        game.ballInFrame = 1;
        resetForNextRoll(true);
        setStatus('Drag to aim \u2014 release to charge');
    }

    function resetForNextRoll(rerack) {
        state = 'aiming';
        holdActive = false;
        holdContinue = null;
        aim.mode = null;
        aim.dragX = null;
        aim.dragY = null;
        aim.pressX = null;
        aim.pressY = null;
        METER.active = false;
        METER.lockedTier = null;
        METER.lockFlash = 0;
        lastTier = null;
        if (rerack) resetPins();
        resetBall();
        renderScoreboard();
    }

    function tierFor(pct) {
        var out = TIERS[0];
        for (var i = 0; i < TIERS.length; i++) {
            if (pct >= TIERS[i].min) out = TIERS[i];
        }
        return out;
    }

    function meterY(pct) {
        return METER.bottom - (pct / 100) * (METER.bottom - METER.top);
    }

    function startCharge() {
        if (state !== 'aiming') return;
        state = 'charging';
        METER.active = true;
        METER.t = 0;
        METER.power = 0;
        METER.lockedTier = null;
        METER.lockFlash = 0;
        setStatus('Click to set power!');
    }

    function cancelCharge() {
        state = 'aiming';
        METER.active = false;
        setStatus('Drag to aim \u2014 release to charge');
    }

    function holdFrameEnd(cont, msg) {
        state = 'hold';
        holdActive = true;
        holdContinue = cont;
        renderScoreboard();
        setStatus(msg || 'Frame done \u2014 click to continue');
    }

    function engageHold() {
        if (!holdActive) return;
        holdActive = false;
        var cont = holdContinue;
        holdContinue = null;
        if (cont) cont();
    }

    function lockAndThrow() {
        if (state !== 'charging') return;
        var pct = Math.max(0, Math.min(100, METER.power * 100));
        var tier = tierFor(pct);
        var speed = tier.weak ? weakSpeedFor(pct, tier) : tier.speed;
        METER.active = false;
        METER.lockedTier = tier;
        METER.lockFlash = 0.28;
        lastTier = tier;
        var spread = tier.weak ? 2.5 : 3.5 + METER.power * 4;
        var err = angleErrOverride !== null ? angleErrOverride : (Math.random() + Math.random() - 1) * spread;
        lastAngleErr = err;
        lastLaunchAngle = aim.angle + err;
        var a = (lastLaunchAngle * Math.PI) / 180;
        ball.vx = Math.sin(a) * speed;
        ball.vy = -Math.cos(a) * speed;
        ball.damp = tier.weak ? 1.6 : LANE_DAMP;
        ball.maxShot = !!tier.max;
        rollStanding = standingCount();
        ball.active = true;
        ball.age = 0;
        state = 'throwing';
        setStatus('Rolling\u2026');
    }

    function collidePins(dt) {
        var i, j;
        for (i = 0; i < pins.length; i++) {
            var p = pins[i];
            if (p.dead) continue;
            var dxb = ball.x - p.x;
            var dyb = ball.y - p.y;
            var dist = Math.sqrt(dxb * dxb + dyb * dyb);
            var minD = BALL_R + PIN_R + 6;
            if (dist < minD && ball.active && !ball.inGutter) {
                if (ball.maxShot && !burst) startBurst(ball.x, ball.y);
                if (dist === 0) { dist = 0.01; dxb = 0.01; dyb = 0; }
                var nx = dxb / dist, ny = dyb / dist;
                var push = (minD - dist) / dt;
                p.vx += ball.vx * 0.8 + nx * push * 0.5;
                p.vy += ball.vy * 0.8 + ny * push * 0.5;
                if (!p.down) {
                    ball.vx *= 0.9;
                    ball.vy *= 0.9;
                }
            }
        }
        for (i = 0; i < pins.length; i++) {
            var a = pins[i];
            if (a.dead) continue;
            for (j = i + 1; j < pins.length; j++) {
                var b = pins[j];
                if (b.dead) continue;
                var ax = a.x - b.x, ay = a.y - b.y;
                var ad = Math.sqrt(ax * ax + ay * ay);
                var amd = PIN_R * 2 + 10;
                if (ad < amd && ad > 0) {
                    var s = (amd - ad) / ad * 0.5;
                    a.x += ax * s; a.y += ay * s;
                    b.x -= ax * s; b.y -= ay * s;
                    var rvx = a.vx - b.vx, rvy = a.vy - b.vy;
                    var rel = (rvx * ax + rvy * ay) / ad;
                    if (rel < 0) {
                        var imp = -rel / 2;
                        a.vx += (ax / ad) * imp; a.vy += (ay / ad) * imp;
                        b.vx -= (ax / ad) * imp; b.vy -= (ay / ad) * imp;
                    }
                }
            }
        }
    }

    function stepPins(dt) {
        var i;
        for (i = 0; i < pins.length; i++) {
            var p = pins[i];
            if (p.down) continue;
            p.x += p.vx * dt;
            p.y += p.vy * dt;
            var damp = Math.max(0, 1 - 2.5 * dt);
            p.vx *= damp;
            p.vy *= damp;
            var dx = p.x - p.baseX, dy = p.y - p.baseY;
            var moved = Math.sqrt(dx * dx + dy * dy);
            if (moved > PIN_FALL) {
                p.down = true;
                p.rot = Math.min(95, Math.max(-95, (dx + dy) * 1.4));
            }
            if (moved > 90) p.dead = true;
            if (p.x < LANE_L + 4 || p.x > LANE_R - 4 || p.y < PIT_Y + 4) {
                p.down = true;
                p.dead = true;
                p.rot = 90;
            }
        }
    }

    function ballDone() {
        if (ball.age > 5) return true;
        if (ball.vy >= 0 && ball.y > FOUL_Y) return true;
        if (ball.y < PIT_Y - 8) return true;
        if (Math.abs(ball.vx) + Math.abs(ball.vy) < 15) return true;
        return false;
    }

    function pinsSettled() {
        for (var i = 0; i < pins.length; i++) {
            if (!pins[i].down && (Math.abs(pins[i].vx) + Math.abs(pins[i].vy)) > 3) return false;
        }
        return true;
    }

    function step(dt) {
        if (state !== 'throwing' && state !== 'settling') return;
        var sdt = dt / 4;
        for (var s = 0; s < 4; s++) {
            if (ball.active) {
                var damp = Math.max(0, 1 - ball.damp * sdt);
                ball.vx *= damp;
                ball.vy *= damp;

                if (ball.inGutter) {
                    var gutterTarget = ball.x < CENTER ? GUTTER_LX : GUTTER_RX;
                    ball.x += (gutterTarget - ball.x) * 0.12;
                } else if (ball.x < LANE_L + BALL_R) {
                    ball.x = LANE_L + BALL_R;
                    if (ball.vx < 0) ball.vx = 0;
                    ball.inGutter = true;
                    ball.vy *= 0.5;
                } else if (ball.x > LANE_R - BALL_R) {
                    ball.x = LANE_R - BALL_R;
                    if (ball.vx > 0) ball.vx = 0;
                    ball.inGutter = true;
                    ball.vy *= 0.5;
                }

                ball.x += ball.vx * sdt;
                ball.y += ball.vy * sdt;
                ball.age += sdt;

                if (ball.inGutter) ball.vy *= Math.max(0, 1 - 1.2 * sdt);

                collidePins(sdt);
                stepPins(sdt);

                if (ballDone()) {
                    ball.active = false;
                    state = 'settling';
                    setStatus('Counting pins\u2026');
                }
            } else {
                stepPins(sdt);
            }
        }
        if (!ball.active && pinsSettled()) {
            state = 'aiming';
            var knocked = Math.max(0, rollStanding - standingCount());
            recordRoll(knocked);
        }
    }

    function drawGutterChannel(x) {
        var w = 26;
        var grad = ctx.createLinearGradient(x - w, 0, x + w, 0);
        grad.addColorStop(0, palette.rubber);
        grad.addColorStop(0.5, palette.slate);
        grad.addColorStop(1, palette.rubber);
        ctx.fillStyle = grad;
        ctx.fillRect(x - w, PIT_Y, w * 2, FOUL_Y - PIT_Y);
        ctx.fillStyle = 'rgba(255,255,255,0.10)';
        ctx.fillRect(x - 3, PIT_Y, 6, FOUL_Y - PIT_Y);
        ctx.strokeStyle = palette.gold;
        ctx.lineWidth = 1.5;
        ctx.beginPath();
        ctx.moveTo(x - w + 2, PIT_Y);
        ctx.lineTo(x - w + 2, FOUL_Y);
        ctx.moveTo(x + w - 2, PIT_Y);
        ctx.lineTo(x + w - 2, FOUL_Y);
        ctx.stroke();
    }

    function drawLane() {
        var grad = ctx.createLinearGradient(0, FOUL_Y, 0, PIT_Y);
        grad.addColorStop(0, palette.laneLight);
        grad.addColorStop(0.5, palette.lane);
        grad.addColorStop(1, palette.laneDark);
        ctx.fillStyle = grad;
        ctx.fillRect(LANE_L, PIT_Y, LANE_R - LANE_L, FOUL_Y - PIT_Y);

        drawGutterChannel(GUTTER_LX);
        drawGutterChannel(GUTTER_RX);

        ctx.strokeStyle = palette.slate;
        ctx.lineWidth = 2;
        for (var i = 0; i < 3; i++) {
            var x = CENTER - 34 + i * 34;
            ctx.beginPath();
            ctx.moveTo(x, PIT_Y + 4);
            ctx.lineTo(x, FOUL_Y - 4);
            ctx.stroke();
        }

        ctx.strokeStyle = palette.coral;
        ctx.lineWidth = 4;
        ctx.beginPath();
        ctx.moveTo(LANE_L - 26, FOUL_Y);
        ctx.lineTo(LANE_R + 26, FOUL_Y);
        ctx.stroke();

        ctx.fillStyle = palette.slate;
        var chevronY = 320;
        var chevs = [-44, -22, 0, 22, 44];
        for (var c = 0; c < chevs.length; c++) {
            var cx = CENTER + chevs[c];
            ctx.beginPath();
            ctx.moveTo(cx - 9, chevronY + 8);
            ctx.lineTo(cx, chevronY - 4);
            ctx.lineTo(cx + 9, chevronY + 8);
            ctx.closePath();
            ctx.fill();
        }

        ctx.fillStyle = palette.fog;
        for (var p = 0; p < pins.length; p++) {
            var spot = PIN_SPOTS[p];
            ctx.beginPath();
            ctx.arc(CENTER + spot[0], spot[1], 6, 0, Math.PI * 2);
            ctx.fill();
        }
    }

    function drawPin(p) {
        ctx.save();
        if (p.down) {
            ctx.translate(p.x, p.y);
            ctx.rotate(p.rot * Math.PI / 180);
            ctx.globalAlpha = 0.55;
            ctx.fillStyle = palette.slate;
            ctx.fillRect(-PIN_R - 3, -2, PIN_R * 2 + 6, 4);
            ctx.restore();
            return;
        }
        ctx.translate(p.x, p.y);
        ctx.fillStyle = palette.pinWhite;
        ctx.strokeStyle = palette.navy;
        ctx.lineWidth = 1.5;
        ctx.beginPath();
        ctx.arc(0, 0, PIN_R, 0, Math.PI * 2);
        ctx.fill();
        ctx.stroke();
        ctx.fillStyle = palette.coral;
        ctx.fillRect(-3, -3, 6, 2);
        ctx.fillRect(-3, 1, 6, 2);
        ctx.restore();
    }

    function drawBall() {
        if (!ball.active && ball.y < PIT_Y + BALL_R) return;
        var bc = BALL_COLORS[ballColor] || BALL_COLORS.coral;
        if (!ball.active && state !== 'throwing' && state !== 'settling') {
            var grad = ctx.createRadialGradient(ball.x - 5, ball.y - 5, 2, ball.x, ball.y, BALL_R);
            grad.addColorStop(0, bc.light);
            grad.addColorStop(0.55, bc.main);
            grad.addColorStop(1, palette.rubber);
            ctx.fillStyle = grad;
        } else {
            var grad2 = ctx.createRadialGradient(ball.x - 5, ball.y - 5, 2, ball.x, ball.y, BALL_R);
            grad2.addColorStop(0, bc.dark);
            grad2.addColorStop(1, palette.rubber);
            ctx.fillStyle = grad2;
        }
        ctx.beginPath();
        ctx.arc(ball.x, ball.y, BALL_R, 0, Math.PI * 2);
        ctx.fill();
        ctx.strokeStyle = palette.pinWhite;
        ctx.lineWidth = 1.5;
        ctx.stroke();
        if (!ball.active) {
            ctx.fillStyle = palette.pinWhite;
            ctx.beginPath();
            ctx.arc(ball.x - 4, ball.y - 3, 2.6, 0, Math.PI * 2);
            ctx.arc(ball.x + 4, ball.y - 3, 2.6, 0, Math.PI * 2);
            ctx.arc(ball.x, ball.y + 4, 2.6, 0, Math.PI * 2);
            ctx.fill();
        }
    }

    function drawAimGuide() {
        if (state !== 'aiming' && state !== 'charging') return;
        var a = (aim.angle * Math.PI) / 180;
        var len = 150;
        var dx = Math.sin(a) * len;
        var dy = -Math.cos(a) * len;
        var ex = ball.x + dx, ey = ball.y + dy;
        ctx.save();
        ctx.setLineDash([7, 7]);
        ctx.strokeStyle = '#000000';
        ctx.lineWidth = 3;
        ctx.beginPath();
        ctx.moveTo(ball.x, ball.y);
        ctx.lineTo(ex, ey);
        ctx.stroke();
        ctx.setLineDash([]);
        ctx.fillStyle = palette.goldLight;
        ctx.beginPath();
        ctx.arc(ex, ey, 6, 0, Math.PI * 2);
        ctx.fill();
        ctx.restore();
    }

    function roundRectPath(x, y, w, h, r) {
        ctx.beginPath();
        ctx.moveTo(x + r, y);
        ctx.arcTo(x + w, y, x + w, y + h, r);
        ctx.arcTo(x + w, y + h, x, y + h, r);
        ctx.arcTo(x, y + h, x, y, r);
        ctx.arcTo(x, y, x + w, y, r);
        ctx.closePath();
    }

    function drawMeter() {
        if (state !== 'charging' && METER.lockFlash <= 0) return;
        var bx = METER.x, bw = METER.w;
        var i, y1, y2;
        ctx.save();
        ctx.fillStyle = palette.rubber;
        roundRectPath(bx - 2, METER.top - 2, bw + 4, METER.bottom - METER.top + 4, 5);
        ctx.fill();
        ctx.strokeStyle = palette.navy;
        ctx.lineWidth = 2;
        roundRectPath(bx - 2, METER.top - 2, bw + 4, METER.bottom - METER.top + 4, 5);
        ctx.stroke();
        for (i = TIERS.length - 1; i >= 0; i--) {
            y1 = meterY(TIERS[i].min);
            y2 = i < TIERS.length - 1 ? meterY(TIERS[i + 1].min) : METER.bottom;
            ctx.fillStyle = TIERS[i].color;
            ctx.fillRect(bx, y1, bw, y2 - y1);
        }
        ctx.fillStyle = 'rgba(42,31,22,0.6)';
        for (i = 1; i < TIERS.length; i++) {
            var sep = meterY(TIERS[i].min);
            ctx.fillRect(bx - 1, sep - 1, bw + 2, 2);
        }
        var pulse = 0.5 + 0.5 * Math.sin(performance.now() / 180);
        ctx.fillStyle = 'rgba(255,225,90,' + (0.28 * pulse).toFixed(3) + ')';
        ctx.fillRect(bx - 5, METER.top - 5, bw + 10, meterY(94) - METER.top + 10);
        if (state === 'charging') {
            var pct = METER.power * 100;
            var iy = meterY(pct);
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(bx - 6, iy - 2, bw + 12, 4);
            ctx.textBaseline = 'middle';
            ctx.textAlign = 'left';
            var t = tierFor(pct);
            if (t.max || t.time) {
                ctx.fillStyle = t.max ? '#ffe15a' : '#ffffff';
                ctx.font = 'bold 9px monospace';
                ctx.fillText(t.max ? 'MAX' : (t.time + 's'), bx + bw + 7, iy);
            }
        }
        if (METER.lockFlash > 0 && METER.lockedTier) {
            var lt = METER.lockedTier;
            var idx = TIERS.indexOf(lt);
            y1 = meterY(lt.min);
            y2 = lt.max ? METER.top : meterY(TIERS[idx + 1].min);
            ctx.globalAlpha = Math.min(1, METER.lockFlash * 4) * 0.45;
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(bx, y1, bw, y2 - y1);
            ctx.globalAlpha = 1;
        }
        ctx.restore();
    }

    function startBurst(x, y) {
        burst = { x: x, y: y, t: 0, life: 0.45, shards: [] };
        for (var i = 0; i < 14; i++) {
            burst.shards.push({
                a: (i / 14) * Math.PI * 2 + Math.random() * 0.5,
                sp: 120 + Math.random() * 240,
                col: [palette.coral, palette.gold, palette.pinWhite][i % 3],
                sz: 2 + Math.random() * 3
            });
        }
    }

    function drawBurst() {
        if (!burst) return;
        var k = burst.t / burst.life;
        var ease = 1 - Math.pow(1 - k, 3);
        var r = 26 + ease * 150;
        var i, s;
        ctx.save();
        ctx.globalAlpha = 1 - k;
        ctx.strokeStyle = palette.gold;
        ctx.lineWidth = 4;
        ctx.beginPath();
        ctx.arc(burst.x, burst.y, r, 0, Math.PI * 2);
        ctx.stroke();
        ctx.strokeStyle = palette.coral;
        ctx.lineWidth = 2;
        ctx.beginPath();
        ctx.arc(burst.x, burst.y, r * 0.66, 0, Math.PI * 2);
        ctx.stroke();
        for (i = 0; i < burst.shards.length; i++) {
            s = burst.shards[i];
            var d = ease * 200 * (s.sp / 260);
            ctx.fillStyle = s.col;
            ctx.beginPath();
            ctx.arc(burst.x + Math.cos(s.a) * d, burst.y + Math.sin(s.a) * d, s.sz * (1 - k * 0.6), 0, Math.PI * 2);
            ctx.fill();
        }
        ctx.restore();
    }

    function drawCommentary() {
        if (!commentary) return;
        var a = 1;
        if (commentary.t < 0.18) a = commentary.t / 0.18;
        else if (commentary.t > commentary.life - 0.4) a = Math.max(0, (commentary.life - commentary.t) / 0.4);
        var cx = CENTER, cy = 96;
        ctx.save();
        ctx.globalAlpha = a;
        ctx.font = 'bold 13px monospace';
        var tw = ctx.measureText(commentary.text).width;
        var bw2 = Math.min(330, tw + 28);
        var bh2 = 26;
        var x = cx - bw2 / 2, y = cy - bh2 / 2;
        ctx.fillStyle = 'rgba(42,31,22,0.94)';
        roundRectPath(x, y, bw2, bh2, 9);
        ctx.fill();
        ctx.strokeStyle = palette.gold;
        ctx.lineWidth = 2;
        roundRectPath(x, y, bw2, bh2, 9);
        ctx.stroke();
        ctx.beginPath();
        ctx.moveTo(cx - 7, y + bh2 - 1);
        ctx.lineTo(cx, y + bh2 + 9);
        ctx.lineTo(cx + 7, y + bh2 - 1);
        ctx.closePath();
        ctx.fill();
        ctx.fillStyle = palette.pinWhite;
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(commentary.text, cx, y + bh2 / 2 + 1);
        ctx.restore();
    }

    function render() {
        ctx.clearRect(0, 0, W, H);
        ctx.fillStyle = palette.rubber;
        ctx.fillRect(0, 0, W, H);
        drawLane();
        for (var i = 0; i < pins.length; i++) drawPin(pins[i]);
        drawAimGuide();
        drawBall();
        drawMeter();
        drawBurst();
        drawCommentary();
    }

    var last = null;
    function loop(ts) {
        var dt = (ts - last) / 1000;
        if (dt <= 0 || !isFinite(dt)) dt = 1 / 60;
        else if (dt > 0.033) dt = 0.033;
        last = ts;

        if (state === 'charging' && METER.active) {
            METER.t += dt;
            var p = (METER.t % METER.period) / METER.period;
            METER.power = p < 0.5 ? p * 2 : 2 - p * 2;
        }
        if (METER.lockFlash > 0) METER.lockFlash -= dt;
        if (commentary) {
            commentary.t += dt;
            if (commentary.t >= commentary.life) commentary = null;
        }
        if (burst) {
            burst.t += dt;
            if (burst.t >= burst.life) burst = null;
        }

        step(dt);
        render();
        requestAnimationFrame(loop);
    }

    function canvasPos(e) {
        var r = canvas.getBoundingClientRect();
        return {
            x: (e.clientX - r.left) * (W / r.width),
            y: (e.clientY - r.top) * (H / r.height)
        };
    }

    function capturePointer(e) {
        if (canvas.setPointerCapture) {
            try { canvas.setPointerCapture(e.pointerId); } catch (err) {}
        }
    }

    canvas.addEventListener('contextmenu', function (e) { e.preventDefault(); });

    canvas.addEventListener('pointerdown', function (e) {
        if (holdActive) { engageHold(); return; }
        var p = canvasPos(e);
        if (state === 'charging') {
            if (e.button === 0) lockAndThrow();
            else if (e.button === 2) cancelCharge();
            return;
        }
        if (state !== 'aiming') return;
        if (e.button === 0) {
            aim.mode = 'aim';
            aim.pressX = p.x;
            aim.pressY = p.y;
            aim.dragX = p.x;
            aim.dragY = p.y;
            capturePointer(e);
        } else if (e.button === 2) {
            aim.mode = 'pos';
            aim.dragX = p.x;
            aim.dragY = p.y;
            capturePointer(e);
        }
    });

    canvas.addEventListener('pointermove', function (e) {
        if (aim.mode === null) return;
        var p = canvasPos(e);
        if (aim.mode === 'aim') {
            aim.angle = Math.max(-MAX_ANGLE, Math.min(MAX_ANGLE, (p.x - aim.pressX) * 0.35));
            aim.dragX = p.x;
            aim.dragY = p.y;
        } else if (aim.mode === 'pos') {
            ball.x = Math.max(LANE_L + BALL_R, Math.min(LANE_R - BALL_R, p.x));
            aim.dragX = p.x;
            aim.dragY = p.y;
        }
    });

    canvas.addEventListener('pointerup', function (e) {
        if (aim.mode === 'aim') {
            aim.mode = null;
            aim.dragX = null;
            aim.dragY = null;
            aim.pressX = null;
            aim.pressY = null;
            startCharge();
        } else {
            aim.mode = null;
            aim.dragX = null;
            aim.dragY = null;
            aim.pressX = null;
            aim.pressY = null;
        }
    });

    canvas.addEventListener('pointercancel', function () {
        aim.mode = null;
        aim.dragX = null;
        aim.dragY = null;
        aim.pressX = null;
        aim.pressY = null;
    });

    document.addEventListener('keydown', function (e) {
        if (holdActive) { engageHold(); return; }
        if (e.key === 'Escape') {
            if (state === 'charging') cancelCharge();
            return;
        }
        if (state === 'charging' && e.key === ' ') {
            e.preventDefault();
            lockAndThrow();
            return;
        }
        if (state !== 'aiming') return;
        if (e.key === 'ArrowLeft') { aim.angle = Math.max(-MAX_ANGLE, aim.angle - 4); e.preventDefault(); }
        else if (e.key === 'ArrowRight') { aim.angle = Math.min(MAX_ANGLE, aim.angle + 4); e.preventDefault(); }
        else if (e.key === ' ') { e.preventDefault(); startCharge(); }
    });

    if (els.again) {
        els.again.addEventListener('click', function () {
            game.currentFrame = 1;
            game.ballInFrame = 1;
            for (var i = 0; i < 10; i++) game.frames[i] = { rolls: [], total: null };
            game.lastTotal = 0;
            els.over.hidden = true;
            ball.x = CENTER;
            resetForNextRoll(true);
            setStatus('Drag to aim \u2014 release to charge');
            renderScoreboard();
        });
    }

    function submitScore() {
        var totals = computeScores(game.frames);
        var framesData = [];
        for (var i = 0; i < 10; i++) {
            framesData.push({ rolls: game.frames[i].rolls, total: totals[i] });
        }
        var score = totals[9] === null ? 0 : totals[9];

        els.overScore.textContent = String(score);
        els.overBadge.hidden = true;
        els.overMeta.textContent = 'Final score \u2014 saving to the leaderboard\u2026';
        els.over.hidden = false;

        if (score <= 0) {
            els.overMeta.textContent = 'A gutter-heavy night. Roll again?';
            return;
        }

        fetch(cfg.scoresUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ score: score, frames_data: framesData })
        }).then(function (res) {
            return res.json().catch(function () { return { error: 'bad response' }; }).then(function (data) {
                return { ok: res.ok, data: data };
            });
        }).then(function (r) {
            if (r.ok && r.data.ok) {
                if (r.data.high_score) {
                    els.overBadge.hidden = false;
                    els.overMeta.textContent = 'New personal best \u2014 the club is impressed.';
                } else {
                    els.overMeta.textContent = 'Saved to the leaderboard.';
                }
            } else {
                els.overMeta.textContent = 'Could not save this game (' + (r.data.error || 'unknown error') + ').';
            }
        }).catch(function () {
            els.overMeta.textContent = 'Could not reach the server \u2014 score not saved.';
        });
    }

    renderScoreboard();
    setStatus('Drag to aim \u2014 release to charge');
    requestAnimationFrame(loop);

    window.addEventListener('ballcolorchange', function (e) {
        if (e.detail && BALL_COLORS[e.detail]) ballColor = e.detail;
    });

    if (window.__BOWLING_TEST__) {
        window.__bowling = {
            snapshot: function () {
                var t = lastTier;
                return {
                    state: state,
                    angle: aim.angle,
                    power: METER.power,
                    tier: t ? { min: t.min, time: t.time, speed: t.speed, max: !!t.max, weak: t.weak || 0 } : null,
                    ball: { x: ball.x, y: ball.y, vx: ball.vx, vy: ball.vy, active: ball.active, inGutter: ball.inGutter },
                    charging: state === 'charging',
                    meterActive: METER.active,
                    frame: game.currentFrame,
                    ballColor: ballColor,
                    commentary: commentary ? commentary.text : null,
                    burst: !!burst,
                    angleErr: lastAngleErr,
                    launchAngle: lastLaunchAngle,
                    pinsStanding: standingCount()
                };
            },
            tierFor: function (pct) {
                var t = tierFor(pct);
                return { min: t.min, time: t.time, speed: t.speed, max: !!t.max, weak: t.weak || 0 };
            },
            startCharge: startCharge,
            cancelCharge: cancelCharge,
            setPower: function (pct) { METER.power = Math.max(0, Math.min(1, pct / 100)); },
            lock: lockAndThrow,
            aim: function (deg) { aim.angle = Math.max(-MAX_ANGLE, Math.min(MAX_ANGLE, deg)); },
            setBallColor: function (name) { if (BALL_COLORS[name]) ballColor = name; },
            setAngleError: function (deg) { angleErrOverride = deg === null || deg === undefined ? null : deg; },
            pinsData: function () {
                var out = [];
                for (var i = 0; i < pins.length; i++) {
                    out.push({ num: pins[i].num, x: Math.round(pins[i].x), y: Math.round(pins[i].y), vx: Math.round(pins[i].vx * 100) / 100, vy: Math.round(pins[i].vy * 100) / 100, down: pins[i].down, dead: pins[i].dead });
                }
                return out;
            },
            reset: function () {
                game.currentFrame = 1;
                game.ballInFrame = 1;
                for (var i = 0; i < 10; i++) game.frames[i] = { rolls: [], total: null };
                game.lastTotal = 0;
                if (els.over) els.over.hidden = true;
                ball.x = CENTER;
                resetForNextRoll(true);
                setStatus('Drag to aim \u2014 release to charge');
                renderScoreboard();
            }
        };
    }
})();
