<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-white leading-tight">
                {{ __('Saražotā produkcija') }}
            </h2>
            <div class="hidden sm:block text-sm text-slate-400">
                Noliktava • Pārvietojumi • Grāmatvedība
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Controls card --}}
            <div class="mb-6 rounded-2xl border border-white/10 bg-white/5 backdrop-blur shadow-xl">
                <div class="p-4 sm:p-5 flex flex-col lg:flex-row lg:items-center justify-between gap-4 flex-wrap">

                    <form method="GET" class="flex flex-wrap gap-3 items-center w-full lg:w-auto">
                        <div class="relative w-full sm:w-96">
                            <input type="text" name="q" value="{{ $q }}" placeholder="Meklēt pēc produkta vai svītrkoda"
                                   class="w-full rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-sm text-white placeholder:text-slate-500
                                          focus:border-red-500/50 focus:ring-red-500/20">
                        </div>

                        <label class="inline-flex items-center gap-2 text-sm text-slate-300 rounded-xl bg-white/5 px-3 py-2 ring-1 ring-white/10">
                            <input type="checkbox" name="only_not_accounted" value="1" {{ $onlyNotAccounted ? 'checked' : '' }}
                                   class="rounded border-white/20 bg-[#0B0F14]/60 text-red-600 focus:ring-red-500/30">
                            Tikai neiegrāmatotie
                        </label>

                        <button class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-semibold shadow">
                            Meklēt
                        </button>

                        <a href="{{ route('inventory.transfers.index') }}"
                           class="px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-slate-200 text-sm ring-1 ring-white/10 transition">
                            Notīrīt
                        </a>

                        <a href="{{ route('inventory.scan') }}"
                           class="px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-slate-200 text-sm ring-1 ring-white/10 transition">
                            ← Atpakaļ uz skeneri
                        </a>
                    </form>

                    <div class="flex items-center gap-3 w-full lg:w-auto">
                        <button id="bulk-delete"
                                class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-semibold shadow">
                            Dzēst atlasītos
                        </button>
                    </div>
                </div>
            </div>

            {{-- Table card (phone scroll yes, PC no) --}}
            <div class="rounded-2xl border border-white/10 bg-white/5 backdrop-blur shadow-xl">
                <div class="overflow-x-auto lg:overflow-visible">
                    <table class="w-full text-sm table-fixed lg:table-auto">
                        <thead class="bg-white/5">
                            <tr class="text-left text-slate-200">
                                <th class="px-4 py-3 text-center w-[60px]">
                                    <input type="checkbox" id="check-all" title="Atlasīt visu"
                                           class="rounded border-white/20 bg-[#0B0F14]/60 text-red-600 focus:ring-red-500/30">
                                </th>
                                <th class="px-4 py-3 whitespace-nowrap">Produkts</th>
                                <th class="px-4 py-3 whitespace-nowrap text-right">Daudzums</th>
                                <th class="px-4 py-3 whitespace-nowrap hidden md:table-cell">Izveidoja</th>
                                <th class="px-4 py-3 whitespace-nowrap">Grāmatvedība</th>
                                <th class="px-4 py-3 whitespace-nowrap hidden lg:table-cell">Datums</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-white/10">
                        @forelse ($transfers as $t)
                            <tr data-id="{{ $t->id }}" class="hover:bg-white/5 transition-colors">
                                <td class="px-4 py-3 text-center">
                                    <input type="checkbox"
                                           class="row-check rounded border-white/20 bg-[#0B0F14]/60 text-red-600 focus:ring-red-500/30"
                                           value="{{ $t->id }}"
                                           {{ $t->accounted ? 'checked' : '' }}>
                                </td>

                                <td class="px-4 py-3">
                                    <div class="font-medium text-white break-words">
                                        {{ $t->product->nosaukums }}
                                    </div>
                                    <div class="text-xs text-slate-400">
                                        SVK: {{ $t->product->svitr_kods }}
                                    </div>
                                </td>

                                <td class="px-4 py-3 text-right text-slate-200">
                                    {{ $t->qty }}
                                </td>

                                <td class="px-4 py-3 text-slate-300 hidden md:table-cell">
                                    {{ $t->creator->name ?? ('ID '.$t->created_by) }}
                                </td>

                                <td class="px-4 py-3">
                                    <span class="acc-status inline-flex items-center rounded-xl px-3 py-1 text-xs ring-1 ring-white/10
                                        {{ $t->accounted ? 'bg-emerald-500/15 text-emerald-200' : 'bg-amber-500/15 text-amber-200' }}">
                                        {{ $t->accounted ? 'Ievadīts' : 'Nav ievadīts' }}
                                    </span>
                                    <div class="text-xs text-slate-400 mt-1 acc-when">
                                        {{ $t->accounted_at ? $t->accounted_at->format('Y-m-d H:i') : '' }}
                                    </div>
                                </td>

                                <td class="px-4 py-3 text-slate-300 hidden lg:table-cell">
                                    {{ $t->created_at->format('Y-m-d H:i') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-10 text-center text-slate-400">
                                    Nav ierakstu.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Pagination --}}
            <div class="mt-6">
                <div class="rounded-2xl border border-white/10 bg-white/5 backdrop-blur p-4">
                    {{ $transfers->links() }}
                </div>
            </div>

            <div id="bulk-status" class="mt-3 text-sm text-slate-300"></div>

            <div class="mt-6 h-1 bg-gradient-to-r from-transparent via-red-600/40 to-transparent rounded"></div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const token = @json(csrf_token());
        const status = document.getElementById('bulk-status');

        const all = document.getElementById('check-all');
        all?.addEventListener('change', async () => {
            const checkboxes = document.querySelectorAll('.row-check');
            checkboxes.forEach(cb => cb.checked = all.checked);
            await updateAccounting([...checkboxes].map(c => parseInt(c.value,10)), all.checked);
        });

        // Toggle individually
        document.querySelectorAll('.row-check').forEach(cb => {
            cb.addEventListener('change', async () => {
                const id = parseInt(cb.value,10);
                await updateAccounting([id], cb.checked);
            });
        });

        // Bulk delete
        document.getElementById('bulk-delete').addEventListener('click', async () => {
            const ids = [...document.querySelectorAll('.row-check:checked')].map(c => parseInt(c.value,10));
            if (!ids.length) { status.textContent = 'Nav atlasītu ierakstu.'; return; }
            if (!confirm('Dzēst atlasītos ierakstus?')) return;
            status.textContent = 'Dzēšu...';
            const {res, data} = await postJSON(@json(route('inventory.transfers.delete')), 'DELETE', { ids });
            if (res.ok && data.ok) {
                status.textContent = 'Dzēsts ✓';
                location.reload();
            } else {
                status.textContent = data.message || 'Kļūda dzēšot.';
            }
        });

        async function postJSON(url, method, body) {
            const res = await fetch(url, {
                method,
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                body: JSON.stringify(body)
            });
            const data = await res.json().catch(()=> ({}));
            return {res, data};
        }

        async function updateAccounting(ids, accounted) {
            if (!ids.length) return;
            const {res, data} = await postJSON(@json(route('inventory.transfers.account')), 'PATCH', { ids, accounted });
            if (res.ok && data.ok) {
                ids.forEach(id => {
                    const row = document.querySelector(`tr[data-id="${id}"]`);
                    if (!row) return;
                    const acc = row.querySelector('.acc-status');
                    const when = row.querySelector('.acc-when');

                    if (acc) {
                        if (accounted) {
                            acc.textContent = 'Ievadīts';
                            acc.className = 'acc-status inline-flex items-center rounded-xl px-3 py-1 text-xs ring-1 ring-white/10 bg-emerald-500/15 text-emerald-200';
                            const now = new Date();
                            const pad = n => String(n).padStart(2,'0');
                            when.textContent = `${now.getFullYear()}-${pad(now.getMonth()+1)}-${pad(now.getDate())} ${pad(now.getHours())}:${pad(now.getMinutes())}`;
                        } else {
                            acc.textContent = 'Nav ievadīts';
                            acc.className = 'acc-status inline-flex items-center rounded-xl px-3 py-1 text-xs ring-1 ring-white/10 bg-amber-500/15 text-amber-200';
                            when.textContent = '';
                        }
                    }
                });
                status.textContent = '';
            } else {
                status.textContent = data.message || 'Kļūda atzīmējot kā ievadītu.';
            }
        }
    });
    </script>
</x-app-layout>