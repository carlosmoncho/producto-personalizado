<?php

namespace App\Services\File;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Servicio para manejo de subida de archivos
 *
 * Centraliza la lógica de subida, validación y eliminación de archivos
 * para productos (imágenes y modelos 3D).
 *
 * @package App\Services\File
 */
class FileUploadService
{
    /**
     * Obtener el disco de almacenamiento configurado
     * En producción usa S3, en desarrollo usa local (public)
     */
    private function getDisk(): string
    {
        return config('filesystems.default', 'public');
    }

    /**
     * Extensiones permitidas para imágenes
     */
    private const ALLOWED_IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    /**
     * Extensiones permitidas para modelos 3D
     */
    private const ALLOWED_3D_EXTENSIONS = ['glb', 'gltf'];

    /**
     * Tamaño máximo para modelos 3D (20MB en bytes)
     */
    private const MAX_3D_FILE_SIZE = 20 * 1024 * 1024; // 20MB

    /**
     * Subir múltiples imágenes de producto
     *
     * @param array $imageFiles Array de UploadedFile
     * @param string $directory Directorio donde guardar (ej: 'products')
     * @return array Array de paths guardados
     * @throws \Exception Si hay error al guardar
     */
    public function uploadProductImages(array $imageFiles, string $directory = 'products'): array
    {
        $imagePaths = [];

        try {
            foreach ($imageFiles as $image) {
                if (!($image instanceof UploadedFile)) {
                    throw new \InvalidArgumentException('Todos los elementos deben ser instancias de UploadedFile');
                }

                // Validar que es imagen
                if (!$image->isValid()) {
                    throw new \Exception('Archivo de imagen inválido');
                }

                $extension = strtolower($image->getClientOriginalExtension());

                if (!in_array($extension, self::ALLOWED_IMAGE_EXTENSIONS)) {
                    throw new \Exception("Extensión de imagen no permitida: {$extension}");
                }

                // Guardar imagen
                $disk = $this->getDisk();
                \Log::info('Intentando guardar imagen', [
                    'disk' => $disk,
                    'directory' => $directory,
                    'original_name' => $image->getClientOriginalName(),
                    'size' => $image->getSize()
                ]);

                try {
                    $path = $image->store($directory, $disk);
                } catch (\Exception $storeException) {
                    \Log::error('Error específico al guardar en storage', [
                        'disk' => $disk,
                        'error' => $storeException->getMessage(),
                        'trace' => $storeException->getTraceAsString()
                    ]);
                    throw new \Exception('Error al guardar en ' . $disk . ': ' . $storeException->getMessage());
                }

                if (!$path) {
                    throw new \Exception('Error al guardar la imagen - path vacío');
                }

                // Verificar que se guardó
                if (!Storage::disk($this->getDisk())->exists($path)) {
                    throw new \Exception('Error: el archivo no existe después de guardar');
                }

                $imagePaths[] = $path;

                \Log::info('Imagen de producto guardada', [
                    'path' => $path,
                    'size' => $image->getSize(),
                    'mime' => $image->getMimeType()
                ]);
            }

            return $imagePaths;

        } catch (\Exception $e) {
            // Si hay error, eliminar las imágenes que se guardaron
            $this->deleteFiles($imagePaths);

            \Log::error('Error al subir imágenes de producto', [
                'error' => $e->getMessage(),
                'saved_paths' => $imagePaths
            ]);

            throw $e;
        }
    }

    /**
     * Subir modelo 3D con validación y nombre único
     *
     * @param UploadedFile $file Archivo 3D
     * @param string $directory Directorio donde guardar (default: '3d-models')
     * @param string|null $productName Nombre del producto (para logging)
     * @return string Path del archivo guardado
     * @throws \Exception Si hay error en validación o guardado
     */
    public function upload3DModel(
        UploadedFile $file,
        string $directory = '3d-models',
        ?string $productName = null
    ): string {
        try {
            // Validar archivo
            if (!$file->isValid()) {
                throw new \Exception('Archivo 3D inválido');
            }

            $extension = strtolower($file->getClientOriginalExtension());

            // Validar extensión
            if (!in_array($extension, self::ALLOWED_3D_EXTENSIONS)) {
                throw new \Exception(
                    "El archivo debe ser un modelo 3D válido (.glb o .gltf). Recibido: .{$extension}"
                );
            }

            // Validar tamaño
            if ($file->getSize() > self::MAX_3D_FILE_SIZE) {
                $maxMB = self::MAX_3D_FILE_SIZE / (1024 * 1024);
                $fileMB = round($file->getSize() / (1024 * 1024), 2);
                throw new \Exception(
                    "El archivo 3D es demasiado grande ({$fileMB}MB). Máximo permitido: {$maxMB}MB"
                );
            }

            // Generar nombre único para evitar colisiones
            $fileName = Str::random(40) . '.' . $extension;

            // Guardar archivo
            $path = $file->storeAs($directory, $fileName, $this->getDisk());

            if (!$path) {
                throw new \Exception('Error al guardar el archivo 3D');
            }

            // Verificar que se guardó correctamente
            if (!Storage::disk($this->getDisk())->exists($path)) {
                throw new \Exception('Error al verificar el archivo 3D guardado');
            }

            \Log::info('Modelo 3D guardado', [
                'product' => $productName,
                'file' => $fileName,
                'path' => $path,
                'size' => $file->getSize(),
                'extension' => $extension
            ]);

            return $path;

        } catch (\Exception $e) {
            \Log::error('Error al subir modelo 3D', [
                'product' => $productName,
                'error' => $e->getMessage(),
                'file_size' => $file->getSize(),
                'extension' => $file->getClientOriginalExtension()
            ]);

            throw $e;
        }
    }

    /**
     * Eliminar archivo único de forma segura
     *
     * @param string|null $filePath Path del archivo a eliminar
     * @param string|null $disk Disco de almacenamiento (null = usar configurado)
     * @return bool True si se eliminó, false si no existía
     */
    public function deleteFile(?string $filePath, ?string $disk = null): bool
    {
        $disk = $disk ?? $this->getDisk();
        if (!$filePath) {
            return false;
        }

        try {
            // Prevenir path traversal
            if ($this->isPathUnsafe($filePath)) {
                \Log::warning('Intento de eliminar archivo con path inseguro', [
                    'path' => $filePath
                ]);
                return false;
            }

            if (Storage::disk($disk)->exists($filePath)) {
                Storage::disk($disk)->delete($filePath);

                \Log::info('Archivo eliminado', [
                    'path' => $filePath,
                    'disk' => $disk
                ]);

                return true;
            }

            return false;

        } catch (\Exception $e) {
            \Log::error('Error al eliminar archivo', [
                'path' => $filePath,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Eliminar múltiples archivos
     *
     * @param array $filePaths Array de paths
     * @param string|null $disk Disco de almacenamiento (null = usar configurado)
     * @return int Cantidad de archivos eliminados
     */
    public function deleteFiles(array $filePaths, ?string $disk = null): int
    {
        $disk = $disk ?? $this->getDisk();
        $deleted = 0;

        foreach ($filePaths as $filePath) {
            if ($this->deleteFile($filePath, $disk)) {
                $deleted++;
            }
        }

        return $deleted;
    }

    /**
     * Verificar si un path es inseguro (prevenir path traversal)
     *
     * @param string $path
     * @return bool True si el path es inseguro
     */
    private function isPathUnsafe(string $path): bool
    {
        // Detectar intentos de path traversal
        return str_contains($path, '..') ||
               str_contains($path, '//') ||
               str_starts_with($path, '/');
    }

    /**
     * Obtener información de un archivo
     *
     * @param string $filePath
     * @param string|null $disk
     * @return array|null Array con info del archivo o null si no existe
     */
    public function getFileInfo(string $filePath, ?string $disk = null): ?array
    {
        $disk = $disk ?? $this->getDisk();
        try {
            if (!Storage::disk($disk)->exists($filePath)) {
                return null;
            }

            return [
                'path' => $filePath,
                'size' => Storage::disk($disk)->size($filePath),
                'mime_type' => Storage::disk($disk)->mimeType($filePath),
                'last_modified' => Storage::disk($disk)->lastModified($filePath),
                'url' => Storage::disk($disk)->url($filePath),
            ];

        } catch (\Exception $e) {
            \Log::error('Error al obtener info de archivo', [
                'path' => $filePath,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * Obtener URL pública de un archivo
     *
     * @param string|null $filePath Path del archivo
     * @return string|null URL pública o null si no existe
     */
    public function getPublicUrl(?string $filePath): ?string
    {
        if (!$filePath) {
            return null;
        }

        $disk = $this->getDisk();

        try {
            return Storage::disk($disk)->url($filePath);
        } catch (\Exception $e) {
            \Log::error('Error al obtener URL de archivo', [
                'path' => $filePath,
                'disk' => $disk,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Obtener el disco de almacenamiento actual
     *
     * @return string
     */
    public function getCurrentDisk(): string
    {
        return $this->getDisk();
    }

    /**
     * Validar que un archivo es una imagen válida
     *
     * @param UploadedFile $file
     * @return bool
     */
    public function isValidImage(UploadedFile $file): bool
    {
        if (!$file->isValid()) {
            return false;
        }

        $extension = strtolower($file->getClientOriginalExtension());
        return in_array($extension, self::ALLOWED_IMAGE_EXTENSIONS);
    }

    /**
     * Validar que un archivo es un modelo 3D válido
     *
     * @param UploadedFile $file
     * @return bool
     */
    public function isValid3DModel(UploadedFile $file): bool
    {
        if (!$file->isValid()) {
            return false;
        }

        $extension = strtolower($file->getClientOriginalExtension());

        return in_array($extension, self::ALLOWED_3D_EXTENSIONS) &&
               $file->getSize() <= self::MAX_3D_FILE_SIZE;
    }
}
