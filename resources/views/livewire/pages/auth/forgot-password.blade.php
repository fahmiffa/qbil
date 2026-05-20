<?php

use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Models\User;
use App\Services\WhatsappService;

new #[Layout('layouts.guest')] class extends Component
{
    public string $phone = '';

    /**
     * Send a password reset link to the provided phone number.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'phone' => ['required', 'string'],
        ]);

        $throttleKey = 'password-reset-' . request()->ip() . '-' . $this->phone;

        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->addError('phone', "Terlalu banyak percobaan. Silakan coba lagi dalam {$seconds} detik.");
            return;
        }

        RateLimiter::hit($throttleKey, 900); // 15 menit

        $user = User::where('phone', $this->phone)->first();

        if (!$user) {
            $this->addError('phone', 'Nomor telepon tidak ditemukan.');
            return;
        }

        $token = Password::broker()->createToken($user);

        $superadmin = User::where('role', 0)->first();
        if (!$superadmin || empty($superadmin->phone)) {
            $this->addError('phone', 'Sistem tidak dapat mengirim pesan, kontak admin.');
            return;
        }

        $senderPhone = $superadmin->phone;
        $resetUrl = route('password.reset', ['token' => $token, 'phone' => $this->phone]);

        $message = "Halo {$user->name},\n\nSilakan klik link berikut untuk mereset password Anda:\n{$resetUrl}\n\nLink ini akan kadaluarsa dalam beberapa saat dan hanya dapat digunakan sekali. Jika Anda tidak meminta reset password, abaikan pesan ini.";

        $waService = app(WhatsappService::class);
        $sent = $waService->sendMessage($senderPhone, $user->phone, $message);

        if (!$sent) {
            $this->addError('phone', 'Gagal mengirim pesan WhatsApp. Silakan coba lagi nanti.');
            return;
        }

        $this->reset('phone');

        session()->flash('status', __('Tautan reset password telah dikirim ke WhatsApp Anda.'));
    }
}; ?>

<div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-black px-6">
    <div class="w-full sm:max-w-md bg-slate-900 shadow-2xl shadow-blue-500/10 rounded-3xl p-8 md:p-10 border border-white/5 relative overflow-hidden">
        {{-- Decorative background --}}
        <div class="absolute -top-10 -right-10 w-32 h-32 bg-blue-500/5 rounded-full blur-2xl"></div>

        <div class="mb-10 text-center">
            <div class="inline-flex items-center justify-center size-16 bg-gradient-to-br from-blue-400 to-blue-600 rounded-2xl shadow-lg shadow-blue-600/20 mb-6">
                <!-- Lock Icon -->
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="size-8 text-white">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
            </div>
            <h2 class="text-3xl font-black text-white tracking-tight leading-none mb-3">{{ __('Reset Password') }}</h2>
            <p class="text-white/40 font-medium text-sm leading-relaxed">{{ __('Minta link reset password yang dikirim ke WhatsApp Anda.') }}</p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form wire:submit="sendPasswordResetLink" class="space-y-6">
            <!-- Phone Address -->
            <div>
                <x-input-label for="phone" :value="__('Nomor WhatsApp')" class="font-bold text-white/70 ml-1 mb-1.5" />
                <x-text-input wire:model="phone" id="phone" class="block w-full !rounded-2xl border-white/10 bg-white/5 text-white focus:ring-blue-600 focus:border-blue-600 px-4 py-3 placeholder-white/20" type="text" name="phone" required autofocus placeholder="08xxxxxxxxxx" />
                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full flex justify-center items-center gap-2 py-4 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl font-black text-sm uppercase tracking-widest shadow-xl shadow-blue-600/20 transition-all duration-200 active:scale-[0.97]">
                    {{ __('Kirim Link Reset') }}
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
            </div>

            <div class="text-center mt-6">
                <a class="text-sm font-bold text-white/50 hover:text-white transition-colors focus:outline-none focus:underline" href="{{ route('login') }}" wire:navigate>
                    {{ __('Kembali ke Login') }}
                </a>
            </div>
        </form>
    </div>
</div>