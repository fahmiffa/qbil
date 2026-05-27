<?php

use App\Models\User;
use App\Models\Feature;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $address = '';

    // Feature selection
    public string $billing_mode = 'pra';
    public string $selected_service = 'pppoe';

    public function register(\App\Services\WhatsappService $whatsappService): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone'],
            'address' => ['required', 'string'],
            'billing_mode' => ['required_if:selected_service,pppoe,static', 'in:pra,pasca'],
            'selected_service' => ['required', 'in:pppoe,static,hotspot'],
        ], [
            'phone.unique' => 'Nomor WhatsApp ini sudah terdaftar.',
            'email.unique' => 'Email ini sudah terdaftar.',
        ]);

        // 1. WhatsApp Number Verification using Service
        $superAdmin = User::where('role', 0)->first();
        $adminPhone = $superAdmin ? $superAdmin->phone : '085640431181';

        if (!$whatsappService->checkNumber($adminPhone, $this->phone)) {
            $this->addError('phone', 'Nomor WhatsApp tidak valid atau tidak terdaftar.');
            return;
        }

        // Generate Random 5 digit password
        $rawPassword = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'password' => Hash::make($rawPassword),
            'role' => 1,
            'whatsapp_server_id' => 2,
            'status' => 'active',
        ]);

        // Attach Default Features
        $defaultFeatures = Feature::whereIn('parameter', ['whatsapp', 'mikrotik'])->pluck('id');
        $user->features()->attach($defaultFeatures);

        // Attach Billing Mode Feature
        if (in_array($this->selected_service, ['pppoe', 'static'])) {
            $billingFeature = Feature::where('parameter', $this->billing_mode)->first();
            if ($billingFeature) {
                $user->features()->attach($billingFeature->id);
            }
        }

        // Attach Service Feature
        $serviceFeature = Feature::where('parameter', $this->selected_service)->first();
        if ($serviceFeature) {
            $user->features()->attach($serviceFeature->id);
        }

        // 2. Queue Password Message via Generic Job
        $message = "Halo *{$this->name}*,\n\n"
            . "Selamat bergabung di *QBIL*.\n"
            . "Pendaftaran Anda berhasil dan akun Anda telah *AKTIF*.\n\n"
            . "Berikut adalah detail login Anda:\n"
            . "Email: *{$this->email}*\n"
            . "Password: *{$rawPassword}*\n\n"
            . "Silakan login di: " . config('app.url') . "/login\n\n"
            . "Terima kasih.";

        \App\Jobs\SendWhatsAppMessageJob::dispatch(
            $adminPhone,
            $this->phone,
            $message
        );

        // 3. Queue Password via Email Service
        \App\Jobs\SendRegistrationEmailJob::dispatch(
            $this->email,
            $this->name,
            $rawPassword
        );

        event(new Registered($user));

        session()->flash('status', 'Pendaftaran berhasil! Akun Anda sudah AKTIF. Detail login telah dikirim ke WhatsApp dan Email Anda.');
        $this->redirect(route('login'), navigate: true);
    }
}; ?>

<div class="min-h-screen bg-[#0a0a0b] text-white selection:bg-blue-500 flex flex-col items-center justify-center py-12 px-6">
    <!-- Header -->
    <div class="flex flex-col items-center mb-10">
        <div class="size-14 bg-blue-600 rounded-2xl flex items-center justify-center shadow-2xl shadow-blue-600/20 mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-white">
                <path d="M20.34 17.52a10 10 0 1 0-2.82 2.82" />
                <circle cx="19" cy="19" r="2" />
                <path d="m13.41 13.41 4.18 4.18" />
                <circle cx="12" cy="12" r="2" />
            </svg>
        </div>
        <h1 class="text-3xl font-black tracking-tight mb-1 uppercase italic">Buat Akun</h1>
        <p class="text-white/40 text-sm font-medium tracking-wide">Lengkapi data untuk memulai manajemen ISP Anda.</p>
    </div>

    <!-- Registration Card -->
    <div class="w-full max-w-2xl bg-slate-900 border border-white/5 rounded-3xl p-8 md:p-12 shadow-2xl shadow-blue-500/10 relative overflow-hidden">
        {{-- Decorative background --}}
        <div class="absolute -top-10 -right-10 w-32 h-32 bg-blue-500/5 rounded-full blur-2xl"></div>

        <form wire:submit="register" class="relative z-10 space-y-8">

            <!-- Section 1: Profil ISP -->
            <div class="space-y-6">
                <h3 class="text-xs font-black text-white/20 uppercase tracking-[0.3em] flex items-center gap-3">
                    <span class="size-1.5 bg-blue-500 rounded-full"></span>
                    Profil Instansi
                </h3>

                <div class="grid md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <x-input-label for="name" class="text-[11px] font-bold text-white/50 ml-1">Nama</x-input-label>
                        <input wire:model="name" id="name" type="text" class="w-full bg-slate-800/50 border-white/10 rounded-2xl px-6 py-4 focus:border-blue-600 focus:bg-slate-800 transition-all outline-none font-bold placeholder-white/20 text-white" placeholder="ISP Merdeka" required autofocus>
                        <x-input-error :messages="$errors->get('name')" />
                    </div>
                    <div class="space-y-2">
                        <x-input-label for="phone" class="text-[11px] font-bold text-white/50 ml-1">WhatsApp</x-input-label>
                        <input wire:model="phone" id="phone" type="text" class="w-full bg-slate-800/50 border-white/10 rounded-2xl px-6 py-4 focus:border-blue-600 focus:bg-slate-800 transition-all outline-none font-bold placeholder-white/20 text-white" placeholder="08123xxx" required>
                        <x-input-error :messages="$errors->get('phone')" />
                    </div>
                </div>

                <div class="space-y-2">
                    <x-input-label for="email" class="text-[11px] font-bold text-white/50 ml-1">Email</x-input-label>
                    <input wire:model="email" id="email" type="email" class="w-full bg-slate-800/50 border-white/10 rounded-2xl px-6 py-4 focus:border-blue-600 focus:bg-slate-800 transition-all outline-none font-bold placeholder-white/20 text-blue-400" placeholder="admin@domain.com" required>
                    <x-input-error :messages="$errors->get('email')" />
                </div>

                <div class="space-y-2">
                    <x-input-label for="address" class="text-[11px] font-bold text-white/50 ml-1">Alamat</x-input-label>
                    <textarea wire:model="address" id="address" class="w-full bg-slate-800/50 border-white/10 rounded-2xl px-6 py-4 focus:border-blue-600 focus:bg-slate-800 transition-all outline-none font-bold placeholder-white/20 text-white min-h-[100px]" placeholder="Masukkan alamat lengkap..." required></textarea>
                    <x-input-error :messages="$errors->get('address')" />
                </div>
            </div>

            <!-- Section 2: Konfigurasi Layanan -->
            <div class="space-y-6 pt-4 border-t border-white/5">
                <h3 class="text-xs font-black text-white/20 uppercase tracking-[0.3em] flex items-center gap-3">
                    <span class="size-1.5 bg-blue-500 rounded-full"></span>
                    Konfigurasi Layanan
                </h3>

                <div class="grid md:grid-cols-2 gap-6 items-end">
                    <div class="space-y-4">
                        <label class="text-[11px] font-bold text-white/50 ml-1">Jenis Layanan</label>
                        <select wire:model.live="selected_service" class="w-full bg-slate-800 border-white/5 rounded-2xl px-6 py-4 focus:border-blue-600 focus:bg-slate-700 transition-all outline-none font-bold text-sm text-white shadow-xl">
                            <option value="pppoe" class="bg-slate-800 text-white">PPPoE</option>
                            <option value="static" class="bg-slate-800 text-white">Static IP</option>
                            <option value="hotspot" class="bg-slate-800 text-white">Hotspot</option>
                        </select>
                    </div>

                    @if(in_array($selected_service, ['pppoe', 'static']))
                    <div class="space-y-4">
                        <label class="text-[11px] font-bold text-white/50 ml-1">Metode Penagihan</label>
                        <div class="flex gap-2">
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" wire:model="billing_mode" value="pra" class="sr-only peer">
                                <div class="px-4 py-3 text-center rounded-xl bg-white/5 border border-white/10 text-[11px] font-black uppercase tracking-widest transition-all peer-checked:bg-blue-600 peer-checked:border-blue-600">Prabayar</div>
                            </label>
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" wire:model="billing_mode" value="pasca" class="sr-only peer">
                                <div class="px-4 py-3 text-center rounded-xl bg-white/5 border border-white/10 text-[11px] font-black uppercase tracking-widest transition-all peer-checked:bg-blue-600 peer-checked:border-blue-600">Pascabayar</div>
                            </label>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Footer / Submit Group -->
            <div class="pt-8">
                <button type="submit" wire:loading.attr="disabled" wire:target="register" class="w-full bg-blue-600 hover:bg-blue-700 disabled:opacity-70 disabled:cursor-not-allowed text-white font-black py-5 rounded-2xl transition-all shadow-2xl shadow-blue-600/30 uppercase tracking-[0.2em] active:scale-95 text-sm flex items-center justify-center">
                    <span wire:loading.remove wire:target="register">
                        Daftar Akun
                    </span>
                    <div wire:loading.flex wire:target="register" class="items-center justify-center gap-3">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Memproses...</span>
                    </div>
                </button>
                <div class="mt-8 text-center flex flex-col items-center gap-4">
                    <p class="text-xs font-bold text-white/30 uppercase tracking-widest">
                        Sudah memiliki akses? <a href="{{ route('login') }}" class="text-blue-500 hover:text-blue-400 transition-colors" wire:navigate>Back to Login</a>
                    </p>
                </div>
            </div>
        </form>
    </div>

    <!-- Footer -->
    <footer class="mt-12 text-center">
        <p class="text-[10px] font-black text-white/10 uppercase tracking-[0.5em]">&copy; {{ date('Y') }} QBILL INFRASTRUCTURE</p>
    </footer>
</div>