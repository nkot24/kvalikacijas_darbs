<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Pasūtījums {{ $order->pasutijuma_numurs }}
        </h2>
    </x-slot>

    <div class="py-6">

        {{-- Back Button --}}
        <div class="max-w-4xl mx-auto mb-4 px-6">
            <a href="{{ route('orders.index') }}"
            class="inline-block px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                ← Atpakaļ
            </a>
        </div>



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

            {{-- Action Buttons --}}
            <div class="pt-6 flex flex-wrap gap-3">
                {{-- Edit order --}}
                <a href="{{ route('orders.edit', $order) }}" 
                   class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Rediģēt pasūtījumu
                </a>

                {{-- Create production --}}
                <a href="{{ route('productions.create', ['order_id' => $order->id]) }}" 
                   class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                    Pievienot ražošanai
                </a>

                {{-- Edit production (only if exists) --}}
                @if($order->production)
                    <a href="{{ route('productions.edit', $order->production->id) }}" 
                       class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                        Labot ražošanu
                    </a>
                @endif

                {{-- Print --}}
                <a href="{{ route('orders.print', $order) }}" target="_blank"
                   class="px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700">
                    Izprintēt
                </a>

                {{-- Invoice --}}
                <a href="{{ route('avansa_rekini.create', [
                        'client_id' => $order->client_id ? $order->client_id : 'one_time',
                        'order_id'  => $order->id
                    ]) }}" 
                   class="px-4 py-2 bg-amber-500 text-white rounded hover:bg-amber-600">
                    Avansa rēķins
                </a>

                {{-- Delete --}}
                <form action="{{ route('orders.destroy', $order) }}" method="POST" 
                      onsubmit="return confirm('Vai tiešām vēlaties dzēst šo pasūtījumu?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                        Dzēst
                    </button>
                </form>
            </div>
        </div>

        {{-- Procesu progress --}}
        <div class="max-w-4xl mx-auto mt-6 bg-white shadow-sm rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-3">Procesi</h3>

            @php
                $production = $order->production ?? null;
            @endphp

            @if (!$production)
                <p>Šim pasūtījumam vēl nav izveidota ražošana.</p>
            @else
                @php
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

                        $byUser = $task->workLogs
                            ->groupBy('user_id')
                            ->map(function ($logs, $uid) {
                                return [
                                    'id'    => $uid,
                                    'name'  => optional($logs->first()->user)->name ?? 'Nezināms',
                                    'total' => (int) $logs->sum('amount'),
                                ];
                            })
                            ->sortByDesc('total');

                        $progressForTask = collect();
                        if ($task->process && method_exists($task->process, 'progress')) {
                            $progressForTask = $task->process
                                ->progress()
                                ->where('task_id', $task->id)
                                ->get();
                        }

                        $progressByUser = $progressForTask
                            ->groupBy('user_id')
                            ->map(function ($rows) {
                                $latest = $rows->sortByDesc('created_at')->first();
                                $sumHours = $rows
                                    ->whereNotNull('spent_time')
                                    ->sum('spent_time');
                                $latest->aggregated_spent_time = $sumHours;
                                return $latest;
                            });

                        $displayUserIds = $byUser->pluck('id')->filter()->all();
                        $totalSpent = collect($displayUserIds)
                            ->map(fn($uid) => optional($progressByUser->get($uid))->aggregated_spent_time)
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
            @endif
        </div>
    </div>
</x-app-layout>
