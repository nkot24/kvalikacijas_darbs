<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Jauns iepirkums</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto bg-white shadow-sm rounded-lg p-6">
            <div class="mb-4">
                <a href="{{ route('orderList.index') }}" class="text-blue-600 hover:underline">← Atpakaļ uz sarakstu</a>
            </div>

            <form method="POST" action="{{ route('orderList.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-4">
                    <label class="block text-sm font-medium">Nosaukums</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="mt-1 block w-full border rounded p-2" required>
                    @error('name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium">Daudzums</label>
                    <input type="number" name="quantity" value="{{ old('quantity',1) }}" min="1" class="mt-1 block w-full border rounded p-2" required>
                    @error('quantity') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label for="photo" class="block text-sm font-medium text-gray-700">Foto:</label>
                    <input type="file" name="photo" id="photo"
                        class="mt-1 block w-full border border-gray-300 rounded p-2">
                   @error('photo')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>


                <div class="mt-6">
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Saglabāt</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
