<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Izpildītie iepirkumi</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-full mx-auto">

            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mx-[100px] mb-4" role="alert">
                    <strong class="font-bold">Veiksmīgi!</strong>
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white shadow-sm rounded-lg p-6">
                <div class="mb-6 px-[100px]">
                    <a href="{{ route('orderList.index') }}" class="px-4 py-2 bg-gray-100 text-gray-800 rounded hover:bg-gray-200">
                        ← Atpakaļ uz aktīvajiem
                    </a>
                </div>

                <div class="overflow-x-auto px-[100px]">
                    <table class="table-auto w-full min-w-[1200px] border-collapse border border-gray-300 bg-white">
                        <thead>
                            <tr>
                                <th class="border px-3 py-2">Nosaukums</th>
                                <th class="border px-3 py-2 text-right">Daudzums</th>
                                <th class="border px-3 py-2">Foto</th>
                                <th class="border px-3 py-2">Statuss</th>
                                <th class="border px-3 py-2">Izveidoja</th>
                                <th class="border px-3 py-2">Piegādātājs</th> <!-- NEW -->
                                <th class="border px-3 py-2">Kad pasūtīts</th>
                                <th class="border px-3 py-2">Kad jāatnāk</th>
                                <th class="border px-3 py-2">Kad atnāca</th>
                                <th class="border px-3 py-2 w-[180px] text-center">Darbības</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($completed as $order)
                                <tr class="border">
                                    <td class="border px-3 py-2">{{ $order->name }}</td>
                                    <td class="border px-3 py-2 text-right">{{ $order->quantity }}</td>
                                    <td class="border px-3 py-2">
                                        @if($order->photo_path)
                                            <a href="{{ asset('storage/'.$order->photo_path) }}" target="_blank">
                                                <img src="{{ asset('storage/'.$order->photo_path) }}" alt="foto" class="h-10 w-10 object-cover rounded">
                                            </a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="border px-3 py-2">
                                        <span class="px-2 py-1 rounded text-xs bg-green-100 text-green-800">{{ $order->status }}</span>
                                    </td>
                                    <td class="border px-3 py-2">{{ optional($order->creator)->name ?? '—' }}</td>
                                    <td class="border px-3 py-2">{{ $order->supplier_name ?? '—' }}</td> <!-- NEW -->
                                    <td class="border px-3 py-2">{{ $order->ordered_at?->format('Y-m-d') ?? '—' }}</td>
                                    <td class="border px-3 py-2">{{ $order->expected_at?->format('Y-m-d') ?? '—' }}</td>
                                    <td class="border px-3 py-2 font-semibold">{{ $order->arrived_at?->format('Y-m-d') ?? '—' }}</td>
                                    <td class="border px-3 py-2 text-center space-x-3">
                                        <a href="{{ route('orderList.edit', $order) }}" class="text-blue-600 hover:text-blue-800">Skat./rediģēt</a>
                                        <form method="POST" class="inline"
                                              action="{{ route('orderList.destroy', $order) }}"
                                              onsubmit="return confirm('Vai tiešām dzēst šo ierakstu?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800">Dzēst</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="10" class="text-center py-4">Nav izpildītu iepirkumu.</td></tr> <!-- colspan updated -->
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
