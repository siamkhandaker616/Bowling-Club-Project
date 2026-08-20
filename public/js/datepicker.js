/* === Self-injected CSS === */
(function () {
    if (document.getElementById('dpicker-css')) return;
    var s = document.createElement('style');
    s.id = 'dpicker-css';
    s.textContent =
        '.dpicker-wrap{position:relative;display:inline-block;width:100%}' +
        '.dpicker-display{cursor:pointer;font-family:var(--font-mono);font-size:.7rem;padding:6px 14px;border:2px solid var(--navy);border-radius:50px;background:var(--pin-white);color:var(--navy);outline:none;width:100%;box-sizing:border-box;transition:all .15s;text-align:left}' +
        '.dpicker-display::placeholder{color:var(--fog);font-style:italic}' +
        '.dpicker-display:hover{background:var(--cloud);transform:translateY(-1px);box-shadow:var(--hard)}' +
        '.dpicker-display:focus{border-color:var(--gold);box-shadow:0 0 0 3px rgba(201,143,61,0.25)}' +
        '.dpicker-popup{position:fixed;z-index:9999;background:var(--lane-wood-light);border:3px solid var(--navy);box-shadow:6px 6px 0 var(--navy);padding:0;width:280px;border-radius:12px;overflow:hidden}' +
        '.dpicker-popup.hidden{display:none}' +
        '.dp-nav-row{display:flex;align-items:center;justify-content:space-between;background:var(--navy);padding:10px 12px}' +
        '.dp-nav{width:28px;height:28px;border-radius:50%;border:2px solid var(--gold);background:var(--coral);color:var(--pin-white);cursor:pointer;font-size:.7rem;line-height:1;transition:all .12s ease;font-family:var(--font-mono);font-weight:700}' +
        '.dp-nav:hover{background:var(--gold);color:var(--navy);transform:scale(1.15)}' +
        '.dp-title{font-family:var(--font-header);font-size:.72rem;text-transform:uppercase;letter-spacing:1px;color:var(--gold-light)}' +
        '.dp-dow-row{display:grid;grid-template-columns:repeat(7,1fr);text-align:center;padding:6px 12px 2px}' +
        '.dp-dow{font-family:var(--font-mono);font-size:.5rem;text-transform:uppercase;color:var(--coral);padding:3px 0;font-weight:700;letter-spacing:.5px}' +
        '.dp-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:3px;text-align:center;padding:4px 12px 8px}' +
        '.dp-day{padding:6px 2px;font-family:var(--font-mono);font-size:.65rem;cursor:pointer;border:2px solid transparent;border-radius:8px;transition:all .12s ease;color:var(--navy);font-weight:600}' +
        '.dp-day:hover{background:var(--gold-light);border-color:var(--gold);transform:translateY(-2px);box-shadow:0 2px 0 var(--gold)}' +
        '.dp-day.dp-dim{cursor:default;color:var(--slate);font-weight:400;opacity:.4}' +
        '.dp-day.dp-dim:hover{transform:none;background:transparent;border-color:transparent;box-shadow:none;opacity:.5}' +
        '.dp-day.dp-today{border-color:var(--coral);font-weight:700;color:var(--coral);box-shadow:inset 0 0 0 1px var(--coral)}' +
        '.dp-day.dp-selected{background:var(--coral);color:var(--pin-white);border-color:var(--navy);box-shadow:0 2px 0 var(--navy);transform:translateY(-1px)}' +
        '.dp-footer{display:flex;gap:6px;justify-content:flex-end;padding:8px 12px;background:var(--navy);border-top:2px solid var(--rubber)}' +
        '.dpicker-btn{font-family:var(--font-mono);font-size:.6rem;padding:5px 14px;border:2px solid var(--gold);border-radius:50px;background:transparent;color:var(--gold-light);cursor:pointer;text-transform:uppercase;letter-spacing:.5px;transition:all .12s;font-weight:700}' +
        '.dpicker-btn:hover{background:var(--gold);color:var(--navy)}' +
        '.dpicker-btn-accent{background:var(--gold);color:var(--navy);border-color:var(--gold)}' +
        '.dpicker-btn-accent:hover{background:var(--gold-light);box-shadow:0 2px 0 var(--rubber)}' +
        '.dpicker-popup--dt{width:380px;padding:0}' +
        '.dpicker-display--dt{min-width:200px}' +
        '.dp-dt-wrap{display:flex;gap:0;align-items:stretch}' +
        '.dp-dt-cal{flex:1}' +
        '.dp-dt-time{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:12px;background:var(--navy);border-left:3px solid var(--gold)}' +
        '.dp-dt-time-label{font-family:var(--font-mono);font-size:.5rem;text-transform:uppercase;color:var(--coral-light);margin-bottom:6px;letter-spacing:1px;font-weight:700}' +
        '.dp-dt-time-sep{font-family:var(--font-mono);font-size:.9rem;color:var(--gold-light);margin:2px 0;font-weight:700}' +
        '.dp-dt-time-input{width:48px;text-align:center;padding:6px;border:2px solid var(--gold);border-radius:8px;font-family:var(--font-mono);font-size:.75rem;background:var(--pin-white);color:var(--navy);box-sizing:border-box;font-weight:700}' +
        '.dp-dt-time-input:focus{border-color:var(--coral);outline:none;box-shadow:0 0 0 2px rgba(212,87,79,0.3)}' +
        '.dp-dt-time-input::-webkit-inner-spin-button,.dp-dt-time-input::-webkit-outer-spin-button{-webkit-appearance:none;margin:0}' +
        '.dp-dt-time-input[type=number]{-moz-appearance:textfield}';
    document.head.appendChild(s);
})();

/* === CONSTANTS === */
var MONTHS = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
var DAY_HEADERS = ['Su','Mo','Tu','We','Th','Fr','Sa'];

/* === PICKER MANAGER === */
var DPM = {
    openPickers: new Set(),
    onScroll: function () {
        DPM.openPickers.forEach(function (p) {
            if (p.popup && !p.popup.classList.contains('hidden')) p.positionPopup();
        });
    },
    onResize: function () {
        DPM.openPickers.forEach(function (p) {
            if (p.popup && !p.popup.classList.contains('hidden')) p.positionPopup();
        });
    },
    onClick: function (e) {
        DPM.openPickers.forEach(function (p) {
            if (p.popup && !p.popup.classList.contains('hidden') &&
                !p.wrapper.contains(e.target) && !p.popup.contains(e.target)) {
                p.close();
            }
        });
    },
    register: function (picker) {
        DPM.openPickers.add(picker);
        if (DPM.openPickers.size === 1) {
            window.addEventListener('scroll', DPM.onScroll);
            window.addEventListener('resize', DPM.onResize);
            document.addEventListener('click', DPM.onClick);
        }
    },
    unregister: function (picker) {
        DPM.openPickers.delete(picker);
        if (DPM.openPickers.size === 0) {
            window.removeEventListener('scroll', DPM.onScroll);
            window.removeEventListener('resize', DPM.onResize);
            document.removeEventListener('click', DPM.onClick);
        }
    }
};

/* === DATE PICKER CLASS === */
function DatePicker(input) {
    this.input = input;
    this.isDateTime = input.type === 'datetime-local';
    this.date = null;
    this.viewDate = new Date();
    this.popup = null;
    this.display = null;
    this.wrapper = null;

    if (this.input.value) {
        if (this.isDateTime) {
            this.date = new Date(this.input.value);
        } else {
            this.date = new Date(this.input.value + 'T00:00:00');
        }
    }

    this.build();
    this.bindEvents();
}

DatePicker._instances = {};

DatePicker.getInstance = function (id) {
    return DatePicker._instances[id] || null;
};

DatePicker.prototype.build = function () {
    this.wrapper = document.createElement('div');
    this.wrapper.className = 'dpicker-wrap';
    this.input.parentNode.insertBefore(this.wrapper, this.input);
    this.wrapper.appendChild(this.input);

    this.display = document.createElement('input');
    this.display.type = 'text';
    this.display.className = 'dpicker-display' + (this.isDateTime ? ' dpicker-display--dt' : '');
    this.display.readOnly = true;
    this.display.placeholder = this.input.placeholder || (this.isDateTime ? 'Pick date & time' : 'Pick a date');
    this.wrapper.appendChild(this.display);

    if (this.input.value) {
        this.display.value = this._formatDisplay(this.input.value);
    }

    this.input.style.display = 'none';
    this.input.setAttribute('autocomplete', 'off');

    this.popup = document.createElement('div');
    this.popup.className = 'dpicker-popup hidden' + (this.isDateTime ? ' dpicker-popup--dt' : '');
    this.popup.style.position = 'fixed';
    document.body.appendChild(this.popup);

    this.render();

    if (this.input.id) {
        DatePicker._instances[this.input.id] = this;
    }
};

DatePicker.prototype._formatDisplay = function (val) {
    if (!val) return '';
    var parts = val.split('T');
    var ymd = parts[0].split('-');
    var monthIndex = parseInt(ymd[1], 10) - 1;
    var monthStr = MONTHS[monthIndex] || '';
    var day = parseInt(ymd[2], 10);
    var result = day + ' ' + monthStr + ' ' + ymd[0];
    if (parts[1]) result += ' \u2022 ' + parts[1];
    return result;
};

DatePicker.prototype.render = function () {
    var year = this.viewDate.getFullYear();
    var month = this.viewDate.getMonth();
    var firstDay = new Date(year, month, 1).getDay();
    var daysInMonth = new Date(year, month + 1, 0).getDate();
    var daysInPrev = new Date(year, month, 0).getDate();
    var now = new Date();

    var html = '<div class="dp-nav-row">';
    html += '<button type="button" class="dp-nav" data-action="prev">&#9664;</button>';
    html += '<span class="dp-title">' + MONTHS[month] + ' ' + year + '</span>';
    html += '<button type="button" class="dp-nav" data-action="next">&#9654;</button>';
    html += '</div>';

    if (this.isDateTime) {
        html += '<div class="dp-dt-wrap"><div class="dp-dt-cal">';
    }

    html += '<div class="dp-dow-row">';
    for (var i = 0; i < DAY_HEADERS.length; i++) {
        html += '<span class="dp-dow">' + DAY_HEADERS[i] + '</span>';
    }
    html += '</div>';

    html += '<div class="dp-grid">';
    for (var i = 0; i < firstDay; i++) {
        var pd = daysInPrev - firstDay + 1 + i;
        html += '<span class="dp-day dp-dim" data-day="' + pd + '">' + pd + '</span>';
    }
    for (var d = 1; d <= daysInMonth; d++) {
        var isToday = d === now.getDate() && month === now.getMonth() && year === now.getFullYear();
        var isSelected = this.date && d === this.date.getDate() && month === this.date.getMonth() && year === this.date.getFullYear();
        var cls = 'dp-day';
        if (isToday) cls += ' dp-today';
        if (isSelected) cls += ' dp-selected';
        html += '<span class="' + cls + '" data-day="' + d + '">' + d + '</span>';
    }
    var totalCells = firstDay + daysInMonth;
    var remaining = totalCells <= 35 ? (35 - totalCells) : (42 - totalCells);
    for (var n = 1; n <= remaining; n++) {
        html += '<span class="dp-day dp-dim" data-day="' + n + '">' + n + '</span>';
    }
    html += '</div>';

    if (this.isDateTime) {
        html += '</div>';
        var h = this.date ? String(this.date.getHours()).padStart(2, '0') : '08';
        var m = this.date ? String(this.date.getMinutes()).padStart(2, '0') : '00';
        html += '<div class="dp-dt-time">';
        html += '<span class="dp-dt-time-label">Time</span>';
        html += '<input type="number" class="dp-dt-time-input dp-hour" value="' + h + '" min="0" max="23" step="1">';
        html += '<span class="dp-dt-time-sep">:</span>';
        html += '<input type="number" class="dp-dt-time-input dp-min" value="' + m + '" min="0" max="55" step="5">';
        html += '</div></div>';
    }

    html += '<div class="dp-footer">';
    html += '<button type="button" class="dpicker-btn" data-action="clear">Clear</button>';
    html += '<button type="button" class="dpicker-btn dpicker-btn-accent" data-action="close">Done</button>';
    html += '</div>';

    this.popup.innerHTML = html;
};

DatePicker.prototype.positionPopup = function () {
    var rect = this.display.getBoundingClientRect();
    var popupW = this.isDateTime ? 380 : 280;
    var popupH = this.popup.offsetHeight;
    var top;

    if (rect.top > popupH + 10) {
        top = rect.top - popupH - 6;
    } else {
        top = rect.bottom + 6;
    }

    var left = rect.left;
    if (left + popupW > window.innerWidth) {
        left = window.innerWidth - popupW - 10;
    }
    if (left < 10) left = 10;

    this.popup.style.left = left + 'px';
    this.popup.style.top = top + 'px';
};

DatePicker.prototype.bindEvents = function () {
    var self = this;

    this.display.addEventListener('click', function () {
        self.toggle();
    });

    this.popup.addEventListener('click', function (e) {
        e.stopPropagation();
        var target = e.target;

        if (target.dataset.action === 'prev') {
            self.viewDate.setMonth(self.viewDate.getMonth() - 1);
            self.render();
        } else if (target.dataset.action === 'next') {
            self.viewDate.setMonth(self.viewDate.getMonth() + 1);
            self.render();
        } else if (target.dataset.action === 'clear') {
            self.clear();
            self.render();
        } else if (target.dataset.action === 'close') {
            self.close();
        } else if (target.classList.contains('dp-day') && !target.classList.contains('dp-dim')) {
            var day = parseInt(target.dataset.day);
            if (self.isDateTime) {
                var hEl = self.popup.querySelector('.dp-hour');
                var mEl = self.popup.querySelector('.dp-min');
                var currH = hEl ? parseInt(hEl.value) || 0 : 0;
                var currM = mEl ? parseInt(mEl.value) || 0 : 0;
                self.date = new Date(self.viewDate.getFullYear(), self.viewDate.getMonth(), day, currH, currM);
                self.render();
            } else {
                self.date = new Date(self.viewDate.getFullYear(), self.viewDate.getMonth(), day);
                self.updateValue();
                self.close();
            }
        }
    });

    this.popup.addEventListener('change', function (e) {
        if (self.isDateTime && (e.target.classList.contains('dp-hour') || e.target.classList.contains('dp-min'))) {
            if (self.date) {
                var h = parseInt(self.popup.querySelector('.dp-hour').value) || 0;
                var m = parseInt(self.popup.querySelector('.dp-min').value) || 0;
                self.date.setHours(h, m, 0, 0);
            }
        }
    });

    this.popup.addEventListener('input', function (e) {
        if (self.isDateTime && (e.target.classList.contains('dp-hour') || e.target.classList.contains('dp-min'))) {
            var hEl = self.popup.querySelector('.dp-hour');
            var mEl = self.popup.querySelector('.dp-min');
            var h = parseInt(hEl.value);
            if (isNaN(h) || h < 0) hEl.value = '00';
            if (h > 23) hEl.value = '23';
            var m = parseInt(mEl.value);
            if (isNaN(m) || m < 0) mEl.value = '00';
            if (m > 55) mEl.value = '55';
            if (self.date) {
                self.date.setHours(parseInt(hEl.value) || 0, parseInt(mEl.value) || 0, 0, 0);
            }
        }
    });
};

DatePicker.prototype.toggle = function () {
    if (this.popup.classList.contains('hidden')) {
        this.open();
    } else {
        this.close();
    }
};

DatePicker.prototype.open = function () {
    var self = this;
    DPM.openPickers.forEach(function (p) { if (p !== self) p.close(); });
    this.viewDate = this.date ? new Date(this.date) : new Date();
    this.render();
    this.popup.classList.remove('hidden');
    DPM.register(this);
    requestAnimationFrame(function () {
        self.positionPopup();
    });
};

DatePicker.prototype.close = function () {
    this.popup.classList.add('hidden');
    DPM.unregister(this);
};

DatePicker.prototype.updateValue = function () {
    if (!this.date) return;
    var y = this.date.getFullYear();
    var mo = String(this.date.getMonth() + 1).padStart(2, '0');
    var d = String(this.date.getDate()).padStart(2, '0');

    if (this.isDateTime) {
        var h = String(this.date.getHours()).padStart(2, '0');
        var m = String(this.date.getMinutes()).padStart(2, '0');
        var value = y + '-' + mo + '-' + d + 'T' + h + ':' + m;
        this.display.value = this._formatDisplay(value);
        this.input.value = value;
    } else {
        var value = y + '-' + mo + '-' + d;
        this.display.value = this._formatDisplay(value);
        this.input.value = value;
    }
    this.input.dispatchEvent(new Event('change', { bubbles: true }));
};

DatePicker.prototype.clear = function () {
    this.date = null;
    this.display.value = '';
    this.input.value = '';
    this.input.dispatchEvent(new Event('change', { bubbles: true }));
};

DatePicker.prototype.syncFromInput = function () {
    if (this.input.value) {
        if (this.isDateTime) {
            this.date = new Date(this.input.value);
        } else {
            this.date = new Date(this.input.value + 'T00:00:00');
        }
        this.display.value = this._formatDisplay(this.input.value);
    } else {
        this.date = null;
        this.display.value = '';
    }
};

/* === INIT === */
document.addEventListener('DOMContentLoaded', function () {
    var inputs = document.querySelectorAll('input[data-datepicker]');
    for (var i = 0; i < inputs.length; i++) {
        new DatePicker(inputs[i]);
    }
});
