<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Darba sākšana / beigšana') }}
        </h2>
    </x-slot>

    <div class="py-8 max-w-xl mx-auto text-center">
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
            <form method="POST" action="{{ route('work.end') }}">
                @csrf
                <x-primary-button>Beigt darbu</x-primary-button>
            </form>
        @else
            <p class="text-gray-600 mt-4">Darbs šodien jau pabeigts.</p>
            <p class="mt-2">Nostrādāts no {{ $log->start_time }} līdz {{ $log->end_time }}</p>
        @endif
    </div>
</x-app-layout>
