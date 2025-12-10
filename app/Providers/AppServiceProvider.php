<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configurar reglas de contraseñas seguras
        Password::defaults(function () {
            $rule = Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols();

            // En producción, hacer las reglas más estrictas
            return app()->environment('production')
                ? $rule->uncompromised()
                : $rule;
        });

        // ============ RATE LIMITING PERSONALIZADO ============
        // Rate limiters con protección multi-nivel para prevenir abuso

        // API General - 60 req/min
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Lectura Pública - Para endpoints de solo lectura (categorías, productos)
        // En local: límites muy altos para desarrollo
        // En producción: 100 req/min, 1000 req/hora
        RateLimiter::for('public-read', function (Request $request) {
            if (app()->environment('local')) {
                return [
                    Limit::perMinute(1000)->by($request->ip()),
                    Limit::perHour(10000)->by($request->ip()),
                ];
            }

            return [
                Limit::perMinute(100)->by($request->ip()),
                Limit::perHour(1000)->by($request->ip()),
            ];
        });

        // Cálculo de Precios - Límite medio (puede haber mucha interacción del usuario)
        // En local: límites altos para desarrollo
        // En producción: 20 req/min, 200 req/hora
        RateLimiter::for('price-calculation', function (Request $request) {
            if (app()->environment('local')) {
                return [
                    Limit::perMinute(200)->by($request->ip()),
                    Limit::perHour(2000)->by($request->ip()),
                ];
            }

            return [
                Limit::perMinute(20)->by($request->ip()),
                Limit::perHour(200)->by($request->ip()),
            ];
        });

        // Creación de Pedidos - MUY RESTRICTIVO (operación crítica)
        // 2 req/min, 10 req/hora, 50 req/día
        RateLimiter::for('orders', function (Request $request) {
            return [
                Limit::perMinute(2)->by($request->ip())
                    ->response(function (Request $request, array $headers) {
                        return response()->json([
                            'error' => 'Demasiadas solicitudes. Límite: 2 pedidos por minuto.',
                            'retry_after' => $headers['Retry-After'] ?? 60,
                        ], 429);
                    }),
                Limit::perHour(10)->by($request->ip())
                    ->response(function (Request $request, array $headers) {
                        return response()->json([
                            'error' => 'Límite horario excedido. Límite: 10 pedidos por hora.',
                            'retry_after' => $headers['Retry-After'] ?? 3600,
                        ], 429);
                    }),
                Limit::perDay(50)->by($request->ip())
                    ->response(function (Request $request, array $headers) {
                        return response()->json([
                            'error' => 'Límite diario excedido. Límite: 50 pedidos por día.',
                            'retry_after' => $headers['Retry-After'] ?? 86400,
                        ], 429);
                    }),
            ];
        });

        // API Estricta - Para operaciones sensibles (validación, configuración)
        // En local: límites altos para desarrollo
        // En producción: 30 req/min, 300 req/hora
        RateLimiter::for('api-strict', function (Request $request) {
            if (app()->environment('local')) {
                return [
                    Limit::perMinute(300)->by($request->ip()),
                    Limit::perHour(3000)->by($request->ip()),
                ];
            }

            return [
                Limit::perMinute(30)->by($request->ip()),
                Limit::perHour(300)->by($request->ip()),
            ];
        });

        // Health Check - Alto límite para monitoreo pero con protección anti-abuso
        // 1000 req/min para permitir monitoreo intensivo pero evitar DoS
        RateLimiter::for('health-check', function (Request $request) {
            return Limit::perMinute(1000)->by($request->ip());
        });
    }
}
