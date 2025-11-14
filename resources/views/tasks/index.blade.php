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
                        <p><strong>Klients:</strong> {{ $task->production->order->klients ?? $task->production->order->client->nosaukums }}</p>
                        {{ optional($task->production->order->product)->nosaukums ?? $task->production->order->produkts }}
                    </h4>

                    {{-- Lietotāji --}}
                    <p>
                        <strong>Lietotāji:</strong>
                        @if ($task->user_id !== null)
                            {{ $task->user->name ?? 'Nezināms lietotājs' }}
                        @else
                            @php
                                $processUsers = $task->process->users->pluck('id')->toArray();
                                $assignedUsers = $task->assignedUsers->pluck('id')->toArray();
                                $assignedNames = $task->assignedUsers->pluck('name')->toArray();
                                $isSharedWithAll = count(array_diff($processUsers, $assignedUsers)) === 0;
                            @endphp

                            @if ($isSharedWithAll)
                                <span class="text-blue-600">(Kopīgs uzdevums)</span>
                            @else
                                <span class="text-blue-600">({{ implode(', ', $assignedNames) }})</span>
                            @endif
                        @endif
                    </p>

                    <p><strong>Process:</strong> {{ $task->process->processa_nosaukums }}</p>
                    <p><strong>Daudzums:</strong> {{ $task->production->order->daudzums }}</p>
                    <p><strong>Piezīmes:</strong> {{ $task->production->order->piezimes ?? '-' }}</p>
                    <p><strong>Prioritāte:</strong> {{ $task->production->order->prioritāte }}</p>
                    <p><strong>Izpildes datums:</strong> {{ $task->production->order->izpildes_datums }}</p>

                    {{-- Progress --}}
                    <p class="mt-2 text-green-700 font-semibold">
                        Izpildītais daudzums: {{ $task->done_amount ?? 0 }} no {{ $task->production->order->daudzums }}
                    </p>

                    {{-- Files --}}
                    @php
                        $files = $task->relationLoaded('files')
                                ? $task->files->sortByDesc('id')
                                : $task->files()->latest()->get();
                    @endphp
                    <div class="mt-3">
                        <h5 class="font-semibold text-sm text-gray-800 mb-1">Faili</h5>
                        @if ($files->isEmpty())
                            <p class="text-sm text-gray-500">Nav pievienotu failu.</p>
                        @else
                            <ul class="text-sm space-y-1">
                                @foreach ($files as $f)
                                    <li class="flex items-center gap-2">
                                        📎
                                        <a href="{{ route('process-files.view', $f) }}" target="_blank" class="text-indigo-600 hover:underline">
                                            {{ $f->original_name }}
                                        </a>
                                        <span class="text-gray-500">({{ round(($f->size ?? 0)/1024, 1) }} KB)</span>
                                        <a href="{{ route('process-files.download', $f) }}" class="ml-2 text-indigo-600 hover:underline">
                                            Lejupielādēt
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                    {{-- Update form --}}
                    <form action="{{ route('tasks.update', $task) }}" method="POST" class="mt-4 task-form">
                        @csrf
                        @method('PUT')
                        <div class="flex flex-col md:flex-row items-center gap-4">
                            <label for="status">Statuss:</label>
                            <select name="status" required class="status-select border rounded px-2 py-1"
                                    data-task-id="{{ $task->id }}">
                                <option value="nav uzsākts" {{ $task->status == 'nav uzsākts' ? 'selected' : '' }}>Nav uzsākts</option>
                                <option value="daļēji pabeigts" {{ $task->status == 'daļēji pabeigts' ? 'selected' : '' }}>Daļēji pabeigts</option>
                                <option value="pabeigts" {{ $task->status == 'pabeigts' ? 'selected' : '' }}>Pabeigts</option>
                            </select>

                            <input type="number" name="done_amount" min="0" placeholder="Paveiktais daudzums (gab.)"
                                   class="done-input border rounded px-2 py-1"
                                   data-task-id="{{ $task->id }}"
                                   style="{{ $task->status == 'daļēji pabeigts' ? '' : 'display:none' }}">

                            <input type="number" name="spent_time" min="0.01" step="0.01" placeholder="Pavadītais laiks (stundas)"
                                   class="spent-input border rounded px-2 py-1"
                                   data-task-id="{{ $task->id }}"
                                   style="{{ in_array($task->status, ['daļēji pabeigts','pabeigts']) ? '' : 'display:none' }}">

                            <input type="text" name="comment" placeholder="Komentārs (neobligāts)"
                                   class="comment-input border rounded px-2 py-1"
                                   data-task-id="{{ $task->id }}"
                                   style="{{ in_array($task->status, ['daļēji pabeigts','pabeigts']) ? '' : 'display:none' }}">

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

        {{-- Future Tasks --}}
        <h3 class="text-lg font-bold mb-2">Uzdevumi kas būs</h3>
        <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
            @forelse ($futureTasks as $task)
                <div class="border p-4 rounded mb-4">
                    <h4 class="font-bold">
                        <p><strong>Klients:</strong> {{ $task->production->order->klients ?? $task->production->order->client->nosaukums }}</p>
                        {{ optional($task->production->order->product)->nosaukums ?? $task->production->order->produkts }}
                    </h4>

                    {{-- Lietotāji --}}
                    <p>
                        <strong>Lietotāji:</strong>
                        @if ($task->user_id !== null)
                            {{ $task->user->name ?? 'Nezināms lietotājs' }}
                        @else
                            @php
                                $processUsers = $task->process->users->pluck('id')->toArray();
                                $assignedUsers = $task->assignedUsers->pluck('id')->toArray();
                                $assignedNames = $task->assignedUsers->pluck('name')->toArray();
                                $isSharedWithAll = count(array_diff($processUsers, $assignedUsers)) === 0;
                            @endphp

                            @if ($isSharedWithAll)
                                <span class="text-blue-600">(Kopīgs uzdevums)</span>
                            @else
                                <span class="text-blue-600">({{ implode(', ', $assignedNames) }})</span>
                            @endif
                        @endif
                    </p>

                    <p><strong>Process:</strong> {{ $task->process->processa_nosaukums }}</p>
                    <p><strong>Daudzums:</strong> {{ $task->production->order->daudzums }}</p>
                    <p><strong>Piezīmes:</strong> {{ $task->production->order->piezimes ?? '-' }}</p>
                    <p><strong>Prioritāte:</strong> {{ $task->production->order->prioritāte }}</p>
                    <p><strong>Izpildes datums:</strong> {{ $task->production->order->izpildes_datums }}</p>

                    <p class="mt-2 text-green-700 font-semibold">
                        Izpildītais daudzums: {{ $task->done_amount ?? 0 }} no {{ $task->production->order->daudzums }}
                    </p>

                    {{-- Files --}}
                    @php
                        $files = $task->relationLoaded('files')
                                ? $task->files->sortByDesc('id')
                                : $task->files()->latest()->get();
                    @endphp
                    <div class="mt-3">
                        <h5 class="font-semibold text-sm text-gray-800 mb-1">Faili</h5>
                        @if ($files->isEmpty())
                            <p class="text-sm text-gray-500">Nav pievienotu failu.</p>
                        @else
                            <ul class="text-sm space-y-1">
                                @foreach ($files as $f)
                                    <li class="flex items-center gap-2">
                                        📎
                                        <a href="{{ route('process-files.view', $f) }}" target="_blank" class="text-indigo-600 hover:underline">
                                            {{ $f->original_name }}
                                        </a>
                                        <span class="text-gray-500">({{ round(($f->size ?? 0)/1024, 1) }} KB)</span>
                                        <a href="{{ route('process-files.download', $f) }}" class="ml-2 text-indigo-600 hover:underline">
                                            Lejupielādēt
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                    {{-- Update form --}}
                    <form action="{{ route('tasks.update', $task) }}" method="POST" class="mt-4 task-form">
                        @csrf
                        @method('PUT')
                        <div class="flex flex-col md:flex-row items-center gap-4">
                            <label for="status">Statuss:</label>
                            <select name="status" required class="status-select border rounded px-2 py-1"
                                    data-task-id="{{ $task->id }}">
                                <option value="nav uzsākts" {{ $task->status == 'nav uzsākts' ? 'selected' : '' }}>Nav uzsākts</option>
                                <option value="daļēji pabeigts" {{ $task->status == 'daļēji pabeigts' ? 'selected' : '' }}>Daļēji pabeigts</option>
                                <option value="pabeigts" {{ $task->status == 'pabeigts' ? 'selected' : '' }}>Pabeigts</option>
                            </select>

                            <input type="number" name="done_amount" min="0" placeholder="Paveiktais daudzums (gab.)"
                                   class="done-input border rounded px-2 py-1"
                                   data-task-id="{{ $task->id }}"
                                   style="{{ $task->status == 'daļēji pabeigts' ? '' : 'display:none' }}">

                            <input type="number" name="spent_time" min="0.01" step="0.01" placeholder="Pavadītais laiks (stundas)"
                                   class="spent-input border rounded px-2 py-1"
                                   data-task-id="{{ $task->id }}"
                                   style="{{ in_array($task->status, ['daļēji pabeigts','pabeigts']) ? '' : 'display:none' }}">

                            <input type="text" name="comment" placeholder="Komentārs (neobligāts)"
                                   class="comment-input border rounded px-2 py-1"
                                   data-task-id="{{ $task->id }}"
                                   style="{{ in_array($task->status, ['daļēji pabeigts','pabeigts']) ? '' : 'display:none' }}">

                            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                                Atjaunināt
                            </button>
                        </div>
                    </form>
                </div>
            @empty
                <p>Nav gaidāmu uzdevumu.</p>
            @endforelse
        </div>
    </div>

    {{-- JS logic for field toggles --}}
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.status-select').forEach(select => {
            const taskId = select.dataset.taskId;
            const doneInput    = document.querySelector(`.done-input[data-task-id="${taskId}"]`);
            const spentInput   = document.querySelector(`.spent-input[data-task-id="${taskId}"]`);
            const commentInput = document.querySelector(`.comment-input[data-task-id="${taskId}"]`);

            function updateVisibility() {
                const v = select.value;
                const showDone = (v === 'daļēji pabeigts');
                const needTimeAndComment = (v === 'daļēji pabeigts' || v === 'pabeigts');

                if (doneInput) doneInput.style.display = showDone ? 'inline-block' : 'none';
                if (!showDone) doneInput.value = '';

                if (spentInput) {
                    spentInput.style.display = needTimeAndComment ? 'inline-block' : 'none';
                    spentInput.required = needTimeAndComment;
                    if (!needTimeAndComment) spentInput.value = '';
                }

                if (commentInput) {
                    commentInput.style.display = needTimeAndComment ? 'inline-block' : 'none';
                    if (!needTimeAndComment) commentInput.value = '';
                }
            }

            select.addEventListener('change', updateVisibility);
            updateVisibility();
        });
    });
    </script>
</x-app-layout>
