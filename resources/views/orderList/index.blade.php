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
                <div class="mb-6 px-[100px] flex justify-end">
                    <a href="{{ route('orderList.create') }}"
                       class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                        + Pievienot jaunu pasūtījumu
                    </a>
                </div>

                <div class="overflow-x-auto px-[100px]">
                    <table class="table-auto w-full min-w-[500px] border-collapse border border-gray-300 bg-white">
                        <thead>
                            <tr>
                                <th class="border border-gray-300 px-4 py-2">Nosaukums</th>
                                <th class="border border-gray-300 w-[60px] text-center">Darbības</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($orderList as $order)
                                <tr class="border border-gray-300">
                                    <td class="border border-gray-300 px-4 py-2">{{ $order->name }}</td>
                                    <td class="border border-gray-300 text-center">
                                        <form method="POST"
                                            action="{{ route('orderList.destroy', ['orderList' => $order->getKey()]) }}"
                                            onsubmit="return confirm('Vai tiešām esat pasūtījis šo produktu?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-green-600 hover:text-green-800 text-lg">✔️</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center py-4">Nav pieejamu pasūtījumu.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
