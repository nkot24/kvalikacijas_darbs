<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Pabeigtie pasūtījumi
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-full mx-auto">

            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mx-[100px] mb-4" role="alert">
                    <strong class="font-bold">Veiksmīgi!</strong>
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <!-- Search + Back Button -->
            <div class="mb-6 px-[100px] flex flex-col lg:flex-row lg:items-center justify-between gap-4 flex-wrap">

                <!-- Left: Search -->
                <form method="GET" action="{{ route('orders.complete') }}" class="flex gap-2 items-center">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="🔍 Meklēt..."
                        class="border rounded px-4 py-2 w-64 text-sm" />
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">
                        Meklēt
                    </button>
                    @if(request('search'))
                        <a href="{{ route('orders.complete') }}" class="text-sm px-4 py-2 text-gray-600 hover:underline">Notīrīt</a>
                    @endif
                </form>

                <!-- Right: Back Button -->
                <div class="flex items-center gap-4">
                    <a href="{{ route('orders.index') }}"
                        class="px-4 py-2 bg-gray-300 text-black rounded hover:bg-gray-400">
                        ← Atpakaļ uz aktīvajiem pasūtījumiem
                    </a>
                </div>
            </div>

            <!-- Sort Helper -->
            @php
                function sortLinkComplete($column, $label) {
                    $isCurrent = request('sort') === $column;
                    $direction = $isCurrent && request('direction') === 'asc' ? 'desc' : 'asc';
                    $arrow = $isCurrent ? (request('direction') === 'asc' ? '⬆️' : '⬇️') : '';
                    $query = array_merge(request()->all(), ['sort' => $column, 'direction' => $direction]);
                    return '<a href="'.route('orders.complete', $query).'" class="hover:underline">'.$label.' '.$arrow.'</a>';
                }
            @endphp

            <!-- Orders Table -->
            <div class="overflow-x-auto px-[100px]">
                <table class="table-auto w-full min-w-[1000px] border-collapse border border-gray-300 bg-white">
                    <thead>
                        <tr>
                            <th class="border px-3 py-1 max-w-[180px] truncate whitespace-nowrap text-sm">{!! sortLinkComplete('pasutijuma_numurs', 'Pasūtījuma numurs') !!}</th>
                            <th class="border px-3 py-1 max-w-[180px] truncate whitespace-nowrap text-sm">{!! sortLinkComplete('datums', 'Datums') !!}</th>
                            <th class="border px-3 py-1 max-w-[200px] truncate whitespace-nowrap text-sm">{!! sortLinkComplete('klients', 'Klients') !!}</th>
                            <th class="border px-3 py-1 max-w-[200px] truncate whitespace-nowrap text-sm">Produkts</th>
                            <th class="border px-3 py-1 max-w-[100px] truncate whitespace-nowrap text-sm">{!! sortLinkComplete('daudzums', 'Daudzums') !!}</th>
                            <th class="border px-3 py-1 max-w-[180px] truncate whitespace-nowrap text-sm">{!! sortLinkComplete('izpildes_datums', 'Izpildes datums') !!}</th>
                            <th class="border px-3 py-1 max-w-[120px] truncate whitespace-nowrap text-sm">{!! sortLinkComplete('prioritāte', 'Prioritāte') !!}</th>
                            <th class="border px-3 py-1 max-w-[180px] truncate whitespace-nowrap text-sm">{!! sortLinkComplete('statuss', 'Statuss') !!}</th>
                            <th class="border px-3 py-1 max-w-[250px] truncate whitespace-nowrap text-sm">Piezīmes</th>
                           
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($orders as $order)
                            <tr
                                onclick="window.location='{{ route('orders.show', $order->id) }}'"
                                class="cursor-pointer transition-colors even:bg-yellow-50 odd:bg-white hover:bg-gray-100"
                                title="Klikšķiniet, lai atvērtu pasūtījumu"
                            >
                                <td class="border px-3 py-1 max-w-[180px] truncate whitespace-nowrap text-sm">{{ $order->pasutijuma_numurs }}</td>
                                <td class="border px-3 py-1 max-w-[180px] truncate whitespace-nowrap text-sm">{{ $order->datums }}</td>
                                <td class="border px-3 py-1 max-w-[200px] truncate whitespace-nowrap text-sm">{{ $order->client->nosaukums ?? $order->klients }}</td>
                                <td class="border px-3 py-1 max-w-[200px] truncate whitespace-nowrap text-sm">{{ $order->product->nosaukums ?? $order->produkts }}</td>
                                <td class="border px-3 py-1 max-w-[100px] text-center text-sm">{{ $order->daudzums }}</td>
                                <td class="border px-3 py-1 max-w-[180px] truncate whitespace-nowrap text-sm">{{ $order->izpildes_datums }}</td>
                                <td class="border px-3 py-1 max-w-[120px] truncate whitespace-nowrap text-sm">{{ $order->prioritāte }}</td>
                                <td class="border px-3 py-1 max-w-[180px] truncate whitespace-nowrap text-sm">{{ $order->statuss }}</td>
                                <td class="border px-3 py-1 max-w-[250px] truncate whitespace-nowrap text-sm">{{ $order->piezimes ?? '-' }}</td>

                                
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4 text-gray-600">Nav pabeigtu pasūtījumu.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-6 px-[100px]">
                {{ $orders->links() }}
            </div>

        </div>
    </div>
</x-app-layout>
