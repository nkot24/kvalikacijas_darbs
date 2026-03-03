<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-white leading-tight">
                Rediģēt pasūtījumu
            </h2>

            <a href="{{ route('orders.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-slate-200 ring-1 ring-white/10 transition">
                ← Atpakaļ
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6">
            <div class="rounded-2xl border border-white/10 bg-white/5 backdrop-blur shadow-xl">
                <div class="p-6 sm:p-7">

                    @if ($errors->any())
                        <div class="mb-5 rounded-2xl border border-red-500/20 bg-red-500/10 px-4 py-3 text-red-200">
                            <div class="font-semibold mb-1">Kļūda</div>
                            <ul class="list-disc pl-5 text-sm text-red-200/90 space-y-0.5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('orders.update', $order) }}" method="POST" class="space-y-5">
                        @csrf
                        @method('PUT')

                        <div>
                            <label for="client_id" class="block mb-2 text-sm font-semibold text-slate-200">
                                Izvēlēties klientu (vai atstāt tukšu):
                            </label>
                            <select name="client_id" id="client_id"
                                    class="w-full rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-sm text-white
                                           focus:border-red-500/50 focus:ring-red-500/20">
                                <option class="bg-[#0B0F14]" value="">— Nav izvēlēts —</option>
                                @foreach ($clients as $client)
                                    <option class="bg-[#0B0F14]" value="{{ $client->id }}" {{ $order->client_id == $client->id ? 'selected' : '' }}>
                                        {{ $client->nosaukums }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-slate-400">Ja izvēlies klientu, “Vienreizējs klients” nav vajadzīgs.</p>
                        </div>

                        <div>
                            <label for="klients" class="block mb-2 text-sm font-semibold text-slate-200">
                                Vienreizējs klienta nosaukums (ja nav izvēlēts):
                            </label>
                            <input type="text" name="klients" id="klients" value="{{ $order->klients }}"
                                   class="w-full rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-sm text-white placeholder:text-slate-500
                                          focus:border-red-500/50 focus:ring-red-500/20">
                        </div>

                        <div>
                            <label for="products_id" class="block mb-2 text-sm font-semibold text-slate-200">
                                Izvēlēties produktu (vai atstāt tukšu):
                            </label>
                            <select name="products_id" id="products_id"
                                    class="w-full rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-sm text-white
                                           focus:border-red-500/50 focus:ring-red-500/20">
                                <option class="bg-[#0B0F14]" value="">— Nav izvēlēts —</option>
                                @foreach ($products as $product)
                                    <option class="bg-[#0B0F14]" value="{{ $product->id }}" {{ $order->products_id == $product->id ? 'selected' : '' }}>
                                        {{ $product->nosaukums }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-slate-400">Ja izvēlies produktu, “Vienreizējs produkts” nav vajadzīgs.</p>
                        </div>

                        <div>
                            <label for="produkts" class="block mb-2 text-sm font-semibold text-slate-200">
                                Vienreizējs produkta nosaukums (ja nav izvēlēts):
                            </label>
                            <input type="text" name="produkts" id="produkts" value="{{ $order->produkts }}"
                                   class="w-full rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-sm text-white placeholder:text-slate-500
                                          focus:border-red-500/50 focus:ring-red-500/20">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="daudzums" class="block mb-2 text-sm font-semibold text-slate-200">Daudzums:</label>
                                <input type="number" name="daudzums" id="daudzums" value="{{ $order->daudzums }}" required
                                       class="w-full rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-sm text-white
                                              focus:border-red-500/50 focus:ring-red-500/20">
                            </div>

                            <div>
                                <label for="izpildes_datums" class="block mb-2 text-sm font-semibold text-slate-200">Izpildes datums:</label>
                                <input type="date" name="izpildes_datums" id="izpildes_datums" value="{{ $order->izpildes_datums }}"
                                       class="w-full rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-sm text-white
                                              focus:border-red-500/50 focus:ring-red-500/20">
                            </div>

                            <div>
                                <label for="prioritāte" class="block mb-2 text-sm font-semibold text-slate-200">Prioritāte:</label>
                                <select name="prioritāte" id="prioritāte"
                                        class="w-full rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-sm text-white
                                               focus:border-red-500/50 focus:ring-red-500/20">
                                    <option class="bg-[#0B0F14]" value="zema" {{ $order->prioritāte == 'zema' ? 'selected' : '' }}>Zema</option>
                                    <option class="bg-[#0B0F14]" value="normāla" {{ $order->prioritāte == 'normāla' ? 'selected' : '' }}>Normāla</option>
                                    <option class="bg-[#0B0F14]" value="augsta" {{ $order->prioritāte == 'augsta' ? 'selected' : '' }}>Augsta</option>
                                </select>
                            </div>

                            <div>
                                <label for="statuss" class="block mb-2 text-sm font-semibold text-slate-200">Statuss:</label>
                                <select name="statuss" id="statuss"
                                        class="w-full rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-sm text-white
                                               focus:border-red-500/50 focus:ring-red-500/20">
                                    <option class="bg-[#0B0F14]" value="nav nodots ražošanai" {{ $order->statuss == 'nav nodots ražošanai' ? 'selected' : '' }}>
                                        Nav nodots ražošanai
                                    </option>
                                    <option class="bg-[#0B0F14]" value="nodots ražošanai" {{ $order->statuss == 'nodots ražošanai' ? 'selected' : '' }}>
                                        Nodots ražošanai
                                    </option>
                                    <option class="bg-[#0B0F14]" value="pabeigts" {{ $order->statuss == 'pabeigts' ? 'selected' : '' }}>
                                        Pabeigts
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="piezimes" class="block mb-2 text-sm font-semibold text-slate-200">Piezīmes:</label>
                            <textarea name="piezimes" id="piezimes" rows="4"
                                      class="w-full rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-sm text-white placeholder:text-slate-500
                                             focus:border-red-500/50 focus:ring-red-500/20">{{ $order->piezimes }}</textarea>
                        </div>

                        <div class="pt-2 flex items-center justify-end gap-3">
                            <a href="{{ route('orders.show', $order) }}"
                               class="px-5 py-2.5 rounded-xl bg-white/5 hover:bg-white/10 text-slate-200 ring-1 ring-white/10 transition">
                                Atcelt
                            </a>

                            <button type="submit"
                                    class="bg-red-600 hover:bg-red-700 text-white px-6 py-2.5 rounded-xl font-semibold shadow">
                                Saglabāt izmaiņas
                            </button>
                        </div>

                    </form>
                </div>
            </div>

            <div class="mt-6 h-1 bg-gradient-to-r from-transparent via-red-600/40 to-transparent rounded"></div>
        </div>
    </div>
</x-app-layout>