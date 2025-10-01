<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Izveidot jaunu pasūtījumu
        </h2>
    </x-slot>

    {{-- Alpine.js (remove if already included) --}}
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <div class="py-6">
        <div class="max-w-4xl mx-auto bg-white p-6 shadow-md rounded">
            <form action="{{ route('orders.store') }}" method="POST">
                @csrf

                {{-- Klients (searchable select) --}}
                <div class="mb-4">
                    <label class="block mb-1 font-semibold">Izvēlieties klientu</label>

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
                               class="w-full border rounded px-3 py-2"
                               autocomplete="off">
                        <input type="hidden" :name="name" :value="value">
                        <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-500">▾</span>

                        <ul x-show="open"
                            x-transition
                            @mousedown.prevent
                            class="absolute left-0 right-0 z-20 mt-1 bg-white border rounded shadow max-h-60 overflow-auto">
                            <template x-for="(opt, i) in filtered" :key="opt.value">
                                <li @click="choose(i)"
                                    @mouseenter="activeIndex = i"
                                    :class="i === activeIndex ? 'bg-blue-50' : ''"
                                    class="px-3 py-2 cursor-pointer"
                                    x-text="opt.label"></li>
                            </template>
                            <li x-show="filtered.length === 0" class="px-3 py-2 text-slate-500">Nav rezultātu…</li>
                        </ul>
                    </div>
                </div>

                <div class="mb-4" id="one_time_client_div" style="display:none;">
                    <label class="block mb-1 font-semibold">Vienreizējs klienta nosaukums</label>
                    <input type="text" name="klients" class="w-full border rounded px-3 py-2" placeholder="Piem. Jauns Klients">
                </div>

                {{-- Produkts (searchable select) --}}
                <div class="mb-4">
                    <label class="block mb-1 font-semibold">Izvēlieties produktu</label>

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
                               class="w-full border rounded px-3 py-2"
                               autocomplete="off">
                        <input type="hidden" :name="name" :value="value">
                        <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-500">▾</span>

                        <ul x-show="open"
                            x-transition
                            @mousedown.prevent
                            class="absolute left-0 right-0 z-20 mt-1 bg-white border rounded shadow max-h-60 overflow-auto">
                            <template x-for="(opt, i) in filtered" :key="opt.value">
                                <li @click="choose(i)"
                                    @mouseenter="activeIndex = i"
                                    :class="i === activeIndex ? 'bg-blue-50' : ''"
                                    class="px-3 py-2 cursor-pointer"
                                    x-text="opt.label"></li>
                            </template>
                            <li x-show="filtered.length === 0" class="px-3 py-2 text-slate-500">Nav rezultātu…</li>
                        </ul>
                    </div>
                </div>

                <div class="mb-4" id="one_time_product_div" style="display:none;">
                    <label class="block mb-1 font-semibold">Vienreizējs produkta nosaukums</label>
                    <input type="text" name="produkts" class="w-full border rounded px-3 py-2">
                </div>

                {{-- Daudzums --}}
                <div class="mb-4">
                    <label class="block mb-1 font-semibold">Daudzums</label>
                    <input type="number" name="daudzums" class="w-full border rounded px-3 py-2" required>
                </div>

                {{-- Izpildes datums --}}
                <div class="mb-4">
                    <label class="block mb-1 font-semibold">Izpildes datums</label>
                    <input type="date" name="izpildes_datums" class="w-full border rounded px-3 py-2" required>
                </div>

                {{-- Prioritāte --}}
                <div class="mb-4">
                    <label class="block mb-1 font-semibold">Prioritāte</label>
                    <select name="prioritāte" class="w-full border rounded px-3 py-2">
                        <option value="zema">Zema</option>
                        <option value="normāla" selected>Normāla</option>
                        <option value="augsta">Augsta</option>
                    </select>
                </div>

                {{-- Piezīmes --}}
                <div class="mb-4">
                    <label class="block mb-1 font-semibold">Piezīmes</label>
                    <textarea name="piezimes" class="w-full border rounded px-3 py-2"></textarea>
                </div>

                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    Saglabāt pasūtījumu
                </button>
            </form>
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
