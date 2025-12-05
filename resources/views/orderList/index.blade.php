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

            <div class="bg-white shadow-sm rounded-lg p-4 sm:p-6">
                {{-- Top bar --}}
                <div
                    class="mb-6 px-4 sm:px-6 lg:px-[100px]
                           flex justify-between"
                >
                    <a href="{{ route('orderList.create') }}"
                       class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                        + Pievienot jaunu iepirkumu
                    </a>
                </div>

                {{-- Table area: outer padding, inner horizontal scroll --}}
                <div class="px-4 sm:px-6 lg:px-[100px]">
                    <div class="overflow-x-auto">
                        <table
                            class="table-auto w-full min-w-[1100px] border-collapse border border-gray-300 bg-white text-xs sm:text-sm"
                        >
                            <thead>
                                <tr>
                                    <th class="border px-3 py-2">Nosaukums</th>
                                    <th class="border px-3 py-2 text-right">Daudzums</th>
                                    <th class="border px-3 py-2">Foto</th>
                                    <th class="border px-3 py-2">Statuss</th>
                                    <th class="border px-3 py-2">Izveidoja</th>
                                    <th class="border px-3 py-2">Piegādātājs</th>
                                    <th class="border px-3 py-2">Kad pasūtīts</th>
                                    <th class="border px-3 py-2">Kad jāatnāk</th>
                                    <th class="border px-3 py-2">Kad atnāca</th>
                                    <th class="border px-3 py-2 w-[180px] text-center">Darbības</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($orderList as $order)
                                    <tr class="border even:bg-gray-50">
                                        <td class="border px-3 py-2">{{ $order->name }}</td>
                                        <td class="border px-3 py-2 text-right">{{ $order->quantity }}</td>
                                        <td class="border px-3 py-2">
                                            @if($order->photo_path)
                                                <a href="{{ asset('storage/'.$order->photo_path) }}"
                                                   target="_blank"
                                                   class="inline-block">
                                                    <img src="{{ asset('storage/'.$order->photo_path) }}"
                                                         alt="foto"
                                                         class="h-10 w-10 object-cover rounded">
                                                </a>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="border px-3 py-2">
                                            @php
                                                $badge = match($order->status) {
                                                    'pasūtīts' => 'bg-yellow-100 text-yellow-800',
                                                    'saņemts'  => 'bg-green-100 text-green-800',
                                                    default    => 'bg-gray-100 text-gray-800',
                                                };
                                            @endphp
                                            <span class="px-2 py-1 rounded text-xs {{ $badge }}">
                                                {{ $order->status }}
                                            </span>
                                        </td>
                                        <td class="border px-3 py-2">
                                            {{ optional($order->creator)->name ?? '—' }}
                                        </td>
                                        <td class="border px-3 py-2">
                                            {{ $order->supplier_name ?? '—' }}
                                        </td>
                                        <td class="border px-3 py-2">
                                            {{ $order->ordered_at?->format('Y-m-d') ?? '—' }}
                                        </td>
                                        <td class="border px-3 py-2">
                                            {{ $order->expected_at?->format('Y-m-d') ?? '—' }}
                                        </td>
                                        <td class="border px-3 py-2">
                                            {{ $order->arrived_at?->format('Y-m-d') ?? '—' }}
                                        </td>
                                        <td class="border px-3 py-2 text-center space-x-3 whitespace-nowrap">
                                            <a href="{{ route('orderList.edit', $order) }}"
                                               class="text-blue-600 hover:text-blue-800">
                                                Rediģēt
                                            </a>
                                            <form method="POST"
                                                  class="inline"
                                                  action="{{ route('orderList.destroy', $order) }}"
                                                  onsubmit="return confirm('Vai tiešām dzēst šo ierakstu?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="text-red-600 hover:text-red-800">
                                                    Dzēst
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-4">
                                            Nav pieejamu iepirkumu.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
