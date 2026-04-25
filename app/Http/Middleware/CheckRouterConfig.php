<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRouterConfig
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && !$user->hasFeature('mikrotik')) {
            return $next($request);
        }

        // Periksa apakah user sudah login dan memiliki konfigurasi router
        if ($user && !$user->router) {
            
            // Izinkan jika mengakses dashboard, profile, router config itu sendiri, atau logout
            $allowedRoutes = ['router', 'dashboard', 'profile', 'logout', 'verification.notice', 'password.confirm'];

            if (!$request->routeIs($allowedRoutes)) {
                return redirect()->route('router')->with('warning', 'Akses Dibatasi! Silahkan lengkapi konfigurasi Router Mikrotik Anda terlebih dahulu.');
            }
        }

        return $next($request);
    }
}
