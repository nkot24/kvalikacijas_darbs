<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Pasūtījumu saraksts
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

            <div class="bg-white shadow-sm rounded-lg p-6">

                <!-- Buttons Section -->
                <div class="mb-6 px-[100px] flex flex-col md:flex-row md:items-center gap-4">

                    <!-- Export Orders -->
                    <a href="{{ route('orders.fullExport') }}"
                       class="inline-block px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        📤 Eksportēt pasūtījumus
                    </a>

                    <!-- Import Orders -->
                    <form action="{{ route('orders.fullImport') }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-2">
                        @csrf
                        <label class="block text-sm font-medium text-gray-700">📥 Importēt no Excel:</label>
                        <input type="file" name="file"
                               class="block text-sm text-gray-500 file:mr-4 file:py-2 file:px-4
                                      file:rounded file:border-0 file:text-sm file:font-semibold
                                      file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                               required>
                        <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                            Augšupielādēt
                        </button>
                    </form>

                    <!-- Add Order -->
                    <a href="{{ route('orders.create') }}"
                       class="inline-block px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                        + Pievienot jaunu pasūtījumu
                    </a>
                </div>

                <!-- Orders Table -->
                <div class="overflow-x-auto px-[100px]">
                    <table class="table-auto w-full min-w-[1000px] border-collapse border border-gray-300 bg-white">
                        <thead>
                            <tr>
                                <th class="border px-4 py-2">Pasūtījuma numurs</th>
                                <th class="border px-4 py-2">Datums</th>
                                <th class="border px-4 py-2">Klients</th>
                                <th class="border px-4 py-2">Produkts</th>
                                <th class="border px-4 py-2">Daudzums</th>
                                <th class="border px-4 py-2">Izpildes datums</th>
                                <th class="border px-4 py-2">Prioritāte</th>
                                <th class="border px-4 py-2">Statuss</th>
                                <th class="border px-4 py-2">Piezīmes</th>
                                <th class="border px-4 py-2">Darbības</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($orders as $order)
                                <tr>
                                    <td class="border px-4 py-2">{{ $order->pasutijuma_numurs }}</td>
                                    <td class="border px-4 py-2">{{ $order->datums }}</td>
                                    <td class="border px-4 py-2">{{ $order->client->nosaukums ?? $order->klients }}</td>
                                    <td class="border px-4 py-2">{{ $order->product->nosaukums ?? $order->produkts }}</td>
                                    <td class="border px-4 py-2">{{ $order->daudzums }}</td>
                                    <td class="border px-4 py-2">{{ $order->izpildes_datums }}</td>
                                    <td class="border px-4 py-2">{{ $order->prioritāte }}</td>
                                    <td class="border px-4 py-2">{{ $order->statuss }}</td>
                                    <td class="border px-4 py-2">{{ $order->piezimes ?? '-' }}</td>

                                    <td class="border px-4 py-2 space-y-2">
                                        <a href="{{ route('orders.edit', $order) }}" class="text-blue-600 hover:underline block">Rediģēt</a>
                                        <a href="{{ route('orders.show', $order) }}" class="text-blue-600 hover:underline block">Skatīt</a>
                                        <a href="{{ route('orders.print', $order) }}" target="_blank"
                                        class="text-purple-600 hover:underline block">
                                        🖨️ Izprintēt ražošanas lapu
                                        </a>
                                        <form action="{{ route('orders.destroy', $order) }}" method="POST" onsubmit="return confirm('Vai tiešām vēlaties dzēst šo pasūtījumu?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:underline">Dzēst</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">Nav pieejami pasūtījumi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
