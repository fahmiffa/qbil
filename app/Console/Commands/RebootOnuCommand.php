<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AppSetting;
use App\Models\Olt;
use App\Services\OltSseService;
use Carbon\Carbon;

class RebootOnuCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'olt:reboot-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Jalankan job reboot ONU sesuai jadwal di AppSetting';

    /**
     * Execute the console command.
     */
    public function handle(OltSseService $oltSseService)
    {
        $now = Carbon::now();
        $daysIndo = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu'
        ];
        $currentDayIndo = $daysIndo[$now->dayOfWeekIso];
        $currentTime = $now->format('H:i');

        $settings = AppSetting::whereNotNull('reboot_day')
            ->whereNotNull('reboot_time')
            ->get()
            ->filter(function ($setting) use ($currentDayIndo, $currentTime) {
                if (strtolower($setting->reboot_day) !== strtolower($currentDayIndo)) {
                    return false;
                }
                $time = Carbon::parse($setting->reboot_time)->format('H:i');
                return $time === $currentTime;
            });

        if ($settings->isEmpty()) {
            $this->info("Tidak ada jadwal reboot ONU untuk hari {$currentDayIndo} jam {$currentTime}.");
            return;
        }

        foreach ($settings as $setting) {
            $olts = Olt::where('user_id', $setting->user_id)->get();
            
            foreach ($olts as $olt) {
                $this->info("Memproses OLT: {$olt->name} (User ID: {$setting->user_id})");
                try {
                    $data = $oltSseService->fetchOnuData($olt->id);
                    if ($data['success'] && !empty($data['onuList'])) {
                        foreach ($data['onuList'] as $onu) {
                            $onuId = $onu[0] ?? '';
                            $onuName = $onu[1] ?? '';
                            
                            if ($onuId) {
                                $oltSseService->rebootOnu($olt->id, $onuId, $onuName);
                                $this->info("Dispatched reboot job for ONU: {$onuId} ({$onuName})");
                            }
                        }
                    } else {
                        $this->warn("Gagal mendapatkan data ONU atau kosong untuk OLT {$olt->name}.");
                    }
                } catch (\Exception $e) {
                    $this->error("Gagal memproses OLT {$olt->id}: " . $e->getMessage());
                }
            }
        }
    }
}
