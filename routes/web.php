<?php

use Illuminate\Support\Facades\Route;

// Route::get('/', \App\Livewire\LandingPage::class)->name('home');

Route::get('/up', function () {
    Artisan::call('up');
    Artisan::call('optimize:clear');
        File::put(storage_path('logs/laravel.log'), '');
        return 'Log cleared';
});


Route::get('/down', function () {
    Artisan::call('down');
    return 'Application is now in maintenance mode.';
});

Route::get('dashboard', \App\Livewire\DashboardReport::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware(['auth', 'check.router'])->group(function () {
    Route::get('assets', \App\Livewire\AssetManager::class)->name('assets');
    Route::get('assets/{asset}', \App\Livewire\AssetDetail::class)->name('assets.detail');
    Route::get('customers', \App\Livewire\CustomerManager::class)->name('customers');
    Route::get('customers/{customer}', \App\Livewire\CustomerDetail::class)->name('customers.detail');
    Route::get('packages', \App\Livewire\PackageManager::class)->name('packages');
    Route::get('static-packages', \App\Livewire\StaticPackageManager::class)->name('static-packages');
    Route::get('hotspot', \App\Livewire\HotspotManager::class)->name('hotspot');
    Route::get('whatsapp', \App\Livewire\WhatsappManager::class)->name('whatsapp');
    Route::get('broadcast', \App\Livewire\BroadcastManager::class)->name('broadcast');
    // Route::get('interface', \App\Livewire\MikrotikInterface::class)->name('interface');
    // Route::get('ip-pool', \App\Livewire\MikrotikIpPool::class)->name('ip-pool');
    Route::get('ppp-profiles', \App\Livewire\MikrotikPppProfile::class)->name('ppp-profiles');
    Route::get('hotspot-profiles', \App\Livewire\MikrotikHotspotProfile::class)->name('hotspot-profiles');
    Route::get('app-settings', \App\Livewire\AppManager::class)->name('app-settings');
    Route::get('invoice', \App\Livewire\InvoiceManager::class)->name('invoice');
    Route::get('piutangs', \App\Livewire\PiutangManager::class)->name('piutangs');
    Route::get('activities', \App\Livewire\Activities\ActivityLog::class)->name('activities');
    Route::get('vouchers', \App\Livewire\VoucherManager::class)->name('vouchers');
    Route::get('finance', \App\Livewire\FinanceManager::class)->name('finance');
    Route::get('reports', \App\Livewire\ReportManager::class)->name('reports');
    Route::get('whatsapp-servers', \App\Livewire\WhatsappServerManager::class)->name('whatsapp-servers');
    Route::get('olts', \App\Livewire\OltManager::class)->name('olts');
    Route::get('olts/onu-stream', [App\Http\Controllers\OltSseController::class, 'stream'])->name('olts.onu-stream');
    Route::post('olts/reboot-onu', [App\Http\Controllers\OltSseController::class, 'rebootOnu'])->name('olts.reboot-onu');
});

Route::get('router', \App\Livewire\RouterConfig::class)
    ->middleware(['auth'])
    ->name('router');

// Public Invoice & Order Views
Route::get('i/{invoice}', \App\Livewire\PublicInvoiceView::class)->name('public.invoice');
Route::get('i/{invoice}/print', [App\Http\Controllers\PrintController::class, 'thermal'])->name('public.print-thermal');
Route::get('voucher/{uri}', \App\Livewire\PublicVoucherOrder::class)->name('public.voucher.order');


// Print Routes (Unified)
Route::middleware(['auth'])->group(function () {
    Route::get('print/invoices/bulk', [App\Http\Controllers\PrintController::class, 'bulkInvoices'])->name('print.invoices.bulk');
    Route::get('print/invoice/{invoice}', [App\Http\Controllers\PrintController::class, 'invoice'])->name('print.invoice');
    Route::get('print/piutang/{piutang}', [App\Http\Controllers\PrintController::class, 'piutang'])->name('print.piutang');
    Route::get('print/hotspot-vouchers', [App\Http\Controllers\PrintController::class, 'hotspotVouchers'])->name('hotspot.print-vouchers');
    Route::get('print/reports', [App\Http\Controllers\PrintController::class, 'reports'])->name('print.reports');
});




require __DIR__ . '/auth.php';
