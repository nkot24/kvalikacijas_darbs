<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Izveidot ražošanu</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto bg-white p-6 rounded shadow">

            <form action="{{ route('productions.store') }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label for="order_id" class="block font-semibold">Izvēlieties pasūtījumu:</label>
                    <select name="order_id" required class="w-full border p-2">
                        @foreach ($orders as $order)
                            <option value="{{ $order->id }}">{{ $order->pasutijuma_numurs }} – {{ $order->produkts }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block font-semibold">Izvēlieties procesus:</label>
                    @foreach ($processes as $process)
                        <label class="block">
                            <input type="checkbox" name="process_ids[]" value="{{ $process->id }}">
                            {{ $process->processa_nosaukums }}
                        </label>
                    @endforeach
                </div>

                <div class="mb-4">
                    <label class="block font-semibold">Piešķirt darbiniekus:</label>
                    @foreach ($users as $user)
                        <label class="block">
                            <input type="checkbox" name="user_ids[]" value="{{ $user->id }}">
                            {{ $user->name }} ({{ $user->role }})
                        </label>
                    @endforeach
                </div>

                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Izveidot</button>
            </form>
        </div>
    </div>
</x-app-layout>
