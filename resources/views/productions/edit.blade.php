<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Labot ražošanu
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto bg-white p-6 rounded shadow">
            <form action="{{ route('productions.update', $production) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                {{-- Order --}}
                <div class="mb-6">
                    <label class="block font-semibold mb-2">Pasūtījums</label>
                    <select name="order_id" class="w-full border border-gray-300 p-2 rounded" required>
                        @foreach ($orders as $order)
                            <option value="{{ $order->id }}" {{ $production->order_id == $order->id ? 'selected' : '' }}>
                                #{{ $order->id }} — {{ $order->nosaukums ?? $order->order_name ?? 'Pasūtījums' }}
                            </option>
                        @endforeach
                    </select>
                    @error('order_id') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Processes & Users --}}
                <div class="mb-6">
                    <label class="block font-semibold mb-2">
                        Procesi un darbinieku piešķiršana
                    </label>

                    @foreach ($processes as $process)
                        @php
                            $checked = in_array($process->id, $selectedProcessIds ?? []);
                            $preUsers = $selectedUsersByProcess[$process->id] ?? [];
                        @endphp

                        <div class="border p-4 mb-4 rounded bg-gray-50">
                            <label class="block font-semibold mb-2">
                                <input type="checkbox" name="process_ids[]" value="{{ $process->id }}" {{ $checked ? 'checked' : '' }}>
                                {{ $process->processa_nosaukums ?? ('Process #'.$process->id) }}
                            </label>

                            <label class="block text-sm mb-1">Darbinieki šim procesam:</label>
                            <select name="users[{{ $process->id }}][]" multiple class="w-full border border-gray-300 p-2 rounded">
                                @foreach ($process->users as $user)
                                    <option value="{{ $user->id }}" {{ in_array($user->id, $preUsers) ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->role }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Atstājiet tukšu, lai izveidotu vienu uzdevumu bez piešķirta darbinieka.</p>

                            <label class="block text-sm mt-3">Papildus faili (neobligāti):</label>
                            <input type="file" name="process_files[{{ $process->id }}][]" multiple class="block w-full text-sm" />
                            <p class="text-xs text-gray-500 mt-1">Esošie faili paliek. Šeit var pievienot jaunus.</p>
                        </div>
                    @endforeach

                    @error('process_ids') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex gap-3">
                    <a href="{{ route('productions.show', $production) }}" class="px-4 py-2 rounded border">Atcelt</a>
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                        Saglabāt izmaiņas
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
