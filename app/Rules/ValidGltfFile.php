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
        if (!$value instanceof UploadedFile) {
            $fail('El archivo debe ser un archivo válido.');
            return;
        }

        // Verificar extensión
        $extension = strtolower($value->getClientOriginalExtension());
        if (!in_array($extension, ['glb', 'gltf'])) {
            $fail('El archivo debe tener extensión .glb o .gltf');
            return;
        }

        // Verificar tamaño razonable (máximo 20MB)
        if ($value->getSize() > 20 * 1024 * 1024) {
            $fail('El archivo 3D no debe superar los 20MB.');
            return;
        }

        // Para archivos GLB, verificar magic bytes
        if ($extension === 'glb') {
            if (!$this->validateGlbMagicBytes($value)) {
                $fail('El archivo GLB no tiene una firma válida. Puede estar corrupto o no ser un archivo GLB real.');
                return;
            }
        }

        // Para archivos GLTF, verificar que sea JSON válido
        if ($extension === 'gltf') {
            if (!$this->validateGltfJson($value)) {
                $fail('El archivo GLTF no contiene JSON válido.');
                return;
            }
        }
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
