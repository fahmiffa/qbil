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

<div>
    <div class="mb-8">
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white">{{ __('Welcome back') }}</h2>
        <p class="text-gray-500 dark:text-slate-400 mt-2">{{ __('Please enter your details to sign in.') }}</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="login" class="space-y-5">
        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" class="font-medium" />
            <x-text-input wire:model="form.email" id="email" class="block mt-1.5 w-full !rounded-xl border-gray-200 dark:border-slate-700 focus:ring-blue-500 focus:border-blue-500" type="email" name="email" required autofocus placeholder="name@company.com" />
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <div class="flex items-center justify-between">
                <x-input-label for="password" :value="__('Password')" class="font-medium" />
            </div>

            <x-password-input wire:model="form.password" id="password" class="block mt-1.5 w-full !rounded-xl border-gray-200 dark:border-slate-700 focus:ring-blue-500 focus:border-blue-500"
                            name="password"
                            required placeholder="••••••••" />

            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between">
            <label for="remember" class="inline-flex items-center">
                <input wire:model="form.remember" id="remember" type="checkbox" class="rounded-lg border-gray-300 dark:border-slate-700 text-blue-600 shadow-sm focus:ring-blue-500 dark:bg-slate-800" name="remember">
                <span class="ms-2 text-sm text-gray-600 dark:text-slate-400">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div>
            <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 active:scale-[0.98]">
                {{ __('Sign in') }}
            </button>
        </div>
    </form>
</div>
