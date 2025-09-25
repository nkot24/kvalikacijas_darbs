<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Rediģēt ražošanu #{{ $production->id }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto bg-white p-6 rounded shadow">

            <form action="{{ route('productions.update', $production) }}"
                  method="POST"
                  enctype="multipart/form-data">
                @csrf
                @method('PUT')

                {{-- Order Selection --}}
                <div class="mb-6">
                    <label for="order_id" class="block font-semibold mb-2">
                        Izvēlieties pasūtījumu:
                    </label>

                    <select name="order_id" required class="w-full border border-gray-300 p-2 rounded">
                        @foreach ($orders as $order)
                            <option value="{{ $order->id }}" {{ $production->order_id == $order->id ? 'selected' : '' }}>
                                {{ $order->pasutijuma_numurs }} – {{ $order->product->nosaukums ?? $order->produkts }}
                            </option>
                        @endforeach
                    </select>
                    @error('order_id')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Global files (applies to all selected processes) --}}
                <div class="mb-6">
                    <label class="block font-semibold mb-2">
                        Pievienot jaunus failus visiem izvēlētajiem procesiem:
                    </label>
                    <input type="file"
                           name="global_files[]"
                           multiple
                           class="block w-full text-sm text-gray-700 file:mr-3 file:py-2 file:px-3 file:rounded file:border-0 file:bg-indigo-600 file:text-white hover:file:bg-indigo-700"/>
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
                            $checked = in_array($process->id, $selectedProcessIds ?? []);
                            $preselectedUsers = $selectedUsersByProcess[$process->id] ?? [];

                            // Collect existing files for this process from this production's tasks
                            // (Controller should eager-load: $production->load(['tasks.files','tasks.process']))
                            $tasksForProcess = $production->tasks
                                ? $production->tasks->where('process_id', $process->id)
                                : collect();

                            $existingFiles = $tasksForProcess->flatMap(function ($task) {
                                return $task->files->map(function ($f) use ($task) {
                                    $f->task_id_for_view = $task->id; // optional tag
                                    return $f;
                                });
                            });
                        @endphp

                        <div class="border p-4 mb-4 rounded bg-gray-50">
                            {{-- Process checkbox --}}
                            <label class="block font-semibold mb-2">
                                <input type="checkbox" name="process_ids[]" value="{{ $process->id }}" {{ $checked ? 'checked' : '' }}>
                                {{ $process->processa_nosaukums }}
                            </label>

                            {{-- EXISTING FILES for this process --}}
                            @if($existingFiles->isNotEmpty())
                                <div class="mb-3">
                                    <span class="block text-sm font-semibold mb-1">
                                        Esošie faili šim procesam ({{ $existingFiles->count() }}):
                                    </span>
                                    <ul class="list-disc ml-5 text-sm">
                                        @foreach ($existingFiles as $file)
                                            <li class="flex items-center gap-2">
                                                @if (Route::has('process-files.view'))
                                                    <a href="{{ route('process-files.view', $file) }}"
                                                       target="_blank"
                                                       class="text-indigo-600 hover:underline">
                                                        {{ $file->original_name ?? basename($file->path) }}
                                                    </a>
                                                @else
                                                    <a href="{{ asset('storage/'.$file->path) }}"
                                                       target="_blank"
                                                       class="text-indigo-600 hover:underline">
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

                            {{-- User assignment --}}
                            <label class="block text-sm mb-1">
                                Darbinieki šim procesam:
                            </label>
                            <select name="users[{{ $process->id }}][]" multiple class="w-full border border-gray-300 p-2 rounded">
                                @foreach ($process->users as $user)
                                    <option value="{{ $user->id }}" {{ in_array($user->id, $preselectedUsers) ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->role }})
                                    </option>
                                @endforeach
                            </select>

                            <p class="text-sm text-gray-500 mt-1">
                                Ja neizvēlēsieties nevienu darbinieku, uzdevums tiks piešķirts visiem šī procesa darbiniekiem.
                            </p>

                            {{-- File upload for this process --}}
                            <label class="block text-sm mt-3 mb-1">
                                Pievienot jaunus failus šim procesam:
                            </label>
                            <input type="file"
                                   name="process_files[{{ $process->id }}][]"
                                   multiple
                                   class="block w-full text-sm text-gray-700 file:mr-3 file:py-2 file:px-3 file:rounded file:border-0 file:bg-indigo-600 file:text-white hover:file:bg-indigo-700"/>
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
</x-app-layout>
