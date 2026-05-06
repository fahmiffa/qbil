<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    public function logout(Logout $logout): void
    {
        $logout();
        $this->redirect('/', navigate: true);
    }
}; ?>

<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    class="fixed inset-0 z-50 pointer-events-none transition-all duration-300">

    <!-- Backdrop for Mobile -->
    <div x-show="sidebarOpen"
        @click="sidebarOpen = false"
        class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm lg:hidden z-[-1] pointer-events-auto"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"></div>

    <!-- Sidebar Inner Container -->
    <div :class="sidebarOpen ? 'w-64' : 'w-20'"
        class="h-screen bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 shadow-2xl border-r border-gray-100 dark:border-slate-800 flex flex-col transition-all duration-300 ease-in-out pointer-events-auto overflow-visible">

        <!-- Sidebar Header -->
        <div class="h-16 flex items-center px-4 border-b border-gray-100 dark:border-slate-700/80 bg-white dark:bg-slate-900 transition-colors">
            <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-3 overflow-hidden">
                <div class="shrink-0 bg-blue-600 rounded-lg p-1.5 shadow-sm">
                    <x-application-logo class="w-7 h-7 text-white" />
                </div>
                <span x-show="sidebarOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    class="font-extrabold text-xl tracking-wider text-slate-900 dark:text-white whitespace-nowrap">
                    QBIL
                </span>
            </a>
        </div>

        <!-- Sidebar Navigation -->
        <nav class="flex-1 mt-6 px-3 space-y-2 no-scrollbar overflow-y-auto overflow-x-visible">

            <!-- Dashboard -->
            <x-sidebar-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" icon="home">
                Dashboard
            </x-sidebar-link>

            <div class="pt-4 pb-2">
                <span x-show="sidebarOpen" class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-widest px-3">Layanan</span>
                <div x-show="!sidebarOpen" class="border-t border-slate-100 dark:border-slate-800 mx-3"></div>
            </div>

            <x-sidebar-link :href="route('customers')" :active="request()->routeIs('customers')" icon="users">
                Pelanggan
            </x-sidebar-link>

            @if(auth()->user()->hasFeature('mikrotik'))
            <x-sidebar-link :href="route('hotspot')" :active="request()->routeIs('hotspot')" icon="wifi">
                Hotspot
            </x-sidebar-link>
            @endif

            <x-sidebar-link :href="route('invoice')" :active="request()->routeIs('invoice')" icon="interface">
                Tagihan
            </x-sidebar-link>


            <x-sidebar-link :href="route('piutangs')" :active="request()->routeIs('piutangs')" icon="user-group">
                Piutang
            </x-sidebar-link>

            <x-sidebar-link :href="route('finance')" :active="request()->routeIs('finance')" icon="document-text">
                Buku Kas
            </x-sidebar-link>

            @if(auth()->user()->hasFeature('voucher'))
            <x-sidebar-link :href="route('vouchers')" :active="request()->routeIs('vouchers')" icon="voucher">
                Voucher
            </x-sidebar-link>
            @endif

            <div class="pt-4 pb-2">
                <span x-show="sidebarOpen" class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-widest px-3">Master</span>
                <div x-show="!sidebarOpen" class="border-t border-slate-100 dark:border-slate-800 mx-3"></div>
            </div>

            @if(auth()->user()->hasFeature('mikrotik'))
            <!-- Router Mikrotik -->
            <x-sidebar-link :href="route('router')" :active="request()->routeIs('router')" icon="server">
                Router
            </x-sidebar-link>

            <x-sidebar-link :href="route('assets')" :active="request()->routeIs('assets')" icon="box">
                Asset
            </x-sidebar-link>
            @endif

            {{-- Collapsible Paket Group --}}
            @php
            $paketActive = request()->routeIs('static-packages') || request()->routeIs('ppp-profiles') || request()->routeIs('hotspot-profiles');
            @endphp
            <div x-data="{
                    open: {{ $paketActive ? 'true' : 'false' }} || JSON.parse(localStorage.getItem('paketOpen') || 'false'),
                }"
                x-init="$watch('open', v => localStorage.setItem('paketOpen', v))">

                <button x-ref="paketBtn"
                    x-data="{ tipTop: 0, tipLeft: 0 }"
                    @mouseenter="let rect = $el.getBoundingClientRect(); tipTop = rect.top + (rect.height/2); tipLeft = rect.right + 10"
                    @click="sidebarOpen ? open = !open : (sidebarOpen = true, open = true)"
                    class="group relative w-full flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200
                    {{ $paketActive
                        ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20'
                        : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700/50 hover:text-slate-700 dark:hover:text-slate-200' }}">

                    {{-- Icon: Package/Box --}}
                    <span class="shrink-0 w-6 h-6 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </span>

                    <span x-show="sidebarOpen"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        class="flex-1 text-left font-semibold whitespace-nowrap">
                        Paket
                    </span>

                    {{-- Chevron --}}
                    <svg x-show="sidebarOpen"
                        :class="open ? 'rotate-180' : ''"
                        class="w-4 h-4 transition-transform duration-200 shrink-0"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                    {{-- Tooltip (Visible only when sidebar is collapsed) --}}
                    <div :class="sidebarOpen ? 'hidden' : ''"
                        class="fixed px-3 py-2 bg-slate-900 dark:bg-slate-800 text-white text-xs font-bold rounded-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 whitespace-nowrap z-[9999] shadow-2xl border border-slate-700/50 pointer-events-none"
                        :style="`top: ${tipTop}px; left: ${tipLeft}px; transform: translateY(-50%)`">
                        Paket
                        <!-- Triangle Arrow -->
                        <div class="absolute right-full top-1/2 -translate-y-1/2 border-[6px] border-transparent border-r-slate-900 dark:border-r-slate-800"></div>
                    </div>
                </button>

                {{-- Sub-menu Items --}}
                <div x-show="open && sidebarOpen"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-2"
                    class="ml-4 mt-1 space-y-0.5 border-l-2 border-slate-100 dark:border-slate-700 pl-3">

                    {{-- Static --}}
                    <a href="{{ route('static-packages') }}"
                        wire:navigate
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-150
                        {{ request()->routeIs('static-packages')
                            ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 font-semibold'
                            : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-slate-800 font-medium' }}">
                        <div class="shrink-0 w-6 h-6">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7" />
                            </svg>
                        </div>
                        <span class="whitespace-nowrap">Static</span>
                    </a>

                    @if(auth()->user()->hasFeature('mikrotik'))
                    {{-- PPPoE --}}
                    <a href="{{ route('ppp-profiles') }}"
                        wire:navigate
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-150
                        {{ request()->routeIs('ppp-profiles')
                            ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 font-semibold'
                            : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-slate-800 font-medium' }}">
                        <div class="shrink-0 w-6 h-6">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <span class="whitespace-nowrap">PPPoE</span>
                    </a>

                    {{-- Hotspot --}}
                    <a href="{{ route('hotspot-profiles') }}"
                        wire:navigate
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-150
                        {{ request()->routeIs('hotspot-profiles')
                            ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 font-semibold'
                            : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-slate-800 font-medium' }}">
                        <div class="shrink-0 w-6 h-6">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                            </svg>
                        </div>
                        <span class="whitespace-nowrap">Hotspot</span>
                    </a>
                    @endif

                </div>
            </div>

            <div class="pt-4 pb-2">
                <span x-show="sidebarOpen" class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-widest px-3">Pengaturan</span>
                <div x-show="!sidebarOpen" class="border-t border-slate-100 dark:border-slate-800 mx-3"></div>
            </div>

            <x-sidebar-link :href="route('app-settings')" :active="request()->routeIs('app-settings')" icon="cube">
                Aplikasi
            </x-sidebar-link>

            @if(auth()->user()->hasFeature('whatsapp'))
            <x-sidebar-link :href="route('whatsapp')" :active="request()->routeIs('whatsapp')" icon="message">
                WhatsApp
            </x-sidebar-link>
            @endif

            <x-sidebar-link :href="route('activities')" :active="request()->routeIs('activities')" icon="clock">
                Log Aktivitas
            </x-sidebar-link>

            <x-sidebar-link :href="route('profile')" :active="request()->routeIs('profile')" icon="user">
                Profile
            </x-sidebar-link>


            @if(auth()->user()->role == 0)
            <div class="pt-4 pb-2">
                <span x-show="sidebarOpen" class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-widest px-3">Admin</span>
                <div x-show="!sidebarOpen" class="border-t border-slate-100 dark:border-slate-800 mx-3"></div>
            </div>

            <!-- Akun (User Management) -->
            <x-sidebar-link :href="route('akun')" :active="request()->routeIs('akun')" icon="user-group">
                Kelola User
            </x-sidebar-link>

            <x-sidebar-link :href="route('features')" :active="request()->routeIs('features')" icon="cube">
                Fitur & Modul
            </x-sidebar-link>
            @endif
        </nav>

        <!-- Sidebar Footer (Logout) -->
        <div class="p-4 border-t border-gray-100 dark:border-slate-800">
            <button
                x-on:click="
                    Swal.fire({
                        title: 'Keluar?',
                        text: 'Anda yakin ingin mengakhiri sesi ini?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3b82f6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Ya, Keluar',
                        cancelButtonText: 'Batal',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $wire.logout();
                        }
                    })
                "
                class="group relative flex items-center gap-3 w-full px-3 py-2 text-slate-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/10 rounded-lg transition-all">
                <div class="shrink-0 w-6 h-6">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </div>
                <span x-show="sidebarOpen" class="font-medium whitespace-nowrap">Logout</span>

                <!-- Tooltip -->
                <div x-show="!sidebarOpen"
                    x-data="{ top: 0, left: 0 }"
                    @mouseenter="let rect = $el.parentElement.getBoundingClientRect(); top = rect.top + (rect.height/2); left = rect.right + 10"
                    class="fixed px-3 py-2 bg-slate-900 dark:bg-slate-800 text-white text-xs font-bold rounded-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 whitespace-nowrap z-[100] shadow-2xl border border-slate-700/50 pointer-events-none transform translate-x-2 group-hover:translate-x-0"
                    :style="'top: ' + top + 'px; left: ' + left + 'px; transform: translateY(-50%)'">
                    Logout
                    <!-- Triangle Arrow -->
                    <div class="absolute right-full top-1/2 -translate-y-1/2 border-[6px] border-transparent border-r-slate-900 dark:border-r-slate-800"></div>
                </div>
            </button>

        </div>
    </div>
</aside>