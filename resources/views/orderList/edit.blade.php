<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-white leading-tight">
                Apstrādāt iepirkumu
            </h2>

            <a href="{{ route('orderList.index') }}"
               class="text-sm text-slate-400 hover:text-white transition">
                ← Atpakaļ uz sarakstu
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto">

            {{-- Card --}}
            <div class="rounded-2xl border border-white/10 bg-white/5 backdrop-blur shadow-xl p-6 sm:p-8">

                {{-- Meta info --}}
                <div class="rounded-2xl border border-white/10 bg-[#0B0F14]/40 p-4 sm:p-5 mb-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                        <div class="text-slate-300">
                            <span class="text-slate-400 font-medium">Izveidoja:</span>
                            <span class="text-slate-200">{{ optional($order->creator)->name ?? '—' }}</span>
                        </div>

                        <div class="text-slate-300">
                            <span class="text-slate-400 font-medium">Izveidots:</span>
                            <span class="text-slate-200">{{ $order->created_at?->format('Y-m-d H:i') }}</span>
                        </div>

                        <div class="text-slate-300 sm:col-span-2">
                            <span class="text-slate-400 font-medium">Nosaukums:</span>
                            <span class="text-slate-200">{{ $order->name }}</span>
                            <span class="text-slate-500 mx-2">•</span>
                            <span class="text-slate-400 font-medium">Daudzums:</span>
                            <span class="text-slate-200">{{ $order->quantity }}</span>
                        </div>

                        <div class="text-slate-300 sm:col-span-2 flex items-center gap-3 flex-wrap">
                            <span class="text-slate-400 font-medium">Pašreizējais statuss:</span>
                            <span class="inline-flex items-center rounded-lg px-2.5 py-1 text-xs ring-1 ring-white/10 bg-white/5 text-slate-200">
                                {{ $order->status }}
                            </span>
                        </div>
                    </div>

                    @if($order->photo_path)
                        <div class="mt-4">
                            <a href="{{ asset('storage/'.$order->photo_path) }}" target="_blank" class="inline-block">
                                <img src="{{ asset('storage/'.$order->photo_path) }}"
                                     class="h-16 w-16 object-cover rounded-xl ring-1 ring-white/10"
                                     alt="foto" />
                            </a>
                        </div>
                    @endif
                </div>

                <form method="POST" action="{{ route('orderList.update', $order) }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PUT')

                    {{-- Supplier --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Piegādātājs</label>
                        <input type="text"
                               name="supplier_name"
                               value="{{ old('supplier_name', $order->supplier_name) }}"
                               class="w-full rounded-xl border border-white/10 bg-[#0B0F14]/60 px-4 py-2.5 text-white placeholder:text-slate-500
                                      focus:border-red-500/50 focus:ring-red-500/20">
                        @error('supplier_name')
                            <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Dates --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Kad pasūtīts</label>
                            <input type="date"
                                   name="ordered_at"
                                   value="{{ old('ordered_at', optional($order->ordered_at)->format('Y-m-d')) }}"
                                   class="w-full rounded-xl border border-white/10 bg-[#0B0F14]/60 px-4 py-2.5 text-white
                                          focus:border-red-500/50 focus:ring-red-500/20">
                            @error('ordered_at')
                                <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Kad jāatnāk</label>
                            <input type="date"
                                   name="expected_at"
                                   value="{{ old('expected_at', optional($order->expected_at)->format('Y-m-d')) }}"
                                   class="w-full rounded-xl border border-white/10 bg-[#0B0F14]/60 px-4 py-2.5 text-white
                                          focus:border-red-500/50 focus:ring-red-500/20">
                            @error('expected_at')
                                <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Kad atnāca</label>
                            <input type="date"
                                   name="arrived_at"
                                   value="{{ old('arrived_at', optional($order->arrived_at)->format('Y-m-d')) }}"
                                   class="w-full rounded-xl border border-white/10 bg-[#0B0F14]/60 px-4 py-2.5 text-white
                                          focus:border-red-500/50 focus:ring-red-500/20">
                            @error('arrived_at')
                                <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Photo --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Jauns foto (neobligāts)</label>
                        <input type="file"
                               name="photo"
                               accept="image/*"
                               class="w-full text-sm text-slate-300
                                      file:mr-4 file:py-2 file:px-4
                                      file:rounded-xl file:border-0
                                      file:text-sm file:font-semibold
                                      file:bg-white/10 file:text-white hover:file:bg-white/15
                                      cursor-pointer">
                        @error('photo')
                            <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Actions --}}
                    <div class="pt-2 flex flex-col sm:flex-row gap-3">
                        <button type="submit"
                                class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-semibold shadow transition">
                            Saglabāt
                        </button>

                        <a href="{{ route('orderList.index') }}"
                           class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-white/5 hover:bg-white/10 text-slate-200 ring-1 ring-white/10 transition text-center">
                            Atcelt
                        </a>
                    </div>
                </form>
            </div>

            <div class="mt-6 h-1 bg-gradient-to-r from-transparent via-red-600/40 to-transparent rounded"></div>
        </div>
    </div>
</x-app-layout>