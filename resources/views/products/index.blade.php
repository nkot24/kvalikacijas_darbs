<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-white leading-tight">
                Produktu saraksts
            </h2>
            <div class="hidden sm:block text-sm text-slate-400">
                Produkti • Imports • Eksports
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-full mx-auto">

            {{-- Success --}}
            @if (session('success'))
                <div class="mx-4 sm:mx-6 lg:mx-[100px] mb-5 rounded-2xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-emerald-200">
                    <div class="font-semibold">Veiksmīgi!</div>
                    <div class="text-sm text-emerald-200/90">{{ session('success') }}</div>
                </div>
            @endif

            {{-- Top Controls Card --}}
            <div class="mx-4 sm:mx-6 lg:mx-[100px] mb-6 rounded-2xl border border-white/10 bg-white/5 backdrop-blur shadow-xl">
                <div class="p-4 sm:p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4 flex-wrap">

                    {{-- Export --}}
                    <a
                        href="{{ route('products.export') }}"
                        class="px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-white text-sm ring-1 ring-white/10 transition"
                    >
                        📤 Eksportēt produktus
                    </a>

                    {{-- Import --}}
                    <form
                        action="{{ route('products.import') }}"
                        method="POST"
                        enctype="multipart/form-data"
                        class="flex flex-wrap items-center gap-2"
                    >
                        @csrf

                        <label class="text-sm text-slate-400">📥 Importēt no Excel:</label>

                        <input
                            type="file"
                            name="import_file"
                            class="text-xs sm:text-sm text-slate-300
                                   file:mr-2 file:py-2 file:px-3
                                   file:rounded-xl file:border-0
                                   file:text-xs sm:file:text-sm file:font-semibold
                                   file:bg-white/10 file:text-white hover:file:bg-white/15
                                   cursor-pointer"
                            required
                        >

                        <button
                            type="submit"
                            class="px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-white text-sm ring-1 ring-white/10 transition"
                        >
                            Augšupielādēt
                        </button>
                    </form>

                    {{-- Add --}}
                    <a
                        href="{{ route('products.create') }}"
                        class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-semibold shadow"
                    >
                        + Pievienot jaunu produktu
                    </a>

                </div>
            </div>

            {{-- Sort helper (same style as Orders) --}}
            @php
                function sortLinkProducts($column, $label, $sort_by, $sort_order) {
                    $isCurrent = $sort_by === $column;
                    $direction = ($isCurrent && $sort_order === 'asc') ? 'desc' : 'asc';
                    $arrow = $isCurrent ? ($sort_order === 'asc' ? '⬆️' : '⬇️') : '';
                    $query = array_merge(request()->all(), ['sort_by' => $column, 'sort_order' => $direction]);

                    return '<a href="'.route('products.index', $query).'" class="inline-flex items-center gap-1 hover:text-white hover:underline text-slate-200">'
                        .$label.' <span class="text-xs text-slate-400">'.$arrow.'</span></a>';
                }
            @endphp

            {{-- Table Card (EXACT like Orders index) --}}
            <div class="overflow-x-auto mx-2 sm:mx-4 lg:mx-[100px] rounded-2xl border border-white/10 bg-white/5 backdrop-blur shadow-xl">
                <table class="table-auto w-full text-xs sm:text-sm">
                    <thead class="bg-white/5">
                        <tr class="text-left text-slate-200">
                            <th class="px-4 py-3 whitespace-nowrap">
                                {!! sortLinkProducts('svitr_kods', 'Svītrkods', $sort_by, $sort_order) !!}
                            </th>
                            <th class="px-4 py-3 whitespace-nowrap">
                                {!! sortLinkProducts('nosaukums', 'Nosaukums', $sort_by, $sort_order) !!}
                            </th>
                            <th class="px-4 py-3 whitespace-nowrap">
                                Pārdošanas cena
                            </th>
                            <th class="px-4 py-3 whitespace-nowrap">
                                Vairumtirdzniecības cena
                            </th>
                            <th class="px-4 py-3 whitespace-nowrap">
                                Daudzums noliktavā
                            </th>
                            <th class="px-4 py-3 whitespace-nowrap">
                                Svars (kg)
                            </th>
                            <th class="px-4 py-3 whitespace-nowrap">
                                Nom. grupas kods
                            </th>
                            <th class="px-4 py-3 whitespace-nowrap">
                                Garums (mm)
                            </th>
                            <th class="px-4 py-3 whitespace-nowrap">
                                Platums (mm)
                            </th>
                            <th class="px-4 py-3 whitespace-nowrap">
                                Augstums (mm)
                            </th>
                            <th class="px-4 py-3 whitespace-nowrap text-center">
                                Darbības
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-white/10">
                        @forelse ($products as $product)
                            <tr class="hover:bg-white/5 transition-colors">
                                <td class="px-4 py-3 max-w-[220px] truncate text-white">
                                    {{ $product->svitr_kods }}
                                </td>

                                <td class="px-4 py-3 max-w-[260px] truncate text-slate-200">
                                    {{ $product->nosaukums }}
                                </td>

                                <td class="px-4 py-3 max-w-[160px] truncate text-slate-200">
                                    {{ $product->pardosanas_cena }}
                                </td>

                                <td class="px-4 py-3 max-w-[190px] truncate text-slate-200">
                                    {{ $product->vairumtirdzniecibas_cena ?? '-' }}
                                </td>

                                <td class="px-4 py-3 max-w-[180px] truncate text-slate-200">
                                    {{ $product->daudzums_noliktava ?? '-' }}
                                </td>

                                <td class="px-4 py-3 max-w-[140px] truncate text-slate-200">
                                    {{ $product->svars_neto ?? '-' }}
                                </td>

                                <td class="px-4 py-3 max-w-[180px] truncate text-slate-200">
                                    {{ $product->nomGr_kods ?? '-' }}
                                </td>

                                <td class="px-4 py-3 max-w-[140px] truncate text-slate-200">
                                    {{ $product->garums ?? '-' }}
                                </td>

                                <td class="px-4 py-3 max-w-[140px] truncate text-slate-200">
                                    {{ $product->platums ?? '-' }}
                                </td>

                                <td class="px-4 py-3 max-w-[140px] truncate text-slate-200">
                                    {{ $product->augstums ?? '-' }}
                                </td>

                                <td class="px-4 py-3 text-center whitespace-nowrap space-x-3">
                                    <a href="{{ route('products.edit', $product) }}"
                                       class="text-red-300 hover:text-red-200 hover:underline underline-offset-4">
                                        Rediģēt
                                    </a>

                                    <form method="POST"
                                          action="{{ route('products.destroy', $product) }}"
                                          class="inline"
                                          onsubmit="return confirm('Vai tiešām vēlaties dzēst šo produktu?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="text-slate-300 hover:text-white hover:underline underline-offset-4">
                                            Dzēst
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center py-10 text-slate-400">
                                    Nav pieejami produkti.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>