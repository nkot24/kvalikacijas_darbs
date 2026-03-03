<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-white leading-tight">
                Pabeigtie pasūtījumi
            </h2>

            <a href="{{ route('orders.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-slate-200 ring-1 ring-white/10 transition">
                ← Atpakaļ uz aktīvajiem pasūtījumiem
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

            {{-- Search card --}}
            <div class="mx-4 sm:mx-6 lg:mx-[100px] mb-6 rounded-2xl border border-white/10 bg-white/5 backdrop-blur shadow-xl">
                <div class="p-4 sm:p-5 flex flex-col lg:flex-row lg:items-center justify-between gap-4 flex-wrap">

                    <form method="GET" action="{{ route('orders.complete') }}" class="flex flex-wrap gap-2 items-center w-full lg:w-auto">
                        <div class="relative w-full sm:w-72">
                            <input type="text"
                                   name="search"
                                   value="{{ request('search') }}"
                                   placeholder="Meklēt..."
                                   class="w-full rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-sm text-white placeholder:text-slate-500
                                          focus:border-red-500/50 focus:ring-red-500/20" />
                        </div>

                        <button type="submit"
                                class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-semibold shadow">
                            Meklēt
                        </button>

                        @if(request('search'))
                            <a href="{{ route('orders.complete') }}"
                               class="px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-slate-300 text-sm ring-1 ring-white/10 transition">
                                Notīrīt
                            </a>
                        @endif
                    </form>

                    <div class="hidden lg:flex items-center text-sm text-slate-400">
                        Pabeigtie pasūtījumi • Meklēšana • Kārtošana
                    </div>
                </div>
            </div>

            <!-- Sort Helper -->
            @php
                function sortLinkComplete($column, $label) {
                    $isCurrent = request('sort') === $column;
                    $direction = $isCurrent && request('direction') === 'asc' ? 'desc' : 'asc';
                    $arrow = $isCurrent ? (request('direction') === 'asc' ? '⬆️' : '⬇️') : '';
                    $query = array_merge(request()->all(), ['sort' => $column, 'direction' => $direction]);
                    return '<a href="'.route('orders.complete', $query).'" class="inline-flex items-center gap-1 hover:text-white hover:underline text-slate-200">'.$label.' <span class="text-xs text-slate-400">'.$arrow.'</span></a>';
                }
            @endphp

            {{-- Table (PC no scroll, phone scroll) --}}
            <div class="mx-2 sm:mx-4 lg:mx-[100px] rounded-2xl border border-white/10 bg-white/5 backdrop-blur shadow-xl">
                <div class="overflow-x-auto lg:overflow-visible">
                    <table class="w-full text-sm table-fixed lg:table-auto">
                        <thead class="bg-white/5">
                            <tr class="text-left text-slate-200">
                                <th class="px-4 py-3 whitespace-nowrap">
                                    {!! sortLinkComplete('pasutijuma_numurs', 'Pasūtījuma numurs') !!}
                                </th>
                                <th class="px-4 py-3 whitespace-nowrap">
                                    {!! sortLinkComplete('datums', 'Datums') !!}
                                </th>
                                <th class="px-4 py-3 whitespace-nowrap">
                                    {!! sortLinkComplete('klients', 'Klients') !!}
                                </th>
                                <th class="px-4 py-3 whitespace-nowrap hidden lg:table-cell">
                                    Produkts
                                </th>
                                <th class="px-4 py-3 whitespace-nowrap text-center">
                                    {!! sortLinkComplete('daudzums', 'Daudzums') !!}
                                </th>
                                <th class="px-4 py-3 whitespace-nowrap hidden md:table-cell">
                                    {!! sortLinkComplete('izpildes_datums', 'Izpildes datums') !!}
                                </th>
                                <th class="px-4 py-3 whitespace-nowrap hidden lg:table-cell">
                                    {!! sortLinkComplete('prioritāte', 'Prioritāte') !!}
                                </th>
                                <th class="px-4 py-3 whitespace-nowrap">
                                    {!! sortLinkComplete('statuss', 'Statuss') !!}
                                </th>
                                <th class="px-4 py-3 whitespace-nowrap hidden xl:table-cell">
                                    Piezīmes
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-white/10">
                            @forelse ($orders as $order)
                                <tr
                                    onclick="window.location='{{ route('orders.show', $order->id) }}'"
                                    class="cursor-pointer hover:bg-white/5 transition-colors"
                                    title="Klikšķiniet, lai atvērtu pasūtījumu"
                                >
                                    <td class="px-4 py-3 text-white break-words">
                                        {{ $order->pasutijuma_numurs }}
                                    </td>

                                    <td class="px-4 py-3 text-slate-200 break-words">
                                        {{ $order->datums }}
                                    </td>

                                    <td class="px-4 py-3 text-slate-200 break-words">
                                        {{ $order->client->nosaukums ?? $order->klients }}
                                    </td>

                                    <td class="px-4 py-3 text-slate-200 break-words hidden lg:table-cell">
                                        {{ $order->product->nosaukums ?? $order->produkts }}
                                    </td>

                                    <td class="px-4 py-3 text-center text-slate-200">
                                        {{ $order->daudzums }}
                                    </td>

                                    <td class="px-4 py-3 text-slate-200 break-words hidden md:table-cell">
                                        {{ $order->izpildes_datums }}
                                    </td>

                                    <td class="px-4 py-3 text-slate-200 break-words hidden lg:table-cell">
                                        {{ $order->prioritāte }}
                                    </td>

                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center rounded-lg px-2.5 py-1 text-xs ring-1 ring-white/10 bg-white/10 text-slate-100">
                                            {{ $order->statuss }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-3 text-slate-300 break-words hidden xl:table-cell">
                                        {{ $order->piezimes ?? '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-10 text-slate-400">
                                        Nav pabeigtu pasūtījumu.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-6 mx-4 sm:mx-6 lg:mx-[100px]">
                <div class="rounded-2xl border border-white/10 bg-white/5 backdrop-blur p-4">
                    {{ $orders->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>