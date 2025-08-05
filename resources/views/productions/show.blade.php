<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Ražošanas progress</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto space-y-4">

            <div class="bg-white p-6 rounded shadow">
                <h3 class="text-lg font-bold mb-2">Pasūtījums: {{ $production->order->pasutijuma_numurs }}</h3>
                <p>Produkts: {{ $production->order->product->nosaukums ?? $production->order->produkts }}</p>
                <p>Daudzums: {{ $production->order->daudzums }}</p>
                <p><strong>Piezīmes:</strong> {{ $production->order->piezimes ?? '-' }}</p>
                <p>Prioritāte: {{ $production->order->prioritāte }}</p>
                <p>Izpildes datums: {{ $production->order->izpildes_datums }}</p>
            </div>

            <div class="bg-white p-6 rounded shadow">
                <h4 class="text-md font-bold mb-2">Uzdevumi</h4>
                @foreach ($production->tasks as $task)
                    <div class="mb-4 border-b pb-4">
                        <p><strong>Process:</strong> {{ $task->process->processa_nosaukums }}</p>
                        <p><strong>Darbinieks:</strong> {{ $task->user->name }}</p>
                        <p><strong>Statuss:</strong> {{ $task->status }}</p>
                        @if ($task->done_amount)
                            <p><strong>Izpildīts daudzums:</strong> {{ $task->done_amount }} no {{ $production->order->daudzums }}</p>
                        @endif
                    </div>
                @endforeach
            </div>

        </div>
    </div>
</x-app-layout>
