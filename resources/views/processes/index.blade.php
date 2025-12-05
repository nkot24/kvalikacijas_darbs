<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Procesu saraksts
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-full mx-auto">

            @if (session('success'))
                <div
                    class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative
                           mx-4 sm:mx-6 lg:mx-[100px] mb-4"
                    role="alert"
                >
                    <strong class="font-bold">Veiksmīgi!</strong>
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white shadow-sm rounded-lg p-4 sm:p-6">

                <!-- Add Process Button -->
                <div class="mb-6 px-4 sm:px-6 lg:px-[100px]">
                    <a href="{{ route('processes.create') }}"
                       class="inline-block px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                        + Pievienot jaunu procesu
                    </a>
                </div>

                <!-- Processes Table (only this area can scroll horizontally) -->
                <div class="px-4 sm:px-6 lg:px-[100px]">
                    <div class="overflow-x-auto">
                        <table
                            class="min-w-[800px] w-full table-auto border-collapse border border-gray-300 bg-white text-xs sm:text-sm"
                        >
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="border px-3 py-2">ID</th>
                                    <th class="border px-3 py-2">Nosaukums</th>
                                    <th class="border px-3 py-2">Lietotāji</th>
                                    <th class="border px-3 py-2">Darbības</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($processes as $process)
                                    <tr class="even:bg-gray-50">
                                        <td class="border px-3 py-2">{{ $process->id }}</td>
                                        <td class="border px-3 py-2">{{ $process->processa_nosaukums }}</td>
                                        <td class="border px-3 py-2">
                                            @forelse ($process->users as $user)
                                                <span class="inline-block bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded mr-1 mb-1">
                                                    {{ $user->name }}
                                                </span>
                                            @empty
                                                <span class="text-gray-400 text-sm">Nav pievienotu lietotāju</span>
                                            @endforelse
                                        </td>
                                        <td class="border px-3 py-2">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <!-- UP -->
                                                <form action="{{ route('processes.update', $process) }}"
                                                      method="POST"
                                                      class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="swap" value="up">
                                                    <button type="submit"
                                                            class="px-2 py-1 bg-gray-200 rounded hover:bg-gray-300 text-xs"
                                                            title="Pārvietot uz augšu">
                                                        ▲
                                                    </button>
                                                </form>

                                                <!-- DOWN -->
                                                <form action="{{ route('processes.update', $process) }}"
                                                      method="POST"
                                                      class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="swap" value="down">
                                                    <button type="submit"
                                                            class="px-2 py-1 bg-gray-200 rounded hover:bg-gray-300 text-xs"
                                                            title="Pārvietot uz leju">
                                                        ▼
                                                    </button>
                                                </form>

                                                <a href="{{ route('processes.edit', $process) }}"
                                                   class="text-blue-600 hover:underline text-sm">
                                                    Rediģēt
                                                </a>

                                                <form action="{{ route('processes.destroy', $process) }}"
                                                      method="POST"
                                                      onsubmit="return confirm('Vai tiešām vēlaties dzēst šo procesu?');"
                                                      class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="text-red-600 hover:underline text-sm">
                                                        Dzēst
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4">
                                            Nav pieejamu procesu.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
