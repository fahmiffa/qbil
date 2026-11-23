<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    /**
     * Refresh the component.
     */
    #[\Livewire\Attributes\On('profile-updated')]
    public function refresh(): void
    {
        // Component will re-render and fetch fresh user data
    }

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<div class="flex items-center space-x-2 sm:space-x-4">
    <!-- PWA Install Button (Desktop & Mobile) -->
    <div x-data="{
            canInstall: false,
            isInstalled: window.PWA?.isInstalled || false,
            init() {
                if (this.isInstalled) return;
                window.addEventListener('pwa-installable', () => { this.canInstall = true; });
                window.addEventListener('pwa-installed', () => { this.canInstall = false; this.isInstalled = true; });
                if (window.PWA?.isIos && !this.isInstalled) {
                    this.canInstall = true;
                }
            }
         }"
         x-show="canInstall && !isInstalled"
         style="display: none;">
        <button @click="window.triggerPwaInstall()"
                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:hover:bg-blue-900/50 dark:text-blue-300 rounded-lg text-xs font-bold transition-all shadow-sm"
                title="Install Aplikasi QBIL">
            <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            <span class="hidden sm:inline">Install App</span>
        </button>
    </div>

    <!-- Notifications -->
    <livewire:layout.notification-bell />

    <!-- Dark Mode Toggle -->
    <button @click="darkMode = !darkMode" 
            class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-slate-400 dark:hover:bg-slate-800 transition-colors"
            title="Toggle Dark Mode">
        <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
        </svg>
        <svg x-show="darkMode" class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 18v1m9-9h1m-18 0h1m3.121-6.879l.707.707m12.728 12.728l.707.707M6.343 17.657l-.707.707M17.657 6.343l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>
    </button>

    <x-dropdown align="right" width="48">
        <x-slot name="trigger">
            <button class="inline-flex items-center gap-2 px-3 py-2 border border-gray-100 dark:border-slate-700 text-sm leading-4 font-medium rounded-lg text-gray-600 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-gray-50 dark:hover:bg-slate-700 hover:border-gray-300 dark:hover:border-slate-600 focus:outline-none transition ease-in-out duration-150">
                @if(auth()->user()->photo)
                    <img src="{{ Storage::url(auth()->user()->photo) }}" class="w-6 h-6 rounded-full object-cover ring-1 ring-gray-200 dark:ring-slate-700">
                @else
                    <div class="w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center text-blue-600 dark:text-blue-400 text-xs font-bold">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                @endif
                <div x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name" class="max-w-[100px] truncate"></div>
                <svg class="fill-current h-4 w-4 text-gray-400 dark:text-slate-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
        </x-slot>

        <x-slot name="content">
            <x-dropdown-link :href="route('profile')" wire:navigate>
                {{ __('Profile') }}
            </x-dropdown-link>

            <!-- Authentication -->
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
                class="w-full text-start">
                <x-dropdown-link>
                    {{ __('Log Out') }}
                </x-dropdown-link>
            </button>

        </x-slot>
    </x-dropdown>
</div>
