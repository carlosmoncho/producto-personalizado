<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Health Check Controller
 *
 * Proporciona endpoints para monitoreo del sistema
 */
class HealthCheckController extends Controller
{
    /**
     * Basic health check
     *
     * Returns 200 OK if the application is running
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'service' => config('app.name'),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Detailed health check
     *
     * Verifica todos los componentes críticos del sistema:
     * - Base de datos
     * - Cache
     * - Storage
     * - Configuración
     *
     * @return JsonResponse
     */
    public function detailed(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
            'config' => $this->checkConfig(),
        ];

        $allHealthy = collect($checks)->every(fn($check) => $check['status'] === 'ok');
        $httpStatus = $allHealthy ? 200 : 503;

        return response()->json([
            'status' => $allHealthy ? 'ok' : 'degraded',
            'service' => config('app.name'),
            'timestamp' => now()->toIso8601String(),
            'checks' => $checks,
        ], $httpStatus);
    }

    /**
     * System metrics
     *
     * Retorna métricas del sistema para monitoreo
     *
     * @return JsonResponse
     */
    public function metrics(): JsonResponse
    {
        return response()->json([
            'timestamp' => now()->toIso8601String(),
            'uptime' => $this->getUptime(),
            'memory' => [
                'used' => memory_get_usage(true),
                'used_formatted' => $this->formatBytes(memory_get_usage(true)),
                'peak' => memory_get_peak_usage(true),
                'peak_formatted' => $this->formatBytes(memory_get_peak_usage(true)),
            ],
            'database' => $this->getDatabaseMetrics(),
            'cache' => [
                'driver' => config('cache.default'),
                'working' => $this->checkCache()['status'] === 'ok',
            ],
            'storage' => [
                'disk' => config('filesystems.default'),
                'working' => $this->checkStorage()['status'] === 'ok',
            ],
        ]);
    }

    /**
     * Readiness check
     *
     * Para Kubernetes/Docker - verifica si la app está lista para recibir tráfico
     *
     * @return JsonResponse
     */
    public function ready(): JsonResponse
    {
        $dbCheck = $this->checkDatabase();

        if ($dbCheck['status'] !== 'ok') {
            return response()->json([
                'status' => 'not_ready',
                'reason' => 'Database not available',
            ], 503);
        }

        return response()->json([
            'status' => 'ready',
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Liveness check
     *
     * Para Kubernetes/Docker - verifica si la app está viva
     *
     * @return JsonResponse
     */
    public function alive(): JsonResponse
    {
        return response()->json([
            'status' => 'alive',
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Check database connection
     *
     * @return array
     */
    private function checkDatabase(): array
    {
        try {
            $start = microtime(true);
            DB::connection()->getPdo();
            $latency = round((microtime(true) - $start) * 1000, 2);

            return [
                'status' => 'ok',
                'driver' => config('database.default'),
                'latency_ms' => $latency,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'driver' => config('database.default'),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check cache connection
     *
     * @return array
     */
    private function checkCache(): array
    {
        try {
            $testKey = 'health_check_' . time();
            $testValue = 'test';

            Cache::put($testKey, $testValue, 10);
            $retrieved = Cache::get($testKey);
            Cache::forget($testKey);

            $working = $retrieved === $testValue;

            return [
                'status' => $working ? 'ok' : 'error',
                'driver' => config('cache.default'),
                'working' => $working,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'driver' => config('cache.default'),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check storage
     *
     * @return array
     */
    private function checkStorage(): array
    {
        try {
            $disk = config('filesystems.default');
            $testFile = 'health_check.txt';

            Storage::disk($disk)->put($testFile, 'test');
            $exists = Storage::disk($disk)->exists($testFile);
            Storage::disk($disk)->delete($testFile);

            return [
                'status' => $exists ? 'ok' : 'error',
                'disk' => $disk,
                'writable' => $exists,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'disk' => config('filesystems.default'),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check critical configuration
     *
     * @return array
     */
    private function checkConfig(): array
    {
        $issues = [];

        if (config('app.debug') && app()->environment('production')) {
            $issues[] = 'Debug mode enabled in production';
        }

        if (config('app.key') === 'base64:' . base64_encode('32_character_random_string_here')) {
            $issues[] = 'Default application key detected';
        }

        return [
            'status' => empty($issues) ? 'ok' : 'warning',
            'environment' => app()->environment(),
            'debug' => config('app.debug'),
            'issues' => $issues,
        ];
    }

    /**
     * Get database metrics
     *
     * @return array
     */
    private function getDatabaseMetrics(): array
    {
        try {
            $connection = DB::connection();
            $driver = $connection->getDriverName();

            $metrics = [
                'driver' => $driver,
                'database' => $connection->getDatabaseName(),
            ];

            if ($driver === 'mysql') {
                $status = DB::select('SHOW STATUS');
                $variables = DB::select('SHOW VARIABLES LIKE "max_connections"');

                $metrics['connections'] = collect($status)
                    ->firstWhere('Variable_name', 'Threads_connected')
                    ->Value ?? 'unknown';

                $metrics['max_connections'] = collect($variables)
                    ->first()
                    ->Value ?? 'unknown';
            }

            return $metrics;
        } catch (\Exception $e) {
            return [
                'driver' => config('database.default'),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get application uptime (from Laravel start time)
     *
     * @return string
     */
    private function getUptime(): string
    {
        // Esto es aproximado - para un uptime real necesitarías almacenar el tiempo de inicio
        $start = defined('LARAVEL_START') ? LARAVEL_START : microtime(true);
        $uptime = microtime(true) - $start;

        return $this->formatSeconds($uptime);
    }

    /**
     * Format bytes to human readable
     *
     * @param int $bytes
     * @return string
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Format seconds to human readable
     *
     * @param float $seconds
     * @return string
     */
    private function formatSeconds(float $seconds): string
    {
        if ($seconds < 60) {
            return round($seconds, 2) . 's';
        }

        $minutes = floor($seconds / 60);
        if ($minutes < 60) {
            return $minutes . 'm ' . round($seconds % 60) . 's';
        }

        $hours = floor($minutes / 60);
        if ($hours < 24) {
            return $hours . 'h ' . ($minutes % 60) . 'm';
        }

        $days = floor($hours / 24);
        return $days . 'd ' . ($hours % 24) . 'h';
    }
}
