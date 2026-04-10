<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Contact Us</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html, body {
            height: 100%;
            font-family: system-ui, -apple-system, 'Segoe UI', sans-serif;
            font-size: 15px;
            color: #1e1e2e;
            background: #ffffff;
        }

        body { padding: 1.5rem 1.75rem 2rem; overflow-x: hidden; }

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
            width: 38px; height: 38px;
            border-radius: 10px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }

        .w-header__title { font-size: 1.05rem; font-weight: 700; line-height: 1.2; }
        .w-header__sub   { font-size: .78rem; color: #6b7280; margin-top: .15rem; }

        /* ── Error banner ── */
        .alert-error {
            display: none;
            align-items: flex-start;
            gap: .5rem;
            background: #fef2f2;
            border: 1px solid #fca5a5;
            color: #991b1b;
            border-radius: 8px;
            padding: .75rem 1rem;
            font-size: .84rem;
            line-height: 1.45;
            margin-bottom: 1.1rem;
        }

        .alert-error__icon { flex-shrink: 0; margin-top: 1px; }

        /* ── Form layout ── */
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: .75rem; }
        .form-group { margin-bottom: .9rem; }

        label {
            display: block;
            font-size: .78rem; font-weight: 600;
            color: #374151;
            margin-bottom: .3rem;
            letter-spacing: .01em;
        }

        label .req { color: #ef4444; margin-left: 1px; }
        label .hint { font-weight: 400; color: #b0b7c3; }

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

        textarea { resize: vertical; min-height: 90px; line-height: 1.5; }

        .field-error {
            font-size: .75rem; color: #ef4444;
            margin-top: .25rem; display: none;
        }

        /* ── File picker ── */
        .file-trigger {
            display: flex; align-items: center; gap: .5rem;
            padding: .55rem .8rem;
            border: 1.5px dashed #d1d5db;
            border-radius: 7px;
            font-size: .84rem; color: #6b7280;
            background: #fafafa;
            cursor: pointer;
            transition: border-color .15s, color .15s;
            user-select: none;
        }

        .file-trigger:hover { border-color: #6366f1; color: #6366f1; }
        input[type="file"]  { display: none; }

        .file-chips { display: flex; flex-wrap: wrap; gap: .3rem; margin-top: .45rem; }

        .chip {
            display: inline-flex; align-items: center; gap: .3rem;
            background: #f3f4f6; border: 1px solid #e5e7eb;
            border-radius: 5px; padding: .15rem .5rem;
            font-size: .76rem; color: #374151; max-width: 180px;
        }

        .chip span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

        .chip__remove {
            background: none; border: none; cursor: pointer;
            color: #9ca3af; font-size: .85rem; line-height: 1; padding: 0; flex-shrink: 0;
        }

        .chip__remove:hover { color: #ef4444; }

        /* ── Submit button ── */
        .btn-submit {
            width: 100%; padding: .7rem 1rem;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #fff; border: none; border-radius: 8px;
            font-size: .95rem; font-weight: 600;
            cursor: pointer;
            transition: opacity .15s, transform .1s;
            margin-top: .25rem;
            display: flex; align-items: center; justify-content: center; gap: .5rem;
        }

        .btn-submit:hover   { opacity: .92; }
        .btn-submit:active  { transform: scale(.99); }
        .btn-submit:disabled { opacity: .55; cursor: not-allowed; }

        /* spinner inside button */
        .spinner {
            display: none;
            width: 16px; height: 16px;
            border: 2px solid rgba(255,255,255,.4);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .7s linear infinite;
            flex-shrink: 0;
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        /* ── Success state ── */
        .success-state {
            display: none;
            flex-direction: column; align-items: center;
            text-align: center; padding: 2rem 1rem; gap: .9rem;
        }

        .success-state__icon {
            width: 56px; height: 56px; border-radius: 50%;
            background: #f0fdf4; border: 2px solid #86efac;
            display: flex; align-items: center; justify-content: center;
        }

        .success-state__title { font-size: 1.05rem; font-weight: 700; color: #166534; }

        .success-state__body { font-size: .875rem; color: #6b7280; line-height: 1.55; }

        .success-state__ref {
            font-size: .8rem; color: #9ca3af;
            background: #f9fafb; border: 1px solid #e5e7eb;
            border-radius: 6px; padding: .3rem .75rem;
            font-family: 'SFMono-Regular', Consolas, monospace;
        }

        .btn-new {
            padding: .55rem 1.25rem;
            background: none; border: 1.5px solid #6366f1;
            border-radius: 7px; color: #6366f1;
            font-size: .875rem; font-weight: 600;
            cursor: pointer; transition: background .15s, color .15s;
        }

        .btn-new:hover { background: #6366f1; color: #fff; }

        /* ── Footer ── */
        .w-footer { margin-top: 1.25rem; text-align: center; font-size: .72rem; color: #c4c4cf; }

        @media (max-width: 400px) {
            body { padding: 1.1rem 1rem 1.5rem; }
            .form-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <div class="w-header">
        <div class="w-header__icon" aria-hidden="true">
            <svg width="20" height="20" fill="none" stroke="#fff" stroke-width="2"
                 stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
            </svg>
        </div>
        <div>
            <div class="w-header__title">Contact Us</div>
            <div class="w-header__sub">We usually reply within one business day.</div>
        </div>
    </div>

    {{-- Error banner --}}
    <div class="alert-error" id="alert-error" role="alert" aria-live="assertive">
        <svg class="alert-error__icon" width="16" height="16" fill="none" stroke="currentColor"
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <span id="alert-error-text"></span>
    </div>

    {{-- Form --}}
    <form id="widget-form" novalidate>

        <div class="form-row">
            <div class="form-group">
                <label for="f-name">Full name <span class="req">*</span></label>
                <input type="text" id="f-name" name="name" placeholder="Jane Doe" autocomplete="name">
                <span class="field-error" id="err-name"></span>
            </div>
            <div class="form-group">
                <label for="f-email">Email</label>
                <input type="email" id="f-email" name="email" placeholder="you@example.com" autocomplete="email">
                <span class="field-error" id="err-email"></span>
            </div>
        </div>

        <div class="form-group">
            <label for="f-phone">
                Phone <span class="hint">(E.164 · e.g. +12025550100)</span>
            </label>
            <input type="tel" id="f-phone" name="phone" placeholder="+12025550100" autocomplete="tel">
            <span class="field-error" id="err-phone"></span>
        </div>

        <div class="form-group">
            <label for="f-subject">Subject <span class="req">*</span></label>
            <input type="text" id="f-subject" name="subject" placeholder="How can we help?">
            <span class="field-error" id="err-subject"></span>
        </div>

        <div class="form-group">
            <label for="f-content">Message <span class="req">*</span></label>
            <textarea id="f-content" name="content" placeholder="Describe your issue or question…"></textarea>
            <span class="field-error" id="err-content"></span>
        </div>

        <div class="form-group">
            <label>
                Attachments <span class="hint">(max 5 files · 10 MB each)</span>
            </label>
            <label class="file-trigger" for="f-files">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66L9.41 17.41a2 2 0 01-2.83-2.83l8.49-8.48"/>
                </svg>
                Choose files…
            </label>
            <input type="file" id="f-files" name="files[]" multiple aria-label="Attach files">
            <div class="file-chips" id="file-chips"></div>
            <span class="field-error" id="err-files"></span>
        </div>

        <button type="submit" class="btn-submit" id="submit-btn">
            <span class="spinner" id="spinner" aria-hidden="true"></span>
            <span id="submit-label">Send Message</span>
        </button>

    </form>

    {{-- Success state --}}
    <div class="success-state" id="success-state" aria-live="polite">
        <div class="success-state__icon" aria-hidden="true">
            <svg width="26" height="26" fill="none" stroke="#22c55e" stroke-width="2.5"
                 stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
        </div>
        <div class="success-state__title">Message sent!</div>
        <p class="success-state__body">
            Your request has been submitted.<br>We will get back to you shortly.
        </p>
        <span class="success-state__ref" id="ticket-ref"></span>
        <button class="btn-new" id="btn-new" type="button">Send another message</button>
    </div>

    <div class="w-footer">Powered by {{ config('app.name') }}</div>

<script>
(function () {
    'use strict';

    // ── DOM refs ─────────────────────────────────────────────────────────────
    var form        = document.getElementById('widget-form');
    var submitBtn   = document.getElementById('submit-btn');
    var submitLabel = document.getElementById('submit-label');
    var spinner     = document.getElementById('spinner');
    var fileInput   = document.getElementById('f-files');
    var fileChips   = document.getElementById('file-chips');
    var alertBox    = document.getElementById('alert-error');
    var alertText   = document.getElementById('alert-error-text');
    var successEl   = document.getElementById('success-state');
    var ticketRef   = document.getElementById('ticket-ref');
    var btnNew      = document.getElementById('btn-new');

    var selectedFiles = [];
    var REQUEST_TIMEOUT_MS = 15000;

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
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function val(id) {
        return document.getElementById(id).value.trim();
    }

    function setFieldError(name, msg) {
        var input = form.querySelector('[name="' + name + '"]');
        var err   = document.getElementById('err-' + name);
        if (input) { input.classList.add('is-invalid'); }
        if (err)   { err.textContent = msg; err.style.display = 'block'; }
    }

    function clearErrors() {
        form.querySelectorAll('.is-invalid').forEach(function (el) {
            el.classList.remove('is-invalid');
        });
        form.querySelectorAll('.field-error').forEach(function (el) {
            el.textContent = ''; el.style.display = 'none';
        });
        alertBox.style.display = 'none';
        alertText.textContent  = '';
    }

    function showBannerError(msg) {
        alertText.textContent  = msg;
        alertBox.style.display = 'flex';
        alertBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function setLoading(on) {
        submitBtn.disabled      = on;
        spinner.style.display   = on ? 'block' : 'none';
        submitLabel.textContent = on ? 'Sending…' : 'Send Message';
    }

    function showSuccess(reference) {
        form.style.display      = 'none';
        ticketRef.textContent   = reference;
        successEl.style.display = 'flex';
        successEl.focus();
    }

    function resetAll() {
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

        // Client-side required check before hitting the network
        var name    = val('f-name');
        var subject = val('f-subject');
        var content = val('f-content');
        var hasErr  = false;

        if (! name)    { setFieldError('name',    'Full name is required.'); hasErr = true; }
        if (! subject) { setFieldError('subject', 'Subject is required.');   hasErr = true; }
        if (! content) { setFieldError('content', 'Message is required.');   hasErr = true; }

        if (hasErr) {
            showBannerError('Please fill in all required fields.');
            return;
        }

        var data = new FormData();
        data.append('name',    name);
        data.append('email',   val('f-email'));
        data.append('phone',   val('f-phone'));
        data.append('subject', subject);
        data.append('content', content);
        selectedFiles.forEach(function (f) { data.append('files[]', f); });

        setLoading(true);

        // Abort controller for request timeout
        var controller = new AbortController();
        var timer = setTimeout(function () { controller.abort(); }, REQUEST_TIMEOUT_MS);

        fetch('/api/widget/submit', {
            method:  'POST',
            headers: { 'Accept': 'application/json' },
            body:    data,
            signal:  controller.signal,
        })
        .then(function (res) {
            clearTimeout(timer);
            return res.json().then(function (body) {
                return { status: res.status, body: body };
            });
        })
        .then(function (r) {
            if (r.status === 201) {
                // ── Success ──────────────────────────────────────────────────
                showSuccess(r.body.reference || ('#' + r.body.ticket_id));

            } else if (r.status === 422 && r.body.errors) {
                // ── Validation errors from the API ────────────────────────────
                Object.keys(r.body.errors).forEach(function (field) {
                    // Strip array notation: "files.0" → "files"
                    var name = field.replace(/\.\d+$/, '');
                    setFieldError(name, r.body.errors[field][0]);
                });
                showBannerError('Please fix the errors highlighted below.');

            } else if (r.status === 429) {
                // ── Rate limited ─────────────────────────────────────────────
                showBannerError('Too many requests. Please wait a moment and try again.');

            } else {
                // ── Any other server error ────────────────────────────────────
                showBannerError(r.body.message || 'Something went wrong. Please try again.');
            }
        })
        .catch(function (err) {
            clearTimeout(timer);
            if (err.name === 'AbortError') {
                showBannerError('The request timed out. Please check your connection and try again.');
            } else {
                showBannerError('Network error. Please check your connection and try again.');
            }
        })
        .finally(function () {
            setLoading(false);
        });
    });

    btnNew.addEventListener('click', resetAll);
}());
</script>

</body>
</html>
