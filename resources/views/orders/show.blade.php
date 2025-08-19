<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Pasūtījums {{ $order->pasutijuma_numurs }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto bg-white shadow-sm rounded-lg p-6 space-y-4">
            <div><strong>Datums:</strong> {{ optional($order->datums)->format('d.m.Y H:i') ?? $order->datums }}</div>
            <div><strong>Klients:</strong> {{ $order->client->nosaukums ?? $order->klients }}</div>
            <div><strong>Produkts:</strong> {{ $order->product->nosaukums ?? $order->produkts }}</div>
            <div><strong>Daudzums:</strong> {{ $order->daudzums }}</div>
            <div><strong>Izpildes datums:</strong> 
                {{ $order->izpildes_datums ? \Carbon\Carbon::parse($order->izpildes_datums)->format('d.m.Y') : '—' }}
            </div>
            <div><strong>Prioritāte:</strong> {{ $order->prioritāte }}</div>
            <div><strong>Statuss:</strong> {{ $order->statuss }}</div>
            <div><strong>Piezīmes:</strong> {{ $order->piezimes ?? '—' }}</div>

            <div class="pt-4">
                <a href="{{ route('orders.print', $order) }}" target="_blank"
                   class="inline-block px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                    🖨️ Izprintēt ražošanas lapu
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
