<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Pasūtījums {{ $order->pasutijuma_numurs }}
        </h2>
    </x-slot>

    <div class="py-6">
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

        {{-- NEW: Procesu progress for this order's ražošana --}}
        <div class="max-w-4xl mx-auto mt-6 bg-white shadow-sm rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-3">Procesi</h3>

            @php
                $production = $order->production ?? null;
            @endphp

            @if (!$production)
                <p>Šim pasūtījumam vēl nav izveidota ražošana.</p>
            @else
                @php
                    $orderQty = (int) ($order->daudzums ?? 0);
                    // Make sure controller eager-loaded: production.tasks.process, .user, .workLogs.user
                    $tasks = $production->tasks->sortBy('process_id');
                @endphp

                @forelse ($tasks as $task)
                    @php
                        $done = (int) ($task->done_amount ?? 0);
                        $pct  = $orderQty > 0 ? round(($done / $orderQty) * 100) : 0;

                        $byUser = $task->workLogs
                            ->groupBy('user_id')
                            ->map(fn($logs) => [
                                'name'  => optional($logs->first()->user)->name ?? 'Nezināms',
                                'total' => $logs->sum('amount'),
                            ])
                            ->sortByDesc('total');
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
                                        <li>{{ $row['name'] }} — {{ $row['total'] }}</li>
                                    @endforeach
                                </ul>
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
