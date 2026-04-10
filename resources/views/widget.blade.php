<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Contact Us &mdash; {{ config('app.name') }}</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: system-ui, -apple-system, sans-serif;
            background: #f4f5f7;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            color: #1a1a2e;
        }

        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0,0,0,.08);
            width: 100%;
            max-width: 520px;
            padding: 2.5rem 2rem;
        }

        .card__header { margin-bottom: 1.75rem; }

        .card__title {
            font-size: 1.375rem;
            font-weight: 700;
            color: #1a1a2e;
        }

        .card__subtitle {
            margin-top: .35rem;
            font-size: .875rem;
            color: #6b7280;
        }

        .form-group { margin-bottom: 1.1rem; }

        label {
            display: block;
            font-size: .8125rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: .35rem;
        }

        label .required { color: #ef4444; margin-left: 2px; }

        input[type="text"],
        input[type="email"],
        input[type="tel"],
        textarea {
            width: 100%;
            padding: .6rem .85rem;
            border: 1.5px solid #d1d5db;
            border-radius: 7px;
            font-size: .9375rem;
            color: #1a1a2e;
            background: #fafafa;
            transition: border-color .15s, box-shadow .15s;
            outline: none;
        }

        input:focus, textarea:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99,102,241,.15);
            background: #fff;
        }

        textarea { resize: vertical; min-height: 110px; }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .85rem;
        }

        .file-label {
            display: flex;
            align-items: center;
            gap: .5rem;
            cursor: pointer;
            padding: .6rem .85rem;
            border: 1.5px dashed #d1d5db;
            border-radius: 7px;
            font-size: .875rem;
            color: #6b7280;
            background: #fafafa;
            transition: border-color .15s;
        }

        .file-label:hover { border-color: #6366f1; color: #6366f1; }

        .file-label svg { flex-shrink: 0; }

        input[type="file"] { display: none; }

        #file-list {
            margin-top: .5rem;
            font-size: .8125rem;
            color: #6b7280;
            display: flex;
            flex-wrap: wrap;
            gap: .35rem;
        }

        .file-chip {
            background: #f3f4f6;
            border-radius: 4px;
            padding: .15rem .5rem;
            display: flex;
            align-items: center;
            gap: .3rem;
        }

        .file-chip button {
            background: none;
            border: none;
            cursor: pointer;
            color: #9ca3af;
            font-size: .75rem;
            line-height: 1;
            padding: 0;
        }

        .file-chip button:hover { color: #ef4444; }

        .btn {
            width: 100%;
            padding: .75rem;
            background: #6366f1;
            color: #fff;
            border: none;
            border-radius: 7px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background .15s, opacity .15s;
            margin-top: .5rem;
        }

        .btn:hover { background: #4f46e5; }
        .btn:disabled { opacity: .6; cursor: not-allowed; }

        .alert {
            border-radius: 7px;
            padding: .85rem 1rem;
            font-size: .875rem;
            margin-bottom: 1.25rem;
            display: none;
        }

        .alert--success {
            background: #ecfdf5;
            border: 1px solid #6ee7b7;
            color: #065f46;
        }

        .alert--error {
            background: #fef2f2;
            border: 1px solid #fca5a5;
            color: #991b1b;
        }

        .field-error {
            font-size: .78rem;
            color: #ef4444;
            margin-top: .25rem;
            display: none;
        }

        input.is-invalid, textarea.is-invalid {
            border-color: #ef4444;
        }

        @media (max-width: 480px) {
            .form-row { grid-template-columns: 1fr; }
            .card { padding: 1.75rem 1.25rem; }
        }
    </style>
</head>
<body>

<div class="card">
    <div class="card__header">
        <h1 class="card__title">Contact Us</h1>
        <p class="card__subtitle">Fill in the form below and we will get back to you as soon as possible.</p>
    </div>

    <div class="alert alert--success" id="alert-success" role="alert"></div>
    <div class="alert alert--error"   id="alert-error"   role="alert"></div>

    <form id="widget-form" novalidate>
        @csrf

        <div class="form-row">
            <div class="form-group">
                <label for="name">Full name <span class="required">*</span></label>
                <input type="text" id="name" name="name" placeholder="John Doe" autocomplete="name">
                <span class="field-error" id="err-name"></span>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="you@example.com" autocomplete="email">
                <span class="field-error" id="err-email"></span>
            </div>
        </div>

        <div class="form-group">
            <label for="phone">Phone <small style="font-weight:400;color:#9ca3af">(E.164 format, e.g. +12025550100)</small></label>
            <input type="tel" id="phone" name="phone" placeholder="+12025550100" autocomplete="tel">
            <span class="field-error" id="err-phone"></span>
        </div>

        <div class="form-group">
            <label for="subject">Subject <span class="required">*</span></label>
            <input type="text" id="subject" name="subject" placeholder="How can we help?">
            <span class="field-error" id="err-subject"></span>
        </div>

        <div class="form-group">
            <label for="content">Message <span class="required">*</span></label>
            <textarea id="content" name="content" placeholder="Describe your issue or question in detail…"></textarea>
            <span class="field-error" id="err-content"></span>
        </div>

        <div class="form-group">
            <label>Attachments <small style="font-weight:400;color:#9ca3af">(max 10 MB each)</small></label>
            <label class="file-label" for="files">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66L9.41 17.41a2 2 0 01-2.83-2.83l8.49-8.48"/>
                </svg>
                Choose files…
            </label>
            <input type="file" id="files" name="files[]" multiple>
            <div id="file-list"></div>
        </div>

        <button type="submit" class="btn" id="submit-btn">Send Message</button>
    </form>
</div>

<script>
(function () {
    'use strict';

    const form        = document.getElementById('widget-form');
    const submitBtn   = document.getElementById('submit-btn');
    const fileInput   = document.getElementById('files');
    const fileList    = document.getElementById('file-list');
    const alertOk     = document.getElementById('alert-success');
    const alertErr    = document.getElementById('alert-error');

    // ── File picker ──────────────────────────────────────────────────────────
    let selectedFiles = [];

    fileInput.addEventListener('change', function () {
        Array.from(this.files).forEach(function (file) {
            if (! selectedFiles.find(f => f.name === file.name && f.size === file.size)) {
                selectedFiles.push(file);
            }
        });
        renderFileList();
        this.value = '';
    });

    function renderFileList() {
        fileList.innerHTML = '';
        selectedFiles.forEach(function (file, index) {
            const chip = document.createElement('span');
            chip.className = 'file-chip';
            chip.innerHTML =
                '<span>' + escapeHtml(file.name) + '</span>' +
                '<button type="button" aria-label="Remove ' + escapeHtml(file.name) + '">&times;</button>';
            chip.querySelector('button').addEventListener('click', function () {
                selectedFiles.splice(index, 1);
                renderFileList();
            });
            fileList.appendChild(chip);
        });
    }

    // ── Validation helpers ───────────────────────────────────────────────────
    function showFieldError(fieldId, message) {
        const input = document.getElementById(fieldId);
        const err   = document.getElementById('err-' + fieldId);
        if (input)  input.classList.add('is-invalid');
        if (err)  { err.textContent = message; err.style.display = 'block'; }
    }

    function clearErrors() {
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        form.querySelectorAll('.field-error').forEach(el => { el.textContent = ''; el.style.display = 'none'; });
        hideAlerts();
    }

    function hideAlerts() {
        alertOk.style.display = 'none';
        alertErr.style.display = 'none';
    }

    function showAlert(el, message) {
        el.textContent = message;
        el.style.display = 'block';
    }

    function escapeHtml(str) {
        return str.replace(/[&<>"']/g, function (c) {
            return ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' })[c];
        });
    }

    // ── Submit ───────────────────────────────────────────────────────────────
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        clearErrors();

        const data = new FormData();
        data.append('name',    document.getElementById('name').value.trim());
        data.append('email',   document.getElementById('email').value.trim());
        data.append('phone',   document.getElementById('phone').value.trim());
        data.append('subject', document.getElementById('subject').value.trim());
        data.append('content', document.getElementById('content').value.trim());

        selectedFiles.forEach(function (file) {
            data.append('files[]', file);
        });

        submitBtn.disabled    = true;
        submitBtn.textContent = 'Sending…';

        fetch('/api/widget/submit', {
            method: 'POST',
            headers: { 'Accept': 'application/json' },
            body: data,
        })
        .then(function (res) {
            return res.json().then(function (body) {
                return { status: res.status, body: body };
            });
        })
        .then(function (res) {
            if (res.status === 201) {
                showAlert(alertOk, res.body.message || 'Your message has been sent.');
                form.reset();
                selectedFiles = [];
                renderFileList();
            } else if (res.status === 422 && res.body.errors) {
                // Map Laravel validation errors back to fields
                Object.entries(res.body.errors).forEach(function ([field, messages]) {
                    const fieldId = field.replace(/\.\d+$/, ''); // strip array index
                    showFieldError(fieldId, messages[0]);
                });
                showAlert(alertErr, 'Please fix the errors above and try again.');
            } else {
                showAlert(alertErr, res.body.message || 'Something went wrong. Please try again.');
            }
        })
        .catch(function () {
            showAlert(alertErr, 'Network error. Please check your connection and try again.');
        })
        .finally(function () {
            submitBtn.disabled    = false;
            submitBtn.textContent = 'Send Message';
        });
    });
}());
</script>

</body>
</html>
