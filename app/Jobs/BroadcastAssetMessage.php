<?php

namespace App\Jobs;

use App\Models\Asset;
use App\Models\User;
use App\Services\WhatsappService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BroadcastAssetMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $asset;
    protected $message;
    protected $user;

    /**
     * Create a new job instance.
     */
    public function __construct(Asset $asset, string $message, User $user)
    {
        $this->asset = $asset;
        $this->message = $message;
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(WhatsappService $waService): void
    {
        $customers = $this->asset->customers;
        $senderPhone = $this->user->phone;

        if (!$senderPhone) {
            Log::error("BroadcastAssetMessage: User #{$this->user->id} has no phone number configured.");
            return;
        }

        foreach ($customers as $index => $customer) {
            if (!$customer->phone) {
                continue;
            }

            // Prepare custom message if needed (placeholders)
            $formattedMessage = $waService->formatMessage($this->message, [
                'name' => $customer->name,
                'id_pelanggan' => $customer->id_pelanggan,
                'address' => $customer->address,
            ]);

            $waService->sendMessage($senderPhone, $customer->phone, $formattedMessage);

            // Delay 10 seconds between messages, except for the last one
            if ($index < count($customers) - 1) {
                sleep(10);
            }
        }
    }
}
