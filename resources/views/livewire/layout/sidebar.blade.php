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

<aside :class="sidebarOpen ? 'w-64 translate-x-0' : 'w-20 -translate-x-full lg:translate-x-0 lg:w-20'"
    class="h-screen bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 fixed left-0 top-0 transition-all duration-300 ease-in-out z-40 flex flex-col shadow-xl border-r border-gray-100 dark:border-slate-800 overflow-x-hidden">

    <!-- Backdrop for Mobile -->
    <div x-show="sidebarOpen"
        @click="sidebarOpen = false"
        class="fixed inset-0 bg-black/50 lg:hidden z-[-1]"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"></div>

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
    <nav class="flex-1 mt-6 px-3 space-y-2 overflow-y-auto no-scrollbar">
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

        <x-sidebar-link :href="route('hotspot')" :active="request()->routeIs('hotspot')" icon="wifi">
            Hotspot
        </x-sidebar-link>

        <x-sidebar-link :href="route('invoice')" :active="request()->routeIs('invoice')" icon="interface">
            Invoice
        </x-sidebar-link>



        <div class="pt-4 pb-2">
            <span x-show="sidebarOpen" class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-widest px-3">Master</span>
            <div x-show="!sidebarOpen" class="border-t border-slate-100 dark:border-slate-800 mx-3"></div>
        </div>

        <!-- Router Mikrotik -->
        <x-sidebar-link :href="route('router')" :active="request()->routeIs('router')" icon="server">
            Router
        </x-sidebar-link>

        <x-sidebar-link :href="route('assets')" :active="request()->routeIs('assets')" icon="box">
            Asset
        </x-sidebar-link>

        <x-sidebar-link :href="route('static-packages')" :active="request()->routeIs('static-packages')" icon="static">
            Static
        </x-sidebar-link>

        <x-sidebar-link :href="route('ppp-profiles')" :active="request()->routeIs('ppp-profiles')" icon="ethernet">
            PPPOE
        </x-sidebar-link>

        <x-sidebar-link :href="route('hotspot-profiles')" :active="request()->routeIs('hotspot-profiles')" icon="wifi">
            Hotspot
        </x-sidebar-link>



        <div class="pt-4 pb-2">
            <span x-show="sidebarOpen" class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-widest px-3">Pengaturan</span>
            <div x-show="!sidebarOpen" class="border-t border-slate-100 dark:border-slate-800 mx-3"></div>
        </div>

        <x-sidebar-link :href="route('app-settings')" :active="request()->routeIs('app-settings')" icon="cube">
            Aplikasi
        </x-sidebar-link>

        <x-sidebar-link :href="route('whatsapp')" :active="request()->routeIs('whatsapp')" icon="message">
            WhatsApp
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
            class="flex items-center gap-3 w-full px-3 py-2 text-slate-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/10 rounded-lg transition-all">
            <div class="shrink-0 w-6 h-6">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
            </div>
            <span x-show="sidebarOpen" class="font-medium whitespace-nowrap">Logout</span>
        </button>

    </div>
</aside>