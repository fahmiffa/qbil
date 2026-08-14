<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$s = new App\Services\OltApiService();
$raw = $s->fetchOnuAll();

// Normalize (sama seperti yang sudah di-fix)
$normalized = array_map(function ($item) {
    return [
        'onu_id'      => $item['id']          ?? $item['onu_id']      ?? '',
        'onu_name'    => $item['name']        ?? $item['onu_name']    ?? '',
        'mac_address' => $item['mac_address'] ?? $item['onu_mac']     ?? '',
        'olt'         => $item['olt']         ?? '',
        'status'      => $item['status']      ?? '',
    ];
}, $raw);

echo "Total ONU: " . count($normalized) . PHP_EOL;
if (count($normalized) > 0) {
    echo "Sample normalized: " . json_encode($normalized[0]) . PHP_EOL;
    echo "Sample normalized: " . json_encode($normalized[1]) . PHP_EOL;
}
