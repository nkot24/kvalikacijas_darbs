<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-white leading-tight">
                Lietotāju saraksts
            </h2>
            <div class="hidden sm:block text-sm text-slate-400">
                Lietotāji • Imports • Eksports
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

            {{-- Controls Card (same style as orders.index top card) --}}
            <div class="mx-4 sm:mx-6 lg:mx-[100px] mb-6 rounded-2xl border border-white/10 bg-white/5 backdrop-blur shadow-xl">
                <div class="p-4 sm:p-5 flex flex-col lg:flex-row lg:items-center justify-between gap-4 flex-wrap">

                    <a href="{{ route('users.export') }}"
                       class="px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-white text-sm ring-1 ring-white/10 transition">
                        📤 Eksportēt lietotājus
                    </a>

                    <form action="{{ route('users.import') }}" method="POST" enctype="multipart/form-data"
                          class="flex flex-wrap items-center gap-2">
                        @csrf

                        <label class="text-sm text-slate-400">📥</label>

                        <input type="file" name="import_file"
                               class="text-xs sm:text-sm text-slate-300
                                      file:mr-2 file:py-2 file:px-3
                                      file:rounded-xl file:border-0
                                      file:text-xs sm:file:text-sm file:font-semibold
                                      file:bg-white/10 file:text-white hover:file:bg-white/15
                                      cursor-pointer"
                               required>

                        <button type="submit"
                                class="px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-white text-sm ring-1 ring-white/10 transition">
                            Augšupielādēt
                        </button>
                    </form>

                    <a href="{{ route('users.create') }}"
                       class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-semibold shadow">
                        + Pievienot lietotāju
                    </a>

                </div>
            </div>

            {{-- Table Card (EXACT same as orders.index) --}}
            <div class="overflow-x-auto mx-2 sm:mx-4 lg:mx-[100px] rounded-2xl border border-white/10 bg-white/5 backdrop-blur shadow-xl">
                <table class="table-auto w-full text-xs sm:text-sm">
                    <thead class="bg-white/5">
                        <tr class="text-left text-slate-200">
                            <th class="px-4 py-3 whitespace-nowrap">ID</th>
                            <th class="px-4 py-3 whitespace-nowrap">Vārds</th>
                            <th class="px-4 py-3 whitespace-nowrap">Loma</th>
                            <th class="px-4 py-3 whitespace-nowrap">Parole</th>
                            <th class="px-4 py-3 whitespace-nowrap text-center">Darbības</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-white/10">
                        @forelse ($users as $user)
                            <tr class="hover:bg-white/5 transition-colors">
                                <td class="px-4 py-3 max-w-[90px] truncate text-white">
                                    {{ $user->id }}
                                </td>

                                <td class="px-4 py-3 max-w-[240px] truncate text-slate-200">
                                    {{ $user->name }}
                                </td>

                                <td class="px-4 py-3 max-w-[160px] truncate text-slate-200">
                                    {{ ucfirst($user->role) }}
                                </td>

                                <td class="px-4 py-3 max-w-[320px] truncate text-slate-200 font-mono">
                                    {{ $user->visible_password ?? '-' }}
                                </td>

                                <td class="px-4 py-3 whitespace-nowrap text-center space-x-3">
                                    <a href="{{ route('users.edit', $user) }}"
                                       class="text-red-300 hover:text-red-200 hover:underline underline-offset-4">
                                        Rediģēt
                                    </a>

                                    <form method="POST"
                                          action="{{ route('users.destroy', $user) }}"
                                          class="inline"
                                          onsubmit="return confirm('Vai tiešām vēlaties dzēst šo lietotāju?');">
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
                                <td colspan="5" class="text-center py-10 text-slate-400">
                                    Nav pieejami lietotāji.
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