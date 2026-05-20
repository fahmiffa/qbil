<?php

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;
use App\Models\User;

new #[Layout('layouts.guest')] class extends Component
{
    #[Locked]
    public string $token = '';
    public string $phone = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Mount the component.
     */
    public function mount(string $token): void
    {
        $this->token = $token;

        $this->phone = request()->string('phone');
    }

    /**
     * Reset the password for the given user.
     */
    public function resetPassword(): void
    {
        $this->validate([
            'token' => ['required'],
            'phone' => ['required', 'string'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::where('phone', $this->phone)->first();

        if (!$user) {
            $this->addError('phone', 'Nomor telepon tidak valid.');
            return;
        }

        // We will attempt to reset the user's password using the mapped email
        $credentials = [
            'email' => $user->email,
            'password' => $this->password,
            'password_confirmation' => $this->password_confirmation,
            'token' => $this->token
        ];

        $status = Password::reset(
            $credentials,
            function ($user) {
                $user->forceFill([
                    'password' => Hash::make($this->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status != Password::PASSWORD_RESET) {
            // Display standard Laravel reset error but attached to phone field
            $this->addError('phone', __($status));
            return;
        }

        Session::flash('status', __($status));

        $this->redirectRoute('login', navigate: true);
    }
}; ?>

<div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-black px-6">
    <div class="w-full sm:max-w-md bg-slate-900 shadow-2xl shadow-blue-500/10 rounded-3xl p-8 md:p-10 border border-white/5 relative overflow-hidden">
        {{-- Decorative background --}}
        <div class="absolute -top-10 -right-10 w-32 h-32 bg-blue-500/5 rounded-full blur-2xl"></div>

        <div class="mb-10 text-center">
            <div class="inline-flex items-center justify-center size-16 bg-gradient-to-br from-blue-400 to-blue-600 rounded-2xl shadow-lg shadow-blue-600/20 mb-6">
                <!-- Key Icon -->
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="size-8 text-white">
                    <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"></path>
                </svg>
            </div>
            <h2 class="text-3xl font-black text-white tracking-tight leading-none mb-3">{{ __('Buat Password Baru') }}</h2>
            <p class="text-white/40 font-medium text-sm">{{ __('Silakan masukkan kredensial password baru Anda.') }}</p>
        </div>

        <form wire:submit="resetPassword" class="space-y-6">
            <!-- Phone Number -->
            <div>
                <x-input-label for="phone" :value="__('Nomor WhatsApp')" class="font-bold text-white/70 ml-1 mb-1.5" />
                <x-text-input wire:model="phone" id="phone" class="block w-full !rounded-2xl border-white/10 bg-white/5 text-white focus:ring-blue-600 focus:border-blue-600 px-4 py-3 opacity-70 cursor-not-allowed placeholder-white/20" type="text" name="phone" required readonly />
                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
            </div>

            <!-- Password -->
            <div>
                <x-input-label for="password" :value="__('Password Baru')" class="font-bold text-white/70 ml-1 mb-1.5" />
                <x-password-input wire:model="password" id="password" class="block w-full !rounded-2xl border-white/10 bg-white/5 text-white focus:ring-blue-600 focus:border-blue-600 px-4 py-3 placeholder-white/20" type="password" name="password" required autofocus autocomplete="new-password" placeholder="••••••••" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Confirm Password -->
            <div>
                <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" class="font-bold text-white/70 ml-1 mb-1.5" />
                <x-password-input wire:model="password_confirmation" id="password_confirmation" class="block w-full !rounded-2xl border-white/10 bg-white/5 text-white focus:ring-blue-600 focus:border-blue-600 px-4 py-3 placeholder-white/20" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="••••••••" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full flex justify-center items-center gap-2 py-4 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl font-black text-sm uppercase tracking-widest shadow-xl shadow-blue-600/20 transition-all duration-200 active:scale-[0.97]">
                    {{ __('Simpan Password') }}
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
            </div>
        </form>
    </div>
</div>