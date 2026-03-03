<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-white leading-tight">
                {{ __('Skenēt saražoto produkciju') }}
            </h2>
            <div class="hidden sm:block text-sm text-slate-400">
                Noliktava • Skenēšana • Pārvietošana
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Main Card --}}
            <div class="rounded-2xl border border-white/10 bg-white/5 backdrop-blur shadow-xl overflow-hidden">
                <div class="p-4 sm:p-6">
                    <div class="grid gap-6 lg:grid-cols-2">

                        <!-- Left: camera + status -->
                        <div>
                            <div class="rounded-2xl border border-white/10 bg-[#0B0F14]/50 p-3">
                                <div id="reader" class="w-full max-w-[520px] mx-auto"></div>
                            </div>

                            <div id="result" class="mt-4 rounded-2xl border border-white/10 bg-[#0B0F14]/60 px-4 py-3 text-sm text-slate-200">
                                Gatavs skenēšanai.
                            </div>

                            <div class="mt-4">
                                <button id="scanBtn"
                                    class="w-full py-3 text-base sm:text-lg font-semibold rounded-xl bg-red-600 hover:bg-red-700 text-white shadow">
                                    SKENĒT
                                </button>
                                <p class="text-sm text-slate-400 mt-2">
                                    Kamera startējas automātiski. Nospiediet “SKENĒT” un turiet svītrkodu centrā.
                                </p>
                            </div>
                        </div>

                        <!-- Right: manual -->
                        <div class="space-y-4">
                            <div class="rounded-2xl border border-white/10 bg-[#0B0F14]/60 p-4">
                                <label class="block text-sm font-medium text-slate-300 mb-2">
                                    Pēdējais skenētais:
                                </label>
                                <input id="lastCode" type="text"
                                       class="w-full rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 font-mono text-sm text-white placeholder:text-slate-500"
                                       readonly>
                            </div>

                            <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                                <form id="manualForm" class="flex flex-col sm:flex-row gap-3" onsubmit="return false;">
                                    <input id="manualCode" type="text"
                                           class="flex-1 rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-sm text-white placeholder:text-slate-500 focus:border-red-500/50 focus:ring-red-500/20"
                                           placeholder="Ievadiet svītrkodu manuāli">
                                    <button id="manualBtn"
                                            class="px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-white text-sm font-semibold ring-1 ring-white/10 transition">
                                        Meklēt
                                    </button>
                                </form>

                                <div class="mt-4">
                                    <a href="{{ route('inventory.transfers.index') }}"
                                       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-slate-200 ring-1 ring-white/10 transition">
                                        Skatīt pārvietošanas ierakstus
                                    </a>
                                </div>
                            </div>

                            <div class="h-1 bg-gradient-to-r from-transparent via-red-600/40 to-transparent rounded"></div>
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
                    <div class="text-emerald-200">
                        ✅ Produkts atrasts: <strong>${product.nosaukums}</strong><br>
                        <span class="text-slate-300">Svītrkods: ${product.svitr_kods}</span>
                    </div>

                    <label class="block">
                        <span class="text-sm font-medium text-slate-300">Daudzums</span>
                        <input id="mv-qty" type="number" min="1" step="1" value="1"
                               class="w-full rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-white">
                    </label>

                    <div class="flex flex-col sm:flex-row gap-2">
                        <button id="mv-submit" class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white font-semibold shadow">
                            Pievienot ierakstu
                        </button>
                        <a href="{{ route('inventory.transfers.index') }}"
                           class="px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-slate-200 ring-1 ring-white/10 transition text-center">
                            Skatīt ierakstus
                        </a>
                    </div>

                    <div id="mv-status" class="text-sm text-slate-300"></div>
                </div>
            `;

            document.getElementById('mv-submit').addEventListener('click', async () => {
                const qty = parseInt(document.getElementById('mv-qty').value, 10);
                const status = document.getElementById('mv-status');

                if (!qty || qty < 1) {
                    status.textContent = 'Ievadiet derīgu daudzumu.';
                    status.className = 'text-sm text-red-300';
                    return;
                }

                status.textContent = 'Saglabāju...';
                status.className = 'text-sm text-slate-300';

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
                        })
                    });
                    const data = await res.json();
                    if (res.ok && data.ok) {
                        status.textContent = 'Pievienots ✓';
                        status.className = 'text-sm text-emerald-300';
                        if (navigator.vibrate) navigator.vibrate(40);
                    } else {
                        status.textContent = data.message || 'Kļūda saglabājot';
                        status.className = 'text-sm text-red-300';
                    }
                } catch {
                    status.textContent = 'Tīkla kļūda';
                    status.className = 'text-sm text-red-300';
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
                resultDiv.innerHTML = `<div class="text-red-300">❌ ${data.message || 'Produkts nav atrasts.'}</div>`;
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
                resultDiv.innerHTML = '<span class="text-slate-300">Kamera startēta. Nospiediet “SKENĒT”.</span>';
                return;
            } catch(_) {}
            try {
                await html5QrCode.start({ facingMode: "environment" }, startOpts, onDecode, () => {});
                resultDiv.innerHTML = '<span class="text-slate-300">Kamera startēta. Nospiediet “SKENĒT”.</span>';
                return;
            } catch(_) {}
            try {
                const cams = await Html5Qrcode.getCameras();
                if (cams?.length) {
                    await html5QrCode.start({ deviceId: { exact: cams[0].id } }, startOpts, onDecode, () => {});
                    resultDiv.innerHTML = '<span class="text-slate-300">Kamera startēta (rezerves režīmā). Nospiediet “SKENĒT”.</span>';
                } else {
                    resultDiv.innerHTML = '<span class="text-red-300">Kamera nav atrasta.</span>';
                }
            } catch(e) {
                resultDiv.innerHTML = '<span class="text-red-300">Neizdevās startēt kameru.</span>';
            }
        }

        startCamera();
    });
    </script>
</x-app-layout>