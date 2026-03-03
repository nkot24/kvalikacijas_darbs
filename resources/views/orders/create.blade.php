<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-white leading-tight">
                Izveidot jaunu pasūtījumu
            </h2>

            <a href="{{ route('orders.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-slate-200 ring-1 ring-white/10 transition">
                ← Atpakaļ
            </a>
        </div>
    </x-slot>

    {{-- Alpine.js (remove if already included) --}}
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6">
            <div class="rounded-2xl border border-white/10 bg-white/5 backdrop-blur shadow-xl">
                <div class="p-6 sm:p-7">
                    <form action="{{ route('orders.store') }}" method="POST" class="space-y-5">
                        @csrf

                        {{-- Klients (searchable select) --}}
                        <div>
                            <label class="block mb-2 text-sm font-semibold text-slate-200">Izvēlieties klientu</label>

                            <div class="relative w-full"
                                 x-data='selectBox({
                                    name: "client_id",
                                    placeholder: "-- Nav atlasīts --",
                                    options: [
                                        { value: "", label: "-- Nav atlasīts --" },
                                        { value: "vienreizējs", label: "Vienreizējs klients" },
                                        @foreach ($clients as $c)
                                            { value: "{{ $c->id }}", label: @json($c->nosaukums) },
                                        @endforeach
                                    ],
                                    onChange: (v) => toggleOneTimeClient(v)
                                 })'>

                                <input type="text"
                                       x-model="search"
                                       @focus="open = true"
                                       @click="open = true"
                                       @keydown.arrow-down.prevent="move(1)"
                                       @keydown.arrow-up.prevent="move(-1)"
                                       @keydown.enter.prevent="choose(activeIndex)"
                                       @keydown.escape="open = false"
                                       @click.outside="open = false"
                                       :placeholder="placeholder"
                                       class="w-full rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-sm text-white placeholder:text-slate-500
                                              focus:border-red-500/50 focus:ring-red-500/20"
                                       autocomplete="off">

                                <input type="hidden" :name="name" :value="value">
                                <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-500">▾</span>

                                <ul x-show="open"
                                    x-transition
                                    @mousedown.prevent
                                    class="absolute left-0 right-0 z-50 mt-2 rounded-xl border border-white/10 bg-[#0F172A]/95 backdrop-blur shadow-2xl
                                           max-h-60 overflow-auto">

                                    <template x-for="(opt, i) in filtered" :key="opt.value">
                                        <li @click="choose(i)"
                                            @mouseenter="activeIndex = i"
                                            :class="i === activeIndex ? 'bg-white/10 text-white' : 'text-slate-200'"
                                            class="px-3 py-2 cursor-pointer transition">
                                            <span x-text="opt.label"></span>
                                        </li>
                                    </template>

                                    <li x-show="filtered.length === 0" class="px-3 py-2 text-slate-400">
                                        Nav rezultātu…
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="hidden" id="one_time_client_div" style="display:none;">
                            <label class="block mb-2 text-sm font-semibold text-slate-200">Vienreizējs klienta nosaukums</label>
                            <input type="text"
                                   name="klients"
                                   class="w-full rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-sm text-white placeholder:text-slate-500
                                          focus:border-red-500/50 focus:ring-red-500/20"
                                   placeholder="Piem. Jauns Klients">
                        </div>

                        {{-- Produkts (searchable select) --}}
                        <div>
                            <label class="block mb-2 text-sm font-semibold text-slate-200">Izvēlieties produktu</label>

                            <div class="relative w-full"
                                 x-data='selectBox({
                                    name: "products_id",
                                    placeholder: "-- Nav atlasīts --",
                                    options: [
                                        { value: "", label: "-- Nav atlasīts --" },
                                        { value: "vienreizējs", label: "Vienreizējs produkts" },
                                        @foreach ($products as $p)
                                            { value: "{{ $p->id }}", label: @json($p->nosaukums) },
                                        @endforeach
                                    ],
                                    onChange: (v) => toggleOneTimeProduct(v)
                                 })'>

                                <input type="text"
                                       x-model="search"
                                       @focus="open = true"
                                       @click="open = true"
                                       @keydown.arrow-down.prevent="move(1)"
                                       @keydown.arrow-up.prevent="move(-1)"
                                       @keydown.enter.prevent="choose(activeIndex)"
                                       @keydown.escape="open = false"
                                       @click.outside="open = false"
                                       :placeholder="placeholder"
                                       class="w-full rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-sm text-white placeholder:text-slate-500
                                              focus:border-red-500/50 focus:ring-red-500/20"
                                       autocomplete="off">

                                <input type="hidden" :name="name" :value="value">
                                <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-500">▾</span>

                                <ul x-show="open"
                                    x-transition
                                    @mousedown.prevent
                                    class="absolute left-0 right-0 z-50 mt-2 rounded-xl border border-white/10 bg-[#0F172A]/95 backdrop-blur shadow-2xl
                                           max-h-60 overflow-auto">

                                    <template x-for="(opt, i) in filtered" :key="opt.value">
                                        <li @click="choose(i)"
                                            @mouseenter="activeIndex = i"
                                            :class="i === activeIndex ? 'bg-white/10 text-white' : 'text-slate-200'"
                                            class="px-3 py-2 cursor-pointer transition">
                                            <span x-text="opt.label"></span>
                                        </li>
                                    </template>

                                    <li x-show="filtered.length === 0" class="px-3 py-2 text-slate-400">
                                        Nav rezultātu…
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="hidden" id="one_time_product_div" style="display:none;">
                            <label class="block mb-2 text-sm font-semibold text-slate-200">Vienreizējs produkta nosaukums</label>
                            <input type="text"
                                   name="produkts"
                                   class="w-full rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-sm text-white placeholder:text-slate-500
                                          focus:border-red-500/50 focus:ring-red-500/20">
                        </div>

                        {{-- Two-column fields --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            {{-- Daudzums --}}
                            <div>
                                <label class="block mb-2 text-sm font-semibold text-slate-200">Daudzums</label>
                                <input type="number"
                                       name="daudzums"
                                       class="w-full rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-sm text-white placeholder:text-slate-500
                                              focus:border-red-500/50 focus:ring-red-500/20"
                                       required>
                            </div>

                            {{-- Izpildes datums --}}
                            <div>
                                <label class="block mb-2 text-sm font-semibold text-slate-200">Izpildes datums</label>
                                <input type="date"
                                       name="izpildes_datums"
                                       class="w-full rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-sm text-white
                                              focus:border-red-500/50 focus:ring-red-500/20"
                                       required>
                            </div>

                            {{-- Prioritāte --}}
                            <div class="sm:col-span-2">
                                <label class="block mb-2 text-sm font-semibold text-slate-200">Prioritāte</label>
                                <select name="prioritāte"
                                        class="w-full rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-sm text-white
                                               focus:border-red-500/50 focus:ring-red-500/20">
                                    <option class="bg-[#0B0F14]" value="zema">Zema</option>
                                    <option class="bg-[#0B0F14]" value="normāla" selected>Normāla</option>
                                    <option class="bg-[#0B0F14]" value="augsta">Augsta</option>
                                </select>
                            </div>
                        </div>

                        {{-- Piezīmes --}}
                        <div>
                            <label class="block mb-2 text-sm font-semibold text-slate-200">Piezīmes</label>
                            <textarea name="piezimes"
                                      rows="4"
                                      class="w-full rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-sm text-white placeholder:text-slate-500
                                             focus:border-red-500/50 focus:ring-red-500/20"></textarea>
                        </div>

                        {{-- Submit --}}
                        <div class="pt-2 flex items-center gap-3">
                            <button type="submit"
                                    class="bg-red-600 hover:bg-red-700 text-white px-6 py-2.5 rounded-xl font-semibold shadow">
                                Saglabāt pasūtījumu
                            </button>

                            <a href="{{ route('orders.index') }}"
                               class="px-5 py-2.5 rounded-xl bg-white/5 hover:bg-white/10 text-slate-200 ring-1 ring-white/10 transition">
                                Atcelt
                            </a>
                        </div>

                    </form>
                </div>
            </div>

            {{-- subtle divider glow --}}
            <div class="mt-6 h-1 bg-gradient-to-r from-transparent via-red-600/40 to-transparent rounded"></div>
        </div>
    </div>

    <script>
        // Toggle "vienreizējs" fields
        function toggleOneTimeClient(v){ document.getElementById('one_time_client_div').style.display = (v==='vienreizējs')?'block':'none'; }
        function toggleOneTimeProduct(v){ document.getElementById('one_time_product_div').style.display = (v==='vienreizējs')?'block':'none'; }

        // Minimal reusable searchable select
        function selectBox({ name, options, onChange, placeholder = '' }) {
            return {
                name, options, onChange, placeholder,
                open: false,
                search: '',
                value: '',
                activeIndex: 0,
                get filtered(){
                    const q = this.search.toLowerCase().trim();
                    return q ? this.options.filter(o => o.label.toLowerCase().includes(q)) : this.options;
                },
                move(step){
                    if(!this.open) this.open = true;
                    const max = this.filtered.length - 1;
                    this.activeIndex = Math.min(Math.max(this.activeIndex + step, 0), max);
                },
                choose(i){
                    const opt = this.filtered[i]; if(!opt) return;
                    this.value = opt.value;
                    this.search = opt.label;   // show label in the text field
                    this.open = false;
                    if (this.onChange) this.onChange(this.value);
                }
            }
        }
    </script>
</x-app-layout>