<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    {{-- Alpine.js for the modal (safe to include; it’s deferred) --}}
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <!-- Original Dashboard Section -->
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="rounded-2xl border border-white/10 bg-white/5 shadow-xl">
                <div class="p-6 text-slate-100">
                    {{ __("You're logged in!") }}
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ Work Start/End Section with modal on 'Beigt darbu' -->
    <div class="py-8 max-w-xl mx-auto text-center"
         x-data="{
            open:false,
            lunch:'{{ $log->lunch_minutes ?? '' }}',
            breaks:{{ $log->break_count ?? 0 }}
         }">

        <h3 class="text-lg font-semibold mb-2">Darba sākšana / beigšana</h3>

        <p class="mb-1 text-lg">Datums: {{ $today }}</p>
        <p class="mb-4 text-lg">
            Šomēnes nostrādāts: <strong>{{ $monthHours }}</strong> h
        </p>

        @if (session('success'))
            <div class="mb-4 text-green-600 font-medium">
                {{ session('success') }}
            </div>
        @endif

        @if (!$log || !$log->start_time)
            {{-- Work not started today --}}
            <form method="POST" action="{{ route('work.start') }}">
                @csrf
                <x-primary-button>Sākt darbu</x-primary-button>
            </form>

        @elseif ($log && !$log->end_time)
            {{-- Work started but not finished --}}
            <p class="mb-4 text-gray-700">
                Darbs sācies: <strong>{{ $log->start_time }}</strong>
            </p>

            {{-- Button opens modal instead of posting directly --}}
            <button type="button"
                    @click="open = true"
                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl shadow">
                Beigt darbu
            </button>
            {{-- Modal --}}
            <div x-show="open" x-transition class="fixed inset-0 z-50 flex items-center justify-center">
                <div class="absolute inset-0 bg-black/40" @click="open=false" aria-hidden="true"></div>

                <div class="relative bg-white rounded-2xl shadow-xl p-6 w-full max-w-md text-left">
                    <h3 class="text-lg font-semibold mb-4">Pabeigt darbu</h3>

                    <form method="POST" action="{{ route('work.end') }}">
                        @csrf

                        <div class="grid gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Pusdienas laiks (min)
                                </label>
                                <input type="number"
                                       name="lunch_minutes"
                                       x-model="lunch"
                                       min="0"
                                       placeholder="0"
                                       class="w-full border border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 rounded-lg px-3 py-2">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Paužu skaits <span class="text-xs text-gray-500">(1 pauze = 10 min)</span>
                                </label>
                                <input type="number"
                                       name="break_count"
                                       x-model="breaks"
                                       min="0"
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
            {{-- Work finished today --}}
            <p class="text-gray-600 mt-4">Darbs šodien jau pabeigts.</p>
            <p class="mt-2">
                Nostrādāts no <strong>{{ $log->start_time }}</strong>
                līdz <strong>{{ $log->end_time }}</strong>
            </p>
        @endif
    </div>
</x-app-layout>
