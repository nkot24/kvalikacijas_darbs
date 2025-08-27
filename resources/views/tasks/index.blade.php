<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Mani uzdevumi</h2>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto">

        {{-- Current Tasks --}}
        <h3 class="text-lg font-bold mb-2">Aktuālie uzdevumi</h3>
        <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
            @forelse ($currentTasks as $task)
                <div class="border p-4 rounded mb-4">
                    <h4 class="font-bold">
                        {{ optional($task->production->order->product)->nosaukums ?? $task->production->order->produkts}}
                        @if ($task->user_id === null)
                            <span class="ml-2 text-sm text-blue-600">(Kopīgs uzdevums)</span>
                        @endif
                    </h4>
                    <p><strong>Process:</strong> {{ $task->process->processa_nosaukums }}</p>
                    <p><strong>Daudzums:</strong> {{ $task->production->order->daudzums }}</p>
                    <p><strong>Piezīmes:</strong> {{ $task->production->order->piezimes ?? '-' }}</p>
                    <p><strong>Prioritāte:</strong> {{ $task->production->order->prioritāte }}</p>
                    <p><strong>Izpildes datums:</strong> {{ $task->production->order->izpildes_datums }}</p>

                    {{-- Show progress --}}
                    <p class="mt-2 text-green-700 font-semibold">
                        Izpildītais daudzums: {{ $task->done_amount ?? 0 }} no {{ $task->production->order->daudzums }}
                    </p>

                    {{-- Update form --}}
                    <form action="{{ route('tasks.update', $task) }}" method="POST" class="mt-4 task-form">
                        @csrf
                        @method('PUT')
                        <div class="flex flex-col md:flex-row items-center gap-4">
                            <label for="status">Statuss:</label>
                            <select name="status" required class="status-select border rounded px-2 py-1"
                                    data-task-id="{{ $task->id }}">
                                <option value="nav uzsākts" {{ $task->status == 'nav uzsākts' ? 'selected' : '' }}>
                                    Nav uzsākts
                                </option>
                                <option value="daļēji pabeigts" {{ $task->status == 'daļēji pabeigts' ? 'selected' : '' }}>
                                    Daļēji pabeigts
                                </option>
                                <option value="pabeigts" {{ $task->status == 'pabeigts' ? 'selected' : '' }}>
                                    Pabeigts
                                </option>
                            </select>

                            {{-- Done amount input --}}
                            <input type="number" name="done_amount" min="0"
                                   placeholder="Paveiktais daudzums (gab.)"
                                   class="done-input border rounded px-2 py-1"
                                   data-task-id="{{ $task->id }}"
                                   style="{{ $task->status == 'daļēji pabeigts' ? '' : 'display:none' }}">

                            <button type="submit"
                                    class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                                Atjaunināt
                            </button>
                        </div>
                    </form>
                </div>
            @empty
                <p>Nav aktuālu uzdevumu.</p>
            @endforelse
        </div>

        {{-- Future Tasks --}}
        <h3 class="text-lg font-bold mb-2">Uzdevumi kas būs</h3>
        <div class="bg-white shadow-sm rounded-lg p-6">
            @forelse ($futureTasks as $task)
                <div class="border p-4 rounded mb-4 opacity-50">
                    <h4 class="font-bold">
                        {{ optional($task->production->order->product)->nosaukums ?? $task->production->order->produkts }}
                        @if ($task->user_id === null)
                            <span class="ml-2 text-sm text-blue-600">(Kopīgs uzdevums)</span>
                        @endif
                    </h4>
                    <p><strong>Process:</strong> {{ $task->process->processa_nosaukums }}</p>
                    <p><strong>Daudzums:</strong> {{ $task->production->order->daudzums }}</p>
                    <p><strong>Piezīmes:</strong> {{ $task->production->order->piezimes ?? '-' }}</p>
                    <p><strong>Prioritāte:</strong> {{ $task->production->order->prioritāte }}</p>
                    <p><strong>Izpildes datums:</strong> {{ $task->production->order->izpildes_datums }}</p>

                    {{-- Future tasks are read-only here --}}
                    <p class="mt-2 text-gray-600">
                        Izpildītais daudzums: {{ $task->done_amount ?? 0 }} no {{ $task->production->order->daudzums }}
                    </p>
                </div>
            @empty
                <p>Nav gaidāmu uzdevumu.</p>
            @endforelse
        </div>
    </div>

    {{-- Toggle logic for the "done amount" input --}}
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.status-select').forEach(select => {
            select.addEventListener('change', (e) => {
                const taskId = e.target.dataset.taskId;
                const input = document.querySelector(`.done-input[data-task-id="${taskId}"]`);
                if (e.target.value === 'daļēji pabeigts') {
                    input.style.display = 'inline-block';
                } else {
                    input.style.display = 'none';
                    input.value = ''; // only clear if not partially done
                }
            });
        });
    });
    </script>
</x-app-layout>
