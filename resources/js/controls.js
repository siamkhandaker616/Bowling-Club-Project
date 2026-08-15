/* === Oil Alley custom controls — payload-safe binders (v4) ===
 * The decorative controls sync to REAL inputs (native or hidden) so
 * form submissions carry the exact same payload as a plain control.
 * Binder 0 (select dropdown) keeps the v3 contract: a BUTTON.input.select
 * followed by a .select-pop, syncing .opt[data-v] to a hidden input.
 */

(function () {
    'use strict';

    function el(sel, root) { return (root || document).querySelector(sel); }
    function els(sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); }

    function closeSelects() {
        els('.select-pop.open').forEach(function (p) { p.classList.remove('open'); });
    }
    document.addEventListener('click', closeSelects);

    function init(root) {
        root = root || document;

        /* --- Binder 0: .input.select + .select-pop (v3, skips non-BUTTON) --- */
        els('.select', root).forEach(function (btn) {
            if (btn.tagName !== 'BUTTON') return;
            if (btn.__oaBound) return;
            btn.__oaBound = true;
            var pop = btn.nextElementSibling;
            if (!pop || !pop.classList.contains('select-pop')) return;
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                closeSelects();
                pop.classList.toggle('open');
            });
            els('.opt', pop).forEach(function (o) {
                if (o.classList.contains('off')) return;
                o.addEventListener('click', function () {
                    els('.opt', pop).forEach(function (x) { x.classList.remove('on'); });
                    o.classList.add('on');
                    var label = btn.firstChild;
                    if (label) label.textContent = (o.dataset.v || '') + ' ';
                    var hid = pop.parentNode.querySelector('input[type="hidden"]') ||
                              el('input[type="hidden"]', pop);
                    if (hid && o.dataset.v !== undefined) hid.value = o.dataset.v;
                    pop.classList.remove('open');
                });
            });
        });

        /* --- 1: Ball-Return Select --- */
        els('.br-wrap', root).forEach(function (w) {
            if (w.__oaBound) return;
            w.__oaBound = true;
            var trigger = el('.br-trigger', w);
            if (!trigger) return;
            trigger.addEventListener('click', function (e) {
                e.stopPropagation();
                closeSelects();
                w.classList.toggle('open');
            });
            var hid = el('.br-input', w) || el('input[type="hidden"]', w);
            var valEl = el('.br-val', w) || trigger.querySelector('.br-label') || trigger.firstChild;
            els('.br-lane', w).forEach(function (lane) {
                if (lane.classList.contains('off')) return;
                lane.addEventListener('click', function () {
                    els('.br-lane', w).forEach(function (x) { x.classList.remove('on'); });
                    lane.classList.add('on');
                    if (hid) hid.value = lane.dataset.v || lane.dataset.value || '';
                    if (valEl) valEl.textContent = (lane.dataset.v || '') + ' ';
                    w.classList.remove('open');
                });
            });
        });

        /* --- 2: League Calendar Datepicker --- */
        var LC_MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        var LC_DOW = ['S','M','T','W','T','F','S'];
        function lcRender(lc) {
            var year = +(lc.dataset.year || new Date().getFullYear());
            var grid = el('.lc-grid', lc);
            var moEl = el('.lc-mo', lc);
            var mInput = el('.lc-m', lc);
            var month = mInput ? +mInput.value : new Date().getMonth();
            if (!grid || !moEl || !mInput) return;
            moEl.textContent = LC_MONTHS[month] + ' ' + year;
            grid.innerHTML = '';
            LC_DOW.forEach(function (d, i) {
                var c = document.createElement('div');
                c.className = 'lc-dow' + (i === 0 || i === 6 ? ' weekend' : '');
                c.textContent = d;
                grid.appendChild(c);
            });
            var dims = new Date(year, month + 1, 0).getDate();
            var first = new Date(year, month, 1).getDay();
            var rows = 6;
            for (var i = 0; i < rows * 7; i++) {
                var dt = new Date(year, month, 1 - first + i);
                var num = dt.getDate(), m = dt.getMonth();
                var c = document.createElement('div');
                c.className = 'lc-day';
                if (m !== month) {
                    c.classList.add('dim');
                    c.textContent = num;
                    c.addEventListener('click', function () {
                        lcNav(lc, this._m > month ? 1 : -1);
                    });
                } else {
                    c.textContent = num;
                    c.addEventListener('click', function () {
                        els('.lc-day', lc).forEach(function (x) { x.classList.remove('on'); });
                        this.classList.add('on');
                        var hid = el('.lc-input', lc) || el('input[type="hidden"]', lc);
                        var picked = el('.lc-picked', lc);
                        var iso = year + '-' +
                            String(month + 1).padStart(2, '0') + '-' +
                            String(this._num).padStart(2, '0');
                        if (hid) hid.value = iso;
                        if (picked) picked.textContent = LC_MONTHS[month] + ' ' + this._num;
                    });
                }
                c._m = m; c._num = num;
                grid.appendChild(c);
            }
            var picked = el('.lc-picked', lc);
            if (picked && !picked.dataset.kept) picked.textContent = 'Tap a date';
        }
        function lcNav(lc, dir) {
            var mInput = el('.lc-m', lc);
            if (!mInput) return;
            var m = (+mInput.value + dir + 12) % 12;
            mInput.value = String(m);
            lcRender(lc);
        }
        els('.lc', root).forEach(function (lc) {
            if (lc.__oaBound) return;
            lc.__oaBound = true;
            var navs = els('.lc-nav', lc);
            if (navs[0]) navs[0].addEventListener('click', function () { lcNav(lc, -1); });
            if (navs[1]) navs[1].addEventListener('click', function () { lcNav(lc, 1); });
            lcRender(lc);
        });

        /* --- 3: Pin Checkbox (real checkbox already inside) --- */
        els('.pin-check', root).forEach(function (chk) {
            if (chk.__oaBound) return;
            chk.__oaBound = true;
            chk.addEventListener('click', function (e) {
                if (e.target.tagName === 'INPUT') return;
                var input = el('input', chk);
                if (input) input.checked = !input.checked;
            });
        });

        /* --- 4: Ball-Rack Radio (real radios already inside) --- */
        els('.rack', root).forEach(function (rack) {
            if (rack.__oaBound) return;
            rack.__oaBound = true;
            rack.addEventListener('click', function (e) {
                var opt = e.target.closest ? e.target.closest('.rack-opt') : null;
                if (!opt) return;
                var input = el('input', opt);
                if (input && e.target.tagName !== 'INPUT') input.checked = true;
            });
        });

        /* --- 5: Lane-Oil Range Slider --- */
        els('.lane-range', root).forEach(function (rng) {
            if (rng.__oaBound) return;
            rng.__oaBound = true;
            var read = rng.parentNode ? el('.range-read', rng.parentNode) : null;
            var unit = rng.dataset.unit || 'units';
            var label = rng.dataset.label || 'Oil weight';
            function sync() { if (read) read.textContent = label + ' \u00B7 ' + rng.value + ' ' + unit; }
            rng.addEventListener('input', sync);
            sync();
        });

        /* --- 6: Gutter-Ball Validation --- */
        function setPins(stage, standing, random) {
            if (!stage) return;
            var keep = null;
            if (random && standing > 0 && standing < 10) {
                var idx = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9];
                for (var i = 9; i > 0; i--) {
                    var j = Math.floor(Math.random() * (i + 1));
                    var t = idx[i]; idx[i] = idx[j]; idx[j] = t;
                }
                keep = idx.slice(0, standing);
            }
            els('.pin', stage).forEach(function (p, i) {
                p.classList.remove('down', 'strike');
                if (keep) {
                    if (keep.indexOf(i) === -1) p.classList.add('down');
                } else if (i >= standing) {
                    p.classList.add('down');
                }
            });
        }
        function strikePins(stage) {
            if (!stage) return;
            els('.pin', stage).forEach(function (p) {
                p.classList.add('strike');
                setTimeout(function () { p.classList.remove('strike'); p.classList.add('down'); }, 600);
            });
        }
        var gutterToastEl = null;
        function gutterToast(message) {
            if (!gutterToastEl) {
                gutterToastEl = document.createElement('div');
                gutterToastEl.className = 'oa-gutter-toast';
                document.body.appendChild(gutterToastEl);
            }
            gutterToastEl.className = 'oa-gutter-toast err';
            gutterToastEl.textContent = message;
            void gutterToastEl.offsetWidth;
            gutterToastEl.classList.add('show');
            clearTimeout(gutterToastEl._t);
            gutterToastEl._t = setTimeout(function () { gutterToastEl.classList.remove('show'); }, 2600);
        }
        els('.gutter-form', root).forEach(function (form) {
            if (form.__oaBound) return;
            form.__oaBound = true;
            var stage = el('.lane-stage', form);
            var dot = stage ? el('.ball-dot', stage) : null;
            var inputs = els('.input', form);
            inputs.forEach(function (i) {
                i.addEventListener('input', function () {
                    var good = !!i.value.trim();
                    i.classList.toggle('good', good);
                    i.classList.toggle('bad', !good);
                });
            });
            form.addEventListener('submit', function (e) {
                var bads = 0;
                inputs.forEach(function (i) {
                    var good = !!i.value.trim();
                    i.classList.toggle('bad', !good);
                    i.classList.toggle('good', good);
                    if (!good) bads++;
                });
                var allBad = bads > 0 && bads === inputs.length;
                var plural = bads === 1 ? '' : 's';
                setPins(stage, 10);
                if (stage) {
                    stage.classList.remove('roll', 'gutter-roll');
                    void stage.offsetWidth;
                    stage.classList.add(allBad ? 'gutter-roll' : 'roll');
                }
                if (dot) {
                    dot.addEventListener('animationend', function () {
                        if (bads === 0) strikePins(stage);
                        else if (allBad) setPins(stage, 10);
                        else setPins(stage, bads, true);
                        if (stage) stage.classList.remove('roll', 'gutter-roll');
                    }, { once: true });
                } else if (bads > 0 && !allBad) {
                    setPins(stage, bads, true);
                }
                if (bads > 0) {
                    gutterToast(allBad
                        ? 'GUTTER \u2014 ' + bads + ' empty field' + plural + '. Ball missed every pin.'
                        : bads + ' empty field' + plural + '. ' + bads + ' pin' + plural + ' still standing.');
                    e.preventDefault();
                    e.stopPropagation();
                }
            });
        });

        /* --- Close any pop when clicking outside the .br-wrap --- */
        document.addEventListener('click', function (e) {
            els('.br-wrap.open', root).forEach(function (w) {
                if (!w.contains(e.target)) w.classList.remove('open');
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { init(document); });
    } else {
        init(document);
    }

    window.initOilAlleyControls = init;
})();
