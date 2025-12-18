<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class ValidGltfFile implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * Validates that the file is a valid GLTF/GLB file by checking:
     * - File extension
     * - Magic bytes (file signature)
     * - Basic structure validity
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        \Log::info('ValidGltfFile: Iniciando validación', [
            'is_uploaded_file' => $value instanceof UploadedFile,
            'class' => get_class($value),
        ]);

        if (!$value instanceof UploadedFile) {
            \Log::error('ValidGltfFile: No es UploadedFile');
            $fail('El archivo debe ser un archivo válido.');
            return;
        }

        \Log::info('ValidGltfFile: Detalles del archivo', [
            'original_name' => $value->getClientOriginalName(),
            'extension' => $value->getClientOriginalExtension(),
            'size' => $value->getSize(),
            'mime' => $value->getMimeType(),
            'error' => $value->getError(),
            'is_valid' => $value->isValid(),
        ]);

        // Verificar si el archivo se subió correctamente
        if (!$value->isValid()) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'El archivo excede upload_max_filesize de PHP',
                UPLOAD_ERR_FORM_SIZE => 'El archivo excede MAX_FILE_SIZE del formulario',
                UPLOAD_ERR_PARTIAL => 'El archivo se subió parcialmente',
                UPLOAD_ERR_NO_FILE => 'No se subió ningún archivo',
                UPLOAD_ERR_NO_TMP_DIR => 'Falta carpeta temporal',
                UPLOAD_ERR_CANT_WRITE => 'Error al escribir archivo',
                UPLOAD_ERR_EXTENSION => 'Extensión de PHP detuvo la subida',
            ];
            $errorCode = $value->getError();
            $errorMsg = $errorMessages[$errorCode] ?? "Error desconocido: $errorCode";
            \Log::error('ValidGltfFile: Archivo no válido', ['error' => $errorMsg]);
            $fail("Error al subir: $errorMsg");
            return;
        }

        // Verificar extensión
        $extension = strtolower($value->getClientOriginalExtension());
        if (!in_array($extension, ['glb', 'gltf'])) {
            \Log::error('ValidGltfFile: Extensión inválida', ['extension' => $extension]);
            $fail("El archivo debe tener extensión .glb o .gltf (recibido: .$extension)");
            return;
        }

        // Verificar tamaño razonable (máximo 20MB)
        if ($value->getSize() > 20 * 1024 * 1024) {
            $sizeMB = round($value->getSize() / 1024 / 1024, 2);
            \Log::error('ValidGltfFile: Archivo muy grande', ['size_mb' => $sizeMB]);
            $fail("El archivo 3D no debe superar los 20MB (tamaño: {$sizeMB}MB).");
            return;
        }

        // Para archivos GLB, verificar magic bytes
        if ($extension === 'glb') {
            if (!$this->validateGlbMagicBytes($value)) {
                \Log::error('ValidGltfFile: Magic bytes inválidos');
                $fail('El archivo GLB no tiene una firma válida. Puede estar corrupto o no ser un archivo GLB real.');
                return;
            }
        }

        // Para archivos GLTF, verificar que sea JSON válido
        if ($extension === 'gltf') {
            if (!$this->validateGltfJson($value)) {
                \Log::error('ValidGltfFile: JSON inválido en GLTF');
                $fail('El archivo GLTF no contiene JSON válido.');
                return;
            }
        }

        \Log::info('ValidGltfFile: Validación exitosa');
    }

    /**
     * Validate GLB file magic bytes
     * GLB files start with "glTF" (0x676C5446) followed by version
     */
    private function validateGlbMagicBytes(UploadedFile $file): bool
    {
        $handle = fopen($file->getRealPath(), 'rb');

        if (!$handle) {
            return false;
        }

        // Read first 4 bytes (magic)
        $magic = fread($handle, 4);
        fclose($handle);

        // GLB magic bytes: "glTF" (0x676C5446)
        return $magic === 'glTF';
    }

    /**
     * Validate GLTF JSON structure
     */
    private function validateGltfJson(UploadedFile $file): bool
    {
        try {
            $content = file_get_contents($file->getRealPath());

            if ($content === false) {
                return false;
            }

            // Verificar que es JSON válido
            $json = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return false;
            }

            // Verificar que tiene las propiedades básicas de GLTF
            if (!isset($json['asset'])) {
                return false;
            }

            // Verificar versión GLTF
            if (!isset($json['asset']['version'])) {
                return false;
            }

            return true;

        } catch (\Exception $e) {
            return false;
        }
    }
}
