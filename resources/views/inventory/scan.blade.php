<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Skenēt saražoto produkciju') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="grid gap-6 md:grid-cols-2">
                    <!-- Left: camera + status -->
                    <div>
                        <div id="reader" style="width:100%;max-width:520px;"></div>
                        <div id="result" class="mt-3 p-3 border rounded bg-gray-50 text-sm">
                            Gatavs skenēšanai.
                        </div>
                        <div class="mt-4">
                            <button id="scanBtn"
                                class="w-full py-3 text-lg font-semibold rounded bg-blue-600 hover:bg-blue-700 text-white">
                                SKENĒT
                            </button>
                            <p class="text-sm text-gray-500 mt-2">
                                Kamera startējas automātiski. Nospiediet “SKENĒT” un turiet svītrkodu centrā.
                            </p>
                        </div>
                    </div>

                    <!-- Right: manual -->
                    <div>
                        <div class="mb-3">
                            <label class="block text-sm font-medium">Pēdējais skenētais:</label>
                            <input id="lastCode" type="text" class="w-full border rounded p-2 font-mono" readonly>
                        </div>

                        <form id="manualForm" class="mt-4 flex gap-2" onsubmit="return false;">
                            <input id="manualCode" type="text" class="flex-1 border rounded p-2"
                                   placeholder="Ievadiet svītrkodu manuāli">
                            <button id="manualBtn" class="px-4 rounded bg-gray-200 hover:bg-gray-300">Meklēt</button>
                        </form>

                        <div class="mt-4">
                            <a href="{{ route('inventory.transfers.index') }}" class="px-3 py-2 rounded border">
                                Skatīt pārvietošanas ierakstus
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
    document.addEventListener('DOMContentLoaded', async () => {
        const resultDiv = document.getElementById('result');
        const lastCode  = document.getElementById('lastCode');
        const scanBtn   = document.getElementById('scanBtn');
        const manualBtn = document.getElementById('manualBtn');
        const manualCode= document.getElementById('manualCode');

        let html5QrCode = new Html5Qrcode("reader");
        let armed = false, lastFireAt = 0;

        async function verifyProduct(barcode) {
            const res = await fetch(@json(route('inventory.scan.handle')), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': @json(csrf_token())
                },
                body: JSON.stringify({ barcode })
            });
            return await res.json().catch(() => ({}));
        }

        // After scan: ONLY qty field (no "No/Uz")
        async function showTransferForm(product) {
            resultDiv.innerHTML = `
                <div class="space-y-3">
                    <div class="text-green-700">
                        ✅ Produkts atrasts: <strong>${product.nosaukums}</strong><br>
                        Svītrkods: ${product.svitr_kods}
                    </div>

                    <label class="block">
                        <span class="text-sm font-medium">Daudzums</span>
                        <input id="mv-qty" type="number" min="1" step="1" value="1"
                               class="w-full border rounded p-2">
                    </label>

                    <div class="flex gap-2">
                        <button id="mv-submit" class="px-4 py-2 rounded bg-blue-600 text-white">
                            Pievienot ierakstu
                        </button>
                        <a href="{{ route('inventory.transfers.index') }}" class="px-4 py-2 rounded border">
                            Skatīt ierakstus
                        </a>
                    </div>

                    <div id="mv-status" class="text-sm text-gray-600"></div>
                </div>
            `;

            document.getElementById('mv-submit').addEventListener('click', async () => {
                const qty = parseInt(document.getElementById('mv-qty').value, 10);
                const status = document.getElementById('mv-status');

                if (!qty || qty < 1) {
                    status.textContent = 'Ievadiet derīgu daudzumu.';
                    status.className = 'text-sm text-red-600';
                    return;
                }

                status.textContent = 'Saglabāju...';
                try {
                    const res = await fetch(@json(route('inventory.scan.storeTransfer')), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': @json(csrf_token()),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            product_id: product.id,
                            qty: qty
                            // No from_location / to_location
                        })
                    });
                    const data = await res.json();
                    if (res.ok && data.ok) {
                        status.textContent = 'Pievienots ✓';
                        status.className = 'text-sm text-green-600';
                        if (navigator.vibrate) navigator.vibrate(40);
                    } else {
                        status.textContent = data.message || 'Kļūda saglabājot';
                        status.className = 'text-sm text-red-600';
                    }
                } catch {
                    status.textContent = 'Tīkla kļūda';
                    status.className = 'text-sm text-red-600';
                }
            });
        }

        async function processBarcode(code) {
            if (!code) return;
            lastCode.value = code;
            resultDiv.textContent = 'Meklēju produktu...';
            const data = await verifyProduct(code);
            if (data && data.ok && data.product) {
                await showTransferForm(data.product);
            } else {
                resultDiv.innerHTML = `<div class="text-red-700">❌ ${data.message || 'Produkts nav atrasts.'}</div>`;
            }
        }

        manualBtn.addEventListener('click', () => processBarcode(manualCode.value.trim()));

        scanBtn.addEventListener('click', () => {
            armed = true;
            resultDiv.textContent = 'Skenēju...';
            scanBtn.disabled = true;
            scanBtn.classList.add('opacity-70');
        });

        const startOpts = {
            fps: 20,
            qrbox: (viewW, viewH) => {
                const w = Math.floor(viewW * 0.95);
                const h = Math.floor(viewH * 0.65);
                return { width: Math.min(w, viewW - 10), height: Math.min(h, viewH - 10) };
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
            if (!armed || !decodedText || now - lastFireAt < 700) return;
            armed = false;
            lastFireAt = now;
            scanBtn.disabled = false;
            scanBtn.classList.remove('opacity-70');
            processBarcode(decodedText);
        };

        async function startCamera() {
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
