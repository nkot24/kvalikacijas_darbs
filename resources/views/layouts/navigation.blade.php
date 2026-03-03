@auth
    @php
        $isWorker = auth()->user()->role === 'worker';

        $navShell     = "border-b border-white/10 bg-[#0F172A]/70 backdrop-blur";
        $linkBase     = "inline-flex items-center px-3 py-2 text-sm font-medium rounded-lg transition";
        $linkIdle     = "text-slate-300 hover:text-white hover:bg-white/5";
        $linkActive   = "text-white bg-white/10 ring-1 ring-white/10";

        $triggerBase  = "inline-flex items-center px-3 py-2 text-sm font-medium rounded-lg transition";
        $triggerIdle  = "text-slate-300 hover:text-white hover:bg-white/5";

        $userBtn      = "inline-flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-lg text-slate-300 bg-white/5 hover:bg-white/10 hover:text-white ring-1 ring-white/10 transition";
        $mobileBtn    = "inline-flex items-center justify-center p-2 rounded-lg text-slate-300 hover:text-white hover:bg-white/10 ring-1 ring-white/10 transition";

        $panel        = "mt-3 rounded-2xl border border-white/10 bg-[#0B0F14]/60 backdrop-blur p-2";
    @endphp

    <nav x-data="{ open: false }" class="relative z-50 {{ $navShell }}">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Left -->
                <div class="flex items-center gap-6">
                    <!-- Logo -->
                    <a href="{{ $isWorker ? route('tasks.index') : route('dashboard') }}" class="flex items-center gap-3">
                        <img src="{{ asset('images/logo.png') }}" class="h-10 w-auto" alt="Logo">
                        <div class="hidden sm:block leading-tight">
                            <div class="text-white font-semibold tracking-wide">
                                {{ config('app.name', 'Laravel') }}
                            </div>
                            <div class="text-xs text-slate-400">
                                Production • Orders • Analytics
                            </div>
                        </div>
                    </a>

                    @if(!$isWorker)
                        <!-- Desktop dropdowns (Admin) -->
                        <div class="hidden sm:flex items-center gap-2">
                            <!-- Darbības -->
                            <x-dropdown align="left" width="56">
                                <x-slot name="trigger">
                                    <button class="{{ $triggerBase }} {{ $triggerIdle }}">
                                        {{ __('Darbības') }}
                                        <svg class="ms-2 h-4 w-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                </x-slot>

                                <x-slot name="content">
                                    <x-dropdown-link :href="route('orders.index')" :active="request()->routeIs('orders.*')">
                                        {{ __('Pasūtijumi') }}
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('tasks.index')" :active="request()->routeIs('tasks.*')">
                                        {{ __('Uzdevumi') }}
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('orderList.index')" :active="request()->routeIs('orderList.*')">
                                        {{ __('Iepirkuma saraksts') }}
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('inventory.scan')" :active="request()->routeIs('inventory.*')">
                                        {{ __('Skenēt saražoto produkciju') }}
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('inventory.materials.scan')" :active="request()->routeIs('inventory.materials.*')">
                                        {{ __('Norakstīšana') }}
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('work.index')" :active="request()->routeIs('work.*')">
                                        {{ __('Sākt/beigt darbu') }}
                                    </x-dropdown-link>
                                </x-slot>
                            </x-dropdown>

                            <!-- Atskaites -->
                            <x-dropdown align="left" width="56">
                                <x-slot name="trigger">
                                    <button class="{{ $triggerBase }} {{ $triggerIdle }}">
                                        {{ __('Atskaites') }}
                                        <svg class="ms-2 h-4 w-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                </x-slot>

                                <x-slot name="content">
                                    <x-dropdown-link :href="route('orderList.completed')" :active="request()->routeIs('orderList.*')">
                                        {{ __('Izpildītie iepirkumi') }}
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('orders.complete')" :active="request()->routeIs('orders.*')">
                                        {{ __('Izpildītie pasūtījumi') }}
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('inventory.transfers.index')" :active="request()->routeIs('inventory.*')">
                                        {{ __('Saražotā produkcija') }}
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('inventory.materials.index')" :active="request()->routeIs('inventory.materials.*')">
                                        {{ __('Izmantotie materiāli') }}
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('work.hours')" :active="request()->routeIs('work.*')">
                                        {{ __('Darba stundas') }}
                                    </x-dropdown-link>
                                </x-slot>
                            </x-dropdown>

                            <!-- Pamatdati -->
                            <x-dropdown align="left" width="56">
                                <x-slot name="trigger">
                                    <button class="{{ $triggerBase }} {{ $triggerIdle }}">
                                        {{ __('Pamatdati') }}
                                        <svg class="ms-2 h-4 w-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                </x-slot>

                                <x-slot name="content">
                                    <x-dropdown-link :href="route('clients.index')" :active="request()->routeIs('clients.*')">
                                        {{ __('Klienti') }}
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('products.index')" :active="request()->routeIs('products.*')">
                                        {{ __('Produkti') }}
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                                        {{ __('Lietotāji') }}
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('processes.index')" :active="request()->routeIs('processes.*')">
                                        {{ __('Procesi') }}
                                    </x-dropdown-link>
                                </x-slot>
                            </x-dropdown>
                        </div>
                    @else
                        <!-- Desktop links (Worker) -->
                        <div class="hidden sm:flex items-center gap-2">
                            <a href="{{ route('tasks.index') }}"
                               class="{{ $linkBase }} {{ request()->routeIs('tasks.*') ? $linkActive : $linkIdle }}">
                                {{ __('Uzdevumi') }}
                            </a>

                            <a href="{{ route('orderList.index') }}"
                               class="{{ $linkBase }} {{ request()->routeIs('orderList.*') ? $linkActive : $linkIdle }}">
                                {{ __('Iepirkumu saraksts') }}
                            </a>

                            <a href="{{ route('work.index') }}"
                               class="{{ $linkBase }} {{ request()->routeIs('work.*') ? $linkActive : $linkIdle }}">
                                {{ __('Sākt/beigt darbu') }}
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Right -->
                <div class="flex items-center gap-3">
                    <!-- User dropdown (desktop) -->
                    <div class="hidden sm:flex sm:items-center">
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button class="{{ $userBtn }}">
                                    <span class="max-w-[160px] truncate">{{ Auth::user()->name }}</span>
                                    <svg class="h-4 w-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <x-dropdown-link :href="route('profile.edit')">
                                    {{ __('Profile') }}
                                </x-dropdown-link>

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <x-dropdown-link :href="route('logout')"
                                                     onclick="event.preventDefault(); this.closest('form').submit();">
                                        {{ __('Log Out') }}
                                    </x-dropdown-link>
                                </form>
                            </x-slot>
                        </x-dropdown>
                    </div>

                    <!-- Hamburger (mobile) -->
                    <div class="sm:hidden">
                        <button @click="open = ! open" class="{{ $mobileBtn }}" aria-label="Toggle menu">
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path :class="{ 'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path :class="{ 'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Red Accent Line -->
        <div class="h-1 bg-gradient-to-r from-transparent via-red-600 to-transparent"></div>

        <!-- Mobile Panel -->
        <div :class="{ 'block': open, 'hidden': ! open }" class="hidden sm:hidden relative z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="{{ $panel }}">
                    <div class="space-y-1">
                        @if(!$isWorker)
                            <a href="{{ route('dashboard') }}" class="block {{ $linkBase }} {{ request()->routeIs('dashboard') ? $linkActive : $linkIdle }}">
                                {{ __('Dashboard') }}
                            </a>
                            <a href="{{ route('orders.index') }}" class="block {{ $linkBase }} {{ request()->routeIs('orders.*') ? $linkActive : $linkIdle }}">
                                {{ __('Pasūtījumi') }}
                            </a>
                            <a href="{{ route('clients.index') }}" class="block {{ $linkBase }} {{ request()->routeIs('clients.*') ? $linkActive : $linkIdle }}">
                                {{ __('Klienti') }}
                            </a>
                            <a href="{{ route('products.index') }}" class="block {{ $linkBase }} {{ request()->routeIs('products.*') ? $linkActive : $linkIdle }}">
                                {{ __('Produkti') }}
                            </a>
                            <a href="{{ route('users.index') }}" class="block {{ $linkBase }} {{ request()->routeIs('users.*') ? $linkActive : $linkIdle }}">
                                {{ __('Lietotāji') }}
                            </a>
                            <a href="{{ route('processes.index') }}" class="block {{ $linkBase }} {{ request()->routeIs('processes.*') ? $linkActive : $linkIdle }}">
                                {{ __('Procesi') }}
                            </a>
                            <a href="{{ route('tasks.index') }}" class="block {{ $linkBase }} {{ request()->routeIs('tasks.*') ? $linkActive : $linkIdle }}">
                                {{ __('Uzdevumi') }}
                            </a>
                            <a href="{{ route('orderList.index') }}" class="block {{ $linkBase }} {{ request()->routeIs('orderList.*') ? $linkActive : $linkIdle }}">
                                {{ __('Iepirkuma saraksts') }}
                            </a>
                            <a href="{{ route('orderList.completed') }}" class="block {{ $linkBase }} {{ request()->routeIs('orderList.completed') ? $linkActive : $linkIdle }}">
                                {{ __('Izpildītie iepirkumi') }}
                            </a>
                            <a href="{{ route('orders.complete') }}" class="block {{ $linkBase }} {{ request()->routeIs('orders.complete') ? $linkActive : $linkIdle }}">
                                {{ __('Izpildītie pasūtījumi') }}
                            </a>
                            <a href="{{ route('inventory.transfers.index') }}" class="block {{ $linkBase }} {{ request()->routeIs('inventory.transfers.*') ? $linkActive : $linkIdle }}">
                                {{ __('Saražotā produkcija') }}
                            </a>
                            <a href="{{ route('inventory.materials.index') }}" class="block {{ $linkBase }} {{ request()->routeIs('inventory.materials.*') ? $linkActive : $linkIdle }}">
                                {{ __('Izmantotie materiāli') }}
                            </a>
                            <a href="{{ route('inventory.scan') }}" class="block {{ $linkBase }} {{ request()->routeIs('inventory.scan') ? $linkActive : $linkIdle }}">
                                {{ __('Skenēt saražoto produkciju') }}
                            </a>
                            <a href="{{ route('inventory.materials.scan') }}" class="block {{ $linkBase }} {{ request()->routeIs('inventory.materials.scan') ? $linkActive : $linkIdle }}">
                                {{ __('Norakstīšana') }}
                            </a>
                            <a href="{{ route('work.index') }}" class="block {{ $linkBase }} {{ request()->routeIs('work.*') ? $linkActive : $linkIdle }}">
                                {{ __('Sākt/beigt darbu') }}
                            </a>
                            <a href="{{ route('work.hours') }}" class="block {{ $linkBase }} {{ request()->routeIs('work.hours') ? $linkActive : $linkIdle }}">
                                {{ __('Darba stundas') }}
                            </a>
                        @else
                            <a href="{{ route('tasks.index') }}" class="block {{ $linkBase }} {{ request()->routeIs('tasks.*') ? $linkActive : $linkIdle }}">
                                {{ __('Uzdevumi') }}
                            </a>
                            <a href="{{ route('orderList.index') }}" class="block {{ $linkBase }} {{ request()->routeIs('orderList.*') ? $linkActive : $linkIdle }}">
                                {{ __('Iepirkumu saraksts') }}
                            </a>
                            <a href="{{ route('work.index') }}" class="block {{ $linkBase }} {{ request()->routeIs('work.*') ? $linkActive : $linkIdle }}">
                                {{ __('Sākt/beigt darbu') }}
                            </a>
                        @endif
                    </div>

                    <div class="my-3 h-px bg-white/10"></div>

                    <div class="px-2">
                        <div class="text-sm text-white font-medium truncate">{{ Auth::user()->name }}</div>
                        <div class="text-xs text-slate-400 truncate">{{ Auth::user()->email }}</div>
                    </div>

                    <div class="mt-3 space-y-1">
                        <a href="{{ route('profile.edit') }}" class="block {{ $linkBase }} {{ request()->routeIs('profile.edit') ? $linkActive : $linkIdle }}">
                            {{ __('Profile') }}
                        </a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left {{ $linkBase }} {{ $linkIdle }}">
                                {{ __('Log Out') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </nav>
@endauth