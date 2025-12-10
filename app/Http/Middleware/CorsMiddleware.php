<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Manejar preflight requests
        if ($request->isMethod('OPTIONS')) {
            return $this->handlePreflightRequest($request);
        }

        $response = $next($request);

        // Aplicar CORS basado en lista de orígenes permitidos
        $this->addCorsHeaders($request, $response);

        return $response;
    }

    private function handlePreflightRequest(Request $request): Response
    {
        $response = response('', 204);
        $this->addCorsHeaders($request, $response);
        return $response;
    }

    private function addCorsHeaders(Request $request, Response $response): void
    {
        $origin = $request->header('Origin');

        // Obtener orígenes permitidos desde configuración
        $allowedOrigins = config('app.allowed_origins', [
            config('app.url'),
            'http://localhost:3000',
            'http://localhost:8000',
        ]);

        // Solo aplicar CORS a archivos estáticos o rutas API
        if ($this->shouldApplyCors($request)) {
            // Verificar si el origen está en la lista permitida
            if (in_array($origin, $allowedOrigins) || $this->isWildcardMatch($origin, $allowedOrigins)) {
                $response->headers->set('Access-Control-Allow-Origin', $origin);
                $response->headers->set('Access-Control-Allow-Credentials', 'true');
            }

            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept, Authorization, X-Requested-With, X-CSRF-TOKEN');
            $response->headers->set('Access-Control-Max-Age', '3600');
        }
    }

    private function shouldApplyCors(Request $request): bool
    {
        $path = $request->path();

        // Archivos estáticos
        if ($this->isStaticFile($request)) {
            return true;
        }

        // Rutas API
        if (str_starts_with($path, 'api/')) {
            return true;
        }

        return false;
    }

    private function isStaticFile(Request $request): bool
    {
        $path = $request->path();
        $extensions = ['glb', 'gltf', 'jpg', 'jpeg', 'png', 'gif', 'css', 'js', 'webp', 'svg'];

        foreach ($extensions as $ext) {
            if (str_ends_with($path, '.' . $ext)) {
                return true;
            }
        }

        return str_contains($path, 'storage/');
    }

    private function isWildcardMatch(?string $origin, array $allowedOrigins): bool
    {
        if (!$origin) {
            return false;
        }

        foreach ($allowedOrigins as $allowed) {
            if ($allowed === '*') {
                // Solo permitir * en desarrollo
                return config('app.env') === 'local';
            }

            // Soporte para subdominios wildcards (ej: *.midominio.com)
            if (str_contains($allowed, '*')) {
                $pattern = str_replace(['*', '.'], ['.*', '\.'], $allowed);
                if (preg_match('/^' . $pattern . '$/', $origin)) {
                    return true;
                }
            }
        }

        return false;
    }
}