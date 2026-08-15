/* === VIRTUAL BOWLING (Feature 13) — Layer C canvas engine === */
/* Left-drag to aim + power (slingshot), swipe to hook, right-drag to move the ball, or arrow keys + space. */

(function () {
    'use strict';

    var cfg = window.GAME_CONFIG || {};
    var canvas = document.getElementById('game-canvas');
    if (!canvas) return;
    var ctx = canvas.getContext('2d');

    var W = canvas.width, H = canvas.height;
    var LANE_L = 96, LANE_R = 404, CENTER = 250;
    var BALL_R = 14, PIN_R = 7, PIN_FALL = 18;
    var FOUL_Y = 560, BALL_START_Y = 590, PIT_Y = 118;
    var MIN_SPEED = 260, MAX_SPEED = 540;

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

    var pins = [];
    var ball = { x: CENTER, y: BALL_START_Y, vx: 0, vy: 0, active: false, inGutter: false, age: 0 };
    var state = 'aiming';
    var aim = { angle: 0, power: 0.5, dragX: null, dragY: null, hook: 0, mode: null, pressX: null, pressY: null };

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

    function recordRoll(knocked) {
        var f = game.currentFrame;
        if (f < 10) {
            if (game.ballInFrame === 1) {
                if (knocked === 10) {
                    game.frames[f - 1].rolls = [10];
                    setStatus('Strike!');
                    endFrame();
                } else {
                    game.frames[f - 1].rolls = [knocked];
                    game.ballInFrame = 2;
                    setStatus('Roll again \u2014 ' + (10 - knocked) + ' pins left');
                    resetForNextRoll(false);
                }
            } else {
                var first = game.frames[f - 1].rolls[0];
                game.frames[f - 1].rolls = [first, knocked];
                if (first + knocked === 10) setStatus('Spare!');
                else setStatus('Open frame \u2014 ' + (10 - first - knocked) + ' pins');
                endFrame();
            }
        } else {
            var tenth = game.frames[9].rolls;
            tenth.push(knocked);
            var done = (tenth.length === 2 && tenth[0] + tenth[1] < 10) || tenth.length === 3;
            if (done) {
                if (tenth[0] === 10) setStatus('Strike!');
                else if (tenth.length === 2 && tenth[0] + tenth[1] === 10) setStatus('Spare!');
                else setStatus('Open frame');
                endFrame();
            } else {
                setStatus(tenth[0] === 10 ? 'Strike \u2014 two more rolls' : 'Spare \u2014 one more roll');
                resetForNextRoll(standingCount() === 0);
            }
        }
    }

    function endFrame() {
        var totals = computeScores(game.frames);
        game.lastTotal = totals[9] === null ? 0 : totals[9];
        renderScoreboard();

        if (game.currentFrame === 10) {
            state = 'over';
            setStatus('Game over');
            submitScore();
            return;
        }

        game.currentFrame++;
        game.ballInFrame = 1;
        resetForNextRoll(true);
    }

    function resetForNextRoll(rerack) {
        state = 'aiming';
        aim.dragX = null;
        aim.dragY = null;
        aim.hook = 0;
        aim.mode = null;
        aim.pressX = null;
        aim.pressY = null;
        if (rerack) resetPins();
        resetBall();
        renderScoreboard();
    }

    var rollStanding = 0;

    function throwBall() {
        var speed = MIN_SPEED + aim.power * (MAX_SPEED - MIN_SPEED);
        var a = (aim.angle * Math.PI) / 180;
        ball.vx = Math.sin(a) * speed;
        ball.vy = -Math.cos(a) * speed;
        rollStanding = standingCount();
        ball.active = true;
        ball.age = 0;
        state = 'throwing';
        setStatus('Rolling\u2026');
    }

    function throwFromDrag(px, py, rx, ry) {
        var dx = px - rx;
        var dy = py - ry;
        var mag = Math.sqrt(dx * dx + dy * dy);
        if (mag < 12) { state = 'aiming'; return; }
        var speed = Math.max(MIN_SPEED, Math.min(MAX_SPEED, mag * 1.2));
        var scale = speed / mag;
        ball.vx = dx * scale;
        ball.vy = dy * scale;
        aim.hook = Math.max(-2.2, Math.min(2.2, dx / 55));
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

        if (ball.active) {
            var damp = Math.max(0, 1 - 0.55 * dt);
            ball.vx *= damp;
            ball.vy *= damp;

            if (aim.hook !== 0 && ball.vy < 0) {
                ball.vx += aim.hook * 30 * dt;
            }

            if (ball.x < LANE_L + BALL_R) {
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

            ball.x += ball.vx * dt;
            ball.y += ball.vy * dt;

            if (ball.inGutter) ball.vy *= Math.max(0, 1 - 1.2 * dt);

            collidePins(dt);
            stepPins(dt);

            if (ballDone()) {
                ball.active = false;
                state = 'settling';
                setStatus('Counting pins\u2026');
            }
        } else {
            stepPins(dt);
            if (pinsSettled()) {
                state = 'aiming';
                var knocked = Math.max(0, rollStanding - standingCount());
                recordRoll(knocked);
            }
        }
    }

    function drawLane() {
        var grad = ctx.createLinearGradient(0, FOUL_Y, 0, PIT_Y);
        grad.addColorStop(0, palette.laneLight);
        grad.addColorStop(0.5, palette.lane);
        grad.addColorStop(1, palette.laneDark);
        ctx.fillStyle = grad;
        ctx.fillRect(LANE_L, PIT_Y, LANE_R - LANE_L, FOUL_Y - PIT_Y);

        ctx.fillStyle = palette.rubber;
        ctx.fillRect(LANE_L - 26, PIT_Y, 26, FOUL_Y - PIT_Y);
        ctx.fillRect(LANE_R, PIT_Y, 26, FOUL_Y - PIT_Y);

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
        if (!ball.active && state !== 'throwing' && state !== 'settling') {
            var grad = ctx.createRadialGradient(ball.x - 5, ball.y - 5, 2, ball.x, ball.y, BALL_R);
            grad.addColorStop(0, palette.coralLight);
            grad.addColorStop(0.55, palette.coral);
            grad.addColorStop(1, palette.rubber);
            ctx.fillStyle = grad;
        } else {
            var grad2 = ctx.createRadialGradient(ball.x - 5, ball.y - 5, 2, ball.x, ball.y, BALL_R);
            grad2.addColorStop(0, palette.navy);
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
        if (state !== 'aiming') return;
        var dx, dy, ex, ey, len;
        if (aim.mode === 'aim' && aim.pressX !== null && aim.dragX !== null) {
            dx = aim.pressX - aim.dragX;
            dy = aim.pressY - aim.dragY;
            var pull = Math.sqrt(dx * dx + dy * dy);
            if (pull < 10) return;
            len = 90 + Math.min(130, pull * 0.6);
            ex = ball.x + (dx / pull) * len;
            ey = ball.y + (dy / pull) * len;
        } else {
            var a = (aim.angle * Math.PI) / 180;
            len = 90 + aim.power * 130;
            dx = Math.sin(a) * len;
            dy = -Math.cos(a) * len;
            ex = ball.x + dx;
            ey = ball.y + dy;
        }
        var dist = Math.sqrt(dx * dx + dy * dy);
        if (dist < 10) return;
        ctx.save();
        ctx.setLineDash([7, 7]);
        ctx.strokeStyle = palette.coralLight;
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

    function render() {
        ctx.clearRect(0, 0, W, H);
        ctx.fillStyle = palette.rubber;
        ctx.fillRect(0, 0, W, H);
        drawLane();
        for (var i = 0; i < pins.length; i++) drawPin(pins[i]);
        drawAimGuide();
        drawBall();
    }

    var last = null;
    function loop(ts) {
        var dt = (ts - last) / 1000;
        if (dt <= 0 || !isFinite(dt)) dt = 1 / 60;
        else if (dt > 0.033) dt = 0.033;
        last = ts;
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

    canvas.addEventListener('contextmenu', function (e) { e.preventDefault(); });

    canvas.addEventListener('pointerdown', function (e) {
        if (state !== 'aiming') return;
        var p = canvasPos(e);
        if (e.button === 0) {
            aim.mode = 'aim';
            aim.pressX = p.x;
            aim.pressY = p.y;
            aim.dragX = p.x;
            aim.dragY = p.y;
            if (canvas.setPointerCapture) { try { canvas.setPointerCapture(e.pointerId); } catch (err) {} }
        } else if (e.button === 2) {
            aim.mode = 'pos';
            aim.dragX = p.x;
            aim.dragY = p.y;
            if (canvas.setPointerCapture) { try { canvas.setPointerCapture(e.pointerId); } catch (err) {} }
        }
    });

    canvas.addEventListener('pointermove', function (e) {
        if (aim.mode === null) return;
        var p = canvasPos(e);
        if (aim.mode === 'aim') {
            aim.dragX = p.x;
            aim.dragY = p.y;
        } else if (aim.mode === 'pos') {
            ball.x = Math.max(LANE_L + BALL_R, Math.min(LANE_R - BALL_R, p.x));
            aim.dragX = p.x;
            aim.dragY = p.y;
        }
    });

    function endDrag(e) {
        if (aim.mode === null) return;
        var p = canvasPos(e);
        if (aim.mode === 'aim') {
            throwFromDrag(aim.pressX, aim.pressY, p.x, p.y);
        }
        aim.mode = null;
        aim.dragX = null;
        aim.dragY = null;
        aim.pressX = null;
        aim.pressY = null;
    }

    canvas.addEventListener('pointerup', endDrag);
    canvas.addEventListener('pointercancel', function () {
        aim.mode = null;
        aim.dragX = null;
        aim.dragY = null;
        aim.pressX = null;
        aim.pressY = null;
    });

    document.addEventListener('keydown', function (e) {
        if (state !== 'aiming') return;
        if (e.key === 'ArrowLeft') { aim.angle = Math.max(-32, aim.angle - 4); e.preventDefault(); }
        else if (e.key === 'ArrowRight') { aim.angle = Math.min(32, aim.angle + 4); e.preventDefault(); }
        else if (e.key === 'ArrowUp') { aim.power = Math.min(1, aim.power + 0.05); e.preventDefault(); }
        else if (e.key === 'ArrowDown') { aim.power = Math.max(0.15, aim.power - 0.05); e.preventDefault(); }
        else if (e.key === ' ') { e.preventDefault(); throwBall(); }
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
            setStatus('Aim &amp; throw');
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
    setStatus('Aim &amp; throw');
    requestAnimationFrame(loop);
})();
