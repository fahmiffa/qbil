<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', config('app.name', 'Laravel') . ' - Invoice')</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />

        <style>
            body { font-family: 'Plus Jakarta Sans', sans-serif; }
        </style>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @stack('meta')
    </head>
    <body class="antialiased bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 min-h-screen">
        <div class="relative overflow-hidden min-h-screen">
            <!-- Decorative Background Elements -->
            <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 bg-blue-100 dark:bg-blue-900/20 rounded-full blur-3xl opacity-50 pointer-events-none"></div>
            <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-96 h-96 bg-indigo-100 dark:bg-indigo-900/20 rounded-full blur-3xl opacity-50 pointer-events-none"></div>

            <main class="relative z-10">
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
