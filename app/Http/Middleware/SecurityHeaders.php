<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // --- TAMBAHAN ATURAN CSP (DAFTAR TAMU VIP) ---
        $isLocal = app()->environment('local');
        $viteHost = $isLocal ? " http://localhost:5173 http://127.0.0.1:5173 http://[::1]:5173 ws://localhost:5173 ws://127.0.0.1:5173 ws://[::1]:5173" : "";

        $csp = "default-src 'self'; " . 
               "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net" . $viteHost . "; " . 
               "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net" . $viteHost . "; " . 
               "font-src 'self' https://fonts.gstatic.com; " .
               "connect-src 'self'" . $viteHost . "; " .
               "img-src 'self' data:;";
        
        $response->headers->set('Content-Security-Policy', $csp);
        // ----------------------------------------------

        // Aturan lama yang udah ada tetep dibiarin
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), camera=(), microphone=(), payment=(), usb=(), magnetometer=(), accelerometer=()');

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        return $response;
    }
}
