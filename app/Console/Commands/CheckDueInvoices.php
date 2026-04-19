<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Jobs\IsolateCustomerJob;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CheckDueInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:check-due';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for customers past due date and isolate them';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();
        
        $this->info("Checking for customers past due date ($today)...");

        $customers = Customer::where('status', 'active')
            ->whereNotNull('due_date')
            ->where('due_date', '<=', $today)
            ->get();

        if ($customers->isEmpty()) {
            $this->info("No past due customers found.");
            return;
        }

        $this->info("Found " . $customers->count() . " customers. Dispatching isolation jobs...");

        foreach ($customers as $customer) {
            IsolateCustomerJob::dispatch($customer);
            $this->line(" - Dispatched job for customer: {$customer->name} (Due: {$customer->due_date->format('Y-m-d')})");
        }

        $this->info("All jobs dispatched to queue.");
    }
}
