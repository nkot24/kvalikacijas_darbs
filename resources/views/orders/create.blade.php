<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Izveidot jaunu pasūtījumu
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto bg-white p-6 shadow-md rounded">
            <form action="{{ route('orders.store') }}" method="POST">
                @csrf

                {{-- Klients --}}
                <div class="mb-4">
                    <label class="block mb-1 font-semibold">Izvēlieties klientu</label>
                    <select name="client_id" id="client_id" class="w-full border rounded px-3 py-2">
                        <option value="">-- Nav atlasīts --</option>
                        <option value="vienreizējs">Vienreizējs klients</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->nosaukums }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4" id="one_time_client_div" style="display: none;">
                    <label class="block mb-1 font-semibold">Vienreizējs klienta nosaukums</label>
                    <input type="text" name="klients" class="w-full border rounded px-3 py-2" placeholder="Piem. Jauns Klients">
                </div>

                {{-- Produkts --}}
                <div class="mb-4">
                    <label class="block mb-1 font-semibold">Izvēlieties produktu</label>
                    <select name="products_id" id="products_id" class="w-full border rounded px-3 py-2">
                        <option value="">-- Nav atlasīts --</option>
                        <option value="vienreizējs">Vienreizējs produkts</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}">{{ $product->nosaukums }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4" id="one_time_product_div" style="display: none;">
                    <label class="block mb-1 font-semibold">Vienreizējs produkta nosaukums</label>
                    <input type="text" name="produkts" class="w-full border rounded px-3 py-2">
                </div>

                {{-- Daudzums --}}
                <div class="mb-4">
                    <label class="block mb-1 font-semibold">Daudzums</label>
                    <input type="number" name="daudzums" class="w-full border rounded px-3 py-2" required>
                </div>

                {{-- Izpildes datums --}}
                <div class="mb-4">
                    <label class="block mb-1 font-semibold">Izpildes datums</label>
                    <input type="date" name="izpildes_datums" class="w-full border rounded px-3 py-2" required>
                </div>

                {{-- Prioritāte --}}
                <div class="mb-4">
                    <label class="block mb-1 font-semibold">Prioritāte</label>
                    <select name="prioritāte" class="w-full border rounded px-3 py-2">
                        <option value="zema">Zema</option>
                        <option value="normāla" selected>Normāla</option>
                        <option value="augsta">Augsta</option>
                    </select>
                </div>

                {{-- Piezīmes --}}
                <div class="mb-4">
                    <label class="block mb-1 font-semibold">Piezīmes</label>
                    <textarea name="piezimes" class="w-full border rounded px-3 py-2"></textarea>
                </div>

                {{-- Saglabāt --}}
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    Saglabāt pasūtījumu
                </button>
            </form>
        </div>
    </div>

    {{-- Script for toggling one-time fields --}}
    <script>
        const clientSelect = document.getElementById('client_id');
        const oneTimeClientDiv = document.getElementById('one_time_client_div');

        const productSelect = document.getElementById('products_id');
        const oneTimeProductDiv = document.getElementById('one_time_product_div');

        function toggleOneTimeClient() {
            oneTimeClientDiv.style.display = clientSelect.value === 'vienreizējs' ? 'block' : 'none';
        }

        function toggleOneTimeProduct() {
            oneTimeProductDiv.style.display = productSelect.value === 'vienreizējs' ? 'block' : 'none';
        }

        clientSelect.addEventListener('change', toggleOneTimeClient);
        productSelect.addEventListener('change', toggleOneTimeProduct);

        // On page load
        window.addEventListener('DOMContentLoaded', function () {
            toggleOneTimeClient();
            toggleOneTimeProduct();
        });
    </script>
</x-app-layout>
