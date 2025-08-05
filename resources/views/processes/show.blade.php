<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Procesa informācija
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto bg-white p-6 rounded shadow">
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Nosaukums:</h3>
                <p class="text-gray-700">{{ $process->processa_nosaukums }}</p>
            </div>

            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Pievienotie lietotāji:</h3>
                @forelse ($process->users as $user)
                    <div class="text-gray-700 mb-1">
                        • {{ $user->name }} ({{ $user->role }})
                    </div>
                @empty
                    <p class="text-gray-500">Nav pievienotu lietotāju.</p>
                @endforelse
            </div>

            <div class="mt-6">
                <a href="{{ route('processes.edit', $process) }}"
                   class="inline-block px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Rediģēt
                </a>
                <a href="{{ route('processes.index') }}"
                   class="inline-block px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400 ml-2">
                    Atpakaļ uz sarakstu
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
