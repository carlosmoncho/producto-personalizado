<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Cargar rutas de desarrollo SOLO en entorno local
            // Estas rutas incluyen demos y tests que NO deben estar en producción
            if (app()->environment('local')) {
                Route::middleware('web')
                    ->group(base_path('routes/dev.php'));
            }
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(\App\Http\Middleware\SecurityHeadersMiddleware::class);
        $middleware->append(\App\Http\Middleware\CorsMiddleware::class);

        // Excluir rutas de autenticación API de la verificación CSRF
        $middleware->validateCsrfTokens(except: [
            'api/auth/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
