<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Rediģēt ražošanu #{{ $production->id }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto bg-white p-6 rounded shadow">
            <form id="productionForm"
                  action="{{ route('productions.update', $production) }}"
                  method="POST"
                  enctype="multipart/form-data">
                @csrf
                @method('PUT')

                {{-- Order Selection --}}
                <div class="mb-6">
                    <label for="order_id" class="block font-semibold mb-2">Izvēlieties pasūtījumu:</label>
                    <select name="order_id" id="order_id" required class="w-full border border-gray-300 p-2 rounded">
                        @foreach ($orders as $order)
                            <option value="{{ $order->id }}"
                                {{ old('order_id', $production->order_id) == $order->id ? 'selected' : '' }}>
                                {{ $order->pasutijuma_numurs }} – {{ $order->product->nosaukums ?? $order->produkts }}
                            </option>
                        @endforeach
                    </select>
                    @error('order_id')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Global files --}}
                <div class="mb-6">
                    <label class="block font-semibold mb-2" for="global_files">
                        Pievienot jaunus failus visiem izvēlētajiem procesiem:
                    </label>
                    <input type="file" name="global_files[]" id="global_files" multiple
                           class="block w-full text-sm text-gray-700 file:mr-3 file:py-2 file:px-3
                           file:rounded file:border-0 file:bg-indigo-600 file:text-white hover:file:bg-indigo-700"/>
                    <p class="text-xs text-gray-500 mt-1">
                        Ja pievienosiet failus šeit, tie tiks pievienoti visiem pašlaik izvēlētajiem procesiem.
                    </p>
                    @error('global_files.*')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Processes and Users --}}
                <div class="mb-6">
                    <label class="block font-semibold mb-2">
                        Izvēlieties procesus un piešķiriet darbiniekus (ja nepieciešams):
                    </label>

                    @foreach ($processes as $process)
                        @php
                            $processId = $process->id;

                            // Check if this process is checked (old input or selectedProcessIds)
                            $checked = in_array($processId, old('process_ids', $selectedProcessIds ?? []));

                            // Users pre-selected: old input has highest priority, fallback to selectedUsersByProcess
                            $preselectedUsers = old('users.' . $processId, $selectedUsersByProcess[$processId] ?? []);

                            // Get tasks and existing files for this process
                            $tasksForProcess = $production->tasks
                                ? $production->tasks->where('process_id', $processId)
                                : collect();

                            $existingFiles = $tasksForProcess->flatMap(function ($task) {
                                return $task->files->map(function ($file) use ($task) {
                                    $file->task_id_for_view = $task->id;
                                    return $file;
                                });
                            });
                        @endphp

                        <div class="border p-4 mb-4 rounded bg-gray-50">
                            {{-- Process checkbox --}}
                            <label class="block font-semibold mb-2" for="process_{{ $processId }}">
                                <input type="checkbox"
                                       id="process_{{ $processId }}"
                                       name="process_ids[]"
                                       value="{{ $processId }}"
                                       class="process-checkbox"
                                       data-process-id="{{ $processId }}"
                                       {{ $checked ? 'checked' : '' }}>
                                {{ $process->processa_nosaukums }}
                            </label>

                            {{-- Existing files --}}
                            @if($existingFiles->isNotEmpty())
                                <div class="mb-3">
                                    <span class="block text-sm font-semibold mb-1">
                                        Esošie faili šim procesam ({{ $existingFiles->count() }}):
                                    </span>
                                    <ul class="list-disc ml-5 text-sm">
                                        @foreach ($existingFiles as $file)
                                            <li class="flex items-center gap-2">
                                                @if (Route::has('process-files.view'))
                                                    <a href="{{ route('process-files.view', $file) }}" target="_blank" class="text-indigo-600 hover:underline">
                                                        {{ $file->original_name ?? basename($file->path) }}
                                                    </a>
                                                @else
                                                    <a href="{{ asset('storage/'.$file->path) }}" target="_blank" class="text-indigo-600 hover:underline">
                                                        {{ $file->original_name ?? basename($file->path) }}
                                                    </a>
                                                @endif
                                                <span class="text-gray-400 text-xs">(#{{ $file->task_id_for_view }})</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @else
                                <p class="text-sm text-gray-500 mb-3">
                                    Šim procesam pašlaik nav pievienotu failu.
                                </p>
                            @endif

                            {{-- User checkboxes --}}
                            <label class="block text-sm mb-1 font-medium">
                                Darbinieki šim procesam:
                            </label>
                            <div class="grid grid-cols-2 gap-2 mb-2">
                                @foreach ($process->users as $user)
                                    <label class="flex items-center space-x-2 text-sm" for="user_{{ $processId }}_{{ $user->id }}">
                                        <input
                                            type="checkbox"
                                            id="user_{{ $processId }}_{{ $user->id }}"
                                            name="users[{{ $processId }}][]"
                                            value="{{ $user->id }}"
                                            class="user-checkbox process-{{ $processId }}"
                                            {{ in_array($user->id, $preselectedUsers) ? 'checked' : '' }}>
                                        <span>{{ $user->name }} ({{ $user->role }})</span>
                                    </label>
                                @endforeach
                            </div>

                            <p class="text-sm text-gray-500 mt-1">
                                Ja neizvēlēsieties nevienu darbinieku, uzdevums tiks piešķirts visiem šī procesa darbiniekiem.
                            </p>

                            {{-- File upload for this process --}}
                            <label class="block text-sm mt-3 mb-1" for="process_files_{{ $processId }}">
                                Pievienot jaunus failus šim procesam:
                            </label>
                            <input type="file"
                                   id="process_files_{{ $processId }}"
                                   name="process_files[{{ $processId }}][]"
                                   multiple
                                   class="block w-full text-sm text-gray-700 file:mr-3 file:py-2 file:px-3
                                   file:rounded file:border-0 file:bg-indigo-600 file:text-white hover:file:bg-indigo-700"/>
                            <p class="text-xs text-gray-500 mt-1">Varat augšupielādēt vairākus failus.</p>
                        </div>
                    @endforeach

                    @error('process_ids')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                    @error('process_files.*.*')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Submit button --}}
                <div class="mt-6">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                        Saglabāt
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- JavaScript for auto-checking users if none selected --}}
    <script>
        document.getElementById('productionForm').addEventListener('submit', function (e) {
            document.querySelectorAll('.process-checkbox:checked').forEach(checkbox => {
                const processId = checkbox.dataset.processId;
                const userCheckboxes = document.querySelectorAll('.user-checkbox.process-' + processId);
                const anyChecked = Array.from(userCheckboxes).some(cb => cb.checked);

                if (!anyChecked) {
                    // Auto-check all users for this process
                    userCheckboxes.forEach(cb => cb.checked = true);
                }
            });
        });
    </script>
</x-app-layout>
