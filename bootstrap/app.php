<?php

use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Proxy tepercaya (load balancer/Nginx). Isi TRUSTED_PROXIES di .env, mis. "10.0.0.1,10.0.0.2".
        // Tanpa ini, request->isSecure() dan IP klien salah bila di belakang proxy.
        $proxies = env('TRUSTED_PROXIES');
        $middleware->trustProxies(at: $proxies ? array_map('trim', explode(',', $proxies)) : null);

        // Header keamanan untuk semua respons web & API.
        $middleware->appendToGroup('web', SecurityHeaders::class);
        $middleware->appendToGroup('api', SecurityHeaders::class);

        // CATATAN CSRF: middleware CSRF sudah otomatis ada di grup "web" dan TIDAK ada
        // pengecualian (except) di sini. Grup "api" stateless (tanpa cookie sesi), sehingga
        // tidak rentan CSRF; endpoint API yang mengubah data wajib memakai token Bearer.

        $middleware->alias(['role' => EnsureRole::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Semua error di /api/* dikembalikan sebagai JSON (bukan redirect/HTML).
        $exceptions->shouldRenderJsonWhen(fn (Request $request) => $request->is('api/*') || $request->expectsJson());
    })->create();
