<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-white leading-tight">
                Rediģēt ražošanu #{{ $production->id }}
            </h2>
            <div class="hidden sm:block text-sm text-slate-400">
                Ražošana • Procesi • Faili
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-0">

            {{-- Errors --}}
            @if ($errors->any())
                <div class="mb-5 rounded-2xl border border-red-500/20 bg-red-500/10 px-4 py-3 text-red-200">
                    <div class="font-semibold">Kļūda!</div>
                    <ul class="mt-2 list-disc pl-5 text-sm text-red-200/90 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="rounded-2xl border border-white/10 bg-white/5 backdrop-blur shadow-xl">
                <form id="productionForm"
                      action="{{ route('productions.update', $production) }}"
                      method="POST"
                      enctype="multipart/form-data"
                      class="p-5 sm:p-6">
                    @csrf
                    @method('PUT')

                    {{-- Order Selection --}}
                    <div class="mb-6">
                        <label for="order_id" class="block text-sm font-medium text-slate-200 mb-2">
                            Izvēlieties pasūtījumu
                        </label>

                        <select name="order_id" id="order_id" required
                                class="w-full rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-sm text-white
                                       focus:border-red-500/50 focus:ring-red-500/20">
                            @foreach ($orders as $order)
                                <option value="{{ $order->id }}"
                                    {{ old('order_id', $production->order_id) == $order->id ? 'selected' : '' }}>
                                    {{ $order->pasutijuma_numurs }} – {{ $order->product->nosaukums ?? $order->produkts }}
                                </option>
                            @endforeach
                        </select>

                        @error('order_id')
                            <p class="text-sm text-red-300 mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Global files --}}
                    <div class="mb-6">
                        <div class="flex items-center justify-between gap-3 mb-2">
                            <label class="block text-sm font-medium text-slate-200" for="global_files">
                                Pievienot jaunus failus visiem izvēlētajiem procesiem
                            </label>
                            <span class="text-xs text-slate-500">Pievienosies izvēlētajiem procesiem</span>
                        </div>

                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                            <input type="file" name="global_files[]" id="global_files" multiple
                                   class="block w-full text-xs sm:text-sm text-slate-300
                                          file:mr-2 file:py-2 file:px-3
                                          file:rounded-xl file:border-0
                                          file:text-xs sm:file:text-sm file:font-semibold
                                          file:bg-white/10 file:text-white hover:file:bg-white/15
                                          cursor-pointer"/>

                            <p class="text-xs text-slate-500 mt-2">
                                Ja pievienosiet failus šeit, tie tiks pievienoti visiem pašlaik izvēlētajiem procesiem.
                            </p>

                            @error('global_files.*')
                                <p class="text-sm text-red-300 mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Processes and Users --}}
                    <div class="mb-2">
                        <div class="flex items-center justify-between gap-3 mb-3">
                            <label class="block text-sm font-medium text-slate-200">
                                Procesi un darbinieki
                            </label>
                            <span class="text-xs text-slate-500 hidden sm:block">
                                Ja nevienu neizvēlies — tiks piešķirts visiem
                            </span>
                        </div>

                        <div class="space-y-4">
                            @foreach ($processes as $process)
                                @php
                                    $processId = $process->id;

                                    $checked = in_array($processId, old('process_ids', $selectedProcessIds ?? []));

                                    $preselectedUsers = old('users.' . $processId, $selectedUsersByProcess[$processId] ?? []);

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

                                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">

                                    {{-- Process checkbox --}}
                                    <label class="flex items-center gap-3 mb-3" for="process_{{ $processId }}">
                                        <input type="checkbox"
                                               id="process_{{ $processId }}"
                                               name="process_ids[]"
                                               value="{{ $processId }}"
                                               class="process-checkbox h-4 w-4 rounded border-white/20 bg-transparent text-red-600 focus:ring-red-500/20"
                                               data-process-id="{{ $processId }}"
                                               {{ $checked ? 'checked' : '' }}>
                                        <span class="text-sm font-semibold text-white">
                                            {{ $process->processa_nosaukums }}
                                        </span>
                                    </label>

                                    {{-- Existing files --}}
                                    <div class="mb-3">
                                        @if($existingFiles->isNotEmpty())
                                            <div class="text-xs text-slate-400 mb-2">
                                                Esošie faili ({{ $existingFiles->count() }})
                                            </div>

                                            <ul class="space-y-1 text-sm">
                                                @foreach ($existingFiles as $file)
                                                    <li class="flex items-center gap-2">
                                                        <span class="text-slate-500">📎</span>

                                                        @if (Route::has('process-files.view'))
                                                            <a href="{{ route('process-files.view', $file) }}" target="_blank"
                                                               class="text-red-300 hover:text-red-200 hover:underline underline-offset-4">
                                                                {{ $file->original_name ?? basename($file->path) }}
                                                            </a>
                                                        @else
                                                            <a href="{{ asset('storage/'.$file->path) }}" target="_blank"
                                                               class="text-red-300 hover:text-red-200 hover:underline underline-offset-4">
                                                                {{ $file->original_name ?? basename($file->path) }}
                                                            </a>
                                                        @endif

                                                        <span class="text-xs text-slate-500">(#{{ $file->task_id_for_view }})</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <p class="text-sm text-slate-500">
                                                Šim procesam pašlaik nav pievienotu failu.
                                            </p>
                                        @endif
                                    </div>

                                    {{-- User checkboxes --}}
                                    <div class="mb-3">
                                        <div class="text-xs text-slate-400 mb-2">Darbinieki šim procesam</div>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            @foreach ($process->users as $user)
                                                <label class="flex items-center gap-3 rounded-xl border border-white/10 bg-[#0B0F14]/40 px-3 py-2 hover:bg-white/5 transition"
                                                       for="user_{{ $processId }}_{{ $user->id }}">
                                                    <input type="checkbox"
                                                           id="user_{{ $processId }}_{{ $user->id }}"
                                                           name="users[{{ $processId }}][]"
                                                           value="{{ $user->id }}"
                                                           class="user-checkbox process-{{ $processId }} h-4 w-4 rounded border-white/20 bg-transparent text-red-600 focus:ring-red-500/20"
                                                           {{ in_array($user->id, $preselectedUsers) ? 'checked' : '' }}>
                                                    <span class="text-sm text-slate-200">
                                                        {{ $user->name }}
                                                        <span class="text-xs text-slate-500">({{ $user->role }})</span>
                                                    </span>
                                                </label>
                                            @endforeach
                                        </div>

                                        <p class="text-xs text-slate-500 mt-2">
                                            Ja neizvēlēsieties nevienu darbinieku, uzdevums tiks piešķirts visiem šī procesa darbiniekiem.
                                        </p>
                                    </div>

                                    {{-- File upload for this process --}}
                                    <div class="pt-3 border-t border-white/10">
                                        <div class="flex items-center justify-between gap-3 mb-2">
                                            <label class="text-xs text-slate-400" for="process_files_{{ $processId }}">
                                                Pievienot jaunus failus šim procesam
                                            </label>
                                            <span class="text-xs text-slate-500">Var vairāki</span>
                                        </div>

                                        <input type="file"
                                               id="process_files_{{ $processId }}"
                                               name="process_files[{{ $processId }}][]"
                                               multiple
                                               class="block w-full text-xs sm:text-sm text-slate-300
                                                      file:mr-2 file:py-2 file:px-3
                                                      file:rounded-xl file:border-0
                                                      file:text-xs sm:file:text-sm file:font-semibold
                                                      file:bg-white/10 file:text-white hover:file:bg-white/15
                                                      cursor-pointer"/>

                                        <p class="text-xs text-slate-500 mt-2">Varat augšupielādēt vairākus failus.</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @error('process_ids')
                            <p class="text-sm text-red-300 mt-3">{{ $message }}</p>
                        @enderror
                        @error('process_files.*.*')
                            <p class="text-sm text-red-300 mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Submit --}}
                    <div class="mt-6 flex flex-col sm:flex-row gap-3 sm:justify-end">
                        <a href="{{ url()->previous() }}"
                           class="px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-slate-200 text-sm ring-1 ring-white/10 transition text-center">
                            Atcelt
                        </a>

                        <button type="submit"
                                class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-semibold shadow">
                            Saglabāt
                        </button>
                    </div>
                </form>
            </div>

            <div class="mt-6 h-1 bg-gradient-to-r from-transparent via-red-600/40 to-transparent rounded"></div>
        </div>
    </div>

    {{-- JavaScript for auto-checking users if none selected --}}
    <script>
        document.getElementById('productionForm').addEventListener('submit', function () {
            document.querySelectorAll('.process-checkbox:checked').forEach(checkbox => {
                const processId = checkbox.dataset.processId;
                const userCheckboxes = document.querySelectorAll('.user-checkbox.process-' + processId);
                const anyChecked = Array.from(userCheckboxes).some(cb => cb.checked);

                if (!anyChecked) {
                    userCheckboxes.forEach(cb => cb.checked = true);
                }
            });
        });
    </script>
</x-app-layout>