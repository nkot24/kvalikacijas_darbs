<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-white leading-tight">
                Izpildītie iepirkumi
            </h2>
            <div class="hidden sm:block text-sm text-slate-400">
                Meklēšana • Statusi • Vēsture
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

            {{-- Top Controls Card (same style as Orders index) --}}
            <div class="mx-4 sm:mx-6 lg:mx-[100px] mb-6 rounded-2xl border border-white/10 bg-white/5 backdrop-blur shadow-xl">
                <div class="p-4 sm:p-5 flex flex-col lg:flex-row lg:items-end justify-between gap-4 flex-wrap">

                    <div class="flex items-center gap-3">
                        <a href="{{ route('orderList.index') }}"
                           class="px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-slate-200 text-sm ring-1 ring-white/10 transition">
                            ← Atpakaļ uz aktīvajiem
                        </a>
                    </div>

                    {{-- Search --}}
                    <form method="GET" action="{{ route('orderList.completed') }}" class="w-full lg:w-auto">
                        <div class="grid grid-cols-1 lg:grid-cols-4 gap-3">
                            <div class="lg:col-span-3">
                                <label class="block text-xs text-slate-400 mb-1">
                                    Meklēt (Nosaukums / Piegādātājs)
                                </label>
                                <input
                                    type="text"
                                    name="q"
                                    value="{{ $q ?? '' }}"
                                    placeholder="piem., skrūves vai Būvniecības SIA"
                                    class="w-full rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-sm text-white placeholder:text-slate-500 focus:border-red-500/50 focus:ring-red-500/20"
                                >
                            </div>

                            <div class="flex items-end gap-3">
                                <button type="submit"
                                        class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-semibold shadow">
                                    Meklēt
                                </button>

                                <a href="{{ route('orderList.completed') }}"
                                   class="px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-slate-300 text-sm ring-1 ring-white/10 transition">
                                    Notīrīt
                                </a>
                            </div>
                        </div>
                    </form>

                </div>
            </div>

            {{-- Table Card (EXACT same style as Orders index) --}}
            <div class="overflow-x-auto mx-2 sm:mx-4 lg:mx-[100px] rounded-2xl border border-white/10 bg-white/5 backdrop-blur shadow-xl">
                <table class="table-auto w-full text-xs sm:text-sm">
                    <thead class="bg-white/5">
                        <tr class="text-left text-slate-200">
                            <th class="px-4 py-3 whitespace-nowrap">Nosaukums</th>
                            <th class="px-4 py-3 whitespace-nowrap text-right">Daudzums</th>
                            <th class="px-4 py-3 whitespace-nowrap">Foto</th>
                            <th class="px-4 py-3 whitespace-nowrap">Statuss</th>
                            <th class="px-4 py-3 whitespace-nowrap">Izveidoja</th>
                            <th class="px-4 py-3 whitespace-nowrap">Piegādātājs</th>
                            <th class="px-4 py-3 whitespace-nowrap">Kad pasūtīts</th>
                            <th class="px-4 py-3 whitespace-nowrap">Kad jāatnāk</th>
                            <th class="px-4 py-3 whitespace-nowrap">Kad atnāca</th>
                            <th class="px-4 py-3 whitespace-nowrap text-center">Darbības</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-white/10">
                        @forelse ($completed as $order)
                            <tr class="hover:bg-white/5 transition-colors">
                                <td class="px-4 py-3 max-w-[260px] truncate text-white">
                                    {{ $order->name }}
                                </td>

                                <td class="px-4 py-3 max-w-[120px] truncate text-right text-slate-200">
                                    {{ $order->quantity }}
                                </td>

                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if($order->photo_path)
                                        <a href="{{ asset('storage/'.$order->photo_path) }}" target="_blank" class="inline-block">
                                            <img src="{{ asset('storage/'.$order->photo_path) }}"
                                                 alt="foto"
                                                 class="h-10 w-10 object-cover rounded-xl ring-1 ring-white/10">
                                        </a>
                                    @else
                                        <span class="text-slate-500">—</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 max-w-[180px] truncate">
                                    <span class="inline-flex items-center rounded-lg px-2.5 py-1 text-xs ring-1 ring-white/10 bg-emerald-500/15 text-emerald-200">
                                        {{ $order->status }}
                                    </span>
                                </td>

                                <td class="px-4 py-3 max-w-[200px] truncate text-slate-300">
                                    {{ optional($order->creator)->name ?? '—' }}
                                </td>

                                <td class="px-4 py-3 max-w-[220px] truncate text-slate-300">
                                    {{ $order->supplier_name ?? '—' }}
                                </td>

                                <td class="px-4 py-3 max-w-[160px] truncate text-slate-300">
                                    {{ $order->ordered_at?->format('Y-m-d') ?? '—' }}
                                </td>

                                <td class="px-4 py-3 max-w-[160px] truncate text-slate-300">
                                    {{ $order->expected_at?->format('Y-m-d') ?? '—' }}
                                </td>

                                <td class="px-4 py-3 max-w-[160px] truncate text-slate-200 font-semibold">
                                    {{ $order->arrived_at?->format('Y-m-d') ?? '—' }}
                                </td>

                                <td class="px-4 py-3 whitespace-nowrap text-center space-x-3">
                                    <a href="{{ route('orderList.edit', $order) }}"
                                       class="text-red-300 hover:text-red-200 hover:underline underline-offset-4">
                                        Skat./rediģēt
                                    </a>

                                    <form method="POST"
                                          class="inline"
                                          action="{{ route('orderList.destroy', $order) }}"
                                          onsubmit="return confirm('Vai tiešām dzēst šo ierakstu?');">
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
                                <td colspan="10" class="text-center py-10 text-slate-400">
                                    Nav izpildītu iepirkumu.
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