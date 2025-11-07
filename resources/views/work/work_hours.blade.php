<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
            {{ __('Darba stundas') }}
        </h2>
    </x-slot>

    {{-- Alpine.js (for dropdown search) --}}
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <div class="py-8 max-w-6xl mx-auto">
        <div class="bg-white shadow-md rounded-2xl p-6">
            <form method="GET" 
                  class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6 items-end"
                  x-data="{ 
                      search: '{{ request('user_id') === 'all' ? 'Visi' : (optional($users->firstWhere('id', request('user_id')))->name ?? '') }}',
                      open: false 
                  }">

                {{-- ✅ Lietotājs (searchable select) --}}
                <div class="relative">
                    <label class="block mb-1 text-sm font-medium text-gray-700">Lietotājs</label>
                    <input type="text"
                           x-model="search"
                           @focus="open = true"
                           @click="open = true"
                           @click.outside="open = false"
                           placeholder="-- Izvēlies lietotāju --"
                           class="w-full border border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 rounded-lg px-3 py-2"
                           autocomplete="off">
                    <input type="hidden" name="user_id" id="user_id" value="{{ request('user_id') }}">
                    <span class="pointer-events-none absolute right-3 top-9 text-slate-500">▾</span>

                    <ul x-show="open" x-transition
                        class="absolute left-0 right-0 z-20 mt-1 bg-white border rounded shadow max-h-60 overflow-auto">
                        <li @click="
                                search='Visi';
                                document.getElementById('user_id').value='all';
                                open=false;
                            "
                            class="px-3 py-2 cursor-pointer hover:bg-blue-50">
                            Visi
                        </li>

                        @foreach ($users as $u)
                            <li @click="
                                    search='{{ $u->name }}';
                                    document.getElementById('user_id').value='{{ $u->id }}';
                                    open=false;
                                "
                                class="px-3 py-2 cursor-pointer hover:bg-blue-50"
                                x-show="'{{ strtolower($u->name) }}'.includes(search.toLowerCase())">
                                {{ $u->name }}
                            </li>
                        @endforeach
                        <li x-show="!Array.from($el.parentElement.children).some(li => li.offsetParent !== null)"
                            class="px-3 py-2 text-slate-500">
                            Nav rezultātu…
                        </li>
                    </ul>
                </div>

                {{-- Date From --}}
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">No</label>
                    <input type="date" name="from" value="{{ request('from') }}"
                           class="border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 rounded-lg p-2 w-full">
                </div>

                {{-- Date To --}}
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">Līdz</label>
                    <input type="date" name="to" value="{{ request('to') }}"
                           class="border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 rounded-lg p-2 w-full">
                </div>

                {{-- Search --}}
                <div class="flex justify-center md:justify-start">
                    <x-primary-button class="w-full md:w-auto py-2 px-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow">
                        Meklēt
                    </x-primary-button>
                </div>
            </form>

            {{-- ✅ Work logs table --}}
            @if ($logs->count())
                @if (request('user_id') === 'all')
                    {{-- ✅ Summary for all users --}}
                    <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                        <table class="min-w-full text-center">
                            <thead class="bg-gray-100 text-gray-800">
                                <tr>
                                    <th class="px-4 py-2 border-b">Lietotājs</th>
                                    <th class="px-4 py-2 border-b">Kopā stundas (ar pusdienām)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($userTotals as $userId => $hours)
                                    @php $user = $users->firstWhere('id', $userId); @endphp
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-4 py-2 border-b">{{ $user->name ?? 'Dzēsts lietotājs' }}</td>
                                        <td class="px-4 py-2 border-b text-indigo-700 font-semibold">
                                            {{ number_format($hours, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                                <tr class="bg-indigo-50 font-semibold">
                                    <td class="text-right px-4 py-2 border-t border-gray-300">Kopā:</td>
                                    <td class="px-4 py-2 border-t border-gray-300 text-indigo-700">
                                        {{ number_format($totalHours, 2) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @else
                    {{-- ✅ Single user table --}}
                    <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                        <table class="min-w-full text-center">
                            <thead class="bg-gray-100 text-gray-800">
                                <tr>
                                    <th class="px-4 py-2 border-b">Datums</th>
                                    <th class="px-4 py-2 border-b">Sāka darbu</th>
                                    <th class="px-4 py-2 border-b">Beidza darbu</th>
                                    <th class="px-4 py-2 border-b">Pusdienas (min)</th>
                                    <th class="px-4 py-2 border-b">Paužu skaits</th>
                                    <th class="px-4 py-2 border-b">Kopā stundas (ar pusdienām)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($logs as $log)
                                    @php
                                        $displayDate = $log->date instanceof \Carbon\Carbon
                                            ? $log->date->format('Y-m-d')
                                            : \Carbon\Carbon::parse($log->date)->format('Y-m-d');

                                        $hoursClass = $log->adjusted_hours >= 8
                                            ? 'text-green-600 font-semibold'
                                            : 'text-red-600 font-semibold';
                                    @endphp

                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-4 py-2 border-b">{{ $displayDate }}</td>

                                        {{-- ✅ Editable start_time (time) --}}
                                        <td class="px-4 py-2 border-b cursor-pointer hover:bg-blue-50"
                                            data-editable="true"
                                            data-type="time"
                                            data-id="{{ $log->id }}"
                                            data-column="start_time">
                                            {{ $log->start_time ?? '-' }}
                                        </td>

                                        {{-- ✅ Editable end_time (time) --}}
                                        <td class="px-4 py-2 border-b cursor-pointer hover:bg-blue-50"
                                            data-editable="true"
                                            data-type="time"
                                            data-id="{{ $log->id }}"
                                            data-column="end_time">
                                            {{ $log->end_time ?? '-' }}
                                        </td>

                                        {{-- ✅ Editable lunch_minutes (number) --}}
                                        <td class="px-4 py-2 border-b cursor-pointer hover:bg-blue-50"
                                            data-editable="true"
                                            data-type="number"
                                            data-id="{{ $log->id }}"
                                            data-column="lunch_minutes">
                                            {{ $log->lunch_minutes ?? 0 }}
                                        </td>

                                        {{-- ✅ Editable break_count (number) --}}
                                        <td class="px-4 py-2 border-b cursor-pointer hover:bg-blue-50"
                                            data-editable="true"
                                            data-type="number"
                                            data-id="{{ $log->id }}"
                                            data-column="break_count">
                                            {{ $log->break_count ?? 0 }}
                                        </td>

                                        <td class="px-4 py-2 border-b {{ $hoursClass }}">
                                            {{ number_format($log->adjusted_hours, 2) }}
                                        </td>
                                    </tr>
                                @endforeach

                                <tr class="bg-indigo-50 font-semibold">
                                    <td colspan="5" class="text-right px-4 py-2 border-t border-gray-300">Kopā:</td>
                                    <td class="px-4 py-2 border-t border-gray-300 text-indigo-700">
                                        {{ number_format($totalHours, 2) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @endif
            @elseif(request()->filled('user_id'))
                <p class="text-center text-gray-600 mt-4">Dati nav atrasti šim periodam.</p>
            @endif
        </div>
    </div>

    {{-- ✅ Inline editing script --}}
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const editableCells = document.querySelectorAll('[data-editable="true"]');

        editableCells.forEach(cell => {
            cell.addEventListener('dblclick', () => {
                const originalValue = cell.textContent.trim().replace('-', '');
                const logId = cell.dataset.id;
                const column = cell.dataset.column;
                const type = cell.dataset.type; // "time" | "number"

                const input = document.createElement('input');
                if (type === 'time') {
                    input.type = 'time';
                    input.step = 1; // seconds allowed
                    input.value = (originalValue || '').slice(0, 8); // HH:MM:SS
                } else {
                    input.type = 'number';
                    input.min = 0;
                    input.step = 1;
                    input.value = originalValue || '0';
                }
                input.className = 'border border-indigo-400 rounded px-1 py-0.5 text-center w-full';
                cell.innerHTML = '';
                cell.appendChild(input);
                input.focus();

                const save = async () => {
                    const newValue = input.value;
                    if (!newValue && type === 'time') {
                        cell.textContent = originalValue || '-';
                        return;
                    }
                    try {
                        const res = await fetch(`/work-log/update-field/${logId}`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ column, value: newValue })
                        });

                        const data = await res.json();
                        if (data.success) {
                            cell.textContent = (type === 'time') ? newValue : parseInt(newValue, 10);
                            cell.classList.add('bg-green-100');
                            setTimeout(() => {
                                cell.classList.remove('bg-green-100');
                                // Reload to recalc adjusted hours and totals
                                window.location.reload();
                            }, 350);
                        } else {
                            cell.textContent = originalValue || (type === 'time' ? '-' : '0');
                            alert('Kļūda saglabājot!');
                        }
                    } catch (error) {
                        cell.textContent = originalValue || (type === 'time' ? '-' : '0');
                        alert('Servera kļūda');
                    }
                };

                input.addEventListener('blur', save);
                input.addEventListener('keydown', e => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        input.blur();
                    } else if (e.key === 'Escape') {
                        cell.textContent = originalValue || (type === 'time' ? '-' : '0');
                    }
                });
            });
        });
    });
    </script>
</x-app-layout>
