<?php

namespace App\Livewire;

use App\Models\AppSetting;
use Livewire\Component;

class AppManager extends Component
{
    public $template = "Halo {name}, tagihan internet Anda sebesar {total_amount} telah terbit dan akan jatuh tempo pada {due_date}. Mohon segera lakukan pembayaran.";
    public $registration_template = "Selamat Datang {name}! Anda telah terdaftar sebagai pelanggan kami dengan paket {package_name}. Username: {username}, Password: {password}.";
    public $payment_instruction = "";
    public $qr = "";
    
    public $reminder_1_days = -1;
    public $reminder_1_time = '08:00';
    public $reminder_2_days = 0;
    public $reminder_2_time = '08:00';

    public $invoice_gen_days = -1;
    public $invoice_gen_time = '00:00';
    public $isolate_days = 0;
    public $isolate_time = '00:05';

    protected $rules = [
        'template' => 'nullable|string',
        'registration_template' => 'nullable|string',
        'payment_instruction' => 'nullable|string',
        'qr' => 'nullable|string',
        'reminder_1_days' => 'required|integer',
        'reminder_1_time' => 'required',
        'reminder_2_days' => 'required|integer',
        'reminder_2_time' => 'required',
        'invoice_gen_days' => 'required|integer|min:-1|max:1',
        'invoice_gen_time' => 'required',
        'isolate_days' => 'required|integer|min:-1|max:1',
        'isolate_time' => 'required',
    ];

    protected $messages = [
        'invoice_gen_days.required' => 'Rentang hari harus diisi.',
        'invoice_gen_days.integer' => 'Rentang hari harus berupa angka.',
        'invoice_gen_days.min' => 'Rentang hari minimal -1 (H-1).',
        'invoice_gen_days.max' => 'Rentang hari maksimal 1 (H+1).',
        'invoice_gen_time.required' => 'Waktu eksekusi harus diisi.',
        'isolate_days.required' => 'Rentang hari isolir harus diisi.',
        'isolate_days.integer' => 'Rentang hari isolir harus berupa angka.',
        'isolate_days.min' => 'Rentang hari isolir minimal -1 (H-1).',
        'isolate_days.max' => 'Rentang hari isolir maksimal 1 (H+1).',
        'isolate_time.required' => 'Waktu eksekusi isolir harus diisi.',
        'reminder_1_days.required' => 'Rentang hari notifikasi 1 harus diisi.',
        'reminder_1_time.required' => 'Waktu pengiriman notifikasi 1 harus diisi.',
        'reminder_2_days.required' => 'Rentang hari notifikasi 2 harus diisi.',
        'reminder_2_time.required' => 'Waktu pengiriman notifikasi 2 harus diisi.',
    ];

    public function mount()
    {
        $setting = AppSetting::where('user_id', auth()->id())->first();
        if ($setting) {
            $this->template = $setting->template ?? $this->template;
            $this->registration_template = $setting->registration_template ?? $this->registration_template;
            $this->payment_instruction = $setting->payment_instruction ?? "";
            $this->qr = $setting->qr ?? "";
            $this->reminder_1_days = $setting->reminder_1_days;
            $this->reminder_1_time = $setting->reminder_1_time;
            $this->reminder_2_days = $setting->reminder_2_days;
            $this->reminder_2_time = $setting->reminder_2_time;
            $this->invoice_gen_days = $setting->invoice_gen_days;
            $this->invoice_gen_time = $setting->invoice_gen_time;
            $this->isolate_days = $setting->isolate_days;
            $this->isolate_time = $setting->isolate_time;
        }
    }

    public function save()
    {
        $this->validate();

        try {
            AppSetting::updateOrCreate(
                ['user_id' => auth()->id()],
                [
                    'template' => $this->template,
                    'registration_template' => $this->registration_template,
                    'payment_instruction' => $this->payment_instruction,
                    'qr' => $this->qr,
                    'reminder_1_days' => $this->reminder_1_days,
                    'reminder_1_time' => $this->reminder_1_time,
                    'reminder_2_days' => $this->reminder_2_days,
                    'reminder_2_time' => $this->reminder_2_time,
                    'invoice_gen_days' => $this->invoice_gen_days,
                    'invoice_gen_time' => $this->invoice_gen_time,
                    'isolate_days' => $this->isolate_days,
                    'isolate_time' => $this->isolate_time,
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
