<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Klientu saraksts
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-full mx-auto">

            <!-- Success flash message -->
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

                <!-- Export / Import / Add Row -->
                <div
                    class="mb-6 px-4 sm:px-6 lg:px-[100px]
                           flex flex-col md:flex-row md:items-center md:justify-between gap-4 flex-wrap"
                >
                    <a href="{{ route('clients.fullExport') }}"
                       class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                        📤 Eksportēt visus klientus
                    </a>

                    <form action="{{ route('clients.fullImport') }}" method="POST" enctype="multipart/form-data"
                          class="flex flex-wrap items-center gap-2">
                        @csrf
                        <label class="text-sm font-medium text-gray-700">📥 Importēt no Excel:</label>

                        <input type="file" name="import_file"
                               class="text-xs sm:text-sm text-gray-500
                                      file:mr-2 file:py-1.5 file:px-3
                                      file:rounded file:border-0
                                      file:text-xs sm:file:text-sm file:font-semibold
                                      file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                               required>

                        <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                            Augšupielādēt
                        </button>
                    </form>

                    <a href="{{ route('clients.create') }}"
                       class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                        + Pievienot jaunu klientu
                    </a>
                </div>

                <!-- Table Wrapper -->
                <div class="overflow-x-auto px-2 sm:px-4 lg:px-[100px]">
                    <table
                        class="table-auto w-full border-collapse border border-gray-300 bg-white text-xs sm:text-sm"
                    >
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border border-gray-300 px-3 py-2">Nosaukums</th>
                                <th class="border border-gray-300 px-3 py-2">Reģistrācijas numurs</th>
                                <th class="border border-gray-300 px-3 py-2">PVN maksātāja numurs</th>
                                <th class="border border-gray-300 px-3 py-2">Juridiskā adrese</th>
                                <th class="border border-gray-300 px-3 py-2">Kontaktpersonas</th>
                                <th class="border border-gray-300 px-3 py-2">Piegādes adreses</th>
                                <th class="border border-gray-300 px-3 py-2">Darbības</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($clients as $client)
                                <tr class="border border-gray-300 even:bg-gray-50">
                                    <td class="border px-3 py-2 align-top">
                                        {{ $client->nosaukums }}
                                    </td>

                                    <td class="border px-3 py-2 align-top">
                                        {{ $client->registracijas_numurs }}
                                    </td>

                                    <td class="border px-3 py-2 align-top">
                                        {{ $client->pvn_maksataja_numurs ?? '-' }}
                                    </td>

                                    <td class="border px-3 py-2 align-top">
                                        {{ $client->juridiska_adrese ?? '-' }}
                                    </td>

                                    <td class="border px-3 py-2 align-top">
                                        <ul class="space-y-1">
                                            @forelse ($client->contactPersons as $cp)
                                                <li>
                                                    <strong>{{ $cp->kontakt_personas_vards }}</strong><br>
                                                    E-pasts: {{ $cp->{'e-pasts'} ?? '-' }}<br>
                                                    Telefons: {{ $cp->telefons ?? '-' }}
                                                </li>
                                            @empty
                                                <li>-</li>
                                            @endforelse
                                        </ul>
                                    </td>

                                    <td class="border px-3 py-2 align-top">
                                        <ul class="space-y-1">
                                            @forelse ($client->deliveryAddresses as $da)
                                                <li>{{ $da->piegades_adrese }}</li>
                                            @empty
                                                <li>-</li>
                                            @endforelse
                                        </ul>
                                    </td>

                                    <td class="border px-3 py-2 align-top space-y-2">
                                        <a href="{{ route('clients.edit', $client) }}"
                                           class="text-blue-600 hover:underline block">
                                            Rediģēt
                                        </a>

                                        <form method="POST" action="{{ route('clients.destroy', $client) }}"
                                              onsubmit="return confirm('Vai tiešām vēlaties dzēst šo klientu?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:underline">
                                                Dzēst
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">Nav pieejami klienti.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
