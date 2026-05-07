<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceHttps
{
    /**
     * Handle an incoming request.
     *
     * Redirect HTTP → HTTPS di production.
     * Support trusted proxy (Render, Cloudflare, dll) via X-Forwarded-Proto header.
     * Di local/dev, biarkan HTTP (tidak ada SSL cert).
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->environment('production')) {
            return $next($request);
        }

        // Cek apakah request aslinya HTTPS (via trusted proxy header)
        $isHttps = $request->secure()
            || $request->header('X-Forwarded-Proto') === 'https'
            || $request->header('X-Forwarded-Ssl') === 'on';

        if (! $isHttps) {
            return redirect()->secure($request->getRequestUri());
        }

        return $next($request);
    }
}
