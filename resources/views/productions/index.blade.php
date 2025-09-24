<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Ražošanas saraksts</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <a href="{{ route('productions.create') }}"
               class="mb-4 inline-block bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                + Izveidot jaunu ražošanu
            </a>

            @foreach ($productions as $production)
                <div class="bg-white p-6 mb-4 shadow rounded">
                    <h3 class="text-lg font-bold mb-2">Pasūtījums: {{ $production->order->pasutijuma_numurs }}</h3>
                    <p>Produkts: {{ $production->order->product->nosaukums ?? $production->order->produkts }}</p>
                    <p>Daudzums: {{ $production->order->daudzums }}</p>
                    <p>Prioritāte: {{ $production->order->prioritāte }}</p>
                    <p>Izpildes datums: {{ $production->order->izpildes_datums }}</p>
                    <p><strong>Piezīmes:</strong> {{ $production->order->piezimes ?? '-' }}</p>
                    <a href="{{ route('productions.show', $production) }}" class="text-blue-600 hover:underline">Skatīt progresu</a>
                    <a href="{{ route('productions.edit', $production) }}" class=" text-yellow-600 hover:underline">Labot</a>
                    <form action="{{ route('productions.destroy', $production) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:underline"
                                onclick="return confirm('Vai tiešām vēlaties dzēst šo ražošanu?')">Dzēst</button>
                    </form>

                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
