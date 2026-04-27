<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Router;
use App\Models\DhcpServer;
use App\Services\MikrotikService;
use Illuminate\Support\Facades\DB;

class SyncDhcpServers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mikrotik:sync-dhcp {--loop : Whether to run in a continuous loop every 5 seconds}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Sync DHCP Servers from Mikrotik routers to local database';

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
                    $this->info("Syncing DHCP Servers from: {$router->name}");
                    
                    $mikrotik = MikrotikService::getInstance($router);
                    $servers = $mikrotik->getDhcpServers();

                    DB::transaction(function () use ($router, $servers) {
                        // "Replace bulk" logic
                        DhcpServer::where('router_id', $router->id)->delete();

                        $data = [];
                        foreach ($servers as $server) {
                            $data[] = [
                                'user_id'      => $router->user_id,
                                'router_id'    => $router->id,
                                'name'         => $server['name'] ?? 'unknown',
                                'interface'    => $server['interface'] ?? 'unknown',
                                'address_pool' => $server['address-pool'] ?? 'none',
                                'created_at'   => now(),
                                'updated_at'   => now(),
                            ];
                        }

                        if (!empty($data)) {
                            DhcpServer::insert($data);
                        }
                    });

                    $this->info("Successfully synced " . count($servers) . " DHCP servers.");

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
