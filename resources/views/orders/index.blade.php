<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-white leading-tight">
                Pasūtījumu saraksts
            </h2>
            <div class="hidden sm:block text-sm text-slate-400">
                Pārvaldība • Eksports/Imports • Statusi
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

            {{-- Top Controls Card --}}
            <div class="mx-4 sm:mx-6 lg:mx-[100px] mb-6 rounded-2xl border border-white/10 bg-white/5 backdrop-blur shadow-xl">
                <div class="p-4 sm:p-5 flex flex-col lg:flex-row lg:items-center justify-between gap-4 flex-wrap">

                    {{-- Search --}}
                    <form method="GET" action="{{ route('orders.index') }}" class="flex flex-wrap gap-2 items-center w-full lg:w-auto">
                        <div class="relative w-full sm:w-72">
                            <input
                                type="text"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="Meklēt..."
                                class="w-full rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-sm text-white placeholder:text-slate-500 focus:border-red-500/50 focus:ring-red-500/20"
                            />
                            <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-slate-500 text-sm">
                                ⌘K
                            </div>
                        </div>

                        <button
                            type="submit"
                            class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-semibold shadow"
                        >
                            Meklēt
                        </button>

                        @if(request('search'))
                            <a
                                href="{{ route('orders.index') }}"
                                class="px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-slate-300 text-sm ring-1 ring-white/10 transition"
                            >
                                Notīrīt
                            </a>
                        @endif
                    </form>

                    {{-- Actions --}}
                    <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
                        <a
                            href="{{ route('orders.fullExport') }}"
                            class="px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-white text-sm ring-1 ring-white/10 transition"
                        >
                            📤 Eksportēt
                        </a>

                        <form
                            action="{{ route('orders.fullImport') }}"
                            method="POST"
                            enctype="multipart/form-data"
                            class="flex flex-wrap items-center gap-2"
                        >
                            @csrf

                            <label class="text-sm text-slate-400">📥</label>

                            <input
                                type="file"
                                name="file"
                                class="text-xs sm:text-sm text-slate-300
                                       file:mr-2 file:py-2 file:px-3
                                       file:rounded-xl file:border-0
                                       file:text-xs sm:file:text-sm file:font-semibold
                                       file:bg-white/10 file:text-white hover:file:bg-white/15
                                       cursor-pointer"
                                required
                            >

                            <button
                                type="submit"
                                class="px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-white text-sm ring-1 ring-white/10 transition"
                            >
                                Augšupielādēt
                            </button>
                        </form>

                        <a
                            href="{{ route('orders.create') }}"
                            class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-semibold shadow"
                        >
                            + Pievienot jaunu pasūtījumu
                        </a>
                    </div>
                </div>
            </div>

            {{-- Sort Helper --}}
            @php
                function sortLink($column, $label) {
                    $isCurrent = request('sort') === $column;
                    $direction = $isCurrent && request('direction') === 'asc' ? 'desc' : 'asc';
                    $arrow = $isCurrent ? (request('direction') === 'asc' ? '⬆️' : '⬇️') : '';
                    $query = array_merge(request()->all(), ['sort' => $column, 'direction' => $direction]);
                    return '<a href="'.route('orders.index', $query).'" class="inline-flex items-center gap-1 hover:text-white hover:underline text-slate-200">'.$label.' <span class="text-xs text-slate-400">'.$arrow.'</span></a>';
                }
            @endphp

            {{-- Table Card --}}
            <div class="overflow-x-auto mx-2 sm:mx-4 lg:mx-[100px] rounded-2xl border border-white/10 bg-white/5 backdrop-blur shadow-xl">
                <table class="table-auto w-full text-xs sm:text-sm">
                    <thead class="bg-white/5">
                        <tr class="text-left text-slate-200">
                            <th class="px-4 py-3 whitespace-nowrap">
                                {!! sortLink('pasutijuma_numurs', 'Pasūtījuma numurs') !!}
                            </th>
                            <th class="px-4 py-3 whitespace-nowrap">
                                {!! sortLink('datums', 'Datums') !!}
                            </th>
                            <th class="px-4 py-3 whitespace-nowrap">
                                {!! sortLink('klients', 'Klients') !!}
                            </th>
                            <th class="px-4 py-3 whitespace-nowrap">
                                Produkts
                            </th>
                            <th class="px-4 py-3 whitespace-nowrap text-center">
                                {!! sortLink('daudzums', 'Daudzums') !!}
                            </th>
                            <th class="px-4 py-3 whitespace-nowrap">
                                {!! sortLink('izpildes_datums', 'Izpildes datums') !!}
                            </th>
                            <th class="px-4 py-3 whitespace-nowrap">
                                {!! sortLink('prioritāte', 'Prioritāte') !!}
                            </th>
                            <th class="px-4 py-3 whitespace-nowrap">
                                {!! sortLink('statuss', 'Statuss') !!}
                            </th>
                            <th class="px-4 py-3 whitespace-nowrap hidden md:table-cell">
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
                                <td class="px-4 py-3 max-w-[220px] truncate text-white">
                                    {{ $order->pasutijuma_numurs }}
                                </td>
                                <td class="px-4 py-3 max-w-[180px] truncate text-slate-200">
                                    {{ $order->datums }}
                                </td>
                                <td class="px-4 py-3 max-w-[220px] truncate text-slate-200">
                                    {{ $order->client->nosaukums ?? $order->klients }}
                                </td>
                                <td class="px-4 py-3 max-w-[220px] truncate text-slate-200">
                                    {{ $order->product->nosaukums ?? $order->produkts }}
                                </td>
                                <td class="px-4 py-3 max-w-[120px] truncate text-center text-slate-200">
                                    {{ $order->daudzums }}
                                </td>
                                <td class="px-4 py-3 max-w-[180px] truncate text-slate-200">
                                    {{ $order->izpildes_datums }}
                                </td>
                                <td class="px-4 py-3 max-w-[140px] truncate text-slate-200">
                                    {{ $order->prioritāte }}
                                </td>

                                {{-- Status with subtle badge --}}
                                <td class="px-4 py-3 max-w-[240px] truncate">
                                    <span class="inline-flex items-center rounded-lg px-2.5 py-1 text-xs ring-1 ring-white/10 bg-white/5 text-slate-200">
                                        {{ $order->statuss }}
                                    </span>
                                </td>

                                <td class="px-4 py-3 max-w-[260px] truncate text-slate-300 hidden md:table-cell">
                                    {{ $order->piezimes ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-10 text-slate-400">
                                    Nav pieejami pasūtījumi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-6 mx-4 sm:mx-6 lg:mx-[100px]">
                <div class="rounded-2xl border border-white/10 bg-white/5 backdrop-blur p-4">
                    {{ $orders->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>