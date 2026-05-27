<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-black px-6">
    <div class="w-full sm:max-w-md bg-slate-900 shadow-2xl shadow-blue-500/10 rounded-3xl p-8 md:p-10 border border-white/5 relative overflow-hidden">
        {{-- Decorative background --}}
        <div class="absolute -top-10 -right-10 w-32 h-32 bg-blue-500/5 rounded-full blur-2xl"></div>

        <div class="mb-10 text-center">
            <div class="inline-flex items-center justify-center size-16 bg-gradient-to-br from-blue-400 to-blue-600 rounded-2xl shadow-lg shadow-blue-600/20 mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="size-8 text-white">
                    <path d="M20.34 17.52a10 10 0 1 0-2.82 2.82" />
                    <circle cx="19" cy="19" r="2" />
                    <path d="m13.41 13.41 4.18 4.18" />
                    <circle cx="12" cy="12" r="2" />
                </svg>
            </div>
            <h2 class="text-3xl font-black text-white tracking-tight leading-none mb-3">{{ __('Selamat Datang') }}</h2>
            <p class="text-white/40 font-medium">{{ __('Sign in to manage your billing system.') }}</p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form wire:submit="login" class="space-y-6">
            <!-- Email Address -->
            <div>
                <x-input-label for="email" :value="__('Email')" class="font-bold text-white/70 ml-1 mb-1.5" />
                <x-text-input wire:model="form.email" id="email" class="block w-full !rounded-2xl border-white/10 bg-white/5 text-white focus:ring-blue-600 focus:border-blue-600 px-4 py-3 placeholder-white/20" type="email" name="email" required autofocus placeholder="admin@isp.com" />
                <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
            </div>

            <!-- Password -->
            <div>
                <x-input-label for="password" :value="__('Password')" class="font-bold text-white/70 ml-1 mb-1.5" />
                <x-password-input wire:model="form.password" id="password" class="block w-full !rounded-2xl border-white/10 bg-white/5 text-white focus:ring-blue-600 focus:border-blue-600 px-4 py-3 placeholder-white/20"
                    name="password"
                    required placeholder="••••••••" />

                <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between px-1">
                <label for="remember" class="inline-flex items-center cursor-pointer">
                    <input wire:model="form.remember" id="remember" type="checkbox" class="rounded-lg border-white/10 bg-white/5 text-blue-600 shadow-sm focus:ring-blue-600" name="remember">
                    <span class="ms-2 text-sm font-bold text-white/50">{{ __('Stay signed in') }}</span>
                </label>

                @if (Route::has('password.request'))
                <a class="text-sm font-bold text-white/50 hover:text-white transition-colors focus:outline-none focus:underline" href="{{ route('password.request') }}" wire:navigate>
                    {{ __('Forgot password?') }}
                </a>
                @endif
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full flex justify-center items-center gap-2 py-4 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl font-black text-sm uppercase tracking-widest shadow-xl shadow-blue-600/20 transition-all duration-200 active:scale-[0.97]">
                    {{ __('Sign in') }}
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
            </div>

            <div class="text-center pt-4 border-t border-white/5 mt-6">
                <p class="text-sm font-bold text-white/40">
                    {{ __('Belum punya akun?') }}
                    <a href="{{ route('register') }}" class="text-blue-500 hover:text-blue-400 transition-colors ml-1" wire:navigate>
                        {{ __('Daftar Sekarang') }}
                    </a>
                </p>
            </div>
        </form>
    </div>

    <div class="mt-8 text-center">
        <p class="text-xs font-black text-white/20 uppercase tracking-[0.2em]">
            &copy; {{ date('Y') }} {{ config('app.name') }} Systems
        </p>
    </div>
</div>