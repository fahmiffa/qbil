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
            $routers = Router::all();
            
            foreach ($routers as $router) {
                // We use dispatchSync or dispatch based on requirements. 
                // For a loop, dispatch is better to not block the loop, but 
                // since we want quick updates, let's use dispatch to queue workers.
                CheckRouterConnectionJob::dispatch($router);
            }

            if ($loop) {
                $this->info('Router status check dispatched. Sleeping for 10 seconds...');
                sleep(10);
            }
        } while ($loop);

        $this->info('Router status check jobs dispatched successfully.');
    }
}
