<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-white leading-tight">
                Pasūtījumu saraksts
            </h2>

            <a href="{{ route('orderList.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white font-semibold shadow">
                + Pievienot jaunu iepirkumu
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-full mx-auto">

            @if (session('success'))
                <div class="mx-4 sm:mx-6 lg:mx-[100px] mb-5 rounded-2xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-emerald-200">
                    <div class="font-semibold">Veiksmīgi!</div>
                    <div class="text-sm text-emerald-200/90">{{ session('success') }}</div>
                </div>
            @endif

            {{-- Table Card (same style as Orders index) --}}
            <div class="overflow-x-auto mx-2 sm:mx-4 lg:mx-[100px] rounded-2xl border border-white/10 bg-white/5 backdrop-blur shadow-xl">
                <table class="table-auto w-full text-xs sm:text-sm">
                    <thead class="bg-white/5">
                        <tr class="text-left text-slate-200">
                            <th class="px-4 py-3 whitespace-nowrap">Nosaukums</th>
                            <th class="px-4 py-3 whitespace-nowrap text-right">Daudzums</th>
                            <th class="px-4 py-3 whitespace-nowrap">Foto</th>
                            <th class="px-4 py-3 whitespace-nowrap">Statuss</th>
                            <th class="px-4 py-3 whitespace-nowrap hidden md:table-cell">Izveidoja</th>
                            <th class="px-4 py-3 whitespace-nowrap hidden lg:table-cell">Piegādātājs</th>
                            <th class="px-4 py-3 whitespace-nowrap hidden md:table-cell">Kad pasūtīts</th>
                            <th class="px-4 py-3 whitespace-nowrap hidden lg:table-cell">Kad jāatnāk</th>
                            <th class="px-4 py-3 whitespace-nowrap hidden xl:table-cell">Kad atnāca</th>
                            <th class="px-4 py-3 whitespace-nowrap text-center">Darbības</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-white/10">
                        @forelse ($orderList as $order)
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
                                    @php
                                        $badge = match($order->status) {
                                            'pasūtīts' => 'bg-amber-500/15 text-amber-200',
                                            'saņemts'  => 'bg-emerald-500/15 text-emerald-200',
                                            default    => 'bg-white/5 text-slate-200',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center rounded-lg px-2.5 py-1 text-xs ring-1 ring-white/10 {{ $badge }}">
                                        {{ $order->status }}
                                    </span>
                                </td>

                                <td class="px-4 py-3 max-w-[200px] truncate text-slate-300 hidden md:table-cell">
                                    {{ optional($order->creator)->name ?? '—' }}
                                </td>

                                <td class="px-4 py-3 max-w-[220px] truncate text-slate-300 hidden lg:table-cell">
                                    {{ $order->supplier_name ?? '—' }}
                                </td>

                                <td class="px-4 py-3 max-w-[160px] truncate text-slate-300 hidden md:table-cell">
                                    {{ $order->ordered_at?->format('Y-m-d') ?? '—' }}
                                </td>

                                <td class="px-4 py-3 max-w-[160px] truncate text-slate-300 hidden lg:table-cell">
                                    {{ $order->expected_at?->format('Y-m-d') ?? '—' }}
                                </td>

                                <td class="px-4 py-3 max-w-[160px] truncate text-slate-300 hidden xl:table-cell">
                                    {{ $order->arrived_at?->format('Y-m-d') ?? '—' }}
                                </td>

                                <td class="px-4 py-3 whitespace-nowrap text-center space-x-3">
                                    <a href="{{ route('orderList.edit', $order) }}"
                                       class="text-red-300 hover:text-red-200 hover:underline underline-offset-4">
                                        Rediģēt
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
                                    Nav pieejamu iepirkumu.
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