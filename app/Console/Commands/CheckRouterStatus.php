<?php

namespace App\Console\Commands;

use App\Models\Router;
use App\Jobs\CheckRouterConnectionJob;
use Illuminate\Console\Command;

class CheckRouterStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'router:check-status {--loop}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check connectivity status for all configured routers';

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
                CheckRouterConnectionJob::dispatch($router);
            }


            if ($loop) {
                sleep(60);
            }
        } while ($loop);

        $this->info('Router status check jobs dispatched successfully.');
    }
}
