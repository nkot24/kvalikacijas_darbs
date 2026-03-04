<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-white leading-tight">
                Procesu saraksts
            </h2>
            <div class="hidden sm:block text-sm text-slate-400">
                Procesi • Kārtošana • Lietotāji
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-full mx-auto">

            {{-- Success --}}
            @if (session('success'))
                <div class="mx-4 sm:mx-6 lg:mx-[100px] mb-5 rounded-2xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-emerald-200">
                    <div class="font-semibold">Veiksmīgi!</div>
                    <div class="text-sm text-emerald-200/90">{{ session('success') }}</div>
                </div>
            @endif

            {{-- Top Controls Card --}}
            <div class="mx-4 sm:mx-6 lg:mx-[100px] mb-6 rounded-2xl border border-white/10 bg-white/5 backdrop-blur shadow-xl">
                <div class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 flex-wrap">
                    <div class="text-sm text-slate-400">
                        Pārvaldiet ražošanas procesus un tiem piesaistītos lietotājus.
                    </div>

                    <a href="{{ route('processes.create') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-semibold shadow">
                        + Pievienot jaunu procesu
                    </a>
                </div>
            </div>

            {{-- Table Card (Orders index style) --}}
            <div class="overflow-x-auto mx-2 sm:mx-4 lg:mx-[100px] rounded-2xl border border-white/10 bg-white/5 backdrop-blur shadow-xl">
                <table class="table-auto w-full text-xs sm:text-sm">
                    <thead class="bg-white/5">
                        <tr class="text-left text-slate-200">
                            <th class="px-4 py-3 whitespace-nowrap">ID</th>
                            <th class="px-4 py-3 whitespace-nowrap">Nosaukums</th>
                            <th class="px-4 py-3 whitespace-nowrap">Lietotāji</th>
                            <th class="px-4 py-3 whitespace-nowrap text-center w-[220px]">Darbības</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-white/10">
                        @forelse ($processes as $process)
                            <tr class="hover:bg-white/5 transition">

                                <td class="px-4 py-3 text-white whitespace-nowrap">
                                    {{ $process->id }}
                                </td>

                                <td class="px-4 py-3 text-slate-200 break-words">
                                    {{ $process->processa_nosaukums }}
                                </td>

                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-2">
                                        @forelse ($process->users as $user)
                                            <span class="inline-flex items-center rounded-xl px-3 py-1 text-xs ring-1 ring-white/10 bg-white/10 text-slate-200">
                                                {{ $user->name }}
                                            </span>
                                        @empty
                                            <span class="text-slate-500 text-sm">Nav pievienotu lietotāju</span>
                                        @endforelse
                                    </div>
                                </td>

                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap items-center justify-center gap-2">

                                        {{-- Up --}}
                                        <form action="{{ route('processes.update', $process) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="swap" value="up">
                                            <button type="submit"
                                                class="inline-flex items-center justify-center h-9 w-9 rounded-xl bg-white/5 hover:bg-white/10 text-slate-200 ring-1 ring-white/10 transition"
                                                title="Pārvietot uz augšu">
                                                ▲
                                            </button>
                                        </form>

                                        {{-- Down --}}
                                        <form action="{{ route('processes.update', $process) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="swap" value="down">
                                            <button type="submit"
                                                class="inline-flex items-center justify-center h-9 w-9 rounded-xl bg-white/5 hover:bg-white/10 text-slate-200 ring-1 ring-white/10 transition"
                                                title="Pārvietot uz leju">
                                                ▼
                                            </button>
                                        </form>

                                        {{-- Edit --}}
                                        <a href="{{ route('processes.edit', $process) }}"
                                           class="px-3 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-slate-200 text-sm ring-1 ring-white/10 transition">
                                            Rediģēt
                                        </a>

                                        {{-- Delete --}}
                                        <form action="{{ route('processes.destroy', $process) }}"
                                              method="POST"
                                              onsubmit="return confirm('Vai tiešām vēlaties dzēst šo procesu?');"
                                              class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="px-3 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-red-300 hover:text-red-200 text-sm ring-1 ring-white/10 transition">
                                                Dzēst
                                            </button>
                                        </form>

                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-10 text-slate-400">
                                    Nav pieejamu procesu.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6 h-1 mx-4 sm:mx-6 lg:mx-[100px] bg-gradient-to-r from-transparent via-red-600/40 to-transparent rounded"></div>

        </div>
    </div>
</x-app-layout>