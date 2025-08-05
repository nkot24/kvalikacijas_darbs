<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Rediģēt procesu
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto bg-white p-6 rounded shadow">
            <form action="{{ route('processes.update', $process) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Process Name -->
                <div class="mb-4">
                    <label for="processa_nosaukums" class="block text-sm font-medium text-gray-700">Procesa nosaukums</label>
                    <input type="text" name="processa_nosaukums" id="processa_nosaukums"
                           class="w-full mt-1 border-gray-300 rounded shadow-sm focus:ring focus:ring-blue-200"
                           value="{{ old('processa_nosaukums', $process->processa_nosaukums) }}" required>
                    @error('processa_nosaukums')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Assign Users with Checkboxes -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pievienot lietotājus</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach ($users as $user)
                            <label class="flex items-center space-x-2">
                                <input type="checkbox" name="user_ids[]" value="{{ $user->id }}"
                                       class="text-blue-600 border-gray-300 rounded shadow-sm"
                                       {{ in_array($user->id, $selectedUsers) ? 'checked' : '' }}>
                                <span>{{ $user->name }} ({{ $user->role }})</span>
                            </label>
                        @endforeach
                    </div>
                    @error('user_ids')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Buttons -->
                <div class="flex justify-end gap-4">
                    <a href="{{ route('processes.index') }}" class="text-gray-600 hover:underline">Atcelt</a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        Saglabāt izmaiņas
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
