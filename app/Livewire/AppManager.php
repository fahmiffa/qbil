<?php

namespace App\Livewire;

use App\Models\AppSetting;
use App\Models\ActivityLog;
use Livewire\Component;

class AppManager extends Component
{
    public $template = "@{user_name}\nAssalamualaikum.wr.wb\nSemoga Kita selalu diberikan Keberkahan dan Nikmat Rejeki, Kami informasikan data dibawah ini belum melakukan pembayaran\nID Pelanggan : *{id_pelanggan}*\nNama : *{name}*\nAlamat : *{address}* \nNama Paket : *{package_name}*\nPeriode : *{period}*\nJatuh Tempo : *{due_date}*\nStatus : *Belum Lunas*\nHarga : *{amount}*\nTotal Yang Harus Dibayar : *{amount}*\n*Pembayaran Melalui*\nKlik link Dibawah ini: \n*{public_url}*\n*TRANSFER MANUAL MELALUI REKENING DIBAWAH INI:*\nKode Unik : *{unique_code}*\nTotal yang harus di transfer :*{total_amount}*\n_(Pastikan jumlah yang di transfer sesuai dengan total yang harus di transfer.)_\nMOHON KIRIM BUKTI PEMBAYARAN JIKA SDH MELAKUKAN TRANSAKSI VIA TRANSFER\nTrimakasih\nMohon abaikan pesan ini jika sudah melakukan pembayaran.\n\n*@{user_name}*\n\nTerima Kasih,\nAdmin @{user_name}";
    public $registration_template = "@{user_name}\nTerimakasih telah bergabung dengan Layanan Internet *{user_name}*\nini adalah pesan otomatis dari sistem\n\nAnda baru saja terdaftar ke sistem e-billing kami\n\nNama : {name}\nNama Paket : {package_name}\nHarga : Rp. {amount}\nPeriode : {period}\nJatuh Tempo : {due_date}\n     \n*@{user_name}*\n\nTerima Kasih,\nStaff @{user_name}";
    public $payment_template = "@{user_name}\n*{user_name}*\nAlhamdulillah Semoga Allah SWT. Selalu memberikan Keberkahan,Kesehatan dan Kemudahan Rizki Kepada Kita semua,Amiin\n\nBerikut terlampir kwitansi pembayaran yang baru saja dilakukan silahkan di download\n\nNama : {name}\nAlamat : {address}\nJumlah Bayar : Rp. {amount}\nUntuk Pembayaran : {package_name}\nBulan : {period}\nJatuh Tempo : {due_date}\nDownload Bukti Pembayaran: {public_url}\nKami Ucapkan Terimakasih\n\n*@{user_name}*\n\nTerima Kasih,\nAdmin @{user_name}";
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

    public function rules()
    {
        return [
            'template' => 'nullable|string',
            'registration_template' => 'nullable|string',
            'payment_template' => 'nullable|string',
            'payment_instruction' => 'nullable|string',
            'qr' => 'nullable|string',
            'reminder_1_days' => 'required|integer|min:' . $this->invoice_gen_days . '|max:10',
            'reminder_1_time' => [
                'required',
                function ($attribute, $value, $fail) {
                    if ($this->reminder_1_days == $this->invoice_gen_days) {
                        if ($value <= $this->invoice_gen_time) {
                            $fail("Waktu pengiriman harus setelah waktu generate invoice (Minimal: " . date('H:i', strtotime($this->invoice_gen_time . ' +1 minute')) . ").");
                        }
                    }
                }
            ],
            'reminder_2_days' => 'required|integer|min:' . $this->reminder_1_days . '|max:10',
            'reminder_2_time' => [
                'required',
                function ($attribute, $value, $fail) {
                    // Cek terhadap waktu generate invoice (jika harinya sama)
                    if ($this->reminder_2_days == $this->invoice_gen_days) {
                        if ($value <= $this->invoice_gen_time) {
                            $fail("Waktu pengiriman harus setelah waktu generate invoice (Minimal: " . date('H:i', strtotime($this->invoice_gen_time . ' +1 minute')) . ").");
                            return;
                        }
                    }
                    
                    // Cek terhadap waktu notifikasi pertama (jika harinya sama)
                    if ($this->reminder_2_days == $this->reminder_1_days) {
                        if ($value <= $this->reminder_1_time) {
                            $fail("Notifikasi kedua harus dikirim setelah notifikasi pertama (Minimal: " . date('H:i', strtotime($this->reminder_1_time . ' +1 minute')) . ").");
                        }
                    }
                }
            ],
            'invoice_gen_days' => 'required|integer|min:-10|max:1',
            'invoice_gen_time' => 'required',
            'isolate_days' => 'required|integer|min:-1|max:1',
            'isolate_time' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'invoice_gen_days.required' => 'Rentang hari harus diisi.',
            'invoice_gen_days.integer' => 'Rentang hari harus berupa angka.',
            'invoice_gen_days.min' => 'Rentang hari minimal -10 (H-10).',
            'invoice_gen_days.max' => 'Rentang hari maksimal 1 (H+1).',
            'invoice_gen_time.required' => 'Waktu eksekusi harus diisi.',
            'isolate_days.required' => 'Rentang hari isolir harus diisi.',
            'isolate_days.integer' => 'Rentang hari isolir harus berupa angka.',
            'isolate_days.min' => 'Rentang hari isolir minimal -1 (H-1).',
            'isolate_days.max' => 'Rentang hari isolir maksimal 1 (H+1).',
            'isolate_time.required' => 'Waktu eksekusi isolir harus diisi.',
            'reminder_1_days.required' => 'Rentang hari notifikasi 1 harus diisi.',
            'reminder_1_days.min' => 'Pengingat 1 tidak boleh sebelum invoice dibuat (Min: ' . $this->invoice_gen_days . ').',
            'reminder_1_days.max' => 'Rentang hari maksimal 10 (H+10).',
            'reminder_1_time.required' => 'Waktu pengiriman notifikasi 1 harus diisi.',
            'reminder_2_days.required' => 'Rentang hari notifikasi 2 harus diisi.',
            'reminder_2_days.min' => 'Hari notifikasi 2 tidak boleh sebelum notifikasi 1 (Min: ' . $this->reminder_1_days . ').',
            'reminder_2_days.max' => 'Rentang hari maksimal 10 (H+10).',
            'reminder_2_time.required' => 'Waktu pengiriman notifikasi 2 harus diisi.',
        ];
    }

    public function mount()
    {
        $setting = AppSetting::where('user_id', auth()->id())->first();
        if ($setting) {
            $this->template = $setting->template ?? $this->template;
            $this->registration_template = $setting->registration_template ?? $this->registration_template;
            $this->payment_template = $setting->payment_template ?? $this->payment_template;
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
                    'payment_template' => $this->payment_template,
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

            // Log Activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'title' => 'UPDATE SETTING',
                'message' => "Memperbarui pengaturan aplikasi (Template & Billing)",
                'type' => 'app_crud'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', message: 'Gagal menyimpan pengaturan: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.app-manager')
            ->layout('layouts.app', ['header' => 'Pengaturan']);
    }
}
