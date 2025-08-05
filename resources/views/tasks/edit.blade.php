<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Atjaunināt uzdevumu</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-xl mx-auto bg-white p-6 shadow rounded">
            <form action="{{ route('tasks.update', $task) }}" method="POST">
                @csrf
                @method('PUT')

                <p class="mb-2"><strong>Pasūtījums:</strong> {{ $task->production->order->pasutijuma_numurs }}</p>
                <p class="mb-2"><strong>Produkts:</strong> {{ $task->production->order->produkts }}</p>
                <p class="mb-2"><strong>Process:</strong> {{ $task->process->processa_nosaukums }}</p>

                <div class="mb-4">
                    <label for="status" class="block font-semibold">Statuss:</label>
                    <select name="status" id="status" class="w-full border p-2">
                        <option value="nav uzsākts" {{ $task->status == 'nav uzsākts' ? 'selected' : '' }}>Nav uzsākts</option>
                        <option value="daļēji pabeigts" {{ $task->status == 'daļēji pabeigts' ? 'selected' : '' }}>Daļēji pabeigts</option>
                        <option value="pabeigts" {{ $task->status == 'pabeigts' ? 'selected' : '' }}>Pabeigts</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="done_amount" class="block font-semibold">Izpildītais daudzums:</label>
                    <input type="number" name="done_amount" id="done_amount" value="{{ $task->done_amount }}"
                           class="w-full border p-2" min="0">
                </div>

                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Saglabāt</button>
            </form>
        </div>
    </div>
</x-app-layout>
