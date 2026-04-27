<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Router;
use App\Models\IpPool;
use App\Services\MikrotikService;

class SyncIpPools extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mikrotik:sync-pools {--loop : Whether to run in a continuous loop every 5 seconds}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Sync IP Pools from Mikrotik routers to local database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $loop = $this->option('loop');

        do {
            $routers = Router::with('user')->get();

            if ($routers->isEmpty()) {
                $this->warn('No routers found to sync.');
            }

            foreach ($routers as $router) {
                if ($router->user && !$router->user->hasFeature('mikrotik')) {
                    continue;
                }

                try {
                    $this->info("Syncing IP Pools from: {$router->name} ({$router->host})");
                    
                    $service = MikrotikService::getInstance($router);
                    $pools = $service->getIpPools();

                    // Using a transaction to ensure data integrity during replace
                    \DB::transaction(function () use ($router, $pools) {
                        // Remove existing pools for this router
                        IpPool::where('router_id', $router->id)->delete();

                        $data = [];
                        foreach ($pools as $pool) {
                            $data[] = [
                                'user_id'   => $router->user_id,
                                'router_id' => $router->id,
                                'name'      => $pool['name'],
                                'address'   => $pool['ranges'],
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }

                        if (!empty($data)) {
                            IpPool::insert($data);
                        }
                    });

                    // $this->info("Successfully synced " . count($pools) . " pools.");

                } catch (\Exception $e) {
                    $this->error("Error syncing router {$router->name}: " . $e->getMessage());
                }
            }

            if ($loop) {
                // $this->info("Sleeping for 5 seconds...");
                sleep(60);
            }

        } while ($loop);

        return 0;
    }
}
