<x-app-layout>
    <div class="max-w-5xl mx-auto p-6 bg-white shadow rounded">
        <h2 class="text-xl font-bold mb-4">Izveidot Avansa rēķinu</h2>

        @if ($errors->any())
            <div class="mb-4 p-3 rounded bg-red-50 text-red-700">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('avansa_rekini.generate') }}" method="POST">
            @csrf

            <!-- Klients -->
            <div class="mb-4">
                <label class="block font-semibold">Klients</label>
                <select name="client_id" id="client_id" class="w-full border rounded p-2" required>
                    <option value="">-- Izvēlies klientu --</option>
                    <option value="one_time"
                        @selected(old('client_id', request('client_id'))==='one_time')
                    >— Vienreizējs klients —</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}"
                            @selected((string)old('client_id', request('client_id')) === (string)$client->id)
                        >{{ $client->nosaukums }}</option>
                    @endforeach
                </select>
                <small class="text-gray-500">
                    “Vienreizējs klients” rādīs pasūtījumus, kuriem <strong>client_id ir NULL</strong>.
                </small>
            </div>

            <!-- Pasūtījumi (dinamiski) -->
            <div class="mb-4">
                <label class="block font-semibold">Pasūtījumi</label>
                <div id="orders_container" class="space-y-3"></div>
                <small class="text-gray-500">*Tiek parādīti izvēlētā klienta pasūtījumi</small>
            </div>

            <!-- Pievienot PVN? -->
            <div class="mb-4">
                <label class="block font-semibold">Pievienot PVN?</label>
                <select name="add_pvn" class="w-full border rounded p-2" required>
                    <option value="1" @selected(old('add_pvn','1')==='1')>Jā, pievienot PVN (21%)</option>
                    <option value="0" @selected(old('add_pvn')==='0')>Nē, nepievienot PVN</option>
                </select>
            </div>

            <!-- Avansa maksājums? (select) -->
            <div class="mb-4">
                <label class="block font-semibold">Avansa maksājums?</label>
                <select name="use_advance" id="use_advance" class="w-full border rounded p-2" required>
                    <option value="0" @selected(old('use_advance','0')==='0')>Nē</option>
                    <option value="1" @selected(old('use_advance', request('use_advance'))==='1')>Jā</option>
                </select>
            </div>

            <!-- Avansa maksājums (%) - visible only when Jā -->
            <div class="mb-4" id="advance_block" style="display:none;">
                <label class="block font-semibold">Avansa maksājums (%)</label>
                <input type="number" name="advance_percent" min="0" max="100" step="0.01"
                       class="w-full border rounded p-2" placeholder="Piem., 60" value="{{ old('advance_percent') }}">
                <small class="text-gray-500">Ja norādīts, “Summa apmaksai” būs šis procents no pilnās summas (ar/bez PVN atbilstoši izvēlei).</small>
                @error('advance_percent')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Speciālās atzīmes -->
            <div class="mb-4">
                <label class="block font-semibold">Speciālās atzīmes</label>
                <textarea name="special_notes" rows="3" class="w-full border rounded p-2"
                          placeholder="Papildu informācija, piegādes piezīmes u.tml.">{{ old('special_notes') }}</textarea>
            </div>

            <!-- Pogas -->
            <div class="flex space-x-3 mt-6">
                <button type="submit" name="action" value="download" class="px-4 py-2 bg-blue-600 text-white rounded">
                    Lejupielādēt PDF
                </button>
                <button type="submit" name="action" value="print" class="px-4 py-2 bg-gray-700 text-white rounded">
                    Atvērt pārlūkā
                </button>
            </div>
        </form>
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
            row.className = "border rounded p-3";

            const top = document.createElement('div');
            top.className = "flex items-center space-x-2";

            const cb = document.createElement('input');
            cb.type = "checkbox";
            cb.name = "orders[]";
            cb.value = order.id;
            cb.className = "w-4 h-4";

            const label = document.createElement('label');
            label.className = "font-medium";
            label.textContent = `${order.pasutijuma_numurs} - ${order.produkts} (daudz.: ${order.daudzums})`;

            top.appendChild(cb);
            top.appendChild(label);
            row.appendChild(top);

            if (!order.has_price) {
                const priceWrap = document.createElement('div');
                priceWrap.className = "mt-2";
                priceWrap.style.display = "none";

                const note = document.createElement('p');
                note.className = "text-sm text-amber-700 mb-2";
                note.textContent = "Nav vienības cenas. Ievadi kopējo cenu šim pasūtījumam:";

                const input = document.createElement('input');
                input.type = "number";
                input.step = "0.01";
                input.min = "0";
                input.name = `order_custom_total[${order.id}]`;
                input.placeholder = "Cena šim pasūtījumam (kopā, €)";
                input.className = "w-full border rounded p-2";
                input.disabled = true;

                const hint = document.createElement('small');
                hint.className = "text-gray-500";
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
                info.className = "text-sm text-gray-600 mt-2";
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
                    ordersContainer.innerHTML = `<p class="text-gray-500">Nav pieejamu pasūtījumu.</p>`;
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
                ordersContainer.innerHTML = `<p class="text-red-600">Neizdevās ielādēt pasūtījumus. (${err.message})</p>`;
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
