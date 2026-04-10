<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Contact Us</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        /* ── Base ── */
        html, body {
            height: 100%;
            font-family: system-ui, -apple-system, 'Segoe UI', sans-serif;
            font-size: 15px;
            color: #1e1e2e;
            background: #ffffff;
        }

        body {
            padding: 1.5rem 1.75rem 2rem;
            overflow-x: hidden;
        }

        /* ── Header ── */
        .w-header {
            display: flex;
            align-items: center;
            gap: .75rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1.25rem;
            border-bottom: 1px solid #f0f0f5;
        }

        .w-header__icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .w-header__icon svg { display: block; }

        .w-header__title {
            font-size: 1.05rem;
            font-weight: 700;
            color: #1e1e2e;
            line-height: 1.2;
        }

        .w-header__sub {
            font-size: .78rem;
            color: #6b7280;
            margin-top: .15rem;
        }

        /* ── Alerts ── */
        .alert {
            border-radius: 8px;
            padding: .75rem 1rem;
            font-size: .84rem;
            line-height: 1.45;
            margin-bottom: 1.1rem;
            display: none;
            gap: .5rem;
            align-items: flex-start;
        }

        .alert--success {
            background: #f0fdf4;
            border: 1px solid #86efac;
            color: #166534;
        }

        .alert--error {
            background: #fef2f2;
            border: 1px solid #fca5a5;
            color: #991b1b;
        }

        /* ── Form ── */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .75rem;
        }

        .form-group { margin-bottom: .9rem; }

        label {
            display: block;
            font-size: .78rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: .3rem;
            letter-spacing: .01em;
        }

        label .req { color: #ef4444; margin-left: 1px; }

        input[type="text"],
        input[type="email"],
        input[type="tel"],
        textarea {
            width: 100%;
            padding: .55rem .8rem;
            border: 1.5px solid #e5e7eb;
            border-radius: 7px;
            font-size: .9rem;
            color: #1e1e2e;
            background: #fafafa;
            transition: border-color .15s, box-shadow .15s, background .15s;
            outline: none;
            -webkit-appearance: none;
        }

        input::placeholder, textarea::placeholder { color: #b0b7c3; }

        input:focus, textarea:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99,102,241,.12);
            background: #fff;
        }

        input.is-invalid, textarea.is-invalid {
            border-color: #f87171;
            background: #fff5f5;
        }

        textarea {
            resize: vertical;
            min-height: 90px;
            line-height: 1.5;
        }

        /* ── File picker ── */
        .file-trigger {
            display: flex;
            align-items: center;
            gap: .5rem;
            padding: .55rem .8rem;
            border: 1.5px dashed #d1d5db;
            border-radius: 7px;
            font-size: .84rem;
            color: #6b7280;
            background: #fafafa;
            cursor: pointer;
            transition: border-color .15s, color .15s;
            user-select: none;
        }

        .file-trigger:hover { border-color: #6366f1; color: #6366f1; }

        input[type="file"] { display: none; }

        .file-chips {
            display: flex;
            flex-wrap: wrap;
            gap: .3rem;
            margin-top: .45rem;
        }

        .chip {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            background: #f3f4f6;
            border: 1px solid #e5e7eb;
            border-radius: 5px;
            padding: .15rem .5rem;
            font-size: .76rem;
            color: #374151;
            max-width: 180px;
        }

        .chip span {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .chip__remove {
            background: none;
            border: none;
            cursor: pointer;
            color: #9ca3af;
            font-size: .85rem;
            line-height: 1;
            padding: 0;
            flex-shrink: 0;
        }

        .chip__remove:hover { color: #ef4444; }

        /* ── Field error ── */
        .field-error {
            font-size: .75rem;
            color: #ef4444;
            margin-top: .25rem;
            display: none;
        }

        /* ── Submit button ── */
        .btn-submit {
            width: 100%;
            padding: .7rem 1rem;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: .95rem;
            font-weight: 600;
            cursor: pointer;
            transition: opacity .15s, transform .1s;
            margin-top: .25rem;
            letter-spacing: .01em;
        }

        .btn-submit:hover   { opacity: .92; }
        .btn-submit:active  { transform: scale(.99); }
        .btn-submit:disabled { opacity: .55; cursor: not-allowed; }

        /* ── Success state ── */
        .success-state {
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 2.5rem 1rem;
            gap: 1rem;
        }

        .success-state__icon {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: #f0fdf4;
            border: 2px solid #86efac;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .success-state__title {
            font-size: 1.05rem;
            font-weight: 700;
            color: #166534;
        }

        .success-state__body {
            font-size: .875rem;
            color: #6b7280;
            line-height: 1.55;
        }

        .btn-new {
            margin-top: .5rem;
            padding: .55rem 1.25rem;
            background: none;
            border: 1.5px solid #6366f1;
            border-radius: 7px;
            color: #6366f1;
            font-size: .875rem;
            font-weight: 600;
            cursor: pointer;
            transition: background .15s, color .15s;
        }

        .btn-new:hover { background: #6366f1; color: #fff; }

        /* ── Footer ── */
        .w-footer {
            margin-top: 1.25rem;
            text-align: center;
            font-size: .72rem;
            color: #c4c4cf;
        }

        @media (max-width: 400px) {
            body { padding: 1.1rem 1rem 1.5rem; }
            .form-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    {{-- ── Header ── --}}
    <div class="w-header">
        <div class="w-header__icon" aria-hidden="true">
            <svg width="20" height="20" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
            </svg>
        </div>
        <div>
            <div class="w-header__title">Contact Us</div>
            <div class="w-header__sub">We usually reply within one business day.</div>
        </div>
    </div>

    {{-- ── Error banner ── --}}
    <div class="alert alert--error" id="alert-error" role="alert"></div>

    {{-- ── Form ── --}}
    <form id="widget-form" novalidate>

        <div class="form-row">
            <div class="form-group">
                <label for="name">Full name <span class="req">*</span></label>
                <input type="text" id="name" name="name" placeholder="Jane Doe" autocomplete="name">
                <span class="field-error" id="err-name"></span>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="you@example.com" autocomplete="email">
                <span class="field-error" id="err-email"></span>
            </div>
        </div>

        <div class="form-group">
            <label for="phone">Phone <span style="font-weight:400;color:#b0b7c3">(E.164 · e.g. +12025550100)</span></label>
            <input type="tel" id="phone" name="phone" placeholder="+12025550100" autocomplete="tel">
            <span class="field-error" id="err-phone"></span>
        </div>

        <div class="form-group">
            <label for="subject">Subject <span class="req">*</span></label>
            <input type="text" id="subject" name="subject" placeholder="How can we help?">
            <span class="field-error" id="err-subject"></span>
        </div>

        <div class="form-group">
            <label for="content">Message <span class="req">*</span></label>
            <textarea id="content" name="content" placeholder="Describe your issue or question…"></textarea>
            <span class="field-error" id="err-content"></span>
        </div>

        <div class="form-group">
            <label>Attachments <span style="font-weight:400;color:#b0b7c3">(max 10 MB each)</span></label>
            <label class="file-trigger" for="files" id="file-trigger">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66L9.41 17.41a2 2 0 01-2.83-2.83l8.49-8.48"/>
                </svg>
                <span id="file-trigger-text">Choose files…</span>
            </label>
            <input type="file" id="files" name="files[]" multiple aria-label="Attach files">
            <div class="file-chips" id="file-chips"></div>
        </div>

        <button type="submit" class="btn-submit" id="submit-btn">
            Send Message
        </button>

    </form>

    {{-- ── Success state (replaces form on success) ── --}}
    <div class="success-state" id="success-state" aria-live="polite">
        <div class="success-state__icon" aria-hidden="true">
            <svg width="26" height="26" fill="none" stroke="#22c55e" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
        </div>
        <div class="success-state__title">Message sent!</div>
        <p class="success-state__body" id="success-msg">
            Your request has been submitted. We will get back to you shortly.
        </p>
        <button class="btn-new" id="btn-new">Send another message</button>
    </div>

    <div class="w-footer">Powered by {{ config('app.name') }}</div>

<script>
(function () {
    'use strict';

    var form        = document.getElementById('widget-form');
    var submitBtn   = document.getElementById('submit-btn');
    var fileInput   = document.getElementById('files');
    var fileChips   = document.getElementById('file-chips');
    var alertErr    = document.getElementById('alert-error');
    var successEl   = document.getElementById('success-state');
    var successMsg  = document.getElementById('success-msg');
    var btnNew      = document.getElementById('btn-new');

    var selectedFiles = [];

    // ── File picker ──────────────────────────────────────────────────────────
    fileInput.addEventListener('change', function () {
        Array.from(this.files).forEach(function (f) {
            var dupe = selectedFiles.some(function (x) {
                return x.name === f.name && x.size === f.size;
            });
            if (! dupe) { selectedFiles.push(f); }
        });
        this.value = '';
        renderChips();
    });

    function renderChips() {
        fileChips.innerHTML = '';
        selectedFiles.forEach(function (f, i) {
            var chip = document.createElement('span');
            chip.className = 'chip';
            chip.innerHTML =
                '<span title="' + esc(f.name) + '">' + esc(f.name) + '</span>' +
                '<button type="button" class="chip__remove" aria-label="Remove ' + esc(f.name) + '">&times;</button>';
            chip.querySelector('.chip__remove').addEventListener('click', function () {
                selectedFiles.splice(i, 1);
                renderChips();
            });
            fileChips.appendChild(chip);
        });
    }

    // ── Helpers ──────────────────────────────────────────────────────────────
    function esc(s) {
        return String(s).replace(/[&<>"']/g, function (c) {
            return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];
        });
    }

    function fieldErr(id, msg) {
        var el  = document.getElementById(id);
        var err = document.getElementById('err-' + id);
        if (el)  { el.classList.add('is-invalid'); }
        if (err) { err.textContent = msg; err.style.display = 'block'; }
    }

    function clearErrors() {
        form.querySelectorAll('.is-invalid').forEach(function (el) {
            el.classList.remove('is-invalid');
        });
        form.querySelectorAll('.field-error').forEach(function (el) {
            el.textContent = ''; el.style.display = 'none';
        });
        alertErr.style.display = 'none';
    }

    function showError(msg) {
        alertErr.textContent   = msg;
        alertErr.style.display = 'flex';
    }

    function showSuccess(msg) {
        form.style.display         = 'none';
        successMsg.textContent     = msg;
        successEl.style.display    = 'flex';
    }

    function resetForm() {
        form.reset();
        selectedFiles = [];
        renderChips();
        clearErrors();
        form.style.display      = '';
        successEl.style.display = 'none';
    }

    // ── Submit ───────────────────────────────────────────────────────────────
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        clearErrors();

        var data = new FormData();
        data.append('name',    document.getElementById('name').value.trim());
        data.append('email',   document.getElementById('email').value.trim());
        data.append('phone',   document.getElementById('phone').value.trim());
        data.append('subject', document.getElementById('subject').value.trim());
        data.append('content', document.getElementById('content').value.trim());
        selectedFiles.forEach(function (f) { data.append('files[]', f); });

        submitBtn.disabled    = true;
        submitBtn.textContent = 'Sending…';

        fetch('/api/widget/submit', {
            method:  'POST',
            headers: { 'Accept': 'application/json' },
            body:    data,
        })
        .then(function (res) {
            return res.json().then(function (body) {
                return { status: res.status, body: body };
            });
        })
        .then(function (r) {
            if (r.status === 201) {
                showSuccess(r.body.message || 'Your message has been sent.');
            } else if (r.status === 422 && r.body.errors) {
                Object.entries(r.body.errors).forEach(function (entry) {
                    fieldErr(entry[0].replace(/\.\d+$/, ''), entry[1][0]);
                });
                showError('Please fix the errors above and try again.');
            } else {
                showError(r.body.message || 'Something went wrong. Please try again.');
            }
        })
        .catch(function () {
            showError('Network error. Please check your connection and try again.');
        })
        .finally(function () {
            submitBtn.disabled    = false;
            submitBtn.textContent = 'Send Message';
        });
    });

    btnNew.addEventListener('click', resetForm);
}());
</script>

</body>
</html>
