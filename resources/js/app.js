import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

/* === Password visibility toggle (Assignment 3 pattern) === */

window.togglePwVisibility = function (inputId, btn) {
    var input = document.getElementById(inputId);
    if (input.type === 'password') {
        input.type = 'text';
        btn.innerHTML = '<img src="/images/eye-open.svg" alt="" width="20" height="20">';
    } else {
        input.type = 'password';
        btn.innerHTML = '<img src="/images/eye-closed.svg" alt="" width="20" height="20">';
    }
};

/* === Custom form validation — SUBMIT ONLY === */

(function () {
    'use strict';

    function showError(input, errorId, message) {
        input.classList.add('error');
        input.classList.remove('valid');
        var el = document.getElementById(errorId);
        if (el) {
            el.textContent = message;
            el.classList.add('show');
        }
    }

    function clearError(input, errorId) {
        input.classList.remove('error', 'valid');
        var el = document.getElementById(errorId);
        if (el) {
            el.textContent = '';
            el.classList.remove('show');
        }
    }

    function validateEmail(input, errorId) {
        var val = input.value.trim();
        if (!val) { showError(input, errorId, 'Email is required'); return false; }
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) { showError(input, errorId, 'Please enter a valid email'); return false; }
        clearError(input, errorId);
        return true;
    }

    function validateRequired(input, errorId, label) {
        if (!input.value.trim()) { showError(input, errorId, label + ' is required'); return false; }
        clearError(input, errorId);
        return true;
    }

    function validatePassword(input, errorId, minLen) {
        minLen = minLen || 8;
        if (!input.value) { showError(input, errorId, 'Password is required'); return false; }
        if (input.value.length < minLen) { showError(input, errorId, 'Password must be at least ' + minLen + ' characters'); return false; }
        clearError(input, errorId);
        return true;
    }

    function validateConfirm(pw, confirm, errorId) {
        if (!confirm.value) { showError(confirm, errorId, 'Please confirm your password'); return false; }
        if (confirm.value !== pw.value) { showError(confirm, errorId, 'Passwords do not match'); return false; }
        clearError(confirm, errorId);
        return true;
    }

    /* === Password strength pins === */

    function getPwStrength(pw) {
        var score = 0;
        if (pw.length >= 8) score++;
        if (pw.length >= 12) score++;
        if (/[A-Z]/.test(pw) && /[a-z]/.test(pw)) score++;
        if (/[0-9]/.test(pw)) score++;
        if (/[^A-Za-z0-9]/.test(pw)) score++;
        if (score <= 1) return 1;
        if (score <= 2) return 2;
        if (score <= 3) return 3;
        return 4;
    }

    var strengthLabels = ['', 'Weak', 'Fair', 'Good', 'Strong'];

    function updatePwStrength(pw) {
        var el = document.getElementById('pwStrength');
        var label = document.getElementById('pwLabel');
        if (!el) return;
        if (!pw) { el.setAttribute('data-strength', '0'); if (label) label.textContent = ''; return; }
        var s = getPwStrength(pw);
        el.setAttribute('data-strength', String(s));
        if (label) label.textContent = strengthLabels[s];
    }

    /* === Setup: only submit listener, plus input clears error after submit === */

    function setupForm(formId, validateFn) {
        var form = document.getElementById(formId);
        if (!form) return;
        var submitted = false;

        form.addEventListener('submit', function (e) {
            submitted = true;
            if (!validateFn()) { e.preventDefault(); e.stopPropagation(); }
        });

        /* After first submit attempt, clear errors on input so user sees them vanish as they fix */
        var inputs = form.querySelectorAll('.auth-input');
        for (var i = 0; i < inputs.length; i++) {
            (function (input) {
                input.addEventListener('input', function () {
                    if (submitted && input.classList.contains('error')) {
                        clearError(input, input.id + 'Error');
                    }
                });
            })(inputs[i]);
        }
    }

    setupForm('loginForm', function () {
        var ok = true;
        if (!validateEmail(document.getElementById('email'), 'emailError')) ok = false;
        if (!validateRequired(document.getElementById('password'), 'passwordError', 'Password')) ok = false;
        return ok;
    });

    setupForm('registerForm', function () {
        var ok = true;
        if (!validateRequired(document.getElementById('name'), 'nameError', 'Name')) ok = false;
        if (!validateEmail(document.getElementById('email'), 'emailError')) ok = false;
        if (!validatePassword(document.getElementById('password'), 'passwordError')) ok = false;
        if (!validateConfirm(document.getElementById('password'), document.getElementById('password_confirmation'), 'password_confirmationError')) ok = false;
        return ok;
    });

    setupForm('forgotForm', function () {
        return validateEmail(document.getElementById('email'), 'emailError');
    });

    setupForm('resetForm', function () {
        var ok = true;
        if (!validateEmail(document.getElementById('email'), 'emailError')) ok = false;
        if (!validatePassword(document.getElementById('password'), 'passwordError')) ok = false;
        if (!validateConfirm(document.getElementById('password'), document.getElementById('password_confirmation'), 'password_confirmationError')) ok = false;
        return ok;
    });

    setupForm('confirmForm', function () {
        return validateRequired(document.getElementById('password'), 'passwordError', 'Password');
    });

    /* Password strength live update (register page only) */
    var pwInput = document.getElementById('password');
    if (pwInput && document.getElementById('pwStrength')) {
        pwInput.addEventListener('input', function () { updatePwStrength(pwInput.value); });
    }
})();
