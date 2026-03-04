<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-white leading-tight">
                Rediģēt procesu
            </h2>
            <div class="hidden sm:block text-sm text-slate-400">
                Procesi • Labojumi
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-0">

            {{-- Card --}}
            <div class="rounded-2xl border border-white/10 bg-white/5 backdrop-blur shadow-xl">
                <form action="{{ route('processes.update', $process) }}" method="POST" class="p-5 sm:p-6">
                    @csrf
                    @method('PUT')

                    {{-- Process name --}}
                    <div class="mb-5">
                        <label for="processa_nosaukums" class="block text-sm font-medium text-slate-200 mb-1">
                            Procesa nosaukums
                        </label>
                        <input
                            type="text"
                            name="processa_nosaukums"
                            id="processa_nosaukums"
                            value="{{ old('processa_nosaukums', $process->processa_nosaukums) }}"
                            required
                            class="w-full rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-sm text-white placeholder:text-slate-500
                                   focus:border-red-500/50 focus:ring-red-500/20"
                            placeholder="piem., Metināšana"
                        >
                        @error('processa_nosaukums')
                            <p class="mt-2 text-sm text-red-300">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Users --}}
                    <div class="mb-6">
                        <div class="flex items-center justify-between gap-3 mb-2">
                            <label class="block text-sm font-medium text-slate-200">
                                Pievienot lietotājus
                            </label>
                            <div class="text-xs text-slate-500">
                                Atzīmē, kuri lietotāji pieder šim procesam
                            </div>
                        </div>

                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @foreach ($users as $user)
                                    <label class="flex items-center gap-3 rounded-xl border border-white/10 bg-[#0B0F14]/40 px-3 py-2 hover:bg-white/5 transition">
                                        <input
                                            type="checkbox"
                                            name="user_ids[]"
                                            value="{{ $user->id }}"
                                            class="h-4 w-4 rounded border-white/20 bg-transparent text-red-600 focus:ring-red-500/20"
                                            {{ in_array($user->id, $selectedUsers) ? 'checked' : '' }}
                                        >
                                        <span class="text-sm text-slate-200">
                                            {{ $user->name }}
                                            <span class="text-xs text-slate-500">({{ $user->role }})</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>

                            @error('user_ids')
                                <p class="mt-3 text-sm text-red-300">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex flex-col sm:flex-row gap-3 sm:justify-end">
                        <a href="{{ route('processes.index') }}"
                           class="px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-slate-200 text-sm ring-1 ring-white/10 transition text-center">
                            Atcelt
                        </a>

                        <button type="submit"
                                class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-semibold shadow">
                            Saglabāt izmaiņas
                        </button>
                    </div>
                </form>
            </div>

            <div class="mt-6 h-1 bg-gradient-to-r from-transparent via-red-600/40 to-transparent rounded"></div>
        </div>
    </div>
</x-app-layout>