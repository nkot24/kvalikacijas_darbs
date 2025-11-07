<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Rediģēt pasūtījumu
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto bg-white shadow-sm rounded-lg p-6">
            @if ($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('orders.update', $order) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label for="client_id" class="block text-sm font-medium text-gray-700">Izvēlēties klientu (vai atstāt tukšu):</label>
                    <select name="client_id" id="client_id" class="mt-1 block w-full border-gray-300 rounded">
                        <option value="">— Nav izvēlēts —</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}" {{ $order->client_id == $client->id ? 'selected' : '' }}>
                                {{ $client->nosaukums }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label for="klients" class="block text-sm font-medium text-gray-700">Vienreizējs klienta nosaukums (ja nav izvēlēts):</label>
                    <input type="text" name="klients" id="klients" value="{{ $order->klients }}" class="mt-1 block w-full border-gray-300 rounded">
                </div>

                <div class="mb-4">
                    <label for="products_id" class="block text-sm font-medium text-gray-700">Izvēlēties produktu (vai atstāt tukšu):</label>
                    <select name="products_id" id="products_id" class="mt-1 block w-full border-gray-300 rounded">
                        <option value="">— Nav izvēlēts —</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" {{ $order->products_id == $product->id ? 'selected' : '' }}>
                                {{ $product->nosaukums }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label for="produkts" class="block text-sm font-medium text-gray-700">Vienreizējs produkta nosaukums (ja nav izvēlēts):</label>
                    <input type="text" name="produkts" id="produkts" value="{{ $order->produkts }}" class="mt-1 block w-full border-gray-300 rounded">
                </div>

                <div class="mb-4">
                    <label for="daudzums" class="block text-sm font-medium text-gray-700">Daudzums:</label>
                    <input type="number" name="daudzums" id="daudzums" value="{{ $order->daudzums }}" class="mt-1 block w-full border-gray-300 rounded" required>
                </div>

                <div class="mb-4">
                    <label for="izpildes_datums" class="block text-sm font-medium text-gray-700">Izpildes datums:</label>
                    <input type="date" name="izpildes_datums" id="izpildes_datums" value="{{ $order->izpildes_datums }}" class="mt-1 block w-full border-gray-300 rounded">
                </div>

                <div class="mb-4">
                    <label for="prioritāte" class="block text-sm font-medium text-gray-700">Prioritāte:</label>
                    <select name="prioritāte" id="prioritāte" class="mt-1 block w-full border-gray-300 rounded">
                        <option value="zema" {{ $order->prioritāte == 'zema' ? 'selected' : '' }}>Zema</option>
                        <option value="normāla" {{ $order->prioritāte == 'normāla' ? 'selected' : '' }}>Normāla</option>
                        <option value="augsta" {{ $order->prioritāte == 'augsta' ? 'selected' : '' }}>Augsta</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="statuss" class="block text-sm font-medium text-gray-700">Statuss:</label>
                    <select name="statuss" id="statuss" class="mt-1 block w-full border-gray-300 rounded">
                        <option value="nav nodots ražošanai" {{ $order->statuss == 'nav nodots ražošanai' ? 'selected' : '' }}>Nav nodots ražošanai</option>
                        <option value="nodots ražošanai" {{ $order->statuss == 'nodots ražošanai' ? 'selected' : '' }}>Nodots ražošanai</option>
                        <option value="pabeigts" {{ $order->statuss == 'pabeigts' ? 'selected' : '' }}>Pabeigts</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="piezimes" class="block text-sm font-medium text-gray-700">Piezīmes:</label>
                    <textarea name="piezimes" id="piezimes" rows="4" class="mt-1 block w-full border-gray-300 rounded">{{ $order->piezimes }}</textarea>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                        Saglabāt izmaiņas
                    </button>
                </div>

            </form>
        </div>
    </div>
</x-app-layout>
