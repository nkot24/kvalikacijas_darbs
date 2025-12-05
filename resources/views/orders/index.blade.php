<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Pasūtījumu saraksts
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-full mx-auto">

            @if (session('success'))
                <div
                    class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative
                           mx-4 sm:mx-6 lg:mx-[100px] mb-4"
                    role="alert"
                >
                    <strong class="font-bold">Veiksmīgi!</strong>
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <!-- Search + Buttons Combined -->
            <div
                class="mb-6 px-4 sm:px-6 lg:px-[100px]
                       flex flex-col lg:flex-row lg:items-center justify-between gap-4 flex-wrap"
            >
                <!-- Left: Search -->
                <form method="GET" action="{{ route('orders.index') }}" class="flex flex-wrap gap-2 items-center">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="🔍 Meklēt..."
                        class="border rounded px-3 py-2 w-full sm:w-64 text-sm"
                    />
                    <button
                        type="submit"
                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm"
                    >
                        Meklēt
                    </button>

                    @if(request('search'))
                        <a
                            href="{{ route('orders.index') }}"
                            class="text-sm px-4 py-2 text-gray-600 hover:underline"
                        >
                            Notīrīt
                        </a>
                    @endif
                </form>

                <!-- Right: Action Buttons -->
                <div class="flex flex-wrap items-center gap-3">
                    <a
                        href="{{ route('orders.fullExport') }}"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm"
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
                        <label class="text-sm text-gray-700">📥</label>
                        <input
                            type="file"
                            name="file"
                            class="text-xs sm:text-sm text-gray-500
                                   file:mr-2 file:py-1.5 file:px-3
                                   file:rounded file:border-0 file:text-xs sm:file:text-sm file:font-semibold
                                   file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                            required
                        >
                        <button
                            type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm"
                        >
                            Augšupielādēt
                        </button>
                    </form>

                    <a
                        href="{{ route('orders.create') }}"
                        class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm"
                    >
                        + Pievienot jaunu pasūtījumu
                    </a>
                </div>
            </div>

            <!-- Sort Helper -->
            @php
                function sortLink($column, $label) {
                    $isCurrent = request('sort') === $column;
                    $direction = $isCurrent && request('direction') === 'asc' ? 'desc' : 'asc';
                    $arrow = $isCurrent ? (request('direction') === 'asc' ? '⬆️' : '⬇️') : '';
                    $query = array_merge(request()->all(), ['sort' => $column, 'direction' => $direction]);
                    return '<a href="'.route('orders.index', $query).'" class="hover:underline">'.$label.' '.$arrow.'</a>';
                }
            @endphp

            <!-- Orders Table -->
            <div class="overflow-x-auto px-2 sm:px-4 lg:px-[100px]">
                <table class="table-auto w-full border-collapse border border-gray-300 bg-white text-xs sm:text-sm">
                    <thead>
                        <tr>
                            <th class="border px-3 py-1 max-w-[180px] truncate whitespace-nowrap">
                                {!! sortLink('pasutijuma_numurs', 'Pasūtījuma numurs') !!}
                            </th>
                            <th class="border px-3 py-1 max-w-[180px] truncate whitespace-nowrap">
                                {!! sortLink('datums', 'Datums') !!}
                            </th>
                            <th class="border px-3 py-1 max-w-[200px] truncate whitespace-nowrap">
                                {!! sortLink('klients', 'Klients') !!}
                            </th>
                            <th class="border px-3 py-1 max-w-[200px] truncate whitespace-nowrap">
                                Produkts
                            </th>
                            <th class="border px-3 py-1 max-w-[100px] truncate whitespace-nowrap">
                                {!! sortLink('daudzums', 'Daudzums') !!}
                            </th>
                            <th class="border px-3 py-1 max-w-[180px] truncate whitespace-nowrap">
                                {!! sortLink('izpildes_datums', 'Izpildes datums') !!}
                            </th>
                            <th class="border px-3 py-1 max-w-[120px] truncate whitespace-nowrap">
                                {!! sortLink('prioritāte', 'Prioritāte') !!}
                            </th>
                            <th class="border px-3 py-1 max-w-[220px] truncate whitespace-nowrap">
                                {!! sortLink('statuss', 'Statuss') !!}
                            </th>
                            {{-- Optional: hide notes on very small screens --}}
                            <th class="border px-3 py-1 max-w-[250px] truncate whitespace-nowrap hidden md:table-cell">
                                Piezīmes
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            <tr
                                onclick="window.location='{{ route('orders.show', $order->id) }}'"
                                class="cursor-pointer transition-colors even:bg-yellow-50 odd:bg-white hover:bg-gray-100"
                                title="Klikšķiniet, lai atvērtu pasūtījumu"
                            >
                                <td class="border px-3 py-1 max-w-[180px] truncate whitespace-nowrap">
                                    {{ $order->pasutijuma_numurs }}
                                </td>
                                <td class="border px-3 py-1 max-w-[180px] truncate whitespace-nowrap">
                                    {{ $order->datums }}
                                </td>
                                <td class="border px-3 py-1 max-w-[200px] truncate whitespace-nowrap">
                                    {{ $order->client->nosaukums ?? $order->klients }}
                                </td>
                                <td class="border px-3 py-1 max-w-[200px] truncate whitespace-nowrap">
                                    {{ $order->product->nosaukums ?? $order->produkts }}
                                </td>
                                <td class="border px-3 py-1 max-w-[100px] truncate whitespace-nowrap text-center">
                                    {{ $order->daudzums }}
                                </td>
                                <td class="border px-3 py-1 max-w-[180px] truncate whitespace-nowrap">
                                    {{ $order->izpildes_datums }}
                                </td>
                                <td class="border px-3 py-1 max-w-[120px] truncate whitespace-nowrap">
                                    {{ $order->prioritāte }}
                                </td>
                                <td class="border px-3 py-1 max-w-[220px] truncate whitespace-nowrap">
                                    {{ $order->statuss }}
                                </td>
                                <td class="border px-3 py-1 max-w-[250px] truncate whitespace-nowrap hidden md:table-cell">
                                    {{ $order->piezimes ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    Nav pieejami pasūtījumi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-6 px-4 sm:px-6 lg:px-[100px]">
                {{ $orders->links() }}
            </div>

        </div>
    </div>
</x-app-layout>
