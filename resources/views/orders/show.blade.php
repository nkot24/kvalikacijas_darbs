<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-white leading-tight">
                Pasūtījums <span class="text-slate-300">{{ $order->pasutijuma_numurs }}</span>
            </h2>

            <a href="{{ route('orders.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-slate-200 ring-1 ring-white/10 transition">
                ← Atpakaļ
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto px-4 sm:px-6">

            {{-- Top action row (mobile friendly) --}}
            <div class="mb-6 flex flex-wrap gap-3">
                <a href="{{ route('orders.edit', $order) }}"
                   class="px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-white ring-1 ring-white/10 transition">
                    ✏️ Rediģēt
                </a>

                <a href="{{ route('productions.create', ['order_id' => $order->id]) }}"
                   class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white font-semibold shadow">
                    ➕ Pievienot ražošanai
                </a>

                @if($order->production)
                    <a href="{{ route('productions.edit', $order->production->id) }}"
                       class="px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-white ring-1 ring-white/10 transition">
                        🛠 Labot ražošanu
                    </a>
                @endif

                <a href="{{ route('orders.print', $order) }}" target="_blank"
                   class="px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-white ring-1 ring-white/10 transition">
                    🖨 Izprintēt
                </a>

                <a href="{{ route('avansa_rekini.create', [
                        'client_id' => $order->client_id ? $order->client_id : 'one_time',
                        'order_id'  => $order->id
                    ]) }}"
                   class="px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-white ring-1 ring-white/10 transition">
                    🧾 Avansa rēķins
                </a>

                <form action="{{ route('orders.destroy', $order) }}" method="POST"
                      onsubmit="return confirm('Vai tiešām vēlaties dzēst šo pasūtījumu?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white font-semibold shadow">
                        🗑 Dzēst
                    </button>
                </form>
            </div>

            {{-- Order info card --}}
            <div class="rounded-2xl border border-white/10 bg-white/5 backdrop-blur shadow-xl">
                <div class="p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="text-sm text-slate-400">Pasūtījuma numurs</div>
                            <div class="text-xl font-semibold text-white">{{ $order->pasutijuma_numurs }}</div>
                        </div>

                        <div class="text-right">
                            <div class="text-sm text-slate-400">Statuss</div>
                            <span class="inline-flex items-center rounded-xl px-3 py-1 text-sm ring-1 ring-white/10 bg-white/10 text-white">
                                {{ $order->statuss }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="rounded-xl border border-white/10 bg-[#0B0F14]/40 p-4">
                            <div class="text-xs text-slate-400">Datums</div>
                            <div class="text-white">
                                {{ optional($order->datums)->format('d.m.Y H:i') ?? $order->datums }}
                            </div>
                        </div>

                        <div class="rounded-xl border border-white/10 bg-[#0B0F14]/40 p-4">
                            <div class="text-xs text-slate-400">Izpildes datums</div>
                            <div class="text-white">
                                {{ $order->izpildes_datums ? \Carbon\Carbon::parse($order->izpildes_datums)->format('d.m.Y') : '—' }}
                            </div>
                        </div>

                        <div class="rounded-xl border border-white/10 bg-[#0B0F14]/40 p-4">
                            <div class="text-xs text-slate-400">Klients</div>
                            <div class="text-white">
                                {{ $order->client->nosaukums ?? $order->klients }}
                            </div>
                        </div>

                        <div class="rounded-xl border border-white/10 bg-[#0B0F14]/40 p-4">
                            <div class="text-xs text-slate-400">Produkts</div>
                            <div class="text-white">
                                {{ $order->product->nosaukums ?? $order->produkts }}
                            </div>
                        </div>

                        <div class="rounded-xl border border-white/10 bg-[#0B0F14]/40 p-4">
                            <div class="text-xs text-slate-400">Daudzums</div>
                            <div class="text-white font-semibold">
                                {{ $order->daudzums }}
                            </div>
                        </div>

                        <div class="rounded-xl border border-white/10 bg-[#0B0F14]/40 p-4">
                            <div class="text-xs text-slate-400">Prioritāte</div>
                            <div class="text-white">
                                {{ $order->prioritāte }}
                            </div>
                        </div>

                        <div class="sm:col-span-2 rounded-xl border border-white/10 bg-[#0B0F14]/40 p-4">
                            <div class="text-xs text-slate-400">Piezīmes</div>
                            <div class="text-white">
                                {{ $order->piezimes ?? '—' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Processes / Production --}}
            <div class="mt-6 rounded-2xl border border-white/10 bg-white/5 backdrop-blur shadow-xl">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-white">Procesi</h3>
                        <div class="h-1 w-24 bg-gradient-to-r from-transparent via-red-600 to-transparent rounded"></div>
                    </div>

                    @php
                        $production = $order->production ?? null;
                    @endphp

                    @if (!$production)
                        <p class="mt-4 text-slate-300">
                            Šim pasūtījumam vēl nav izveidota ražošana.
                        </p>
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

                        <div class="mt-4 space-y-4">
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

                                    $statusDone = ($task->status === 'pabeigts');
                                @endphp

                                <div class="rounded-2xl border border-white/10 bg-[#0B0F14]/40 p-5 {{ $statusDone ? 'opacity-90' : '' }}">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <div class="text-sm text-slate-400">Process</div>
                                            <div class="text-white font-semibold">
                                                {{ data_get($task, 'process.processa_nosaukums', '-') }}
                                            </div>

                                            @php
                                                $processUsers = $task->process->users->pluck('id')->toArray();
                                                $assignedUsers = $task->assignedUsers->pluck('id')->toArray();
                                                $assignedNames = $task->assignedUsers->pluck('name')->toArray();
                                                $isSharedWithAll = count(array_diff($processUsers, $assignedUsers)) === 0;
                                            @endphp

                                            <div class="mt-1 text-sm text-slate-300">
                                                <span class="text-slate-400 font-medium">Lietotāji:</span>
                                                @if ($task->user_id !== null)
                                                    {{ $task->user->name ?? 'Nezināms lietotājs' }}
                                                @else
                                                    @if ($isSharedWithAll)
                                                        <span class="text-red-300">(Kopīgs uzdevums)</span>
                                                    @else
                                                        <span class="text-red-300">({{ implode(', ', $assignedNames) }})</span>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>

                                        <span class="px-3 py-1 text-xs rounded-xl ring-1 ring-white/10
                                              {{ $statusDone ? 'bg-emerald-500/15 text-emerald-200' : 'bg-white/10 text-slate-200' }}">
                                            {{ $task->status }}
                                        </span>
                                    </div>

                                    {{-- Progress --}}
                                    <div class="mt-4">
                                        <div class="flex items-center justify-between text-sm">
                                            <div class="text-slate-300">
                                                <span class="text-slate-400 font-medium">Progres:</span>
                                                {{ $done }} / {{ $orderQty }} ({{ $pct }}%)
                                            </div>
                                            <div class="text-slate-400 text-xs">
                                                {{ $pct }}%
                                            </div>
                                        </div>

                                        <div class="w-full h-2 bg-white/10 rounded-full mt-2 overflow-hidden">
                                            <div class="h-2 bg-red-600 rounded-full" style="width: {{ $pct }}%"></div>
                                        </div>
                                    </div>

                                    {{-- Files --}}
                                    @if ($task->files->isNotEmpty())
                                        <div class="mt-4">
                                            <div class="text-sm text-slate-400 font-medium">Faili</div>
                                            <ul class="mt-2 space-y-1 text-sm">
                                                @foreach ($task->files as $file)
                                                    <li class="flex items-center gap-2">
                                                        <span class="text-slate-500">•</span>
                                                        @if (Route::has('process-files.view'))
                                                            <a href="{{ route('process-files.view', $file) }}"
                                                               target="_blank"
                                                               class="text-red-300 hover:text-red-200 hover:underline">
                                                                {{ $file->original_name ?? basename($file->path) }}
                                                            </a>
                                                        @else
                                                            <a href="{{ asset('storage/'.$file->path) }}"
                                                               target="_blank"
                                                               class="text-red-300 hover:text-red-200 hover:underline">
                                                                {{ $file->original_name ?? basename($file->path) }}
                                                            </a>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    {{-- Work logs --}}
                                    @if ($byUser->isNotEmpty())
                                        <div class="mt-4">
                                            <div class="text-sm text-slate-400 font-medium">Strādāja</div>

                                            <ul class="mt-2 space-y-1 text-sm text-slate-200">
                                                @foreach ($byUser as $row)
                                                    @php $lp = $progressByUser->get($row['id'] ?? null); @endphp
                                                    <li class="leading-relaxed">
                                                        <span class="text-white font-medium">{{ $row['name'] }}</span>
                                                        <span class="text-slate-400">—</span>
                                                        {{ $row['total'] }}

                                                        @if($lp && !is_null($lp->aggregated_spent_time))
                                                            <span class="text-slate-400">—</span>
                                                            <span class="text-slate-300">
                                                                Pavadītais laiks:
                                                                {{ rtrim(rtrim(number_format($lp->aggregated_spent_time, 2, '.', ''), '0'), '.') }} h
                                                            </span>
                                                        @endif

                                                        @if($lp && !empty($lp->comment))
                                                            <span class="text-slate-400">—</span>
                                                            <span class="text-slate-300">Komentārs: {{ $lp->comment }}</span>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>

                                            @if($totalSpent > 0)
                                                <p class="mt-3 text-sm text-slate-300">
                                                    <span class="text-slate-400 font-medium">Kopējais darba laiks:</span>
                                                    {{ rtrim(rtrim(number_format($totalSpent, 2, '.', ''), '0'), '.') }} h
                                                </p>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <p class="text-slate-300">Šai ražošanai nav uzdevumu.</p>
                            @endforelse
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>