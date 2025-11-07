<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Darba sākšana / beigšana') }}
        </h2>
    </x-slot>

    {{-- Alpine.js for the modal --}}
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <div class="py-8 max-w-xl mx-auto text-center" x-data="{ open:false, lunch:'', breaks:0 }">
        <p class="mb-4 text-lg">Datums: {{ $today }}</p>

        @if (session('success'))
            <div class="mb-4 text-green-600">{{ session('success') }}</div>
        @endif

        @if (!$log || !$log->start_time)
            <form method="POST" action="{{ route('work.start') }}">
                @csrf
                <x-primary-button>Sākt darbu</x-primary-button>
            </form>
        @elseif ($log && !$log->end_time)
            {{-- Button that opens the modal --}}
            <button type="button"
                    @click="open = true"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow">
                Beigt darbu
            </button>

            {{-- Modal --}}
            <div x-show="open" x-transition class="fixed inset-0 z-50 flex items-center justify-center">
                <div class="absolute inset-0 bg-black/40" @click="open=false" aria-hidden="true"></div>

                <div class="relative bg-white rounded-2xl shadow-xl p-6 w-full max-w-md">
                    <h3 class="text-lg font-semibold mb-4">Pabeigt darbu</h3>

                    <form method="POST" action="{{ route('work.end') }}">
                        @csrf

                        <div class="grid gap-4 text-left">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Pusdienas laiks (min)
                                </label>
                                <input type="number" name="lunch_minutes" x-model="lunch" min="0"
                                       placeholder="0"
                                       class="w-full border border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 rounded-lg px-3 py-2">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Paužu skaits <span class="text-xs text-gray-500">(1 pauze = 10 min)</span>
                                </label>
                                <input type="number" name="break_count" x-model="breaks" min="0"
                                       placeholder="0"
                                       class="w-full border border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 rounded-lg px-3 py-2">
                            </div>
                        </div>

                        <div class="mt-6 flex gap-3 justify-end">
                            <button type="button"
                                    @click="open=false"
                                    class="px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-50">
                                Atcelt
                            </button>
                            <button type="submit"
                                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg">
                                Apstiprināt
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @else
            <p class="text-gray-600 mt-4">Darbs šodien jau pabeigts.</p>
            <p class="mt-2">Nostrādāts no {{ $log->start_time }} līdz {{ $log->end_time }}</p>
        @endif
    </div>
</x-app-layout>
