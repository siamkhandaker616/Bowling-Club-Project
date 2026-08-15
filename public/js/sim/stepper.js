(function () {
    'use strict';

    /* Oil Alley number stepper — adapted from Assignment 3 script.js initNumStepper.
     * Any <input type="number" data-stepper> gets ▲/▼ buttons while the native input
     * stays in the form (hidden) so the payload is untouched. data-stepper="edit"
     * keeps the value directly editable; geometry (width/margin) is copied from the
     * input so the surrounding layout keeps its current shape.
     */

    function els(sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); }

    function initNumStepper(input) {
        if (input._stepperBound) return;
        input._stepperBound = true;

        var _isInput = input.dataset.stepper === 'edit';
        var _setting = false;
        var _wrap = input.dataset.stepperWrap !== undefined;
        var _padLen = parseInt(input.dataset.stepperPad, 10) || 0;
        function pad(v) { return _padLen ? String(parseInt(v, 10) || 0).padStart(_padLen, '0') : String(v); }

        var val = _isInput ? document.createElement('input') : document.createElement('span');
        val.className = 'num-step-value';
        if (_isInput) { val.type = 'text'; val.inputMode = 'numeric'; val.value = input.value; }
        else { val.textContent = input.value; }
        var _val = pad(input.value);
        var _nativeValueSetter = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value').set;
        Object.defineProperty(input, 'value', {
            get: function () { return _val; },
            set: function (v) {
                _val = pad(v);
                _nativeValueSetter.call(input, _val);
                if (!val) return;
                if (_isInput) { if (!_setting) { _setting = true; val.value = _val; _setting = false; } }
                else { val.textContent = _val; }
            },
            configurable: true
        });

        var btns = document.createElement('div');
        btns.className = 'num-step-btns';

        function clickBtn(dir) {
            if (input.readOnly || input.disabled) return;
            var step = parseFloat(input.step) || 1;
            var min = input.min !== '' ? parseFloat(input.min) : -Infinity;
            var max = input.max !== '' ? parseFloat(input.max) : Infinity;
            var v = parseFloat(input.value) || 0;
            v = v + dir * step;
            if (_wrap) {
                if (v > max) v = min;
                if (v < min) v = max;
            } else {
                v = Math.max(min, Math.min(max, v));
            }
            input.value = v;
            var evt = new Event('change', { bubbles: true });
            input.dispatchEvent(evt);
        }

        var up = document.createElement('button');
        up.type = 'button';
        up.className = 'num-step-btn';
        up.innerHTML = '&#9650;';
        up.onclick = function () { clickBtn(1); };

        var down = document.createElement('button');
        down.type = 'button';
        down.className = 'num-step-btn';
        down.innerHTML = '&#9660;';
        down.onclick = function () { clickBtn(-1); };

        btns.appendChild(up);
        btns.appendChild(down);

        var wrap = document.createElement('div');
        wrap.className = 'num-stepper';
        wrap.appendChild(val);
        wrap.appendChild(btns);

        input._stepperWrap = wrap;
        input.style.display = 'none';
        input.parentNode.insertBefore(wrap, input.nextSibling);

        if (_isInput) {
            val.addEventListener('input', function () {
                if (_setting) return;
                var n = parseFloat(val.value);
                if (!isNaN(n) && val.value !== '') {
                    var min = input.min !== '' ? parseFloat(input.min) : -Infinity;
                    var max = input.max !== '' ? parseFloat(input.max) : Infinity;
                    n = Math.max(min, Math.min(max, n));
                    if (n !== parseFloat(input.value)) { input.value = n; }
                }
            });
            val.addEventListener('blur', function () {
                var n = parseFloat(val.value);
                if (isNaN(n) || val.value === '') {
                    n = input.min !== '' ? parseFloat(input.min) : 0;
                }
                var min = input.min !== '' ? parseFloat(input.min) : -Infinity;
                var max = input.max !== '' ? parseFloat(input.max) : Infinity;
                n = Math.max(min, Math.min(max, n));
                input.value = n;
            });
            val.addEventListener('wheel', function (e) { e.preventDefault(); clickBtn(e.deltaY > 0 ? -1 : 1); });
        }
        val.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowUp') { e.preventDefault(); clickBtn(1); }
            if (e.key === 'ArrowDown') { e.preventDefault(); clickBtn(-1); }
        });
        if (!_isInput) val.tabIndex = 0;
    }

    function init(root) {
        root = root || document;
        els('input[type="number"][data-stepper]', root).forEach(initNumStepper);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { init(document); });
    } else {
        init(document);
    }

    window.initNumSteppers = init;
})();
