<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-white leading-tight">
                Pievienot produktu
            </h2>
            <div class="hidden sm:block text-sm text-slate-400">
                Produkti • Izveide
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto">

            {{-- Form Card --}}
            <div class="mx-2 sm:mx-4 rounded-2xl border border-white/10 bg-white/5 backdrop-blur shadow-xl">
                <div class="p-5 sm:p-6">
                    <form method="POST" action="{{ route('products.store') }}">
                        @csrf

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">

                            {{-- Svītrkods --}}
                            <div>
                                <label class="block text-sm font-medium text-slate-200 mb-1">Svītrkods</label>
                                <input
                                    type="number"
                                    name="svitr_kods"
                                    required
                                    value="{{ old('svitr_kods') }}"
                                    class="w-full rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-sm text-white placeholder:text-slate-500 focus:border-red-500/50 focus:ring-red-500/20"
                                >
                            </div>

                            {{-- Nosaukums --}}
                            <div>
                                <label class="block text-sm font-medium text-slate-200 mb-1">Nosaukums</label>
                                <input
                                    type="text"
                                    name="nosaukums"
                                    required
                                    value="{{ old('nosaukums') }}"
                                    class="w-full rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-sm text-white placeholder:text-slate-500 focus:border-red-500/50 focus:ring-red-500/20"
                                >
                            </div>

                            {{-- Pārdošanas cena --}}
                            <div>
                                <label class="block text-sm font-medium text-slate-200 mb-1">Pārdošanas cena</label>
                                <input
                                    type="number"
                                    step="0.01"
                                    name="pardosanas_cena"
                                    required
                                    value="{{ old('pardosanas_cena') }}"
                                    class="w-full rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-sm text-white placeholder:text-slate-500 focus:border-red-500/50 focus:ring-red-500/20"
                                >
                            </div>

                            {{-- Vairumtirdzniecības cena --}}
                            <div>
                                <label class="block text-sm font-medium text-slate-200 mb-1">Vairumtirdzniecības cena</label>
                                <input
                                    type="number"
                                    step="0.01"
                                    name="vairumtirdzniecibas_cena"
                                    value="{{ old('vairumtirdzniecibas_cena') }}"
                                    class="w-full rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-sm text-white placeholder:text-slate-500 focus:border-red-500/50 focus:ring-red-500/20"
                                >
                            </div>

                            {{-- Daudzums noliktavā --}}
                            <div>
                                <label class="block text-sm font-medium text-slate-200 mb-1">Daudzums noliktavā</label>
                                <input
                                    type="number"
                                    name="daudzums_noliktava"
                                    value="{{ old('daudzums_noliktava') }}"
                                    class="w-full rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-sm text-white placeholder:text-slate-500 focus:border-red-500/50 focus:ring-red-500/20"
                                >
                            </div>

                            {{-- Svars --}}
                            <div>
                                <label class="block text-sm font-medium text-slate-200 mb-1">Svars (neto, kg)</label>
                                <input
                                    type="number"
                                    step="0.01"
                                    name="svars_neto"
                                    value="{{ old('svars_neto') }}"
                                    class="w-full rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-sm text-white placeholder:text-slate-500 focus:border-red-500/50 focus:ring-red-500/20"
                                >
                            </div>

                            {{-- Nom grupa --}}
                            <div>
                                <label class="block text-sm font-medium text-slate-200 mb-1">Nomenklatūras grupas kods</label>
                                <input
                                    type="text"
                                    name="nomGr_kods"
                                    required
                                    value="{{ old('nomGr_kods') }}"
                                    class="w-full rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-sm text-white placeholder:text-slate-500 focus:border-red-500/50 focus:ring-red-500/20"
                                >
                            </div>

                            {{-- Garums --}}
                            <div>
                                <label class="block text-sm font-medium text-slate-200 mb-1">Garums (mm)</label>
                                <input
                                    type="number"
                                    step="0.01"
                                    name="garums"
                                    value="{{ old('garums') }}"
                                    class="w-full rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-sm text-white placeholder:text-slate-500 focus:border-red-500/50 focus:ring-red-500/20"
                                >
                            </div>

                            {{-- Platums --}}
                            <div>
                                <label class="block text-sm font-medium text-slate-200 mb-1">Platums (mm)</label>
                                <input
                                    type="number"
                                    step="0.01"
                                    name="platums"
                                    value="{{ old('platums') }}"
                                    class="w-full rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-sm text-white placeholder:text-slate-500 focus:border-red-500/50 focus:ring-red-500/20"
                                >
                            </div>

                            {{-- Augstums --}}
                            <div>
                                <label class="block text-sm font-medium text-slate-200 mb-1">Augstums (mm)</label>
                                <input
                                    type="number"
                                    step="0.01"
                                    name="augstums"
                                    value="{{ old('augstums') }}"
                                    class="w-full rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-sm text-white placeholder:text-slate-500 focus:border-red-500/50 focus:ring-red-500/20"
                                >
                            </div>

                        </div>

                        {{-- Actions --}}
                        <div class="mt-6 flex flex-col sm:flex-row justify-end gap-3">
                            <a href="{{ route('products.index') }}"
                               class="px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-slate-200 text-sm ring-1 ring-white/10 transition text-center">
                                Atcelt
                            </a>

                            <button type="submit"
                                    class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-semibold shadow">
                                Saglabāt
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="mt-6 h-1 mx-2 sm:mx-4 bg-gradient-to-r from-transparent via-red-600/40 to-transparent rounded"></div>
        </div>
    </div>
</x-app-layout>