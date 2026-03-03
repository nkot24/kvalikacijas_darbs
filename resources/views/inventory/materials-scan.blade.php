<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-white leading-tight">
                {{ __('Skenēt izmantotos materiālus') }}
            </h2>
            <div class="hidden sm:block text-sm text-slate-400">
                Noliktava • Materiāli • Skenēšana
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Main Card --}}
            <div class="rounded-2xl border border-white/10 bg-white/5 backdrop-blur shadow-xl">
                <div class="p-5 sm:p-6">

                    <div class="grid gap-6 lg:grid-cols-2">
                        {{-- Left: Camera --}}
                        <div>
                            <div class="rounded-2xl border border-white/10 bg-[#0B0F14]/40 p-3">
                                <div id="reader" class="w-full" style="max-width:520px;"></div>
                            </div>

                            <div class="mt-4">
                                <button id="scanBtn"
                                        class="w-full py-3 text-base sm:text-lg font-semibold rounded-xl bg-red-600 hover:bg-red-700 text-white shadow">
                                    SKENĒT
                                </button>

                                <p class="text-sm text-slate-400 mt-3">
                                    Kamera startējas automātiski. Nospiediet “SKENĒT” un turiet QR vai svītrkodu centrā.
                                </p>
                            </div>
                        </div>

                        {{-- Right: Status + Manual --}}
                        <div>
                            <div id="result"
                                 class="rounded-2xl border border-white/10 bg-[#0B0F14]/60 px-4 py-4 text-sm text-slate-200">
                                Gatavs skenēšanai.
                            </div>

                            <div class="mt-5 rounded-2xl border border-white/10 bg-white/5 p-4">
                                <div class="text-sm font-semibold text-white mb-3">
                                    Manuāla pievienošana
                                </div>

                                <form id="manualForm" class="flex gap-2" onsubmit="return false;">
                                    <input id="manualCode" type="text"
                                           class="flex-1 rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-sm text-white placeholder:text-slate-500
                                                  focus:border-red-500/50 focus:ring-red-500/20"
                                           placeholder="Ievadiet kodu vai materiāla nosaukumu">
                                    <button id="manualBtn"
                                            class="px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-white text-sm font-semibold ring-1 ring-white/10 transition">
                                        Pievienot
                                    </button>
                                </form>

                                <div class="mt-3 text-xs text-slate-400">
                                    Padoms: vari ievadīt svītrkodu un uzreiz apstiprināt.
                                </div>
                            </div>

                            <div class="mt-5 h-1 bg-gradient-to-r from-transparent via-red-600/40 to-transparent rounded"></div>
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
                    <div class="text-emerald-200">
                        ✅ Nolasīts kods: <strong class="text-white">${code}</strong>
                    </div>

                    <label class="block">
                        <span class="text-xs font-medium text-slate-300">Daudzums</span>
                        <input id="mv-qty" type="number" min="1" step="1" value="1"
                               class="mt-1 w-full rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-sm text-white
                                      focus:border-red-500/50 focus:ring-red-500/20">
                    </label>

                    <button id="mv-submit" class="w-full px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white font-semibold shadow">
                        Saglabāt
                    </button>

                    <div id="mv-status" class="text-sm text-slate-300 text-center"></div>
                </div>
            `;

            document.getElementById('mv-submit').addEventListener('click', async () => {
                const qty = parseInt(document.getElementById('mv-qty').value, 10);
                const status = document.getElementById('mv-status');

                if (!qty || qty < 1) {
                    status.textContent = 'Ievadiet derīgu daudzumu.';
                    status.className = 'text-sm text-red-300 text-center';
                    return;
                }

                status.textContent = 'Saglabāju...';
                status.className = 'text-sm text-slate-300 text-center';

                const data = await saveMaterial(code, qty);
                if (data.ok) {
                    status.textContent = 'Saglabāts ✓';
                    status.className = 'text-sm text-emerald-200 text-center';
                    if (navigator.vibrate) navigator.vibrate(40);
                } else {
                    status.textContent = data.message || 'Kļūda saglabājot.';
                    status.className = 'text-sm text-red-300 text-center';
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
                await html5QrCode.start({ facingMode: "environment" }, startOpts, onDecode, () => {});
                resultDiv.innerHTML = '<span class="text-slate-300">Kamera startēta. Nospiediet “SKENĒT”.</span>';
            } catch {
                resultDiv.innerHTML = '<span class="text-red-300">Neizdevās startēt kameru.</span>';
            }
        }

        startCamera();
    });
    </script>
</x-app-layout>