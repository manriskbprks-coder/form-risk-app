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
        $csp = "default-src 'self'; " . 
               "script-src 'self' 'unsafe-inline' 'unsafe-eval'; " . 
               "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; " . 
               "font-src 'self' https://fonts.gstatic.com; " .
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
