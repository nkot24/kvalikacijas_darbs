<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-white leading-tight">
                Pievienot lietotāju
            </h2>
            <div class="hidden sm:block text-sm text-slate-400">
                Lietotāji • Jauns ieraksts
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-xl mx-auto px-4 sm:px-6">

            {{-- Card --}}
            <div class="rounded-2xl border border-white/10 bg-white/5 backdrop-blur shadow-xl">
                <div class="p-6 sm:p-7">

                    <form method="POST" action="{{ route('users.store') }}" class="space-y-5">
                        @csrf

                        {{-- Name --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-200 mb-1">Vārds</label>
                            <input
                                type="text"
                                name="name"
                                required
                                class="w-full rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-sm text-white placeholder:text-slate-500
                                       focus:border-red-500/50 focus:ring-red-500/20"
                                placeholder="Piem., Jānis Bērziņš"
                            >
                        </div>

                        {{-- Role --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-200 mb-1">Loma</label>
                            <select
                                name="role"
                                required
                                class="w-full rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-sm text-white
                                       focus:border-red-500/50 focus:ring-red-500/20"
                            >
                                <option value="" class="text-slate-900">-- Izvēlieties --</option>
                                <option value="admin" class="text-slate-900">Administrators</option>
                                <option value="worker" class="text-slate-900">Darbinieks</option>
                            </select>
                            <p class="mt-1 text-xs text-slate-400">Izvēlieties lietotāja piekļuves līmeni sistēmā.</p>
                        </div>

                        {{-- Actions --}}
                        <div class="flex flex-col sm:flex-row sm:justify-end gap-3 pt-2">
                            <a href="{{ route('users.index') }}"
                               class="inline-flex justify-center px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-slate-200 text-sm ring-1 ring-white/10 transition">
                                Atcelt
                            </a>
                            <button type="submit"
                                    class="inline-flex justify-center px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-semibold shadow">
                                Saglabāt
                            </button>
                        </div>
                    </form>

                </div>
            </div>

            <div class="mt-6 h-1 bg-gradient-to-r from-transparent via-red-600/40 to-transparent rounded"></div>
        </div>
    </div>
</x-app-layout>