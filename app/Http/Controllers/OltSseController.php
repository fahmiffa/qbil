<?php

namespace App\Http\Controllers;

use App\Services\OltSseService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OltSseController extends Controller
{
    /**
     * SSE refresh interval in seconds.
     */
    private const REFRESH_INTERVAL = 30;

    public function __construct(
        protected OltSseService $oltSseService
    ) {}

    /**
     * Stream ONU data from OLT via Server-Sent Events.
     * Refreshes every REFRESH_INTERVAL seconds.
     */
    public function stream(Request $request): StreamedResponse
    {
        $oltId = $request->query('olt_id');

        return response()->stream(function () use ($oltId) {
            set_time_limit(0);
            ini_set('max_execution_time', 0);

            // Max ~1 hour of streaming
            $maxIterations = (int) (3600 / self::REFRESH_INTERVAL);
            $iteration     = 0;

            while ($iteration < $maxIterations) {
                if (connection_aborted()) {
                    break;
                }

                $data = $this->oltSseService->fetchOnuData($oltId);

                echo "event: onu-update\n";
                echo "data: " . json_encode($data) . "\n\n";

                ob_flush();
                flush();

                $iteration++;

                // Wait REFRESH_INTERVAL but bail early if client disconnects.
                // Use 1s chunks so connection_aborted() is checked more frequently.
                if ($iteration < $maxIterations) {
                    for ($i = 0; $i < self::REFRESH_INTERVAL; $i++) {
                        if (connection_aborted()) break 2;
                        sleep(1);
                    }
                }
            }

            // Closing heartbeat
            echo "event: close\n";
            echo "data: {}\n\n";
            ob_flush();
            flush();

        }, 200, [
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache, no-store',
            'X-Accel-Buffering' => 'no',
            'Connection'        => 'keep-alive',
        ]);
    }

    /**
     * Dispatch Job to reboot ONU
     */
    public function rebootOnu(Request $request)
    {
        $request->validate([
            'olt_id'   => 'required|integer',
            'onu_id'   => 'required|string',
            'onu_name' => 'required|string',
        ]);

        $this->oltSseService->rebootOnu(
            $request->olt_id,
            $request->onu_id,
            $request->onu_name
        );

        return response()->json(['success' => true, 'message' => 'Perintah reboot telah dikirim dan sedang diproses di latar belakang.']);
    }
}
