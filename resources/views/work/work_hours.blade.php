<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-white leading-tight">
                {{ __('Darba stundas') }}
            </h2>
            <div class="hidden sm:block text-sm text-slate-400">
                Filtri • Kopsavilkums • Labojumi
            </div>
        </div>
    </x-slot>

    {{-- Alpine.js (for dropdown search) --}}
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <div class="py-6">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Filters Card --}}
            <div class="mb-6 rounded-2xl border border-white/10 bg-white/5 backdrop-blur shadow-xl">
                <form method="GET"
                      class="p-4 sm:p-5 grid grid-cols-1 md:grid-cols-4 gap-4 items-end"
                      x-data="{
                        search: '{{ request('user_id') === 'all' ? 'Visi' : (optional($users->firstWhere('id', request('user_id')))->name ?? '') }}',
                        open: false
                      }">

                    {{-- User searchable select --}}
                    <div class="relative">
                        <label class="block mb-1 text-sm font-medium text-slate-300">Lietotājs</label>

                        <input type="text"
                               x-model="search"
                               @focus="open = true"
                               @click="open = true"
                               @click.outside="open = false"
                               placeholder="-- Izvēlies lietotāju --"
                               class="w-full rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-sm text-white placeholder:text-slate-500
                                      focus:border-red-500/50 focus:ring-red-500/20"
                               autocomplete="off">

                        <input type="hidden" name="user_id" id="user_id" value="{{ request('user_id') }}">

                        <span class="pointer-events-none absolute right-3 top-9 text-slate-500">▾</span>

                        <ul x-show="open" x-transition
                            class="absolute left-0 right-0 z-50 mt-2 overflow-auto max-h-64
                                   rounded-2xl border border-white/10 bg-[#0B0F14]/95 backdrop-blur shadow-2xl ring-1 ring-black/30">
                            <li @click="
                                    search='Visi';
                                    document.getElementById('user_id').value='all';
                                    open=false;
                                "
                                class="px-3 py-2 cursor-pointer text-slate-200 hover:bg-white/10">
                                Visi
                            </li>

                            @foreach ($users as $u)
                                <li @click="
                                        search='{{ $u->name }}';
                                        document.getElementById('user_id').value='{{ $u->id }}';
                                        open=false;
                                    "
                                    class="px-3 py-2 cursor-pointer text-slate-200 hover:bg-white/10"
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
                        <label class="block mb-1 text-sm font-medium text-slate-300">No</label>
                        <input type="date" name="from" value="{{ request('from') }}"
                               class="w-full rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-sm text-white
                                      focus:border-red-500/50 focus:ring-red-500/20">
                    </div>

                    {{-- Date To --}}
                    <div>
                        <label class="block mb-1 text-sm font-medium text-slate-300">Līdz</label>
                        <input type="date" name="to" value="{{ request('to') }}"
                               class="w-full rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-sm text-white
                                      focus:border-red-500/50 focus:ring-red-500/20">
                    </div>

                    {{-- Search --}}
                    <div class="flex justify-center md:justify-start">
                        <button type="submit"
                                class="w-full md:w-auto px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-semibold shadow">
                            Meklēt
                        </button>
                    </div>
                </form>
            </div>

            {{-- Tables --}}
            @if ($logs->count())

                @if (request('user_id') === 'all')
                    {{-- Summary for all users --}}
                    <div class="rounded-2xl border border-white/10 bg-white/5 backdrop-blur shadow-xl overflow-hidden">
                        <div class="overflow-x-auto lg:overflow-visible">
                            <table class="w-full text-sm table-fixed lg:table-auto">
                                <thead class="bg-white/5">
                                    <tr class="text-left text-slate-200">
                                        <th class="px-4 py-3">Lietotājs</th>
                                        <th class="px-4 py-3 text-right">Kopā stundas (ar pusdienām)</th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-white/10">
                                    @foreach ($userTotals as $userId => $hours)
                                        @php $user = $users->firstWhere('id', $userId); @endphp
                                        <tr class="hover:bg-white/5 transition-colors">
                                            <td class="px-4 py-3 text-white">
                                                {{ $user->name ?? 'Dzēsts lietotājs' }}
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <span class="inline-flex items-center rounded-xl px-3 py-1 text-xs ring-1 ring-white/10 bg-white/10 text-slate-100">
                                                    {{ number_format($hours, 2) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach

                                    <tr class="bg-white/5">
                                        <td class="px-4 py-3 text-right text-slate-200 font-semibold">
                                            Kopā:
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <span class="inline-flex items-center rounded-xl px-3 py-1 text-xs ring-1 ring-white/10 bg-emerald-500/15 text-emerald-200 font-semibold">
                                                {{ number_format($totalHours, 2) }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                @else
                    {{-- Single user table --}}
                    <div class="rounded-2xl border border-white/10 bg-white/5 backdrop-blur shadow-xl overflow-hidden">
                        <div class="overflow-x-auto lg:overflow-visible">
                            <table class="w-full text-sm table-fixed lg:table-auto">
                                <thead class="bg-white/5">
                                    <tr class="text-left text-slate-200">
                                        <th class="px-4 py-3 whitespace-nowrap">Datums</th>
                                        <th class="px-4 py-3 whitespace-nowrap">Sāka darbu</th>
                                        <th class="px-4 py-3 whitespace-nowrap">Beidza darbu</th>
                                        <th class="px-4 py-3 whitespace-nowrap">Pusdienas (min)</th>
                                        <th class="px-4 py-3 whitespace-nowrap">Paužu skaits</th>
                                        <th class="px-4 py-3 whitespace-nowrap text-right">Kopā stundas</th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-white/10">
                                    @foreach ($logs as $log)
                                        @php
                                            $displayDate = $log->date instanceof \Carbon\Carbon
                                                ? $log->date->format('Y-m-d')
                                                : \Carbon\Carbon::parse($log->date)->format('Y-m-d');

                                            $hoursPill = $log->adjusted_hours >= 8
                                                ? 'bg-emerald-500/15 text-emerald-200'
                                                : 'bg-amber-500/15 text-amber-200';
                                        @endphp

                                        <tr class="hover:bg-white/5 transition-colors">
                                            <td class="px-4 py-3 text-white whitespace-nowrap">
                                                {{ $displayDate }}
                                            </td>

                                            <td class="px-4 py-3 text-slate-200 cursor-pointer hover:bg-white/5 rounded"
                                                data-editable="true" data-type="time" data-id="{{ $log->id }}" data-column="start_time">
                                                {{ $log->start_time ?? '-' }}
                                            </td>

                                            <td class="px-4 py-3 text-slate-200 cursor-pointer hover:bg-white/5 rounded"
                                                data-editable="true" data-type="time" data-id="{{ $log->id }}" data-column="end_time">
                                                {{ $log->end_time ?? '-' }}
                                            </td>

                                            <td class="px-4 py-3 text-slate-200 cursor-pointer hover:bg-white/5 rounded"
                                                data-editable="true" data-type="number" data-id="{{ $log->id }}" data-column="lunch_minutes">
                                                {{ $log->lunch_minutes ?? 0 }}
                                            </td>

                                            <td class="px-4 py-3 text-slate-200 cursor-pointer hover:bg-white/5 rounded"
                                                data-editable="true" data-type="number" data-id="{{ $log->id }}" data-column="break_count">
                                                {{ $log->break_count ?? 0 }}
                                            </td>

                                            <td class="px-4 py-3 text-right">
                                                <span class="inline-flex items-center rounded-xl px-3 py-1 text-xs ring-1 ring-white/10 {{ $hoursPill }}">
                                                    {{ number_format($log->adjusted_hours, 2) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach

                                    <tr class="bg-white/5">
                                        <td colspan="5" class="px-4 py-3 text-right text-slate-200 font-semibold">
                                            Kopā:
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <span class="inline-flex items-center rounded-xl px-3 py-1 text-xs ring-1 ring-white/10 bg-emerald-500/15 text-emerald-200 font-semibold">
                                                {{ number_format($totalHours, 2) }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

            @elseif(request()->filled('user_id'))
                <div class="rounded-2xl border border-white/10 bg-white/5 backdrop-blur shadow-xl p-6 text-center text-slate-300">
                    Dati nav atrasti šim periodam.
                </div>
            @endif

            <div class="mt-6 h-1 bg-gradient-to-r from-transparent via-red-600/40 to-transparent rounded"></div>
        </div>
    </div>

    {{-- Inline editing script (design tweaks only) --}}
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const editableCells = document.querySelectorAll('[data-editable="true"]');

        editableCells.forEach(cell => {
            cell.addEventListener('dblclick', () => {
                const originalValue = cell.textContent.trim().replace('-', '');
                const logId = cell.dataset.id;
                const column = cell.dataset.column;
                const type = cell.dataset.type;

                const input = document.createElement('input');

                if (type === 'time') {
                    input.type = 'time';
                    input.step = 1;
                    input.value = (originalValue || '').slice(0, 8);
                } else {
                    input.type = 'number';
                    input.min = 0;
                    input.step = 1;
                    input.value = originalValue || '0';
                }

                input.className =
                    'w-full rounded-lg border border-white/10 bg-[#0B0F14]/70 px-2 py-1 text-center text-sm text-white ' +
                    'focus:border-red-500/50 focus:ring-red-500/20';

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

                            cell.classList.add('bg-emerald-500/15');
                            setTimeout(() => {
                                cell.classList.remove('bg-emerald-500/15');
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