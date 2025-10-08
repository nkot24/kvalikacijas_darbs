<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Skenēt svītrkodu') }}
        </h2>
    </x-slot>

    <style>
        /* Frame overlay style like your screenshot */
        .scan-wrap { position: relative; width: 100%; max-width: 640px; margin: 0 auto; }
        .scan-mask {
            position: absolute; inset: 0;
            display: flex; align-items: center; justify-content: center;
            pointer-events: none;
        }
        .scan-box {
            position: relative; width: 92%; aspect-ratio: 16/9;
            box-sizing: content-box;
        }
        .scan-corner { position: absolute; width: 42px; height: 42px; }
        .scan-corner.tl { top: -2px; left: -2px; border-top: 4px solid #22c55e; border-left: 4px solid #22c55e; border-top-left-radius: 6px; }
        .scan-corner.tr { top: -2px; right: -2px; border-top: 4px solid #22c55e; border-right: 4px solid #22c55e; border-top-right-radius: 6px; }
        .scan-corner.bl { bottom: -2px; left: -2px; border-bottom: 4px solid #22c55e; border-left: 4px solid #22c55e; border-bottom-left-radius: 6px; }
        .scan-corner.br { bottom: -2px; right: -2px; border-bottom: 4px solid #22c55e; border-right: 4px solid #22c55e; border-bottom-right-radius: 6px; }
        /* Dim the area outside the scan box */
        .scan-dim {
            position: absolute; inset: 0; background: rgba(0,0,0,0.35);
            -webkit-mask: radial-gradient(closest-side, transparent 0 99%, black 100%) center/0 0 no-repeat;
            mask: radial-gradient(closest-side, transparent 0 99%, black 100%) center/0 0 no-repeat;
        }
        /* We’ll size the “hole” with JS to match the scan-box */
    </style>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="grid gap-6 md:grid-cols-2 items-start">
                    <!-- Left: Live camera with frame -->
                    <div>
                        <div class="scan-wrap">
                            <div id="reader"></div>

                            <div class="scan-mask">
                                <div id="scanBox" class="scan-box">
                                    <span class="scan-corner tl"></span>
                                    <span class="scan-corner tr"></span>
                                    <span class="scan-corner bl"></span>
                                    <span class="scan-corner br"></span>
                                </div>
                            </div>

                            <div id="scanDim" class="scan-dim"></div>
                        </div>

                        <div class="mt-4">
                            <button id="scanBtn"
                                    class="w-full py-3 text-lg font-semibold rounded bg-blue-600 hover:bg-blue-700 text-white">
                                SKENĒT
                            </button>
                            <p class="text-sm text-gray-500 mt-2">
                                Kamera startējas automātiski. Nospiediet “SKENĒT” un turiet svītrkodu rāmī.
                            </p>
                        </div>
                    </div>

                    <!-- Right: Result / manual -->
                    <div>
                        <div class="mb-3">
                            <label class="block text-sm font-medium">Pēdējais skenētais:</label>
                            <input id="lastCode" type="text" class="w-full border rounded p-2 font-mono" readonly>
                        </div>

                        <div id="result" class="p-3 border rounded bg-gray-50 text-sm">
                            Gatavs skenēšanai.
                        </div>

                        <form id="manualForm" class="mt-4 flex gap-2" onsubmit="return false;">
                            <input id="manualCode" type="text" class="flex-1 border rounded p-2"
                                   placeholder="Ievadiet svītrkodu manuāli (izvēles)">
                            <button id="manualBtn" class="px-4 rounded bg-gray-200 hover:bg-gray-300">
                                Pievienot +1
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- html5-qrcode from CDN -->
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
    document.addEventListener('DOMContentLoaded', async () => {
        const resultDiv   = document.getElementById('result');
        const lastCode    = document.getElementById('lastCode');
        const scanBtn     = document.getElementById('scanBtn');
        const manualBtn   = document.getElementById('manualBtn');
        const manualCode  = document.getElementById('manualCode');
        const scanBoxEl   = document.getElementById('scanBox');
        const scanDimEl   = document.getElementById('scanDim');

        // Size the “hole” in the dim layer to match the scan box
        function updateDimHole() {
            const r = scanBoxEl.getBoundingClientRect();
            const wrap = scanDimEl.parentElement.getBoundingClientRect();
            const cx = (r.left + r.right) / 2 - wrap.left;
            const cy = (r.top + r.bottom) / 2 - wrap.top;
            const rx = r.width / 2, ry = r.height / 2;
            // Use CSS mask with a big ellipse matching the scan box
            scanDimEl.style.webkitMask = `radial-gradient(${rx}px ${ry}px at ${cx}px ${cy}px, transparent 98%, black 100%)`;
            scanDimEl.style.mask = scanDimEl.style.webkitMask;
        }
        setTimeout(updateDimHole, 300);
        window.addEventListener('resize', () => setTimeout(updateDimHole, 200));

        let html5QrCode   = new Html5Qrcode("reader");
        let armed         = false;   // only scan when armed by the button
        let lastFireAt    = 0;

        async function sendCode(code) {
            if (!code) return;
            lastCode.value = code;
            resultDiv.textContent = 'Apstrādāju...';
            try {
                const res = await fetch(@json(route('inventory.scan.handle')), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': @json(csrf_token()) },
                    body: JSON.stringify({ barcode: code })
                });
                const data = await res.json();
                if (res.ok && data.ok) {
                    resultDiv.innerHTML = `<div class="text-green-700">
                        ✅ ${data.message}<br>
                        <strong>${data.product.nosaukums}</strong><br>
                        Svītrkods: ${data.product.svitr_kods}<br>
                        Daudzums noliktavā: ${data.product.daudzums_noliktava}
                    </div>`;
                    if (navigator.vibrate) navigator.vibrate(40);
                } else {
                    resultDiv.innerHTML = `<div class="text-red-700">❌ ${data.message || 'Kļūda pievienojot.'}</div>`;
                }
            } catch {
                resultDiv.innerHTML = `<div class="text-red-700">❌ Tīkla kļūda.</div>`;
            }
        }

        // Manual submit
        manualBtn.addEventListener('click', () => {
            const code = manualCode.value.trim();
            sendCode(code);
        });

        // Press to arm one scan
        scanBtn.addEventListener('click', () => {
            armed = true;
            resultDiv.textContent = 'Skenēju... Turiet svītrkodu rāmī.';
            scanBtn.disabled = true;
            scanBtn.classList.add('opacity-70');
        });

        // Ask camera for high resolution + autofocus; use large scan area (matches the frame)
        const startOpts = {
            fps: 20,
            qrbox: () => {
                const r = scanBoxEl.getBoundingClientRect();
                return { width: Math.floor(r.width), height: Math.floor(r.height) };
            },
            formatsToSupport: [
                Html5QrcodeSupportedFormats.EAN_13,
                Html5QrcodeSupportedFormats.EAN_8,
                Html5QrcodeSupportedFormats.CODE_128,
                Html5QrcodeSupportedFormats.CODE_39,
                Html5QrcodeSupportedFormats.UPC_A,
                Html5QrcodeSupportedFormats.UPC_E,
                Html5QrcodeSupportedFormats.QR_CODE
            ],
            experimentalFeatures: { useBarCodeDetectorIfSupported: true }
        };

        // Decoding callback (fires often; we only act when armed)
        const onDecode = (decodedText) => {
            const now = Date.now();
            if (!armed) return;
            if (!decodedText || now - lastFireAt < 700) return;
            armed = false;
            lastFireAt = now;
            scanBtn.disabled = false;
            scanBtn.classList.remove('opacity-70');
            sendCode(decodedText);
        };

        // Enhance video track (autofocus / zoom / torch) when possible
        async function enhanceVideoTrack() {
            try {
                const videoEl = document.querySelector('#reader video');
                const track = videoEl?.srcObject?.getVideoTracks?.()[0];
                if (!track) return;
                const caps = track.getCapabilities?.() || {};
                const advanced = [];
                if (caps.focusMode?.includes('continuous')) advanced.push({ focusMode: 'continuous' });
                if (caps.zoom) {
                    const z = Math.min(caps.zoom.max || 1, Math.max(caps.zoom.min || 1, 1.5));
                    advanced.push({ zoom: z });
                }
                if (caps.torch) advanced.push({ torch: true });
                if (advanced.length) await track.applyConstraints({ advanced });
            } catch(_) {}
        }

        // Start camera (prefer back + 1080p), fallback gracefully
        async function startCamera() {
            updateDimHole();
            const hiResBack = {
                facingMode: { exact: "environment" },
                width:  { ideal: 1920 },
                height: { ideal: 1080 },
                advanced: [{ focusMode: "continuous" }]
            };
            const hintBack = {
                facingMode: "environment",
                width:  { ideal: 1920 },
                height: { ideal: 1080 },
                advanced: [{ focusMode: "continuous" }]
            };

            try {
                await html5QrCode.start(hiResBack, startOpts, onDecode, () => {});
                await enhanceVideoTrack();
                resultDiv.innerHTML = '<span class="text-gray-600">Kamera startēta (1080p). Nospiediet “SKENĒT”.</span>';
                return;
            } catch(_) {}

            try {
                await html5QrCode.start(hintBack, startOpts, onDecode, () => {});
                await enhanceVideoTrack();
                resultDiv.innerHTML = '<span class="text-gray-600">Kamera startēta. Nospiediet “SKENĒT”.</span>';
                return;
            } catch(_) {}

            try {
                const cams = await Html5Qrcode.getCameras();
                if (cams?.length) {
                    await html5QrCode.start({ deviceId: { exact: cams[0].id }, width: { ideal: 1920 }, height: { ideal: 1080 } },
                                             startOpts, onDecode, () => {});
                    await enhanceVideoTrack();
                    resultDiv.innerHTML = '<span class="text-gray-600">Kamera startēta (rezerves režīmā). Nospiediet “SKENĒT”.</span>';
                } else {
                    resultDiv.innerHTML = '<span class="text-red-700">Kamera nav atrasta.</span>';
                }
            } catch(e) {
                resultDiv.innerHTML = '<span class="text-red-700">Neizdevās startēt kameru.</span>';
            }
        }

        startCamera();
    });
    </script>
</x-app-layout>
