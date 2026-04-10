<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Embed Widget &mdash; {{ config('app.name') }}</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: system-ui, -apple-system, 'Segoe UI', sans-serif;
            background: #f4f5f7;
            min-height: 100vh;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 3rem 1.5rem;
            color: #1e1e2e;
        }

        .page { width: 100%; max-width: 780px; }

        h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: .4rem;
        }

        .lead {
            color: #6b7280;
            font-size: .9375rem;
            margin-bottom: 2.5rem;
        }

        /* ── Section ── */
        .section {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,.06);
            padding: 1.75rem 2rem;
            margin-bottom: 1.75rem;
        }

        .section__title {
            font-size: .8125rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: #9ca3af;
            margin-bottom: 1rem;
        }

        /* ── Code block ── */
        .code-wrap { position: relative; }

        pre {
            background: #1e1e2e;
            color: #cdd6f4;
            border-radius: 8px;
            padding: 1.1rem 1.25rem;
            font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
            font-size: .84rem;
            line-height: 1.65;
            overflow-x: auto;
            white-space: pre;
        }

        .copy-btn {
            position: absolute;
            top: .6rem;
            right: .6rem;
            background: rgba(255,255,255,.1);
            border: 1px solid rgba(255,255,255,.15);
            color: #cdd6f4;
            border-radius: 5px;
            padding: .3rem .7rem;
            font-size: .75rem;
            cursor: pointer;
            transition: background .15s;
        }

        .copy-btn:hover { background: rgba(255,255,255,.2); }
        .copy-btn.copied { color: #a6e3a1; border-color: #a6e3a1; }

        /* ── Preview ── */
        .preview-label {
            font-size: .8125rem;
            font-weight: 600;
            color: #6b7280;
            margin-bottom: .75rem;
        }

        iframe {
            width: 100%;
            height: 620px;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            display: block;
        }

        /* ── Tips ── */
        .tips { list-style: none; display: flex; flex-direction: column; gap: .6rem; }

        .tips li {
            display: flex;
            gap: .6rem;
            font-size: .875rem;
            color: #374151;
            line-height: 1.5;
        }

        .tips li::before {
            content: '→';
            color: #6366f1;
            flex-shrink: 0;
            font-weight: 700;
        }

        code {
            background: #f3f4f6;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            padding: .1em .35em;
            font-family: 'SFMono-Regular', Consolas, monospace;
            font-size: .82em;
            color: #6366f1;
        }
    </style>
</head>
<body>
<div class="page">

    <h1>Embed the Contact Widget</h1>
    <p class="lead">Copy the snippet below and paste it anywhere in your website's HTML.</p>

    {{-- ── Embed code ── --}}
    <div class="section">
        <div class="section__title">Embed Snippet</div>
        <div class="code-wrap">
            <pre id="snippet">&lt;iframe
  src="{{ $widgetUrl }}"
  width="480"
  height="620"
  frameborder="0"
  style="border:none;border-radius:12px;box-shadow:0 4px 24px rgba(0,0,0,.1);"
  title="Contact Us"
  loading="lazy"
&gt;&lt;/iframe&gt;</pre>
            <button class="copy-btn" id="copy-btn" type="button">Copy</button>
        </div>
    </div>

    {{-- ── Live preview ── --}}
    <div class="section">
        <div class="section__title">Live Preview</div>
        <p class="preview-label">This is exactly how the widget looks when embedded:</p>
        <iframe
            src="{{ $widgetUrl }}"
            frameborder="0"
            title="Contact Us — live preview"
            loading="lazy"
        ></iframe>
    </div>

    {{-- ── Tips ── --}}
    <div class="section">
        <div class="section__title">Tips</div>
        <ul class="tips">
            <li>Adjust <code>width</code> and <code>height</code> to fit your layout. A minimum width of <code>360px</code> is recommended.</li>
            <li>The widget is fully responsive — on narrow screens the two-column row collapses to a single column automatically.</li>
            <li>No cookies, no tracking scripts are loaded inside the widget.</li>
            <li>Files attached by the visitor are stored securely via the media library and are only accessible to your team.</li>
        </ul>
    </div>

</div>

<script>
(function () {
    var btn     = document.getElementById('copy-btn');
    var snippet = document.getElementById('snippet');

    btn.addEventListener('click', function () {
        var text = snippet.textContent;

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(markCopied);
        } else {
            // Fallback for older browsers / non-HTTPS
            var ta = document.createElement('textarea');
            ta.value = text;
            ta.style.position = 'fixed';
            ta.style.opacity  = '0';
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            document.body.removeChild(ta);
            markCopied();
        }
    });

    function markCopied() {
        btn.textContent = 'Copied!';
        btn.classList.add('copied');
        setTimeout(function () {
            btn.textContent = 'Copy';
            btn.classList.remove('copied');
        }, 2000);
    }
}());
</script>

</body>
</html>
