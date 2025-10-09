{{-- resources/views/productions/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Ražošana #{{ $production->id }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <p><strong>Pasūtījums:</strong> #{{ data_get($production, 'order.id', '-') }}</p>
                    <p><strong>Produkta nosaukums:</strong> {{ $production->order->product->nosaukums ?? $production->order->produkts }}</p>
                    <p><strong>Daudzums:</strong> {{ (int) data_get($production, 'order.daudzums', 0) }}</p>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-3">Procesi</h3>

                    @php
                        $orderQty = (int) data_get($production, 'order.daudzums', 0);
                    @endphp

                    @forelse(($allTasks ?? collect())->sortBy('process_id') as $task)
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

                            // Pull ONLY progress for this task (not all tasks in the process)
                            $progressForTask = collect();
                            if ($task->process) {
                                $progressForTask = $task->process
                                    ->progress()
                                    ->where('task_id', $task->id)  // <-- key filter
                                    ->get();
                            }

                            // Latest ProcessProgress per user (for time/comment display)
                            $progressByUser = $progressForTask
                                ->groupBy('user_id')
                                ->map(function ($rows) {
                                    $latest = $rows->sortByDesc('created_at')->first();
                                    $sumHours = $rows
                                        ->whereNotNull('spent_time')
                                        ->sum('spent_time'); // sum as float

                                    // attach aggregate to the latest row object for convenient access
                                    $latest->aggregated_spent_time = $sumHours;

                                    return $latest;
                                });

                            // Sum ONLY for users we actually list in "Strādāja"
                            $displayUserIds = $byUser->pluck('id')->filter()->all();
                            $totalSpent = collect($displayUserIds)
                                ->map(fn($uid) => optional($progressByUser->get($uid))->spent_time)
                                ->filter(fn($v) => !is_null($v))
                                ->sum();

                            // Sum ALL spent_time for this task (not just latest per user)
                            $totalSpent = $progressForTask->whereNotNull('spent_time')->sum('spent_time');
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

                            @if ($byUser->isNotEmpty())
                                <div class="mt-2">
                                    <strong>Strādāja:</strong>
                                    <ul class="list-disc ml-5 text-sm mt-1">
                                        @foreach ($byUser as $row)
                                            @php $lp = $progressByUser->get($row['id'] ?? null); @endphp
                                            <li>
                                                {{ $row['name'] }} — {{ $row['total'] }}
                                                @if($lp && !is_null($lp->aggregated_spent_time))
                                                    — Pavadītais laiks: 
                                                    {{ rtrim(rtrim(number_format($lp->aggregated_spent_time, 2, '.', ''), '0'), '.') }} stundas
                                                @endif
                                                @if($lp && !empty($lp->comment))
                                                    — Komentārs: {{ $lp->comment }}
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>

                                    @if($totalSpent > 0)
                                        <p class="mt-2 text-sm">
                                            <strong>Kopējais darba laiks:</strong>
                                            {{ rtrim(rtrim(number_format($totalSpent, 2, '.', ''), '0'), '.') }} stundas
                                        </p>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @empty
                        <p>Šai ražošanai nav uzdevumu.</p>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
