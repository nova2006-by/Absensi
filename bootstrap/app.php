<?php

use App\Http\Middleware\EnsureRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => EnsureRole::class,
        ]);

        // Berjalan di belakang reverse proxy Vercel, jadi header
        // X-Forwarded-Proto/Host harus dipercaya agar URL memakai https.
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Token CSRF kedaluwarsa (419) umum terjadi bila form login dibiarkan
        // terbuka lama atau dibuka ulang dari cache browser. Daripada berhenti
        // di halaman "Page Expired" buntu, kembalikan pengguna ke form login.
        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            if ($e->getStatusCode() !== 419) {
                return null;
            }

            $pesan = 'Sesi login sudah berakhir. Silakan masuk kembali.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $pesan], 419);
            }

            return redirect()->route('login')->withErrors(['email' => $pesan]);
        });
    })->create();
