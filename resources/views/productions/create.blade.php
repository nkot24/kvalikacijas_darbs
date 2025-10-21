<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight"> Izveidot ražošanu </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto bg-white p-6 rounded shadow">

            {{-- ⚠️ enctype="multipart/form-data" for file uploads --}}
            <form id="productionForm" action="{{ route('productions.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- Order Selection --}}
                <div class="mb-6">
                    <label for="order_id" class="block font-semibold mb-2"> Izvēlieties pasūtījumu: </label>
                    @php $selectedOrderId = request()->get('order_id'); @endphp
                    <select name="order_id" required class="w-full border border-gray-300 p-2 rounded">
                        @foreach ($orders as $order)
                            <option value="{{ $order->id }}" {{ $selectedOrderId == $order->id ? 'selected' : '' }}>
                                {{ $order->pasutijuma_numurs }} – {{ $order->product->nosaukums ?? $order->produkts }}
                            </option>
                        @endforeach
                    </select>
                    @error('order_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Global files --}}
                <div class="mb-6">
                    <label class="block font-semibold mb-2"> Pievienot failus visiem izvēlētajiem procesiem: </label>
                    <input type="file" name="global_files[]" multiple
                        class="block w-full text-sm text-gray-700 file:mr-3 file:py-2 file:px-3 file:rounded file:border-0 file:bg-indigo-600 file:text-white hover:file:bg-indigo-700" />
                    <p class="text-xs text-gray-500 mt-1"> Šie faili tiks pievienoti visiem izvēlētajiem procesiem. </p>
                    @error('global_files.*') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Processes and Users --}}
                <div class="mb-6">
                    <label class="block font-semibold mb-2"> Izvēlieties procesus un piešķiriet darbiniekus (ja nepieciešams): </label>

                    @foreach ($processes as $process)
                        <div class="border p-4 mb-4 rounded bg-gray-50">

                            {{-- Process checkbox --}}
                            <label class="block font-semibold mb-2">
                                <input type="checkbox" name="process_ids[]" value="{{ $process->id }}" class="process-checkbox" data-process-id="{{ $process->id }}">
                                {{ $process->processa_nosaukums }}
                            </label>

                            {{-- User checkboxes --}}
                            <label class="block text-sm mb-1"> Darbinieki šim procesam: </label>
                            <div class="grid grid-cols-2 gap-2 mb-2">
                                @foreach ($process->users as $user)
                                    <label class="flex items-center space-x-2 text-sm">
                                        <input type="checkbox" name="users[{{ $process->id }}][]" value="{{ $user->id }}"
                                            class="user-checkbox process-{{ $process->id }}">
                                        <span>{{ $user->name }} ({{ $user->role }})</span>
                                    </label>
                                @endforeach
                            </div>
                            <p class="text-sm text-gray-500 mt-1">Ja neizvēlēsieties nevienu darbinieku, uzdevums tiks piešķirts visiem šī procesa darbiniekiem.</p>

                            {{-- File upload for this process --}}
                            <label class="block text-sm mt-3 mb-1"> Pievienot failus šim procesam: </label>
                            <input type="file" name="process_files[{{ $process->id }}][]" multiple
                                class="block w-full text-sm text-gray-700 file:mr-3 file:py-2 file:px-3 file:rounded file:border-0 file:bg-indigo-600 file:text-white hover:file:bg-indigo-700" />
                            <p class="text-xs text-gray-500 mt-1">Varat augšupielādēt vairākus failus.</p>
                        </div>
                    @endforeach

                    @error('process_ids') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    @error('process_files.*.*') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Submit button --}}
                <div class="mt-6">
                    <button type="submit"
                        class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Izveidot</button>
                </div>
            </form>
        </div>
    </div>

    {{-- JavaScript to auto-select users if none are selected --}}
    <script>
        document.getElementById('productionForm').addEventListener('submit', function (e) {
            document.querySelectorAll('.process-checkbox:checked').forEach(checkbox => {
                const processId = checkbox.dataset.processId;
                const userCheckboxes = document.querySelectorAll('.user-checkbox.process-' + processId);
                const anyChecked = Array.from(userCheckboxes).some(cb => cb.checked);

                if (!anyChecked) {
                    // Auto-check all users for this process
                    userCheckboxes.forEach(cb => cb.checked = true);
                }
            });
        });
    </script>
</x-app-layout>
