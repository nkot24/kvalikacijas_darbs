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
                    <!-- Left: Live camera + STATUS MOVED HERE -->
                    <div>
                        <div id="reader" style="width:100%;max-width:520px;"></div>

                        <!-- Status/notification now just under the camera -->
                        <div id="result" class="mt-3 p-3 border rounded bg-gray-50 text-sm">
                            Gatavs skenēšanai.
                        </div>

                        <div class="mt-4">
                            <button id="scanBtn"
                                    class="w-full py-3 text-lg font-semibold rounded bg-blue-600 hover:bg-blue-700 text-white">
                                SKENĒT
                            </button>
                            <p class="text-sm text-gray-500 mt-2">
                                Kamera startējas automātiski. Nospiediet “SKENĒT” un turiet svītrkodu kadra centrā.
                            </p>
                        </div>
                    </div>

                    <!-- Right: Result / manual -->
                    <div>
                        <div class="mb-3">
                            <label class="block text-sm font-medium">Pēdējais skenētais:</label>
                            <input id="lastCode" type="text" class="w-full border rounded p-2 font-mono" readonly>
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
        const resultDiv   = document.getElementById('result');  // now under the camera
        const lastCode    = document.getElementById('lastCode');
        const scanBtn     = document.getElementById('scanBtn');
        const manualBtn   = document.getElementById('manualBtn');
        const manualCode  = document.getElementById('manualCode');

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
            } catch {
                resultDiv.innerHTML = `<div class="text-red-700">❌ Tīkla kļūda.</div>`;
            }
        }

        // Manual submit still available
        manualBtn.addEventListener('click', () => {
            const code = manualCode.value.trim();
            sendCode(code);
        });

        // Arm one scan when the button is pressed
        scanBtn.addEventListener('click', () => {
            armed = true;
            resultDiv.textContent = 'Skenēju... Turiet svītrkodu plašajā kadra zonā.';
            scanBtn.disabled = true;
            scanBtn.classList.add('opacity-70');
        });

        // Auto-start camera with a bigger scan area
        async function startCamera() {
            const startOpts = {
                fps: 20, // smoother detection if device can handle it
                // MUCH bigger scan area: ~95% width & 65% height of view
                qrbox: (viewW, viewH) => {
                    const w = Math.floor(viewW * 0.95);
                    const h = Math.floor(viewH * 0.65);
                    return {
                        width: Math.min(w, viewW - 10),
                        height: Math.min(h, viewH - 10)
                    };
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

            const onDecode = (decodedText) => {
                const now = Date.now();
                if (!armed) return;
                if (!decodedText || now - lastFireAt < 700) return; // throttle duplicates
                armed = false;
                lastFireAt = now;
                scanBtn.disabled = false;
                scanBtn.classList.remove('opacity-70');
                sendCode(decodedText);
            };

            // Prefer back camera; fall back gracefully
            try {
                await html5QrCode.start({ facingMode: { exact: "environment" } }, startOpts, onDecode, () => {});
                resultDiv.innerHTML = '<span class="text-gray-600">Kamera startēta. Nospiediet “SKENĒT”.</span>';
                return;
            } catch(_) {}

            try {
                await html5QrCode.start({ facingMode: "environment" }, startOpts, onDecode, () => {});
                resultDiv.innerHTML = '<span class="text-gray-600">Kamera startēta. Nospiediet “SKENĒT”.</span>';
                return;
            } catch(_) {}

            try {
                const cams = await Html5Qrcode.getCameras();
                if (cams?.length) {
                    await html5QrCode.start({ deviceId: { exact: cams[0].id } }, startOpts, onDecode, () => {});
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
