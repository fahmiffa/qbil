<?php

use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::middleware('guest')->group(function () {
    Volt::route('register', 'pages.auth.register')
        ->name('register');

    Volt::route('login', 'pages.auth.login')
        ->name('login');
        
       Volt::route('/', 'pages.auth.login')
        ->name('home');

    Volt::route('forgot-password', 'pages.auth.forgot-password')
        ->name('password.request');

    Volt::route('reset-password/{token}', 'pages.auth.reset-password')
        ->name('password.reset');
});

Route::middleware(['auth'])->group(function () {

    // Dashboard overridden in web.php

    Route::middleware(['role:0'])->group(function () {
        Route::get('/admin', function () {
            return view('admin');
        });
        Route::get('/akun', \App\Livewire\UserManager::class)->name('akun');
        Route::get('/features', \App\Livewire\FeatureManager::class)->name('features');
    });
});
