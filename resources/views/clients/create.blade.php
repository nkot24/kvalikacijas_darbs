<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-white leading-tight">
                Pievienot klientu
            </h2>
            <div class="hidden sm:block text-sm text-slate-400">
                Klienti • Jauns ieraksts
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="rounded-2xl border border-white/10 bg-white/5 backdrop-blur shadow-xl p-6">

                {{-- Errors --}}
                @if ($errors->any())
                    <div class="mb-6 rounded-xl border border-red-500/20 bg-red-500/10 px-4 py-3 text-red-200">
                        <ul class="list-disc pl-5 text-sm space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('clients.store') }}" method="POST">
                    @csrf

                    {{-- Basic client info --}}
                    <div class="grid md:grid-cols-2 gap-6">

                        <div>
                            <label class="text-sm text-slate-300">Nosaukums</label>
                            <input type="text" name="nosaukums"
                                   value="{{ old('nosaukums') }}"
                                   required autofocus
                                   class="mt-1 w-full rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-white
                                          focus:border-red-500/50 focus:ring-red-500/20">
                        </div>

                        <div>
                            <label class="text-sm text-slate-300">Reģistrācijas numurs</label>
                            <input type="text" name="registracijas_numurs"
                                   value="{{ old('registracijas_numurs') }}"
                                   required
                                   class="mt-1 w-full rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-white
                                          focus:border-red-500/50 focus:ring-red-500/20">
                        </div>

                        <div>
                            <label class="text-sm text-slate-300">PVN maksātāja numurs</label>
                            <input type="text" name="pvn_maksataja_numurs"
                                   value="{{ old('pvn_maksataja_numurs') }}"
                                   class="mt-1 w-full rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-white
                                          focus:border-red-500/50 focus:ring-red-500/20">
                        </div>

                        <div>
                            <label class="text-sm text-slate-300">Juridiskā adrese</label>
                            <input type="text" name="juridiska_adrese"
                                   value="{{ old('juridiska_adrese') }}"
                                   class="mt-1 w-full rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-white
                                          focus:border-red-500/50 focus:ring-red-500/20">
                        </div>

                    </div>

                    {{-- Kontaktpersonas --}}
                    <div class="mt-10">
                        <h3 class="text-lg font-semibold text-white mb-4">
                            Kontaktpersonas
                        </h3>

                        <div id="contacts"></div>

                        <button type="button"
                                id="add-contact"
                                class="mt-2 px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-white text-sm ring-1 ring-white/10">
                            + Pievienot kontaktpersonu
                        </button>
                    </div>

                    {{-- Piegādes adreses --}}
                    <div class="mt-10">
                        <h3 class="text-lg font-semibold text-white mb-4">
                            Piegādes adreses
                        </h3>

                        <div id="addresses"></div>

                        <button type="button"
                                id="add-address"
                                class="mt-2 px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-white text-sm ring-1 ring-white/10">
                            + Pievienot adresi
                        </button>
                    </div>

                    {{-- Submit --}}
                    <div class="mt-8">
                        <button type="submit"
                                class="px-6 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white font-semibold shadow">
                            Saglabāt klientu
                        </button>
                    </div>

                </form>
            </div>

            <div class="mt-6 h-1 bg-gradient-to-r from-transparent via-red-600/40 to-transparent rounded"></div>

        </div>
    </div>

    {{-- JS unchanged except styling --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            let contactIndex = 0;
            let addressIndex = 0;

            document.getElementById('add-contact').addEventListener('click', function () {

                const container = document.getElementById('contacts');

                container.insertAdjacentHTML('beforeend', `
                    <div class="contact mb-4 p-4 rounded-xl border border-white/10 bg-[#0B0F14]/40">

                        <label class="text-sm text-slate-300">Kontaktpersonas vārds</label>
                        <input name="contact_persons[${contactIndex}][kontakt_personas_vards]" required
                               class="w-full rounded-lg border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-white mb-2">

                        <label class="text-sm text-slate-300">E-pasts</label>
                        <input name="contact_persons[${contactIndex}][e-pasts]"
                               class="w-full rounded-lg border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-white mb-2">

                        <label class="text-sm text-slate-300">Telefons</label>
                        <input name="contact_persons[${contactIndex}][telefons]"
                               class="w-full rounded-lg border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-white mb-2">

                        <button type="button"
                                class="remove-contact text-red-400 hover:text-red-300 text-sm">
                            Noņemt
                        </button>

                    </div>
                `);

                contactIndex++;

            });

            document.getElementById('contacts').addEventListener('click', function (e) {
                if (e.target.classList.contains('remove-contact')) {
                    e.target.closest('.contact').remove();
                }
            });

            document.getElementById('add-address').addEventListener('click', function () {

                const container = document.getElementById('addresses');

                container.insertAdjacentHTML('beforeend', `
                    <div class="address mb-4 p-4 rounded-xl border border-white/10 bg-[#0B0F14]/40">

                        <label class="text-sm text-slate-300">Piegādes adrese</label>
                        <input name="delivery_addresses[${addressIndex}][piegades_adrese]" required
                               class="w-full rounded-lg border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-white mb-2">

                        <button type="button"
                                class="remove-address text-red-400 hover:text-red-300 text-sm">
                            Noņemt
                        </button>

                    </div>
                `);

                addressIndex++;

            });

            document.getElementById('addresses').addEventListener('click', function (e) {
                if (e.target.classList.contains('remove-address')) {
                    e.target.closest('.address').remove();
                }
            });

        });
    </script>

</x-app-layout>