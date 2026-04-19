<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::get('dashboard', \App\Livewire\DashboardReport::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware(['auth', 'check.router'])->group(function () {
    Route::get('assets', \App\Livewire\AssetManager::class)->name('assets');
    Route::get('customers', \App\Livewire\CustomerManager::class)->name('customers');
    Route::get('packages', \App\Livewire\PackageManager::class)->name('packages');
    Route::get('static-packages', \App\Livewire\StaticPackageManager::class)->name('static-packages');
    Route::get('hotspot', \App\Livewire\HotspotManager::class)->name('hotspot');
    Route::get('whatsapp', \App\Livewire\WhatsappManager::class)->name('whatsapp');
    // Route::get('interface', \App\Livewire\MikrotikInterface::class)->name('interface');
    // Route::get('ip-pool', \App\Livewire\MikrotikIpPool::class)->name('ip-pool');
    Route::get('ppp-profiles', \App\Livewire\MikrotikPppProfile::class)->name('ppp-profiles');
    Route::get('hotspot-profiles', \App\Livewire\MikrotikHotspotProfile::class)->name('hotspot-profiles');
    Route::get('app-settings', \App\Livewire\AppManager::class)->name('app-settings');
    Route::get('invoice', \App\Livewire\InvoiceManager::class)->name('invoice');
});

Route::get('router', \App\Livewire\RouterConfig::class)
    ->middleware(['auth'])
    ->name('router');

// Public Invoice View
Route::get('i/{invoice}', \App\Livewire\PublicInvoiceView::class)->name('public.invoice');




require __DIR__.'/auth.php';
