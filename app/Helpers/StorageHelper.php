<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;

class StorageHelper
{
    /**
     * Get a safe URL for a storage path
     * Handles both local and S3 storage, with fallbacks
     */
    public static function url(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        // Si ya es una URL absoluta, devolverla tal cual
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $disk = config('filesystems.default', 'public');

        try {
            // Si estamos en producción o el disco es S3, intentar usar S3
            if ($disk === 's3') {
                // Verificar que S3 está configurado correctamente
                $bucket = config('filesystems.disks.s3.bucket');
                if (empty($bucket)) {
                    // S3 no configurado, usar proxy local
                    return url('/api/storage/' . $path);
                }
                return Storage::disk('s3')->url($path);
            }

            // En local, usar el disco public o el proxy
            if ($disk === 'public') {
                return Storage::disk('public')->url($path);
            }

            // Fallback: usar proxy /api/storage/
            return url('/api/storage/' . $path);

        } catch (\Exception $e) {
            // En caso de error, usar proxy como fallback
            \Log::warning('StorageHelper: Error getting URL', [
                'path' => $path,
                'disk' => $disk,
                'error' => $e->getMessage()
            ]);
            return url('/api/storage/' . $path);
        }
    }

    /**
     * Get URLs for multiple paths
     */
    public static function urls(array $paths): array
    {
        return array_map([self::class, 'url'], $paths);
    }
}
