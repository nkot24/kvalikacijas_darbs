<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Skenēt izmantotos materiālus') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <!-- Scanner Section -->
                <div class="flex flex-col items-center">
                    <div id="reader" style="width:100%;max-width:520px;"></div>

                    <div id="result" class="mt-4 p-3 border rounded bg-gray-50 text-sm w-full text-center">
                        Gatavs skenēšanai.
                    </div>

                    <button id="scanBtn"
                        class="mt-4 w-full py-3 text-lg font-semibold rounded bg-blue-600 hover:bg-blue-700 text-white">
                        SKENĒT
                    </button>

                    <p class="text-sm text-gray-500 mt-2 text-center">
                        Kamera startējas automātiski. Nospiediet “SKENĒT” un turiet QR vai svītrkodu centrā.
                    </p>

                    <!-- Manual input form -->
                    <form id="manualForm" class="mt-6 flex gap-2 w-full max-w-md" onsubmit="return false;">
                        <input id="manualCode" type="text"
                               class="flex-1 border rounded p-2"
                               placeholder="Ievadiet kodu vai materiāla nosaukumu">
                        <button id="manualBtn" class="px-4 rounded bg-gray-200 hover:bg-gray-300">Pievienot</button>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
    document.addEventListener('DOMContentLoaded', async () => {
        const resultDiv = document.getElementById('result');
        const scanBtn   = document.getElementById('scanBtn');
        const manualBtn = document.getElementById('manualBtn');
        const manualCode= document.getElementById('manualCode');

        let html5QrCode = new Html5Qrcode("reader");
        let armed = false, lastFireAt = 0;

        async function saveMaterial(code, qty) {
            const res = await fetch(@json(route('inventory.materials.store')), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': @json(csrf_token()),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ svitr_kods: code, qty })
            });
            return await res.json().catch(() => ({}));
        }

        function showAddForm(code) {
            resultDiv.innerHTML = `
                <div class="space-y-3">
                    <div class="text-green-700">
                        ✅ Nolasīts kods: <strong>${code}</strong>
                    </div>
                    <label class="block text-left">
                        <span class="text-sm font-medium">Daudzums</span>
                        <input id="mv-qty" type="number" min="1" step="1" value="1"
                               class="w-full border rounded p-2">
                    </label>
                    <button id="mv-submit" class="w-full px-4 py-2 rounded bg-blue-600 text-white">
                        Saglabāt
                    </button>
                    <div id="mv-status" class="text-sm text-gray-600 text-center"></div>
                </div>
            `;

            document.getElementById('mv-submit').addEventListener('click', async () => {
                const qty = parseInt(document.getElementById('mv-qty').value, 10);
                const status = document.getElementById('mv-status');
                if (!qty || qty < 1) {
                    status.textContent = 'Ievadiet derīgu daudzumu.';
                    status.className = 'text-sm text-red-600 text-center';
                    return;
                }

                status.textContent = 'Saglabāju...';
                const data = await saveMaterial(code, qty);
                if (data.ok) {
                    status.textContent = 'Saglabāts ✓';
                    status.className = 'text-sm text-green-600 text-center';
                    if (navigator.vibrate) navigator.vibrate(40);
                } else {
                    status.textContent = data.message || 'Kļūda saglabājot.';
                    status.className = 'text-sm text-red-600 text-center';
                }
            });
        }

        function processBarcode(code) {
            if (!code) return;
            showAddForm(code.trim());
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
            qrbox: (w, h) => ({ width: Math.min(w * 0.9, 480), height: Math.min(h * 0.6, 320) }),
            formatsToSupport: [
                Html5QrcodeSupportedFormats.QR_CODE,
                Html5QrcodeSupportedFormats.CODE_128,
                Html5QrcodeSupportedFormats.CODE_39,
                Html5QrcodeSupportedFormats.EAN_13
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
                await html5QrCode.start({ facingMode: "environment" }, startOpts, onDecode);
                resultDiv.innerHTML = '<span class="text-gray-600">Kamera startēta. Nospiediet “SKENĒT”.</span>';
            } catch {
                resultDiv.innerHTML = '<span class="text-red-700">Neizdevās startēt kameru.</span>';
            }
        }

        startCamera();
    });
    </script>
</x-app-layout>
