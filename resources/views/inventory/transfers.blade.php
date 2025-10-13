<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Saražotā produkcija') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <form method="GET" class="mb-4 flex flex-wrap gap-2 items-center">
                    <input type="text" name="q" value="{{ $q }}" placeholder="Meklēt pēc produkta vai svītrkoda"
                           class="border rounded p-2 flex-1">
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="only_not_accounted" value="1" {{ $onlyNotAccounted ? 'checked' : '' }}>
                        Tikai neiegrāmatotie
                    </label>
                    <button class="px-4 py-2 rounded bg-blue-600 text-white">Meklēt</button>
                    <a href="{{ route('inventory.transfers.index') }}" class="px-4 py-2 rounded border">Notīrīt</a>
                    <a href="{{ route('inventory.scan') }}" class="px-3 py-2 rounded border">Atpakaļ uz skeneri</a>
                </form>

                <div class="flex gap-2 mb-3">
                    <button id="bulk-delete" class="px-3 py-2 rounded bg-red-600 text-white">
                        Dzēst atlasītos
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full border">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="p-2 border text-center">
                                    <input type="checkbox" id="check-all" title="Atlasīt visu">
                                </th>
                                <th class="text-left p-2 border">Produkts</th>
                                <th class="text-right p-2 border">Daudzums</th>
                                <th class="text-left p-2 border">Izveidoja</th>
                                <th class="text-left p-2 border">Grāmatvedība</th>
                                <th class="text-left p-2 border">Datums</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse ($transfers as $t)
                            <tr data-id="{{ $t->id }}">
                                <td class="p-2 border text-center">
                                    <input type="checkbox" class="row-check" value="{{ $t->id }}" {{ $t->accounted ? 'checked' : '' }}>
                                </td>
                                <td class="p-2 border">
                                    <div class="font-medium">{{ $t->product->nosaukums }}</div>
                                    <div class="text-xs text-gray-500">SVK: {{ $t->product->svitr_kods }}</div>
                                </td>
                                <td class="p-2 border text-right">{{ $t->qty }}</td>
                                <td class="p-2 border">{{ $t->creator->name ?? ('ID '.$t->created_by) }}</td>
                                <td class="p-2 border">
                                    <span class="acc-status {{ $t->accounted ? 'text-green-700' : 'text-orange-700' }}">
                                        {{ $t->accounted ? 'Ievadīts' : 'Nav ievadīts' }}
                                    </span>
                                    <div class="text-xs text-gray-500 acc-when">
                                        {{ $t->accounted_at ? $t->accounted_at->format('Y-m-d H:i') : '' }}
                                    </div>
                                </td>
                                <td class="p-2 border text-sm text-gray-600">
                                    {{ $t->created_at->format('Y-m-d H:i') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-4 text-center text-gray-500">Nav ierakstu.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $transfers->links() }}
                </div>

                <div id="bulk-status" class="mt-3 text-sm text-gray-600"></div>
            </div>
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
                            acc.classList.remove('text-orange-700');
                            acc.classList.add('text-green-700');
                            const now = new Date();
                            const pad = n => String(n).padStart(2,'0');
                            when.textContent = `${now.getFullYear()}-${pad(now.getMonth()+1)}-${pad(now.getDate())} ${pad(now.getHours())}:${pad(now.getMinutes())}`;
                        } else {
                            acc.textContent = 'Nav ievadīts';
                            acc.classList.remove('text-green-700');
                            acc.classList.add('text-orange-700');
                            when.textContent = '';
                        }
                    }
                });
            } else {
                status.textContent = data.message || 'Kļūda atzīmējot kā ievadītu.';
            }
        }
    });
    </script>
</x-app-layout>
