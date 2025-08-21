<x-app-layout>
    <div class="max-w-5xl mx-auto p-6 bg-white shadow rounded">
        <h2 class="text-xl font-bold mb-4">Izveidot Avansa rēķinu</h2>

        <form action="{{ route('avansa_rekini.generate') }}" method="POST">
            @csrf

            <!-- Select client -->
            <div class="mb-4">
                <label class="block font-semibold">Klients</label>
                <select name="client_id" id="client_id" class="w-full border rounded p-2">
                    <option value="">-- Izvēlies klientu --</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->nosaukums }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Orders will be loaded dynamically -->
            <div class="mb-4">
                <label class="block font-semibold">Pasūtījumi</label>
                <div id="orders_container" class="space-y-2"></div>
                <small class="text-gray-500">*Tiek parādīti tikai šī klienta pasūtījumi</small>
            </div>

            <!-- Price Type -->
            <div class="mb-4">
                <label class="block font-semibold">Cena</label>
                <select name="price_type" class="w-full border rounded p-2">
                    <option value="pardosanas_cena">Mazumtirdzniecības cena</option>
                    <option value="vairumtirdzniecibas_cena">Vairumtirdzniecības cena</option>
                </select>
            </div>

            <!-- Discount toggle -->
            <div class="mb-4">
                <label class="block font-semibold">Atlaide</label>
                <select name="atlaide_select" id="atlaide_select" class="w-full border rounded p-2">
                    <option value="0">Bez atlaides</option>
                    <option value="1">Ar atlaidi</option>
                </select>
            </div>

            <!-- Discount input -->
            <div class="mb-4 hidden" id="atlaide_input_wrapper">
                <label class="block font-semibold">Atlaide (%)</label>
                <input type="number" step="0.01" name="atlaide" id="atlaide" class="w-full border rounded p-2" placeholder="Ievadiet atlaidi procentos">
            </div>

            <!-- Action buttons -->
            <div class="flex space-x-3 mt-6">
                <button type="submit" name="action" value="download" class="px-4 py-2 bg-blue-600 text-white rounded">Izveidot</button>
            </div>
        </form>
    </div>

    <script>
        // Toggle discount field
        document.getElementById('atlaide_select').addEventListener('change', function () {
            const wrapper = document.getElementById('atlaide_input_wrapper');
            wrapper.classList.toggle('hidden', this.value !== '1');
        });

        // Load orders based on selected client
        document.getElementById('client_id').addEventListener('change', function () {
            const clientId = this.value;
            const ordersContainer = document.getElementById('orders_container');
            ordersContainer.innerHTML = '';
            if (!clientId) return;

            fetch(`/api/orders/by-client/${clientId}`)
                .then(res => res.json())
                .then(data => {
                    // Sort orders from newest to oldest by id
                    data.sort((a, b) => b.id - a.id);

                    if (data.length === 0) {
                        ordersContainer.innerHTML = `<p class="text-gray-500">Nav pieejamu pasūtījumu.</p>`;
                        return;
                    }

                    data.forEach(order => {
                        const wrapper = document.createElement('div');
                        wrapper.className = "flex items-center space-x-2";

                        const checkbox = document.createElement('input');
                        checkbox.type = "checkbox";
                        checkbox.name = "orders[]";
                        checkbox.value = order.id;
                        checkbox.className = "w-4 h-4";

                        const label = document.createElement('label');
                        label.textContent = `${order.pasutijuma_numurs} - ${order.produkts} (${order.daudzums})`;

                        wrapper.appendChild(checkbox);
                        wrapper.appendChild(label);
                        ordersContainer.appendChild(wrapper);
                    });
                })
                .catch(err => {
                    console.error('Kļūda ielādējot pasūtījumus:', err);
                });
        });
    </script>
</x-app-layout>
