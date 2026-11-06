<?php

use App\Http\Middleware\PastikanPeran;
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
    ->withMiddleware(function (Middleware $middleware): void {
        // Alias middleware peran: ->middleware('role:admin,petugas')
        $middleware->alias([
            'role' => PastikanPeran::class,
        ]);

        // Tamu yang membuka halaman terlindungi diarahkan ke form login.
        $middleware->redirectGuestsTo(fn () => route('login'));

        // Pengguna yang sudah login dan membuka /login diarahkan ke beranda perannya.
        $middleware->redirectUsersTo(
            fn () => auth()->check() ? route(auth()->user()->role->beranda()) : route('login')
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
