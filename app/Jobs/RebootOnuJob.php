<?php

namespace App\Jobs;

use App\Models\Onu;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class RebootOnuJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $onuId;

    /**
     * Create a new job instance.
     */
    public function __construct($onuId)
    {
        $this->onuId = $onuId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $onu = Onu::find($this->onuId);
        if (!$onu) {
            Log::error("RebootOnuJob: ONU not found ID: {$this->onuId}");
            return;
        }


        $ip = $onu->olt_id;
        $username = env('OLT_USERNAME');
        $password = env('OLT_PASSWORD');
        $pon = $onu->pon; // e.g. epon 0/4
        $onu_id_val = $onu->onu_id; // e.g. 10

        try {
            $fp = @fsockopen($ip, 23, $errno, $errstr, 10);
            if (!$fp) {
                throw new \Exception("Could not connect to $ip: $errstr ($errno)");
            }
            stream_set_timeout($fp, 5);

            $readStream = function($socket, $sleep = 1) {
                sleep($sleep);
                $out = '';
                $info = stream_get_meta_data($socket);
                while (!feof($socket) && !$info['timed_out']) {
                    $char = fgetc($socket);
                    if ($char !== false) {
                        $out .= $char;
                    } else {
                        break;
                    }
                    $info = stream_get_meta_data($socket);
                }
                return $out;
            };

            // Login
            sleep(1);
            fputs($fp, "$username\r\n");
            sleep(1);
            fputs($fp, "$password\r\n");
            sleep(1);

            // Execute sequence
            fputs($fp, "enable\r\n");
            sleep(1);

            fputs($fp, "show onu info $pon all\r\n");
            sleep(2);

            fputs($fp, "configure terminal\r\n");
            sleep(1);

            fputs($fp, "interface $pon\r\n");
            sleep(1);

            fputs($fp, "onu $onu_id_val reboot\r\n");
            sleep(1);
            
            // Just in case it asks (Y/N)
            fputs($fp, "y\r\n");
            sleep(1);

            fputs($fp, "exit\r\n");
            fclose($fp);

            Log::info("RebootOnuJob: Reboot executed for ONU {$this->onuId} on OLT {$ip} ($pon onu $onu_id_val)");
        } catch (\Exception $e) {
            Log::error("RebootOnuJob: Telnet failed - " . $e->getMessage());
        }
    }
}
