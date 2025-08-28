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

                            {{-- Done amount input (existing) --}}
                            <input type="number" name="done_amount" min="0"
                                   placeholder="Paveiktais daudzums (gab.)"
                                   class="done-input border rounded px-2 py-1"
                                   data-task-id="{{ $task->id }}"
                                   style="{{ $task->status == 'daļēji pabeigts' ? '' : 'display:none' }}">

                            {{-- NEW: Spent time (minutes) — required for daļēji pabeigts/pabeigts --}}
                            <input type="number" name="spent_time" min="1"
                                   placeholder="Pavadītais laiks (min)"
                                   class="spent-input border rounded px-2 py-1"
                                   data-task-id="{{ $task->id }}"
                                   style="{{ in_array($task->status, ['daļēji pabeigts','pabeigts']) ? '' : 'display:none' }}">

                            {{-- NEW: Comment — optional --}}
                            <input type="text" name="comment"
                                   placeholder="Komentārs (neobligāts)"
                                   class="comment-input border rounded px-2 py-1"
                                   data-task-id="{{ $task->id }}"
                                   style="{{ in_array($task->status, ['daļēji pabeigts','pabeigts']) ? '' : 'display:none' }}">

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

    {{-- Toggle logic for the inputs --}}
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.status-select').forEach(select => {
            const taskId = select.dataset.taskId;
            const doneInput   = document.querySelector(`.done-input[data-task-id="${taskId}"]`);
            const spentInput  = document.querySelector(`.spent-input[data-task-id="${taskId}"]`);
            const commentInput= document.querySelector(`.comment-input[data-task-id="${taskId}"]`);

            function updateVisibility() {
                const v = select.value;

                // Show done_amount only for "daļēji pabeigts"
                if (doneInput) {
                    const showDone = (v === 'daļēji pabeigts');
                    doneInput.style.display = showDone ? 'inline-block' : 'none';
                    if (!showDone) doneInput.value = '';
                }

                // Show spent_time + comment for "daļēji pabeigts" or "pabeigts"
                const needTimeAndComment = (v === 'daļēji pabeigts' || v === 'pabeigts');

                if (spentInput) {
                    spentInput.style.display = needTimeAndComment ? 'inline-block' : 'none';
                    spentInput.required = needTimeAndComment;   // <-- required when shown
                    if (!needTimeAndComment) spentInput.value = '';
                }

                if (commentInput) {
                    commentInput.style.display = needTimeAndComment ? 'inline-block' : 'none';
                    // comment is optional; no required flag
                    if (!needTimeAndComment) commentInput.value = '';
                }
            }

            select.addEventListener('change', updateVisibility);
            updateVisibility(); // init on load
        });
    });
    </script>
</x-app-layout>
