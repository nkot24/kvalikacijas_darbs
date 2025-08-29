<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Jauns pasūtījums
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto bg-white shadow-sm rounded-lg p-6">

            <!-- Back button -->
            <div class="mb-4">
                <a href="{{ route('orderList.index') }}" class="text-blue-600 hover:underline">← Atpakaļ uz sarakstu</a>
            </div>

            <!-- Form -->
            <form method="POST" action="{{ route('orderList.store') }}">
                @csrf

                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700">Nosaukums:</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}"
                           class="mt-1 block w-full border border-gray-300 rounded p-2 focus:ring focus:ring-blue-200"
                           required>
                    @error('name')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <button type="submit"
                            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                        Saglabāt
                    </button>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>
