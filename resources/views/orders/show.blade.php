<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Pasūtījums {{ $order->pasutijuma_numurs }}
        </h2>
    </x-slot>

    <div class="py-6">
        {{-- Pasūtījuma informācija --}}
        <div class="max-w-4xl mx-auto bg-white shadow-sm rounded-lg p-6 space-y-4">
            <div><strong>Datums:</strong> {{ optional($order->datums)->format('d.m.Y H:i') ?? $order->datums }}</div>
            <div><strong>Klients:</strong> {{ $order->client->nosaukums ?? $order->klients }}</div>
            <div><strong>Produkts:</strong> {{ $order->product->nosaukums ?? $order->produkts }}</div>
            <div><strong>Daudzums:</strong> {{ $order->daudzums }}</div>
            <div><strong>Izpildes datums:</strong>
                {{ $order->izpildes_datums ? \Carbon\Carbon::parse($order->izpildes_datums)->format('d.m.Y') : '—' }}
            </div>
            <div><strong>Prioritāte:</strong> {{ $order->prioritāte }}</div>
            <div><strong>Statuss:</strong> {{ $order->statuss }}</div>
            <div><strong>Piezīmes:</strong> {{ $order->piezimes ?? '—' }}</div>

            <div class="pt-4">
                <a href="{{ route('orders.print', $order) }}" target="_blank"
                   class="inline-block px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                    🖨️ Izprintēt ražošanas lapu
                </a>
            </div>
        </div>

        {{-- Procesu progress --}}
        <div class="max-w-4xl mx-auto mt-6 bg-white shadow-sm rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-3">Procesi</h3>

            @php
                /** @var \App\Models\Production|null $production */
                $production = $order->production ?? null;
            @endphp

            @if (!$production)
                <p>Šim pasūtījumam vēl nav izveidota ražošana.</p>
            @else
                @php
                    // Eager-load everything we show (avoids N+1)
                    $production->load([
                        'tasks.process',
                        'tasks.user',
                        'tasks.workLogs.user',
                        'tasks.files',
                    ]);

                    $orderQty = (int) ($order->daudzums ?? 0);
                    $tasks = $production->tasks->sortBy('process_id');
                @endphp

                @forelse ($tasks as $task)
                    @php
                        $done = (int) ($task->done_amount ?? 0);
                        $pct  = $orderQty > 0 ? round(($done / $orderQty) * 100) : 0;

                        // Per-user totals from work logs (keep user id)
                        $byUser = $task->workLogs
                            ->groupBy('user_id')
                            ->map(function ($logs, $uid) {
                                return [
                                    'id'    => $uid,
                                    'name'  => optional($logs->first()->user)->name ?? 'Nezināms',
                                    'total' => $logs->sum('amount'),
                                ];
                            })
                            ->sortByDesc('total');

                        // Pull ONLY progress for this task (if you have process->progress relation)
                        $progressForTask = collect();
                        if ($task->process && method_exists($task->process, 'progress')) {
                            $progressForTask = $task->process
                                ->progress()
                                ->where('task_id', $task->id)
                                ->get();
                        }

                        // Latest ProcessProgress per user (for time/comment display)
                        $progressByUser = $progressForTask
                            ->sortByDesc('created_at')
                            ->groupBy('user_id')
                            ->map->first();

                        // Sum ONLY for users we actually list in "Strādāja"
                        $displayUserIds = $byUser->pluck('id')->filter()->all();
                        $totalSpent = collect($displayUserIds)
                            ->map(fn($uid) => optional($progressByUser->get($uid))->spent_time)
                            ->filter(fn($v) => !is_null($v))
                            ->sum();
                    @endphp

                    <div class="border-b py-3 {{ $task->status === 'pabeigts' ? 'opacity-90' : '' }}">
                        <div class="flex items-start justify-between">
                            <div>
                                <div><strong>Process:</strong> {{ data_get($task, 'process.processa_nosaukums', '-') }}</div>
                                <div class="text-sm text-gray-700">
                                    <strong>Lietotājs:</strong>
                                    @if ($task->user) {{ $task->user->name }}
                                    @else <span class="text-blue-600">Kopīgs uzdevums</span>
                                    @endif
                                </div>
                            </div>
                            <span class="px-2 py-1 text-xs rounded
                                  {{ $task->status === 'pabeigts' ? 'bg-green-100 text-green-800' : 'bg-gray-100' }}">
                                {{ $task->status }}
                            </span>
                        </div>

                        <div class="mt-2">
                            <div><strong>Progres:</strong> {{ $done }} / {{ $orderQty }} ({{ $pct }}%)</div>
                            <div class="w-full h-2 bg-gray-200 rounded mt-1">
                                <div class="h-2 bg-green-500 rounded" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>

                        {{-- Faili pie uzdevuma --}}
                        @if ($task->files->isNotEmpty())
                            <div class="mt-2">
                                <strong>Faili:</strong>
                                <ul class="list-disc ml-5 text-sm mt-1">
                                    @foreach ($task->files as $file)
                                        <li class="flex items-center gap-2">
                                            {{-- View link (prefer controller route if exists, fallback to /storage) --}}
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

                                            
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if ($byUser->isNotEmpty())
                            <div class="mt-2">
                                <strong>Strādāja:</strong>
                                <ul class="list-disc ml-5 text-sm mt-1">
                                    @foreach ($byUser as $row)
                                        @php $lp = $progressByUser->get($row['id'] ?? null); @endphp
                                        <li>
                                            {{ $row['name'] }} — {{ $row['total'] }}
                                            @if($lp && !is_null($lp->spent_time))
                                                — Pavadītais laiks: {{ $lp->spent_time }} min
                                            @endif
                                            @if($lp && !empty($lp->comment))
                                                — Komentārs: {{ $lp->comment }}
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>

                                @if($totalSpent > 0)
                                    <p class="mt-2 text-sm">
                                        <strong>Kopējais darba laiks:</strong> {{ $totalSpent }} min
                                    </p>
                                @endif
                            </div>
                        @endif
                    </div>
                @empty
                    <p>Šai ražošanai nav uzdevumu.</p>
                @endforelse
            @endif
        </div>
    </div>
</x-app-layout>
