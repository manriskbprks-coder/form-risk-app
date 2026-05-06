<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPasswordExpiry
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->mustChangePassword()) {
            // Jangan redirect untuk:
            // - Halaman profile (biar bisa ganti password)
            // - Semua POST request (biar form submission bisa diproses)
            // - Route login/logout (biar ga loop)
            if ($request->isMethod('POST')) {
                return $next($request);
            }

            if (!$request->routeIs('profile.edit') && !$request->routeIs('login') && !$request->routeIs('logout')) {
                return redirect()->route('profile.edit')
                    ->with('error', 'Password Anda sudah berusia lebih dari 30 hari. Silakan ganti password Anda.');
            }
        }

        return $next($request);
    }
}
