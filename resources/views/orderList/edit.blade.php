<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Apstrādāt iepirkumu</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto bg-white shadow-sm rounded-lg p-6">
            <div class="mb-4">
                <a href="{{ route('orderList.index') }}" class="text-blue-600 hover:underline">← Atpakaļ uz sarakstu</a>
            </div>

            <!-- Meta info -->
            <div class="mb-5 text-sm text-gray-700">
                <div><strong>Izveidoja:</strong> {{ optional($order->creator)->name ?? '—' }}</div>
                <div><strong>Izveidots:</strong> {{ $order->created_at?->format('Y-m-d H:i') }}</div>
                <div class="mt-2"><strong>Nosaukums:</strong> {{ $order->name }} &nbsp; <strong>Daudzums:</strong> {{ $order->quantity }}</div>
                @if($order->photo_path)
                    <div class="mt-2">
                        <a href="{{ asset('storage/'.$order->photo_path) }}" target="_blank">
                            <img src="{{ asset('storage/'.$order->photo_path) }}" class="h-16 w-16 object-cover rounded" />
                        </a>
                    </div>
                @endif
                <div class="mt-2"><strong>Pašreizējais statuss:</strong> {{ $order->status }}</div>
            </div>

            <form method="POST" action="{{ route('orderList.update', $order) }}" enctype="multipart/form-data">
                @csrf @method('PUT')

                <div class="mb-4">
                    <label class="block text-sm font-medium">Piegādātājs</label>
                    <input type="text" name="supplier_name" value="{{ old('supplier_name', $order->supplier_name) }}" class="mt-1 block w-full border rounded p-2">
                    @error('supplier_name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium">Kad pasūtīts</label>
                        <input type="date" name="ordered_at" value="{{ old('ordered_at', optional($order->ordered_at)->format('Y-m-d')) }}" class="mt-1 block w-full border rounded p-2">
                        @error('ordered_at') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Kad jāatnāk</label>
                        <input type="date" name="expected_at" value="{{ old('expected_at', optional($order->expected_at)->format('Y-m-d')) }}" class="mt-1 block w-full border rounded p-2">
                        @error('expected_at') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Kad atnāca</label>
                        <input type="date" name="arrived_at" value="{{ old('arrived_at', optional($order->arrived_at)->format('Y-m-d')) }}" class="mt-1 block w-full border rounded p-2">
                        @error('arrived_at') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-medium">Jauns foto (neobligāts)</label>
                    <input type="file" name="photo" accept="image/*" class="mt-1 block w-full">
                    @error('photo') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mt-6 flex gap-2">
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                        Saglabāt
                    </button>
                    <a href="{{ route('orderList.index') }}" class="px-4 py-2 bg-gray-100 text-gray-800 rounded hover:bg-gray-200">
                        Atcelt
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
