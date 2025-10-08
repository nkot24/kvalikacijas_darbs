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
                    <!-- Left: Camera -->
                    <div>
                        <div id="reader" style="width:100%;max-width:480px;"></div>
                        <p class="text-sm text-gray-500 mt-2">
                            Kamera automātiski startējas. Novietojiet svītrkodu kadra centrā.
                        </p>
                    </div>

                    <!-- Right: Results -->
                    <div>
                        <div class="mb-2">
                            <label class="block text-sm font-medium">Pēdējais skenētais:</label>
                            <input id="lastCode" type="text" class="w-full border rounded p-2" readonly>
                        </div>

                        <div id="result" class="p-3 border rounded bg-gray-50 text-sm"></div>

                        <form id="manualForm" class="mt-4 flex gap-2" onsubmit="return false;">
                            <input id="manualCode" type="text" class="flex-1 border rounded p-2"
                                   placeholder="Ievadiet svītrkodu manuāli">
                            <button id="scanBtn" class="px-4 py-2 rounded bg-blue-600 text-white">
                                Skenēt
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
    document.addEventListener('DOMContentLoaded', async function () {
        const resultDiv   = document.getElementById('result');
        const lastCode    = document.getElementById('lastCode');
        const manualCode  = document.getElementById('manualCode');
        const scanBtn     = document.getElementById('scanBtn');

        let html5QrCode   = new Html5Qrcode("reader");
        let lastAt        = 0;

        async function sendCode(code) {
            if (!code) return;
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

        // Manual scan button
        scanBtn.addEventListener('click', () => {
            const code = manualCode.value.trim();
            sendCode(code);
        });

        // Automatically start the back camera for live scanning
        async function startCamera() {
            try {
                await html5QrCode.start(
                    { facingMode: { exact: "environment" } }, // prefer back cam
                    {
                        fps: 10,
                        qrbox: (w, h) => {
                            const size = Math.floor(Math.min(w, h) * 0.7);
                            return { width: size, height: Math.floor(size * 0.55) };
                        },
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
                resultDiv.innerHTML = '<span class="text-gray-600">Kamera startēta. Skenē...</span>';
            } catch (err) {
                try {
                    // fallback to first camera
                    const cams = await Html5Qrcode.getCameras();
                    if (cams && cams.length) {
                        await html5QrCode.start(
                            { deviceId: { exact: cams[0].id } },
                            { fps: 10, qrbox: (w, h) => ({ width: Math.min(w, h)*0.7, height: Math.min(w, h)*0.5 }) },
                            (decodedText) => {
                                const now = Date.now();
                                if (decodedText && decodedText !== lastCode.value && (now - lastAt) > 1000) {
                                    lastAt = now;
                                    sendCode(decodedText);
                                }
                            }
                        );
                        resultDiv.innerHTML = '<span class="text-gray-600">Kamera startēta (rezerves režīmā).</span>';
                    } else {
                        resultDiv.innerHTML = '<span class="text-red-700">Kamera nav atrasta.</span>';
                    }
                } catch (e) {
                    resultDiv.innerHTML = '<span class="text-red-700">Neizdevās startēt kameru: ' + e + '</span>';
                }
            }
        }

        startCamera();
    });
    </script>
</x-app-layout>
