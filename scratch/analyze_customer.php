<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AppSetting;
use App\Models\Customer;
use App\Models\Invoice;
use Carbon\Carbon;

$userId = 5;
$customerId = 1590;

$settings = AppSetting::where('user_id', $userId)->first();
$customer = Customer::where('id', $customerId)->first();

echo "--- ANALYSIS ---\n";
echo "Current Time: " . now()->format('Y-m-d H:i:s') . "\n";

if ($settings) {
    echo "Settings found:\n";
    echo " - isolate_days: [" . $settings->isolate_days . "] (Type: " . gettype($settings->isolate_days) . ")\n";
    echo " - isolate_time: [" . $settings->isolate_time . "]\n";
    
    $check = ($settings && $settings->isolate_days && $settings->isolate_time);
    echo " - if (\$settings && \$settings->isolate_days && \$settings->isolate_time) results in: " . ($check ? 'TRUE' : 'FALSE') . "\n";
}

if ($customer) {
    echo "Customer found:\n";
    echo " - due_date: " . ($customer->due_date ? $customer->due_date->format('Y-m-d') : 'NULL') . "\n";
}

if ($settings) {
    $now = now();
    $isolateTime = Carbon::parse($settings->isolate_time);
    $offsetDays = (int) $settings->isolate_days;

    $isTodayBefore = $now->format('H:i') < $isolateTime->format('H:i');
    $targetToday = $now->copy()->subDays($offsetDays);
    $targetTomorrow = $now->copy()->addDay()->subDays($offsetDays);

    echo "Logic Parameters:\n";
    echo " - targetToday: " . $targetToday->format('Y-m-d') . "\n";
    echo " - targetTomorrow: " . $targetTomorrow->format('Y-m-d') . "\n";
    echo " - isTodayBefore: " . ($isTodayBefore ? 'YES' : 'NO') . "\n";
}
