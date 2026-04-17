<?php

namespace App\Livewire;

use App\Models\AppSetting;
use Livewire\Component;

class AppManager extends Component
{
    public $notif = 3;
    public $template = "Halo {name}, tagihan internet Anda sebesar {total_amount} telah terbit dan akan jatuh tempo pada {due_date}. Mohon segera lakukan pembayaran.";
    public $registration_template = "Selamat Datang {name}! Anda telah terdaftar sebagai pelanggan kami dengan paket {package_name}. Username: {username}, Password: {password}.";

    public function mount()
    {
        $setting = AppSetting::where('user_id', auth()->id())->first();
        if ($setting) {
            $this->notif = $setting->notif;
            if ($setting->template) {
                $this->template = $setting->template;
            }
            if ($setting->registration_template) {
                $this->registration_template = $setting->registration_template;
            }
        }
    }

    public function save()
    {
        $this->validate([
            'notif' => 'required|integer|min:0',
            'template' => 'nullable|string',
            'registration_template' => 'nullable|string',
        ]);

        try {
            AppSetting::updateOrCreate(
                ['user_id' => auth()->id()],
                [
                    'notif' => $this->notif,
                    'template' => $this->template,
                    'registration_template' => $this->registration_template,
                ]
            );

            $this->dispatch('toast', type: 'success', message: 'Pengaturan Aplikasi berhasil disimpan.');
            session()->flash('success', 'Pengaturan Aplikasi berhasil disimpan.');
        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', message: 'Gagal menyimpan pengaturan: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.app-manager')
            ->layout('layouts.app', ['header' => 'Pengaturan Aplikasi']);
    }
}
