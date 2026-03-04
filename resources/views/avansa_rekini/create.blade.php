<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-white leading-tight">
                Izveidot Avansa rēķinu
            </h2>
            <div class="hidden sm:block text-sm text-slate-400">
                Avansa rēķini • Klienti • Pasūtījumi
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-0">

            {{-- Errors --}}
            @if ($errors->any())
                <div class="mx-0 mb-5 rounded-2xl border border-red-500/20 bg-red-500/10 px-4 py-3 text-red-200">
                    <div class="font-semibold">Kļūda!</div>
                    <ul class="mt-2 list-disc pl-5 text-sm text-red-200/90 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Main card --}}
            <div class="rounded-2xl border border-white/10 bg-white/5 backdrop-blur shadow-xl">
                <form action="{{ route('avansa_rekini.generate') }}" method="POST" class="p-5 sm:p-6">
                    @csrf

                    {{-- Klients --}}
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-slate-200 mb-2">Klients</label>

                        <select name="client_id" id="client_id" required
                                class="w-full rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-sm text-white
                                       focus:border-red-500/50 focus:ring-red-500/20">
                            <option value="">-- Izvēlies klientu --</option>

                            <option value="one_time" @selected(old('client_id', request('client_id'))==='one_time')>
                                — Vienreizējs klients —
                            </option>

                            @foreach($clients as $client)
                                <option value="{{ $client->id }}"
                                    @selected((string)old('client_id', request('client_id')) === (string)$client->id)>
                                    {{ $client->nosaukums }}
                                </option>
                            @endforeach
                        </select>

                        <p class="text-xs text-slate-500 mt-2">
                            “Vienreizējs klients” rādīs pasūtījumus, kuriem <strong>client_id ir NULL</strong>.
                        </p>
                    </div>

                    {{-- Pasūtījumi --}}
                    <div class="mb-5">
                        <div class="flex items-center justify-between gap-3 mb-2">
                            <label class="block text-sm font-medium text-slate-200">Pasūtījumi</label>
                            <span class="text-xs text-slate-500">*Tiek parādīti izvēlētā klienta pasūtījumi</span>
                        </div>

                        <div id="orders_container" class="space-y-3"></div>
                    </div>

                    {{-- Pievienot PVN? --}}
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-slate-200 mb-2">Pievienot PVN?</label>
                        <select name="add_pvn" class="w-full rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-sm text-white
                                                     focus:border-red-500/50 focus:ring-red-500/20" required>
                            <option value="1" @selected(old('add_pvn','1')==='1')>Jā, pievienot PVN (21%)</option>
                            <option value="0" @selected(old('add_pvn')==='0')>Nē, nepievienot PVN</option>
                        </select>
                    </div>

                    {{-- Avansa maksājums? --}}
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-slate-200 mb-2">Avansa maksājums?</label>
                        <select name="use_advance" id="use_advance" class="w-full rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-sm text-white
                                                                         focus:border-red-500/50 focus:ring-red-500/20" required>
                            <option value="0" @selected(old('use_advance','0')==='0')>Nē</option>
                            <option value="1" @selected(old('use_advance', request('use_advance'))==='1')>Jā</option>
                        </select>
                    </div>

                    {{-- Avansa maksājums (%) --}}
                    <div class="mb-5" id="advance_block" style="display:none;">
                        <label class="block text-sm font-medium text-slate-200 mb-2">Avansa maksājums (%)</label>
                        <input type="number" name="advance_percent" min="0" max="100" step="0.01"
                               value="{{ old('advance_percent') }}"
                               placeholder="Piem., 60"
                               class="w-full rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-sm text-white placeholder:text-slate-500
                                      focus:border-red-500/50 focus:ring-red-500/20">
                        <p class="text-xs text-slate-500 mt-2">
                            Ja norādīts, “Summa apmaksai” būs šis procents no pilnās summas (ar/bez PVN atbilstoši izvēlei).
                        </p>
                        @error('advance_percent')
                            <p class="text-red-300 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Speciālās atzīmes --}}
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-slate-200 mb-2">Speciālās atzīmes</label>
                        <textarea name="special_notes" rows="3"
                                  class="w-full rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-sm text-white placeholder:text-slate-500
                                         focus:border-red-500/50 focus:ring-red-500/20"
                                  placeholder="Papildu informācija, piegādes piezīmes u.tml.">{{ old('special_notes') }}</textarea>
                    </div>

                    {{-- Buttons --}}
                    <div class="flex flex-col sm:flex-row gap-3 mt-6 sm:justify-end">
                        <button type="submit" name="action" value="print"
                                class="px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-slate-200 text-sm ring-1 ring-white/10 transition">
                            Atvērt pārlūkā
                        </button>

                        <button type="submit" name="action" value="download"
                                class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-semibold shadow">
                            Lejupielādēt PDF
                        </button>
                    </div>
                </form>
            </div>

            <div class="mt-6 h-1 bg-gradient-to-r from-transparent via-red-600/40 to-transparent rounded"></div>
        </div>
    </div>

    <script>
        const ordersContainer = document.getElementById('orders_container');
        const clientSelect = document.getElementById('client_id');
        const useAdvanceSelect = document.getElementById('use_advance');
        const advanceBlock = document.getElementById('advance_block');

        // Presets from URL (?client_id=...&order_id=...)
        const presetClientId = @json(request('client_id'));
        const presetOrderId  = @json(request('order_id'));

        function toggleAdvanceBlock() {
            const show = useAdvanceSelect && useAdvanceSelect.value === '1';
            if (advanceBlock) advanceBlock.style.display = show ? 'block' : 'none';
        }

        function buildOrderRow(order) {
            const row = document.createElement('div');
            row.className = "rounded-2xl border border-white/10 bg-white/5 p-4";

            const top = document.createElement('div');
            top.className = "flex items-start gap-3";

            const cb = document.createElement('input');
            cb.type = "checkbox";
            cb.name = "orders[]";
            cb.value = order.id;
            cb.className = "mt-1 h-4 w-4 rounded border-white/20 bg-transparent text-red-600 focus:ring-red-500/20";

            const right = document.createElement('div');
            right.className = "min-w-0 flex-1";

            const label = document.createElement('div');
            label.className = "text-sm font-semibold text-white break-words";
            label.textContent = `${order.pasutijuma_numurs} - ${order.produkts} (daudz.: ${order.daudzums})`;

            right.appendChild(label);

            top.appendChild(cb);
            top.appendChild(right);
            row.appendChild(top);

            if (!order.has_price) {
                const priceWrap = document.createElement('div');
                priceWrap.className = "mt-3";
                priceWrap.style.display = "none";

                const note = document.createElement('p');
                note.className = "text-sm text-amber-200/90 mb-2";
                note.textContent = "Nav vienības cenas. Ievadi kopējo cenu šim pasūtījumam:";

                const input = document.createElement('input');
                input.type = "number";
                input.step = "0.01";
                input.min = "0";
                input.name = `order_custom_total[${order.id}]`;
                input.placeholder = "Cena šim pasūtījumam (kopā, €)";
                input.className = "w-full rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-sm text-white placeholder:text-slate-500 focus:border-red-500/50 focus:ring-red-500/20";
                input.disabled = true;

                const hint = document.createElement('p');
                hint.className = "text-xs text-slate-500 mt-2";
                hint.textContent = "Piemērs: 100 / daudzums 4 → vienības cena 25.";

                priceWrap.appendChild(note);
                priceWrap.appendChild(input);
                priceWrap.appendChild(hint);
                row.appendChild(priceWrap);

                cb.addEventListener('change', () => {
                    const show = cb.checked;
                    priceWrap.style.display = show ? "block" : "none";
                    input.disabled = !show;
                    if (!show) input.value = "";
                });
            } else {
                const info = document.createElement('p');
                info.className = "text-xs text-slate-500 mt-2";
                info.textContent = "Vienības cena ir saglabāta produktā.";
                row.appendChild(info);
            }

            return row;
        }

        clientSelect.addEventListener('change', function () {
            const client = this.value;
            ordersContainer.innerHTML = '';
            if (!client) return;

            const url = `{{ url('/api/orders/by-client') }}/${client}`;

            fetch(url, {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            })
            .then(async res => {
                if (!res.ok) {
                    const t = await res.text();
                    throw new Error(`HTTP ${res.status}: ${t.slice(0,200)}`);
                }
                return res.json();
            })
            .then(data => {
                data.sort((a, b) => b.id - a.id);

                if (data.length === 0) {
                    ordersContainer.innerHTML = `<p class="text-slate-500">Nav pieejamu pasūtījumu.</p>`;
                    return;
                }

                ordersContainer.innerHTML = '';
                data.forEach(order => ordersContainer.appendChild(buildOrderRow(order)));

                // Precheck preset order if present
                if (presetOrderId) {
                    const cb = ordersContainer.querySelector(`input[type="checkbox"][name="orders[]"][value="${presetOrderId}"]`);
                    if (cb) {
                        cb.checked = true;
                        cb.dispatchEvent(new Event('change')); // reveal price field if needed
                    }
                }
            })
            .catch(err => {
                console.error('Pasūtījumu ielādes kļūda:', err);
                ordersContainer.innerHTML = `<p class="text-red-300">Neizdevās ielādēt pasūtījumus. (${err.message})</p>`;
            });
        });

        // Init on load
        if (presetClientId) {
            clientSelect.value = presetClientId;
            clientSelect.dispatchEvent(new Event('change'));
        } else if (@json((bool)old('client_id'))) {
            clientSelect.dispatchEvent(new Event('change'));
        }

        if (useAdvanceSelect) {
            useAdvanceSelect.addEventListener('change', toggleAdvanceBlock);
            toggleAdvanceBlock();
        }
    </script>
</x-app-layout>