<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Lietotāju saraksts
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-full mx-auto">

            <!-- Flash message -->
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

            <!-- White container -->
            <div class="bg-white shadow-sm rounded-lg p-4 sm:p-6">

                <!-- Buttons: Export, Import, Create -->
                <div
                    class="mb-6 px-4 sm:px-6 lg:px-[100px]
                           flex flex-col md:flex-row md:items-center md:justify-between gap-4 flex-wrap"
                >
                    <!-- Export -->
                    <a href="{{ route('users.export') }}"
                       class="inline-block px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                        📤 Eksportēt lietotājus
                    </a>

                    <!-- Import -->
                    <form action="{{ route('users.import') }}" method="POST" enctype="multipart/form-data"
                          class="flex flex-wrap items-center gap-2">
                        @csrf

                        <label class="text-sm font-medium text-gray-700">
                            📥 Importēt no Excel:
                        </label>

                        <input type="file" name="import_file"
                               class="block max-w-full text-xs sm:text-sm text-gray-500
                                      file:py-1.5 file:px-3
                                      file:text-xs sm:file:text-sm
                                      file:mr-2
                                      file:rounded file:border-0
                                      file:bg-blue-50 file:text-blue-700
                                      hover:file:bg-blue-100"
                               required>

                        <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                            Augšupielādēt
                        </button>
                    </form>

                    <!-- Add New -->
                    <a href="{{ route('users.create') }}"
                       class="inline-block px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                        + Pievienot lietotāju
                    </a>
                </div>

                <!-- Table area (page doesn't scroll, only this can) -->
                <div class="px-4 sm:px-6 lg:px-[100px]">
                    <div class="overflow-x-auto">
                        <table
                            class="min-w-[700px] w-full border-collapse border border-gray-300 bg-white text-xs sm:text-sm"
                        >
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="border px-2 sm:px-3 py-2">ID</th>
                                    <th class="border px-2 sm:px-3 py-2">Vārds</th>
                                    <th class="border px-2 sm:px-3 py-2">Loma</th>
                                    <th class="border px-2 sm:px-3 py-2">Parole</th>
                                    <th class="border px-2 sm:px-3 py-2">Darbības</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($users as $user)
                                    <tr class="even:bg-gray-50">
                                        <td class="border px-2 sm:px-3 py-2">
                                            {{ $user->id }}
                                        </td>
                                        <td class="border px-2 sm:px-3 py-2">
                                            {{ $user->name }}
                                        </td>
                                        <td class="border px-2 sm:px-3 py-2">
                                            {{ ucfirst($user->role) }}
                                        </td>
                                        <td class="border px-2 sm:px-3 py-2 font-mono break-all">
                                            {{ $user->visible_password ?? '-' }}
                                        </td>
                                        <td class="border px-2 sm:px-3 py-2 whitespace-nowrap">
                                            <a href="{{ route('users.edit', $user) }}"
                                               class="text-blue-600 hover:underline mr-2">
                                                Rediģēt
                                            </a>

                                            <form method="POST"
                                                  action="{{ route('users.destroy', $user) }}"
                                                  class="inline"
                                                  onsubmit="return confirm('Vai tiešām vēlaties dzēst šo lietotāju?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="text-red-600 hover:underline">
                                                    Dzēst
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
