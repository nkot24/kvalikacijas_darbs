<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Izveidot ražošanu</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto bg-white p-6 rounded shadow">

            <form action="{{ route('productions.store') }}" method="POST">
                @csrf

                {{-- Order selection --}}
                <div class="mb-6">
                    <label for="order_id" class="block font-semibold mb-2">Izvēlieties pasūtījumu:</label>
                    <select name="order_id" required class="w-full border border-gray-300 p-2 rounded">
                        @foreach ($orders as $order)
                            <option value="{{ $order->id }}">{{ $order->pasutijuma_numurs }} – {{ $order->produkts }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Process and per-process user selection --}}
                <div class="mb-6">
                    <label class="block font-semibold mb-2">Izvēlieties procesus un piešķiriet darbiniekus (ja nepieciešams):</label>

                    @foreach ($processes as $process)
                        <div class="border p-4 mb-4 rounded bg-gray-50">
                            <label class="block font-semibold mb-2">
                                <input type="checkbox" name="process_ids[]" value="{{ $process->id }}">
                                {{ $process->processa_nosaukums }}
                            </label>

                            <label class="block text-sm mb-1">Darbinieki šim procesam:</label>
                            <select name="users[{{ $process->id }}][]" multiple class="w-full border border-gray-300 p-2 rounded">
                                @foreach ($process->users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->role }})</option>
                                @endforeach
                            </select>

                            <p class="text-sm text-gray-500 mt-1">Ja neizvēlēsieties nevienu darbinieku, uzdevums tiks piešķirts visiem šī procesa darbiniekiem.</p>
                        </div>
                    @endforeach
                </div>

                {{-- Submit button --}}
                <div class="mt-6">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                        Izveidot
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
