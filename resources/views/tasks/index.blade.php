<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Mani uzdevumi</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto bg-white shadow-sm rounded-lg p-6">
            @foreach ($tasks as $task)
                <div class="border p-4 rounded mb-4">
                    <h3 class="font-bold text-lg">{{ $task->production->order->produkts ?? 'Produkts nav pieejams' }}</h3>
                    <p><strong>Process:</strong> {{ $task->process->processa_nosaukums }}</p>
                    <p><strong>Daudzums:</strong> {{ $task->production->order->daudzums }}</p>
                    <p><strong>Prioritāte:</strong> {{ $task->production->order->prioritāte }}</p>
                    <p><strong>Izpildes datums:</strong> {{ $task->production->order->izpildes_datums }}</p>

                    <form action="{{ route('tasks.update', $task) }}" method="POST" class="mt-4">
                        @csrf
                        @method('PUT')

                        <div class="flex flex-col md:flex-row items-center gap-4">
                            <label for="status">Statuss:</label>
                            <select name="status" class="border rounded px-2 py-1" required>
                                <option value="nav uzsākts" {{ $task->status == 'nav uzsākts' ? 'selected' : '' }}>Nav uzsākts</option>
                                <option value="daļēji pabeigts" {{ $task->status == 'daļēji pabeigts' ? 'selected' : '' }}>Daļēji pabeigts</option>
                                <option value="pabeigts" {{ $task->status == 'pabeigts' ? 'selected' : '' }}>Pabeigts</option>
                            </select>

                            <input type="number" name="done_amount" value="{{ $task->done_amount }}" class="border rounded px-2 py-1" placeholder="Paveiktais daudzums (gab.)" min="0">

                            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                                Atjaunināt
                            </button>
                        </div>
                    </form>
                </div>
            @endforeach

            @if($tasks->isEmpty())
                <p class="text-center text-gray-600">Nav aktīvu uzdevumu.</p>
            @endif
        </div>
    </div>
</x-app-layout>
