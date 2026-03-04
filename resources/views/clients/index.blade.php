<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-white leading-tight">
                Klientu saraksts
            </h2>
            <div class="hidden sm:block text-sm text-slate-400">
                Klienti • Imports • Eksports
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-full mx-auto">

            {{-- Success message --}}
            @if (session('success'))
                <div class="mx-4 sm:mx-6 lg:mx-[100px] mb-5 rounded-2xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-emerald-200">
                    <div class="font-semibold">Veiksmīgi!</div>
                    <div class="text-sm text-emerald-200/90">{{ session('success') }}</div>
                </div>
            @endif

            {{-- Controls Card --}}
            <div class="mx-4 sm:mx-6 lg:mx-[100px] mb-6 rounded-2xl border border-white/10 bg-white/5 backdrop-blur shadow-xl">
                <div class="p-4 sm:p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4 flex-wrap">

                    <a href="{{ route('clients.fullExport') }}"
                       class="px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-white text-sm ring-1 ring-white/10 transition">
                        📤 Eksportēt visus klientus
                    </a>

                    <form action="{{ route('clients.fullImport') }}" method="POST" enctype="multipart/form-data"
                          class="flex flex-wrap items-center gap-2">
                        @csrf

                        <label class="text-sm text-slate-400">📥 Importēt no Excel:</label>

                        <input type="file" name="import_file"
                               class="text-xs sm:text-sm text-slate-300
                                      file:mr-2 file:py-2 file:px-3
                                      file:rounded-xl file:border-0
                                      file:text-xs sm:file:text-sm file:font-semibold
                                      file:bg-white/10 file:text-white hover:file:bg-white/15"
                               required>

                        <button type="submit"
                                class="px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-white text-sm ring-1 ring-white/10 transition">
                            Augšupielādēt
                        </button>
                    </form>

                    <a href="{{ route('clients.create') }}"
                       class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-semibold shadow">
                        + Pievienot jaunu klientu
                    </a>

                </div>
            </div>

            {{-- Table Card --}}
            <div class="mx-2 sm:mx-4 lg:mx-[100px] rounded-2xl border border-white/10 bg-white/5 backdrop-blur shadow-xl">

                <div class="overflow-x-auto lg:overflow-visible">
                    <table class="w-full text-sm table-fixed lg:table-auto">

                        <thead class="bg-white/5">
                            <tr class="text-left text-slate-200">
                                <th class="px-4 py-3">Nosaukums</th>
                                <th class="px-4 py-3">Reģistrācijas numurs</th>
                                <th class="px-4 py-3">PVN maksātāja numurs</th>
                                <th class="px-4 py-3">Juridiskā adrese</th>
                                <th class="px-4 py-3 hidden lg:table-cell">Kontaktpersonas</th>
                                <th class="px-4 py-3 hidden lg:table-cell">Piegādes adreses</th>
                                <th class="px-4 py-3 text-center w-[140px]">Darbības</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-white/10">

                            @forelse ($clients as $client)

                                <tr class="hover:bg-white/5 transition">

                                    <td class="px-4 py-3 text-white break-words">
                                        {{ $client->nosaukums }}
                                    </td>

                                    <td class="px-4 py-3 text-slate-200">
                                        {{ $client->registracijas_numurs }}
                                    </td>

                                    <td class="px-4 py-3 text-slate-200">
                                        {{ $client->pvn_maksataja_numurs ?? '-' }}
                                    </td>

                                    <td class="px-4 py-3 text-slate-200 break-words">
                                        {{ $client->juridiska_adrese ?? '-' }}
                                    </td>

                                    {{-- Contact persons --}}
                                    <td class="px-4 py-3 text-slate-300 hidden lg:table-cell">
                                        <ul class="space-y-2">
                                            @forelse ($client->contactPersons as $cp)
                                                <li class="text-xs">
                                                    <span class="font-semibold text-white">
                                                        {{ $cp->kontakt_personas_vards }}
                                                    </span><br>

                                                    <span class="text-slate-400">
                                                        {{ $cp->{'e-pasts'} ?? '-' }}
                                                    </span><br>

                                                    <span class="text-slate-400">
                                                        {{ $cp->telefons ?? '-' }}
                                                    </span>
                                                </li>
                                            @empty
                                                <li class="text-slate-500">-</li>
                                            @endforelse
                                        </ul>
                                    </td>

                                    {{-- Delivery addresses --}}
                                    <td class="px-4 py-3 text-slate-300 hidden lg:table-cell">
                                        <ul class="space-y-1 text-xs">
                                            @forelse ($client->deliveryAddresses as $da)
                                                <li>{{ $da->piegades_adrese }}</li>
                                            @empty
                                                <li class="text-slate-500">-</li>
                                            @endforelse
                                        </ul>
                                    </td>

                                    {{-- Actions --}}
                                    <td class="px-4 py-3 text-center space-y-2">

                                        <a href="{{ route('clients.edit', $client) }}"
                                           class="text-red-300 hover:text-red-200 hover:underline underline-offset-4 block">
                                            Rediģēt
                                        </a>

                                        <form method="POST"
                                              action="{{ route('clients.destroy', $client) }}"
                                              onsubmit="return confirm('Vai tiešām vēlaties dzēst šo klientu?');">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    class="text-slate-300 hover:text-white hover:underline underline-offset-4">
                                                Dzēst
                                            </button>
                                        </form>

                                    </td>

                                </tr>

                            @empty

                                <tr>
                                    <td colspan="7" class="text-center py-10 text-slate-400">
                                        Nav pieejami klienti.
                                    </td>
                                </tr>

                            @endforelse

                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-6 h-1 mx-4 sm:mx-6 lg:mx-[100px] bg-gradient-to-r from-transparent via-red-600/40 to-transparent rounded"></div>

        </div>
    </div>
</x-app-layout>