<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Skenēt svītrkodu') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <div id="reader" style="width:100%;max-width:480px;"></div>

                        <!-- Camera controls -->
                        <div class="mt-2 flex items-center gap-2">
                            <button id="flipBtn" type="button" class="px-3 py-1 border rounded">
                                Pārslēgt kameru
                            </button>
                        </div>

                        <p class="text-sm text-gray-500 mt-2">
                            Atļaujiet kamerai piekļuvi un novietojiet svītrkodu kadra centrā.
                        </p>
                    </div>

                    <div>
                        <div class="mb-2">
                            <label class="block text-sm font-medium">Pēdējais skenētais:</label>
                            <input id="lastCode" type="text" class="w-full border rounded p-2" readonly>
                        </div>

                        <div id="result" class="p-3 border rounded bg-gray-50 text-sm"></div>

                        <form id="manualForm" class="mt-4 flex gap-2" onsubmit="return false;">
                            <input id="manualCode" type="text" class="flex-1 border rounded p-2"
                                   placeholder="Ievadiet svītrkodu manuāli">
                            <button id="manualBtn" class="px-4 py-2 rounded bg-blue-600 text-white">
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
    document.addEventListener('DOMContentLoaded', function () {
        const resultDiv   = document.getElementById('result');
        const lastCode    = document.getElementById('lastCode');
        const manualBtn   = document.getElementById('manualBtn');
        const manualCode  = document.getElementById('manualCode');
        const flipBtn     = document.getElementById('flipBtn');

        let html5QrCode   = new Html5Qrcode("reader");
        let allCameras    = [];
        let currentCamIdx = 0;
        let lastAt        = 0;

        async function sendCode(code) {
            lastCode.value = code;
            resultDiv.textContent = 'Apstrādāju...';
            try {
                const res = await fetch(@json(route('inventory.scan.handle')), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': @json(csrf_token())
                    },
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
            } catch (e) {
                resultDiv.innerHTML = `<div class="text-red-700">❌ Tīkla kļūda.</div>`;
            }
        }

        manualBtn.addEventListener('click', () => {
            const code = manualCode.value.trim();
            if (code) sendCode(code);
        });

        async function startWithConstraints(constraints) {
            return html5QrCode.start(
                constraints,
                {
                    fps: 10,
                    // Landscape-ish box works better for 1D
                    qrbox: (w, h) => {
                        const size = Math.floor(Math.min(w, h) * 0.7);
                        return { width: size, height: Math.floor(size * 0.55) };
                    },
                    // Support common 1D + QR formats
                    formatsToSupport: [
                        Html5QrcodeSupportedFormats.QR_CODE,
                        Html5QrcodeSupportedFormats.EAN_13,
                        Html5QrcodeSupportedFormats.EAN_8,
                        Html5QrcodeSupportedFormats.CODE_128,
                        Html5QrcodeSupportedFormats.CODE_39,
                        Html5QrcodeSupportedFormats.UPC_A,
                        Html5QrcodeSupportedFormats.UPC_E
                    ],
                    experimentalFeatures: { useBarCodeDetectorIfSupported: true }
                },
                (decodedText) => {
                    const now = Date.now();
                    if (decodedText && decodedText !== lastCode.value && (now - lastAt) > 1000) {
                        lastAt = now;
                        sendCode(decodedText);
                    }
                },
                () => {}
            );
        }

        async function restartCamera(constraints) {
            try { await html5QrCode.stop(); } catch (_) {}
            try { await html5QrCode.clear(); } catch (_) {}
            return startWithConstraints(constraints).catch(err => {
                resultDiv.innerHTML = '<span class="text-red-700">Neizdevās startēt kameru: ' + err + '</span>';
            });
        }

        async function startPreferredCamera() {
            // 1) Prefer back camera by facingMode on mobile
            try {
                await restartCamera({ facingMode: { exact: "environment" } });
                return;
            } catch (_) {
                // ignore and continue to device list
            }

            // 2) Fallback: list devices and choose a likely back cam
            try {
                allCameras = await Html5Qrcode.getCameras();
                if (!allCameras || !allCameras.length) {
                    resultDiv.innerHTML = '<span class="text-red-700">Kamera nav atrasta.</span>';
                    return;
                }
                const preferredIdx = allCameras.findIndex(c => /back|rear|environment/i.test(c.label || ''));
                currentCamIdx = preferredIdx >= 0 ? preferredIdx : 0;

                await restartCamera({
                    deviceId: { exact: allCameras[currentCamIdx].id },
                    width: { ideal: 1280 },
                    height: { ideal: 720 },
                    facingMode: "environment",
                    advanced: [{ focusMode: "continuous" }]
                });
            } catch (err) {
                resultDiv.innerHTML = '<span class="text-red-700">Neizdevās startēt kameru: ' + err + '</span>';
            }
        }

        // Flip button
        if (flipBtn) {
            flipBtn.addEventListener('click', async () => {
                try { await html5QrCode.stop(); } catch (_) {}
                try { await html5QrCode.clear(); } catch (_) {}

                if (!allCameras || !allCameras.length) {
                    try { allCameras = await Html5Qrcode.getCameras(); } catch (_) {}
                }
                if (!allCameras || !allCameras.length) return;

                currentCamIdx = (currentCamIdx + 1) % allCameras.length;
                await restartCamera({
                    deviceId: { exact: allCameras[currentCamIdx].id },
                    width: { ideal: 1280 },
                    height: { ideal: 720 },
                    facingMode: "environment",
                    advanced: [{ focusMode: "continuous" }]
                });
            });
        }

        startPreferredCamera();
    });
    </script>
</x-app-layout>
