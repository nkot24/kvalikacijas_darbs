@auth
    @if(auth()->user()->role !== 'worker')
        <!-- Full Navigation for Admins and others -->

        <nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
            <!-- Primary Navigation Menu -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <!-- Logo -->
                        <div class="shrink-0 flex items-center">
                            <a href="{{ route('dashboard') }}">
                                <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                            </a>
                        </div>

                        <!-- Navigation Dropdowns -->
                        <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex items-center">

                            <!-- Darbības -->
                            <x-dropdown align="left">
                                <x-slot name="trigger">
                                    <button class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 focus:outline-none">
                                        {{ __('Darbības') }}
                                        <svg class="ms-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                </x-slot>
                                <x-slot name="content">
                                    <x-dropdown-link :href="route('orders.index')" :active="request()->routeIs('orders.*')">
                                        {{ __('Pasūtijumi') }}
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('productions.index')" :active="request()->routeIs('productions.*')">
                                        {{ __('Ražošana') }}
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('tasks.index')" :active="request()->routeIs('tasks.*')">
                                        {{ __('Uzdevumi') }}
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('avansa_rekini.create')" :active="request()->routeIs('avansa_rekini.*')">
                                        {{ __('Avansa rēķins') }}
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('orderList.index')" :active="request()->routeIs('orderList.*')">
                                        {{ __('Iepirkuma saraksts') }}
                                    </x-dropdown-link>
                                </x-slot>
                            </x-dropdown>

                            <!-- Atskaites -->
                            <x-dropdown align="left">
                                <x-slot name="trigger">
                                    <button class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 focus:outline-none">
                                        {{ __('Atskaites') }}
                                        <svg class="ms-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                </x-slot>
                                <x-slot name="content">
                                    {{-- Add report links below --}}
                                    <x-dropdown-link :href="route('orderList.completed')" :active="request()->routeIs('orderList.*')">
                                        {{ __('Izpildītie iepirkumi') }}
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('orders.complete')" :active="request()->routeIs('orders.*')">
                                        {{ __('Izpildītie pasūtījumi') }}
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('inventory.storage')" :active="request()->routeIs('inventory.*')">
                                        {{ __('Noliktava') }}
                                    </x-dropdown-link>
                                </x-slot>
                            </x-dropdown>

                            <!-- Pamatdati -->
                            <x-dropdown align="left">
                                <x-slot name="trigger">
                                    <button class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 focus:outline-none">
                                        {{ __('Pamatdati') }}
                                        <svg class="ms-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                    </div>

                    <!-- Settings Dropdown -->
                    <div class="hidden sm:flex sm:items-center sm:ms-6">
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                    <div>{{ Auth::user()->name }}</div>

                                    <div class="ms-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
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

                    <!-- Hamburger -->
                    <div class="-me-2 flex items-center sm:hidden">
                        <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path :class="{ 'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path :class="{ 'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Responsive Navigation Menu -->
            <div :class="{ 'block': open, 'hidden': ! open }" class="hidden sm:hidden">
                <div class="pt-2 pb-3 space-y-1">
                    <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('orders.index')" :active="request()->routeIs('orders.*')">
                        {{ __('Orders') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('clients.index')" :active="request()->routeIs('clients.*')">
                        {{ __('Clients') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('products.index')" :active="request()->routeIs('products.*')">
                        {{ __('Products') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                        {{ __('Users') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('processes.index')" :active="request()->routeIs('processes.*')">
                        {{ __('Processes') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('productions.index')" :active="request()->routeIs('productions.*')">
                        {{ __('Productions') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('tasks.index')" :active="request()->routeIs('tasks.*')">
                        {{ __('Tasks') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('avansa_rekini.create')" :active="request()->routeIs('avansa_rekini.*')">
                        {{ __('Avansa rēķins') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('orderList.index')" :active="request()->routeIs('orderList.*')">
                        {{ __('Iepirkuma saraksts') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('orderList.completed')" :active="request()->routeIs('orderList.*')">
                        {{ __('Izpildītie iepirkumi') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('orders.complete')" :active="request()->routeIs('orders.*')">
                        {{ __('Izpildītie pasūtījumi') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('inventory.storage')" :active="request()->routeIs('inventory.*')">
                        {{ __('Noliktava') }}
                    </x-responsive-nav-link>
                </div>

                <!-- Responsive Settings Options -->
                <div class="pt-4 pb-1 border-t border-gray-200">
                    <div class="px-4">
                        <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                        <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                    </div>

                    <div class="mt-3 space-y-1">
                        <x-responsive-nav-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-responsive-nav-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-responsive-nav-link :href="route('logout')"
                                                   onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-responsive-nav-link>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

    @else
        <!-- Minimal Navigation for Workers (Uzdevumi + Iepirkumu saraksts only) -->

        <nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16 items-center">
                    <div class="flex items-center gap-6">
                        <a href="{{ route('tasks.index') }}"
                           class="{{ request()->routeIs('tasks.*') ? 'text-gray-900 font-semibold' : 'text-gray-700 hover:text-gray-900' }}">
                            {{ __('Uzdevumi') }}
                        </a>

                        <a href="{{ route('orderList.index') }}"
                           class="{{ request()->routeIs('orderList.*') ? 'text-gray-900 font-semibold' : 'text-gray-700 hover:text-gray-900' }}">
                            {{ __('Iepirkumu saraksts') }}
                        </a>
                    </div>

                    <!-- Settings Dropdown -->
                    <div class="hidden sm:flex sm:items-center sm:ms-6">
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                    <div>{{ Auth::user()->name }}</div>
                                    <div class="ms-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
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

                    <!-- Hamburger -->
                    <div class="-me-2 flex items-center sm:hidden">
                        <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path :class="{ 'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path :class="{ 'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Responsive Navigation Menu (Worker) -->
            <div :class="{ 'block': open, 'hidden': ! open }" class="hidden sm:hidden">
                <div class="pt-2 pb-3 space-y-1">
                    <x-responsive-nav-link :href="route('tasks.index')" :active="request()->routeIs('tasks.*')">
                        {{ __('Uzdevumi') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('orderList.index')" :active="request()->routeIs('orderList.*')">
                        {{ __('Iepirkumu saraksts') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('inventory.scan')" :active="request()->routeIs('inventory.*')">
                        {{ __('Skanēt produktus') }}
                    </x-responsive-nav-link>
                </div>

                <!-- Responsive Settings -->
                <div class="pt-4 pb-1 border-t border-gray-200">
                    <div class="px-4">
                        <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                        <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                    </div>

                    <div class="mt-3 space-y-1">
                        <x-responsive-nav-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-responsive-nav-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-responsive-nav-link :href="route('logout')"
                                                   onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-responsive-nav-link>
                        </form>
                    </div>
                </div>
            </div>
        </nav>
    @endif
@endauth
