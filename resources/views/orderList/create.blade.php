<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-white leading-tight">
                Jauns iepirkums
            </h2>

            <a href="{{ route('orderList.index') }}"
               class="text-sm text-slate-400 hover:text-white transition">
                ← Atpakaļ uz sarakstu
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto">

            {{-- Form Card --}}
            <div class="rounded-2xl border border-white/10 bg-white/5 backdrop-blur shadow-xl p-6 sm:p-8">

                <form method="POST"
                      action="{{ route('orderList.store') }}"
                      enctype="multipart/form-data"
                      class="space-y-6">
                    @csrf

                    {{-- Nosaukums --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">
                            Nosaukums
                        </label>

                        <input type="text"
                               name="name"
                               value="{{ old('name') }}"
                               required
                               class="w-full rounded-xl border border-white/10 bg-[#0B0F14]/60 px-4 py-2.5 text-white placeholder:text-slate-500
                                      focus:border-red-500/50 focus:ring-red-500/20">

                        @error('name')
                            <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Daudzums --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">
                            Daudzums
                        </label>

                        <input type="number"
                               name="quantity"
                               value="{{ old('quantity',1) }}"
                               min="1"
                               required
                               class="w-full rounded-xl border border-white/10 bg-[#0B0F14]/60 px-4 py-2.5 text-white
                                      focus:border-red-500/50 focus:ring-red-500/20">

                        @error('quantity')
                            <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Foto --}}
                    <div>
                        <label for="photo" class="block text-sm font-medium text-slate-300 mb-2">
                            Foto
                        </label>

                        <input type="file"
                               name="photo"
                               id="photo"
                               class="w-full text-sm text-slate-300
                                      file:mr-4 file:py-2 file:px-4
                                      file:rounded-xl file:border-0
                                      file:text-sm file:font-semibold
                                      file:bg-white/10 file:text-white
                                      hover:file:bg-white/15
                                      cursor-pointer">

                        @error('photo')
                            <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Submit --}}
                    <div class="pt-4">
                        <button type="submit"
                                class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-semibold shadow transition">
                            Saglabāt
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</x-app-layout>