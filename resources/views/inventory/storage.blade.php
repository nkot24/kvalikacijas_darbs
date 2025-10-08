<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Noliktava') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="GET" class="mb-4 flex gap-2">
                    <input type="text" name="q" value="{{ $q }}" placeholder="Meklēt pēc nosaukuma vai svītrkoda"
                           class="flex-1 border rounded p-2">
                    <button class="px-4 py-2 rounded bg-blue-600 text-white">Meklēt</button>
                    <a href="{{ route('inventory.storage') }}" class="px-4 py-2 rounded border">Notīrīt</a>
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full border">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="text-left p-2 border">Nosaukums</th>
                                <th class="text-left p-2 border">Svītrkods</th>
                                <th class="text-right p-2 border">Daudzums noliktavā</th>
                                <th class="p-2 border"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($products as $p)
                                <tr data-id="{{ $p->id }}">
                                    <td class="p-2 border">{{ $p->nosaukums }}</td>
                                    <td class="p-2 border">{{ $p->svitr_kods }}</td>
                                    <td class="p-2 border">
                                        <div class="flex items-center justify-end gap-2">
                                            <button type="button" class="qty-dec px-2 py-1 border rounded">−</button>
                                            <input type="number"
                                                   class="qty-input w-28 border rounded p-1 text-right"
                                                   value="{{ $p->daudzums_noliktava }}"
                                                   min="0" step="1">
                                            <button type="button" class="qty-inc px-2 py-1 border rounded">+</button>
                                        </div>
                                    </td>
                                    <td class="p-2 border text-right">
                                        <button type="button" class="save-qty px-3 py-1 rounded bg-blue-600 text-white">
                                            Saglabāt
                                        </button>
                                        <span class="save-status ml-2 text-sm text-gray-500"></span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="p-4 text-center text-gray-500">Nav atrastu produktu.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $products->links() }}
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const token = @json(csrf_token());

        // Click +/−
        document.querySelectorAll('.qty-inc').forEach(btn => {
            btn.addEventListener('click', () => {
                const input = btn.closest('tr').querySelector('.qty-input');
                input.value = Math.max(0, (parseInt(input.value || '0', 10) + 1));
            });
        });
        document.querySelectorAll('.qty-dec').forEach(btn => {
            btn.addEventListener('click', () => {
                const input = btn.closest('tr').querySelector('.qty-input');
                input.value = Math.max(0, (parseInt(input.value || '0', 10) - 1));
            });
        });

        // Save button
        document.querySelectorAll('.save-qty').forEach(btn => {
            btn.addEventListener('click', async () => {
                const row = btn.closest('tr');
                const id = row.dataset.id;
                const input = row.querySelector('.qty-input');
                const status = row.querySelector('.save-status');
                const value = parseInt(input.value, 10);

                if (Number.isNaN(value) || value < 0) {
                    status.textContent = 'Nederīga vērtība';
                    status.className = 'save-status ml-2 text-sm text-red-600';
                    return;
                }

                status.textContent = 'Saglabāju...';
                status.className = 'save-status ml-2 text-sm text-gray-500';

                try {
                    const res = await fetch(@json(route('inventory.storage.updateQty', ['product' => 'ID_PLACEHOLDER']))
                        .replace('ID_PLACEHOLDER', id), {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ daudzums_noliktava: value })
                    });

                    const data = await res.json();
                    if (res.ok && data.ok) {
                        input.value = data.daudzums_noliktava;
                        status.textContent = 'Saglabāts ✓';
                        status.className = 'save-status ml-2 text-sm text-green-600';
                    } else {
                        status.textContent = (data && data.message) ? data.message : 'Kļūda saglabājot';
                        status.className = 'save-status ml-2 text-sm text-red-600';
                    }
                } catch (e) {
                    status.textContent = 'Tīkla kļūda';
                    status.className = 'save-status ml-2 text-sm text-red-600';
                }
            });
        });

        // Enter to save
        document.querySelectorAll('.qty-input').forEach(input => {
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    input.closest('tr').querySelector('.save-qty').click();
                }
            });
        });
    });
    </script>
</x-app-layout>
