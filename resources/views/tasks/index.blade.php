<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Mani uzdevumi</h2>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto">
        <h3 class="text-lg font-bold mb-2">Aktualie uzdevumi</h3>
        <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
            @forelse ($currentTasks as $task)
                <div class="border p-4 rounded mb-4">
                    <h4 class="font-bold">{{ $task->production->order->produkts ?? 'Produkts nav pieejams' }}</h4>
                    <p><strong>Process:</strong> {{ $task->process->processa_nosaukums }}</p>
                    <p><strong>Daudzums:</strong> {{ $task->production->order->daudzums }}</p>
                    <p><strong>Prioritāte:</strong> {{ $task->production->order->prioritāte }}</p>
                    <p><strong>Izpildes datums:</strong> {{ $task->production->order->izpildes_datums }}</p>

                    <form action="{{ route('tasks.update', $task) }}" method="POST" class="mt-4">
                        @csrf
                        @method('PUT')
                        <div class="flex flex-col md:flex-row items-center gap-4">
                            <label for="status">Statuss:</label>
                            <select name="status" required class="border rounded px-2 py-1">
                                <option value="nav uzsākts" {{ $task->status == 'nav uzsākts' ? 'selected' : '' }}>Nav uzsākts</option>
                                <option value="daļēji pabeigts" {{ $task->status == 'daļēji pabeigts' ? 'selected' : '' }}>Daļēji pabeigts</option>
                                <option value="pabeigts" {{ $task->status == 'pabeigts' ? 'selected' : '' }}>Pabeigts</option>
                            </select>

                            <input type="number" name="done_amount" value="{{ $task->done_amount }}" min="0" placeholder="Paveiktais daudzums (gab.)" class="border rounded px-2 py-1">

                            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                                Atjaunināt
                            </button>
                        </div>
                    </form>
                </div>
            @empty
                <p>Nav aktuālu uzdevumu.</p>
            @endforelse
        </div>

        <h3 class="text-lg font-bold mb-2">Uzdevumi kas būs</h3>
        <div class="bg-white shadow-sm rounded-lg p-6">
            @forelse ($futureTasks as $task)
                <div class="border p-4 rounded mb-4 opacity-50">
                    <h4 class="font-bold">{{ $task->production->order->produkts ?? 'Produkts nav pieejams' }}</h4>
                    <p><strong>Process:</strong> {{ $task->process->processa_nosaukums }}</p>
                    <p><strong>Daudzums:</strong> {{ $task->production->order->daudzums }}</p>
                    <p><strong>Prioritāte:</strong> {{ $task->production->order->prioritāte }}</p>
                    <p><strong>Izpildes datums:</strong> {{ $task->production->order->izpildes_datums }}</p>
                </div>
            @empty
                <p>Nav nākamo uzdevumu.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
