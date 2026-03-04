<x-guest-layout>
    <div class="h-screen w-screen overflow-hidden flex items-center justify-center px-4">
        <div class="w-full max-w-md">

            {{-- Login Card --}}
            <div class="rounded-2xl border border-white/10 bg-white/5 backdrop-blur shadow-xl p-6">

                {{-- Logo INSIDE the box --}}
                <div class="flex justify-center mb-6">
                    <img src="{{ asset('images/logo.png') }}" class="h-9 opacity-80" alt="Logo">
                </div>

                {{-- Title --}}
                <div class="mb-6 text-center">
                    <h1 class="text-2xl font-semibold text-white">Pieslēgties</h1>
                    <p class="mt-1 text-sm text-slate-400">
                        Ievadiet vārdu un paroli, lai turpinātu.
                    </p>
                </div>

                <x-auth-session-status class="mb-4 text-slate-200" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="name" :value="__('Name')" class="text-slate-200" />
                        <x-text-input
                            id="name"
                            class="mt-1 block w-full rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-sm text-white placeholder:text-slate-500 focus:border-red-500/50 focus:ring-red-500/20"
                            type="text"
                            name="name"
                            :value="old('name')"
                            required
                            autofocus
                            autocomplete="username"
                        />
                        <x-input-error :messages="$errors->get('name')" class="mt-2 text-red-300" />
                    </div>

                    <div>
                        <x-input-label for="password" :value="__('Password')" class="text-slate-200" />
                        <x-text-input
                            id="password"
                            class="mt-1 block w-full rounded-xl border border-white/10 bg-[#0B0F14]/60 px-3 py-2 text-sm text-white placeholder:text-slate-500 focus:border-red-500/50 focus:ring-red-500/20"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                        />
                        <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-300" />
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <label for="remember_me" class="inline-flex items-center gap-2 select-none">
                            <input
                                id="remember_me"
                                type="checkbox"
                                name="remember"
                                class="h-4 w-4 rounded border-white/20 bg-transparent text-red-600 focus:ring-red-500/20"
                            >
                            <span class="text-sm text-slate-300">{{ __('Remember me') }}</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}"
                               class="text-sm text-slate-300 hover:text-white hover:underline underline-offset-4">
                                Aizmirsāt paroli?
                            </a>
                        @endif
                    </div>

                    <div class="pt-2">
                        <button
                            type="submit"
                            class="w-full inline-flex items-center justify-center px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-semibold shadow"
                        >
                            {{ __('Log in') }}
                        </button>
                    </div>
                </form>
            </div>

            <div class="mt-6 h-1 bg-gradient-to-r from-transparent via-red-600/40 to-transparent rounded"></div>
        </div>
    </div>
</x-guest-layout>