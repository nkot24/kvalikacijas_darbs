<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-semibold text-white">Mani uzdevumi</h2>
            <div class="hidden sm:block text-sm text-slate-400">Aktuālie • Nākamie • Statusi</div>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6">

        {{-- Current Tasks --}}
        <div class="flex items-center justify-between gap-4 mb-3">
            <h3 class="text-lg font-semibold text-white">Aktuālie uzdevumi</h3>
            <div class="h-1 w-28 bg-gradient-to-r from-transparent via-red-600/60 to-transparent rounded"></div>
        </div>

        <div class="rounded-2xl border border-white/10 bg-white/5 backdrop-blur shadow-xl p-5 sm:p-6 mb-6">
            @forelse ($currentTasks as $task)
                <div class="rounded-2xl border border-white/10 bg-[#0B0F14]/40 p-5 mb-4">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                        <div>
                            <div class="text-xs text-slate-400">Klients</div>
                            <div class="text-white font-semibold">
                                {{ $task->production->order->klients ?? $task->production->order->client->nosaukums }}
                            </div>

                            <div class="mt-1 text-slate-200">
                                {{ optional($task->production->order->product)->nosaukums ?? $task->production->order->produkts }}
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center rounded-xl px-3 py-1 text-xs ring-1 ring-white/10 bg-white/10 text-slate-100">
                                {{ $task->process->processa_nosaukums }}
                            </span>
                        </div>
                    </div>

                    {{-- Users --}}
                    <div class="mt-4 text-sm text-slate-200 space-y-1">
                        <p>
                            <span class="text-slate-400 font-medium">Lietotāji:</span>
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
                                    <span class="text-red-300">(Kopīgs uzdevums)</span>
                                @else
                                    <span class="text-red-300">({{ implode(', ', $assignedNames) }})</span>
                                @endif
                            @endif
                        </p>

                        <p><span class="text-slate-400 font-medium">Daudzums:</span> {{ $task->production->order->daudzums }}</p>
                        <p><span class="text-slate-400 font-medium">Piezīmes:</span> {{ $task->production->order->piezimes ?? '-' }}</p>
                        <p><span class="text-slate-400 font-medium">Prioritāte:</span> {{ $task->production->order->prioritāte }}</p>
                        <p><span class="text-slate-400 font-medium">Izpildes datums:</span> {{ $task->production->order->izpildes_datums }}</p>
                    </div>

                    {{-- Progress --}}
                    <div class="mt-4">
                        @php
                            $done = (int) ($task->done_amount ?? 0);
                            $qty  = (int) ($task->production->order->daudzums ?? 0);
                            $pct  = $qty > 0 ? round(($done / $qty) * 100) : 0;
                        @endphp

                        <div class="flex items-center justify-between text-sm">
                            <div class="text-slate-300">
                                <span class="text-slate-400 font-medium">Izpildīts:</span>
                                {{ $done }} / {{ $qty }}
                            </div>
                            <div class="text-slate-400 text-xs">{{ $pct }}%</div>
                        </div>

                        <div class="w-full h-2 bg-white/10 rounded-full mt-2 overflow-hidden">
                            <div class="h-2 bg-red-600 rounded-full" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>

                    {{-- Files --}}
                    @php
                        $files = $task->relationLoaded('files')
                                ? $task->files->sortByDesc('id')
                                : $task->files()->latest()->get();
                    @endphp

                    <div class="mt-4">
                        <h5 class="font-semibold text-sm text-white mb-2">Faili</h5>

                        @if ($files->isEmpty())
                            <p class="text-sm text-slate-400">Nav pievienotu failu.</p>
                        @else
                            <ul class="text-sm space-y-2">
                                @foreach ($files as $f)
                                    <li class="flex flex-wrap items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-3 py-2">
                                        <span class="text-slate-400">📎</span>

                                        <a href="{{ route('process-files.view', $f) }}" target="_blank"
                                           class="text-red-300 hover:text-red-200 hover:underline">
                                            {{ $f->original_name }}
                                        </a>

                                        <span class="text-slate-500">({{ round(($f->size ?? 0)/1024, 1) }} KB)</span>

                                        <a href="{{ route('process-files.download', $f) }}"
                                           class="ml-auto text-slate-200 hover:text-white underline-offset-4 hover:underline">
                                            Lejupielādēt
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                    {{-- Update form --}}
                    <form action="{{ route('tasks.update', $task) }}" method="POST" class="mt-5 task-form">
                        @csrf
                        @method('PUT')

                        <div class="flex flex-col lg:flex-row lg:items-center gap-3">
                            <label for="status" class="text-sm text-slate-300 whitespace-nowrap">Statuss:</label>

                            <select name="status" required
                                    class="status-select w-full lg:w-56 rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-sm text-white
                                           focus:border-red-500/50 focus:ring-red-500/20"
                                    data-task-id="{{ $task->id }}">
                                <option class="bg-[#0B0F14]" value="nav uzsākts" {{ $task->status == 'nav uzsākts' ? 'selected' : '' }}>Nav uzsākts</option>
                                <option class="bg-[#0B0F14]" value="daļēji pabeigts" {{ $task->status == 'daļēji pabeigts' ? 'selected' : '' }}>Daļēji pabeigts</option>
                                <option class="bg-[#0B0F14]" value="pabeigts" {{ $task->status == 'pabeigts' ? 'selected' : '' }}>Pabeigts</option>
                            </select>

                            <input type="number" name="done_amount" min="0" placeholder="Paveiktais daudzums (gab.)"
                                   class="done-input w-full lg:w-64 rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-sm text-white placeholder:text-slate-500
                                          focus:border-red-500/50 focus:ring-red-500/20"
                                   data-task-id="{{ $task->id }}"
                                   style="{{ $task->status == 'daļēji pabeigts' ? '' : 'display:none' }}">

                            <input type="number" name="spent_time" min="0.01" step="0.01" placeholder="Pavadītais laiks (stundas)"
                                   class="spent-input w-full lg:w-64 rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-sm text-white placeholder:text-slate-500
                                          focus:border-red-500/50 focus:ring-red-500/20"
                                   data-task-id="{{ $task->id }}"
                                   style="{{ in_array($task->status, ['daļēji pabeigts','pabeigts']) ? '' : 'display:none' }}">

                            <input type="text" name="comment" placeholder="Komentārs (neobligāts)"
                                   class="comment-input w-full lg:flex-1 rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-sm text-white placeholder:text-slate-500
                                          focus:border-red-500/50 focus:ring-red-500/20"
                                   data-task-id="{{ $task->id }}"
                                   style="{{ in_array($task->status, ['daļēji pabeigts','pabeigts']) ? '' : 'display:none' }}">

                            <button type="submit"
                                    class="w-full lg:w-auto px-5 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-semibold shadow">
                                Atjaunināt
                            </button>
                        </div>
                    </form>
                </div>
            @empty
                <p class="text-slate-400">Nav aktuālu uzdevumu.</p>
            @endforelse
        </div>

        {{-- Future Tasks --}}
        <div class="flex items-center justify-between gap-4 mb-3">
            <h3 class="text-lg font-semibold text-white">Uzdevumi kas būs</h3>
            <div class="h-1 w-28 bg-gradient-to-r from-transparent via-red-600/60 to-transparent rounded"></div>
        </div>

        <div class="rounded-2xl border border-white/10 bg-white/5 backdrop-blur shadow-xl p-5 sm:p-6 mb-6">
            @forelse ($futureTasks as $task)
                <div class="rounded-2xl border border-white/10 bg-[#0B0F14]/40 p-5 mb-4">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                        <div>
                            <div class="text-xs text-slate-400">Klients</div>
                            <div class="text-white font-semibold">
                                {{ $task->production->order->klients ?? $task->production->order->client->nosaukums }}
                            </div>

                            <div class="mt-1 text-slate-200">
                                {{ optional($task->production->order->product)->nosaukums ?? $task->production->order->produkts }}
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center rounded-xl px-3 py-1 text-xs ring-1 ring-white/10 bg-white/10 text-slate-100">
                                {{ $task->process->processa_nosaukums }}
                            </span>
                        </div>
                    </div>

                    {{-- Users --}}
                    <div class="mt-4 text-sm text-slate-200 space-y-1">
                        <p>
                            <span class="text-slate-400 font-medium">Lietotāji:</span>
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
                                    <span class="text-red-300">(Kopīgs uzdevums)</span>
                                @else
                                    <span class="text-red-300">({{ implode(', ', $assignedNames) }})</span>
                                @endif
                            @endif
                        </p>

                        <p><span class="text-slate-400 font-medium">Daudzums:</span> {{ $task->production->order->daudzums }}</p>
                        <p><span class="text-slate-400 font-medium">Piezīmes:</span> {{ $task->production->order->piezimes ?? '-' }}</p>
                        <p><span class="text-slate-400 font-medium">Prioritāte:</span> {{ $task->production->order->prioritāte }}</p>
                        <p><span class="text-slate-400 font-medium">Izpildes datums:</span> {{ $task->production->order->izpildes_datums }}</p>
                    </div>

                    {{-- Progress --}}
                    <div class="mt-4">
                        @php
                            $done = (int) ($task->done_amount ?? 0);
                            $qty  = (int) ($task->production->order->daudzums ?? 0);
                            $pct  = $qty > 0 ? round(($done / $qty) * 100) : 0;
                        @endphp

                        <div class="flex items-center justify-between text-sm">
                            <div class="text-slate-300">
                                <span class="text-slate-400 font-medium">Izpildīts:</span>
                                {{ $done }} / {{ $qty }}
                            </div>
                            <div class="text-slate-400 text-xs">{{ $pct }}%</div>
                        </div>

                        <div class="w-full h-2 bg-white/10 rounded-full mt-2 overflow-hidden">
                            <div class="h-2 bg-red-600 rounded-full" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>

                    {{-- Files --}}
                    @php
                        $files = $task->relationLoaded('files')
                                ? $task->files->sortByDesc('id')
                                : $task->files()->latest()->get();
                    @endphp

                    <div class="mt-4">
                        <h5 class="font-semibold text-sm text-white mb-2">Faili</h5>

                        @if ($files->isEmpty())
                            <p class="text-sm text-slate-400">Nav pievienotu failu.</p>
                        @else
                            <ul class="text-sm space-y-2">
                                @foreach ($files as $f)
                                    <li class="flex flex-wrap items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-3 py-2">
                                        <span class="text-slate-400">📎</span>

                                        <a href="{{ route('process-files.view', $f) }}" target="_blank"
                                           class="text-red-300 hover:text-red-200 hover:underline">
                                            {{ $f->original_name }}
                                        </a>

                                        <span class="text-slate-500">({{ round(($f->size ?? 0)/1024, 1) }} KB)</span>

                                        <a href="{{ route('process-files.download', $f) }}"
                                           class="ml-auto text-slate-200 hover:text-white underline-offset-4 hover:underline">
                                            Lejupielādēt
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                    {{-- Update form --}}
                    <form action="{{ route('tasks.update', $task) }}" method="POST" class="mt-5 task-form">
                        @csrf
                        @method('PUT')

                        <div class="flex flex-col lg:flex-row lg:items-center gap-3">
                            <label for="status" class="text-sm text-slate-300 whitespace-nowrap">Statuss:</label>

                            <select name="status" required
                                    class="status-select w-full lg:w-56 rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-sm text-white
                                           focus:border-red-500/50 focus:ring-red-500/20"
                                    data-task-id="{{ $task->id }}">
                                <option class="bg-[#0B0F14]" value="nav uzsākts" {{ $task->status == 'nav uzsākts' ? 'selected' : '' }}>Nav uzsākts</option>
                                <option class="bg-[#0B0F14]" value="daļēji pabeigts" {{ $task->status == 'daļēji pabeigts' ? 'selected' : '' }}>Daļēji pabeigts</option>
                                <option class="bg-[#0B0F14]" value="pabeigts" {{ $task->status == 'pabeigts' ? 'selected' : '' }}>Pabeigts</option>
                            </select>

                            <input type="number" name="done_amount" min="0" placeholder="Paveiktais daudzums (gab.)"
                                   class="done-input w-full lg:w-64 rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-sm text-white placeholder:text-slate-500
                                          focus:border-red-500/50 focus:ring-red-500/20"
                                   data-task-id="{{ $task->id }}"
                                   style="{{ $task->status == 'daļēji pabeigts' ? '' : 'display:none' }}">

                            <input type="number" name="spent_time" min="0.01" step="0.01" placeholder="Pavadītais laiks (stundas)"
                                   class="spent-input w-full lg:w-64 rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-sm text-white placeholder:text-slate-500
                                          focus:border-red-500/50 focus:ring-red-500/20"
                                   data-task-id="{{ $task->id }}"
                                   style="{{ in_array($task->status, ['daļēji pabeigts','pabeigts']) ? '' : 'display:none' }}">

                            <input type="text" name="comment" placeholder="Komentārs (neobligāts)"
                                   class="comment-input w-full lg:flex-1 rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-sm text-white placeholder:text-slate-500
                                          focus:border-red-500/50 focus:ring-red-500/20"
                                   data-task-id="{{ $task->id }}"
                                   style="{{ in_array($task->status, ['daļēji pabeigts','pabeigts']) ? '' : 'display:none' }}">

                            <button type="submit"
                                    class="w-full lg:w-auto px-5 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-semibold shadow">
                                Atjaunināt
                            </button>
                        </div>
                    </form>
                </div>
            @empty
                <p class="text-slate-400">Nav gaidāmu uzdevumu.</p>
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
                if (!showDone && doneInput) doneInput.value = '';

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