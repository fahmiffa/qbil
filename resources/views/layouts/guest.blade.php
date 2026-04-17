<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Favicon -->
        <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%233b82f6' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M20.34 17.52a10 10 0 1 0-2.82 2.82'/%3E%3Ccircle cx='19' cy='19' r='2'/%3E%3Cpath d='m13.41 13.41 4.18 4.18'/%3E%3Ccircle cx='12' cy='12' r='2'/%3E%3C/svg%3E">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased dark:bg-slate-950">
        <div class="min-h-screen flex">
            <!-- Left Side: Branding & Image (hidden on mobile) -->
            <div class="hidden lg:flex lg:w-1/2 bg-blue-600 relative overflow-hidden">
                <img src="{{ asset('images/login_bg.png') }}" alt="Background" class="absolute inset-0 w-full h-full object-cover mix-blend-overlay opacity-50">
                <div class="relative z-10 flex flex-col justify-center px-20">
                    <div class="mb-12">
                        <x-application-logo class="w-20 h-20 text-white" />
                    </div>
                    <h1 class="text-5xl font-bold text-white mb-6">Simplify Your <br>Billing System.</h1>
                    <p class="text-xl text-blue-100 max-w-md">Manage your ISP, Mikrotik, and customers with ease using our professional e-Billing solution.</p>
                </div>
                <!-- Abstract decorative shapes -->
                <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-blue-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20"></div>
                <div class="absolute -top-20 -right-20 w-80 h-80 bg-indigo-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20"></div>
            </div>

            <!-- Right Side: Login Form -->
            <div class="w-full lg:w-1/2 flex items-center justify-center p-8 bg-gray-50 dark:bg-slate-950">
                <div class="w-full max-w-md">
                    <div class="lg:hidden mb-8 flex justify-center">
                        <x-application-logo class="w-16 h-16 text-blue-600" />
                    </div>
                    
                    <div class="bg-white dark:bg-slate-900 shadow-2xl shadow-blue-500/10 rounded-2xl p-8 border border-gray-100 dark:border-slate-800">
                        {{ $slot }}
                    </div>

                    <div class="mt-8 text-center">
                        <p class="text-sm text-gray-500 dark:text-slate-400">
                            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
