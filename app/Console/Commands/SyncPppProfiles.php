<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Router;
use App\Models\PppProfile;
use App\Services\MikrotikService;
use Illuminate\Support\Facades\DB;

class SyncPppProfiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mikrotik:sync-ppp-profiles {--loop : Whether to run in a continuous loop every 5 seconds}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Sync PPP Profiles from Mikrotik routers to local database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $loop = $this->option('loop');

        do {
            $routers = Router::with('user')->get();

            foreach ($routers as $router) {
                if ($router->user && !$router->user->hasFeature('mikrotik')) {
                    continue;
                }

                try {
                    $this->info("Syncing PPP Profiles from: {$router->name}");
                    
                    $mikrotik = new MikrotikService($router);
                    $profiles = $mikrotik->getPppProfiles();

                    DB::transaction(function () use ($router, $profiles) {
                        // "Replace bulk" logic
                        PppProfile::where('router_id', $router->id)->delete();

                        $data = [];
                        foreach ($profiles as $profile) {
                            $data[] = [
                                'user_id'        => $router->user_id,
                                'router_id'      => $router->id,
                                'name'           => $profile['name'] ?? 'unknown',
                                'local_address'  => $profile['local-address'] ?? null,
                                'remote_address' => $profile['remote-address'] ?? null,
                                'rate_limit'     => $profile['rate-limit'] ?? null,
                                'only_one'       => $profile['only-one'] ?? 'yes',
                                'dns_server'     => $profile['dns-server'] ?? null,
                                'created_at'     => now(),
                                'updated_at'     => now(),
                            ];
                        }

                        if (!empty($data)) {
                            PppProfile::insert($data);
                        }
                    });

                    $this->info("Successfully synced " . count($profiles) . " PPP profiles.");

                } catch (\Exception $e) {
                    $this->error("Error syncing router {$router->name}: " . $e->getMessage());
                }
            }

            if ($loop) {
                sleep(5);
            }

        } while ($loop);

        return 0;
    }
}
