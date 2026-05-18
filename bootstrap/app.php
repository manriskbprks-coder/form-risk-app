<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // --- TAMBAHIN BLOK ALIAS INI ---
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'admin' => \App\Http\Middleware\EnsureAdmin::class,
        ]);

        // Middleware untuk cek password expiry (90 hari)
        $middleware->appendToGroup('web', \App\Http\Middleware\CheckPasswordExpiry::class);

        // Security Headers — dipasang di semua request
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        // Force HTTPS — redirect HTTP → HTTPS di production
        $middleware->append(\App\Http\Middleware\ForceHttps::class);

        // Trusted proxies — biar Laravel tau kalau dibelakang load balancer (Render, Cloudflare, dll)
        $middleware->trustProxies(at: '*', headers: Request::HEADER_X_FORWARDED_FOR |
            Request::HEADER_X_FORWARDED_HOST |
            Request::HEADER_X_FORWARDED_PORT |
            Request::HEADER_X_FORWARDED_PROTO |
            Request::HEADER_X_FORWARDED_AWS_ELB
        );
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
