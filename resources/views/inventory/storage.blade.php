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
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($products as $p)
                                <tr>
                                    <td class="p-2 border">{{ $p->nosaukums }}</td>
                                    <td class="p-2 border">{{ $p->svitr_kods }}</td>
                                    <td class="p-2 border text-right">{{ $p->daudzums_noliktava }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="p-4 text-center text-gray-500">Nav atrastu produktu.</td>
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
</x-app-layout>
