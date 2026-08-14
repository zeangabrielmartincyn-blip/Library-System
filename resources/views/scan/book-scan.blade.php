<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Phone Barcode Scanner</title>
    <style>
        :root {
            color-scheme: dark;
            --bg: #08111f;
            --panel: rgba(12, 18, 32, 0.92);
            --line: rgba(255,255,255,.12);
            --text: #f5f7fb;
            --muted: #b5c0d6;
            --accent: #7dd3fc;
            --accent-2: #34d399;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
            background: radial-gradient(circle at top, rgba(52,211,153,.18), transparent 38%), linear-gradient(180deg, #08111f, #0c172a 60%, #050914);
            color: var(--text);
            display: grid;
            place-items: center;
            padding: 1rem;
        }
        .shell {
            width: min(100%, 560px);
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 24px;
            padding: 1rem;
            box-shadow: 0 24px 80px rgba(0,0,0,.35);
        }
        h1 { margin: 0 0 .35rem; font-size: 1.5rem; }
        p { margin: 0; color: var(--muted); line-height: 1.5; }
        .reader {
            margin-top: 1rem;
            min-height: 360px;
            border-radius: 18px;
            overflow: hidden;
            border: 1px dashed var(--line);
            background: rgba(255,255,255,.03);
        }
        .status {
            margin-top: .9rem;
            font-size: .95rem;
            color: var(--muted);
        }
        .actions {
            display: flex;
            gap: .75rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }
        .btn {
            appearance: none;
            border: 0;
            border-radius: 999px;
            padding: .85rem 1rem;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }
        .btn.primary { background: linear-gradient(135deg, var(--accent-2), #22c55e); color: #04110a; }
        .btn.secondary { background: rgba(255,255,255,.08); color: var(--text); border: 1px solid var(--line); }
        .help {
            display: none;
            margin-top: 1rem;
            padding: 1rem;
            border-radius: 16px;
            border: 1px solid rgba(125,211,252,.25);
            background: rgba(125,211,252,.08);
            color: var(--text);
        }
        .help strong { display:block; margin-bottom:.35rem; }
    </style>
</head>
<body>
    <main class="shell">
        <h1>Scan on Your Phone</h1>
        <p>Point your phone camera at the barcode. When we detect it, the ISBN is sent back to the librarian page.</p>
        <p id="secureHint" style="margin-top:.5rem; color:#fde68a; display:none;">Camera access needs HTTPS or localhost. Open this page through a secure link.</p>

        <div id="phoneReader" class="reader"></div>
        <div id="phoneStatus" class="status">Allow camera access to start scanning.</div>

        <div id="phoneHelp" class="help">
            <strong>Camera blocked?</strong>
            Open this page in Safari or Chrome, allow camera permission, and make sure the phone is in landscape or well lit.
        </div>

        <div class="actions">
            <button class="btn primary" type="button" id="restartScanner">Restart Scanner</button>
            <button class="btn secondary" type="button" id="stopScanner">Stop Scanner</button>
        </div>
    </main>

    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        (function () {
            const token = @json($token);
            const statusEl = document.getElementById('phoneStatus');
            const helpEl = document.getElementById('phoneHelp');
            const restartBtn = document.getElementById('restartScanner');
            const stopBtn = document.getElementById('stopScanner');
            const secureHint = document.getElementById('secureHint');
            let scanner = null;
            let stopping = false;
            const isSecureContext = window.isSecureContext || location.hostname === 'localhost' || location.hostname === '127.0.0.1';

            const setStatus = (message, isError = false) => {
                statusEl.textContent = message;
                statusEl.style.color = isError ? '#fca5a5' : '';
                helpEl.style.display = isError ? 'block' : 'none';
            };

            const normalizeIsbn = (value) => (value || '').replace(/[^0-9Xx]/g, '').toUpperCase();

            const submitIsbn = async (isbn) => {
                const response = await fetch(`{{ url('/scan') }}/${encodeURIComponent(token)}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    },
                    body: JSON.stringify({ isbn }),
                });

                if (!response.ok) {
                    throw new Error('Failed to submit scanned ISBN.');
                }
            };

            const stopScanner = async () => {
                stopping = true;
                try {
                    if (scanner) {
                        await scanner.stop();
                        await scanner.clear();
                    }
                } catch (error) {
                    console.warn(error);
                } finally {
                    scanner = null;
                    stopping = false;
                }
            };

            const startScanner = async () => {
                if (!isSecureContext) {
                    secureHint.style.display = 'block';
                    setStatus('Camera access is only supported in a secure context like HTTPS or localhost.', true);
                    return;
                }

                if (!window.Html5QrcodeScanner) {
                    setStatus('Barcode scanner library failed to load.', true);
                    return;
                }

                if (scanner) {
                    await stopScanner();
                }

                setStatus('Starting camera...');

                scanner = new Html5QrcodeScanner('phoneReader', {
                    fps: 10,
                    qrbox: (viewfinderWidth, viewfinderHeight) => {
                        const width = Math.floor(Math.min(viewfinderWidth, viewfinderHeight) * 0.9);
                        const height = Math.floor(width * 0.5);
                        return { width, height };
                    },
                    disableFlip: true,
                    rememberLastUsedCamera: true,
                    videoConstraints: {
                        facingMode: { ideal: 'environment' },
                        width: { ideal: 1280 },
                        height: { ideal: 720 },
                    },
                    formatsToSupport: [
                        window.Html5QrcodeSupportedFormats.EAN_13,
                        window.Html5QrcodeSupportedFormats.EAN_8,
                        window.Html5QrcodeSupportedFormats.UPC_A,
                        window.Html5QrcodeSupportedFormats.UPC_E,
                        window.Html5QrcodeSupportedFormats.CODE_128,
                        window.Html5QrcodeSupportedFormats.CODE_39,
                        window.Html5QrcodeSupportedFormats.ITF,
                        window.Html5QrcodeSupportedFormats.CODABAR,
                    ],
                    experimentalFeatures: {
                        useBarCodeDetectorIfSupported: true,
                    },
                }, false);

                await scanner.render(
                    async (decodedText) => {
                        const isbn = normalizeIsbn(decodedText);
                        if (!isbn || stopping) {
                            return;
                        }

                        setStatus(`Detected ${isbn}. Sending to librarian page...`);
                        await submitIsbn(isbn);
                        await stopScanner();
                        setStatus('ISBN sent successfully. You can return to the librarian screen.', false);
                    },
                    (error) => {
                        const text = String(error ?? '');
                        if (/permission|denied|notallowed|camera|video/i.test(text)) {
                            setStatus('Camera permission was denied. Enable it in browser settings, then restart.', true);
                        } else {
                            setStatus('Hold the barcode steady inside the frame.');
                        }
                    }
                );
            };

            restartBtn.addEventListener('click', startScanner);
            stopBtn.addEventListener('click', stopScanner);

            startScanner().catch((error) => {
                console.warn(error);
                setStatus('Unable to start the scanner. Please retry.', true);
            });
        })();
    </script>
</body>
</html>