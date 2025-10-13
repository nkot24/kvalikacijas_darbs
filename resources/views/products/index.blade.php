<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Produktu saraksts
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-full mx-auto">

            {{-- Success flash message --}}
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mx-[100px] mb-4" role="alert">
                    <strong class="font-bold">Veiksmīgi!</strong>
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            {{-- White background container with padding and rounded corners --}}
            <div class="bg-white shadow-sm rounded-lg p-6">

                {{-- Import & Export buttons --}}
                <div class="mb-6 px-[100px] flex flex-col md:flex-row md:items-center gap-4">

                    {{-- Export Button --}}
                    <a href="{{ route('products.export') }}"
                       class="inline-block px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        📤 Eksportēt produktus
                    </a>

                    {{-- Import Form --}}
                    <form action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-2">
                        @csrf
                        <label class="block text-sm font-medium text-gray-700">📥 Importēt no Excel:</label>
                        <input type="file" name="import_file"
                               class="block text-sm text-gray-500 file:mr-4 file:py-2 file:px-4
                                      file:rounded file:border-0 file:text-sm file:font-semibold
                                      file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                               required>
                        <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                            Augšupielādēt
                        </button>
                    </form>

                    {{-- Add New Product --}}
                    <a href="{{ route('products.create') }}"
                       class="inline-block px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                        + Pievienot jaunu produktu
                    </a>
                </div>

                {{-- Table with horizontal scroll --}}
                <div class="overflow-x-auto px-[100px]">
                    <table class="table-auto w-full min-w-[1200px] border-collapse border border-gray-300 bg-white">
                        <thead>
                            <tr>
                                <th class="border border-gray-300 px-4 py-2">
                                    <a href="{{ route('products.index', [
                                        'sort_by' => 'svitr_kods',
                                        'sort_order' => ($sort_by === 'svitr_kods' && $sort_order === 'asc') ? 'desc' : 'asc'
                                    ]) }}" class="flex items-center gap-1">
                                        Svītrkods
                                        @if ($sort_by === 'svitr_kods')
                                            @if ($sort_order === 'asc')
                                                🔼
                                            @else
                                                🔽
                                            @endif
                                        @endif
                                    </a>
                                </th>

                                <th class="border border-gray-300 px-4 py-2">
                                    <a href="{{ route('products.index', [
                                        'sort_by' => 'nosaukums',
                                        'sort_order' => ($sort_by === 'nosaukums' && $sort_order === 'asc') ? 'desc' : 'asc'
                                    ]) }}" class="flex items-center gap-1">
                                        Nosaukums
                                        @if ($sort_by === 'nosaukums')
                                            @if ($sort_order === 'asc')
                                                🔼
                                            @else
                                                🔽
                                            @endif
                                        @endif
                                    </a>
                                </th>

                                <th class="border border-gray-300 px-4 py-2">Pārdošanas cena</th>
                                <th class="border border-gray-300 px-4 py-2">Vairumtirdzniecības cena</th>
                                <th class="border border-gray-300 px-4 py-2">Daudzums noliktavā</th>
                                <th class="border border-gray-300 px-4 py-2">Svars (kg)</th>
                                <th class="border border-gray-300 px-4 py-2">Nom. grupas kods</th>
                                <th class="border border-gray-300 px-4 py-2">Garums (mm)</th>
                                <th class="border border-gray-300 px-4 py-2">Platums (mm)</th>
                                <th class="border border-gray-300 px-4 py-2">Augstums (mm)</th>
                                <th class="border border-gray-300 px-4 py-2">Darbības</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($products as $product)
                                <tr>
                                    <td class="border border-gray-300 px-4 py-2">{{ $product->svitr_kods }}</td>
                                    <td class="border border-gray-300 px-4 py-2">{{ $product->nosaukums }}</td>
                                    <td class="border border-gray-300 px-4 py-2">{{ $product->pardosanas_cena }}</td>
                                    <td class="border border-gray-300 px-4 py-2">{{ $product->vairumtirdzniecibas_cena ?? '-' }}</td>
                                    <td class="border border-gray-300 px-4 py-2">{{ $product->daudzums_noliktava ?? '-' }}</td>
                                    <td class="border border-gray-300 px-4 py-2">{{ $product->svars_neto ?? '-' }}</td>
                                    <td class="border border-gray-300 px-4 py-2">{{ $product->nomGr_kods }}</td>
                                    <td class="border border-gray-300 px-4 py-2">{{ $product->garums ?? '-' }}</td>
                                    <td class="border border-gray-300 px-4 py-2">{{ $product->platums ?? '-' }}</td>
                                    <td class="border border-gray-300 px-4 py-2">{{ $product->augstums ?? '-' }}</td>
                                    <td class="border border-gray-300 px-4 py-2 space-y-2">
                                        <a href="{{ route('products.edit', $product) }}" class="text-blue-600 hover:underline block">Rediģēt</a>

                                        <form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('Vai tiešām vēlaties dzēst šo produktu?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:underline">Dzēst</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center py-4">Nav pieejami produkti.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
