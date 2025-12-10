<?php

namespace App\Services\Export;

use Illuminate\Http\Response;
use Illuminate\Support\Collection;

/**
 * Servicio genérico para exportación de datos a CSV
 *
 * Elimina duplicación de código entre CustomerController y OrderController
 * Proporciona funcionalidad común de exportación con encoding UTF-8 y BOM
 *
 * @package App\Services\Export
 */
class CsvExportService
{
    /**
     * Delimitador CSV (punto y coma para compatibilidad con Excel en español)
     */
    private const CSV_DELIMITER = ';';

    /**
     * BOM UTF-8 para correcta visualización de caracteres especiales en Excel
     */
    private const UTF8_BOM = "\xEF\xBB\xBF";

    /**
     * Exportar colección de datos a CSV
     *
     * @param Collection $data Colección de datos a exportar
     * @param array $headers Array de cabeceras para el CSV
     * @param callable $rowMapper Función que mapea cada item a un array de valores
     * @param string $filenamePrefix Prefijo para el nombre del archivo (ej: 'clientes', 'pedidos')
     * @return Response
     * @throws \Exception
     */
    public function export(
        Collection $data,
        array $headers,
        callable $rowMapper,
        string $filenamePrefix = 'export'
    ): Response {
        try {
            // Generar nombre de archivo con timestamp
            $filename = $this->generateFilename($filenamePrefix);

            // Crear contenido CSV
            $csvContent = $this->generateCsvContent($data, $headers, $rowMapper);

            // Retornar respuesta HTTP con headers apropiados
            return $this->createCsvResponse($csvContent, $filename);

        } catch (\Exception $e) {
            \Log::error('Error in CsvExportService::export', [
                'prefix' => $filenamePrefix,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Generar contenido CSV en memoria
     *
     * @param Collection $data
     * @param array $headers
     * @param callable $rowMapper
     * @return string
     */
    private function generateCsvContent(Collection $data, array $headers, callable $rowMapper): string
    {
        // Usar php://temp para mantener CSV en memoria (más eficiente que php://memory para archivos grandes)
        $handle = fopen('php://temp', 'r+');

        if ($handle === false) {
            throw new \RuntimeException('No se pudo abrir el stream temporal para CSV');
        }

        try {
            // Agregar BOM UTF-8 para correcta visualización en Excel
            fwrite($handle, self::UTF8_BOM);

            // Escribir cabeceras
            fputcsv($handle, $headers, self::CSV_DELIMITER);

            // Escribir datos
            foreach ($data as $item) {
                $row = $rowMapper($item);

                // Validar que el mapper retornó un array
                if (!is_array($row)) {
                    throw new \InvalidArgumentException(
                        'El rowMapper debe retornar un array. Recibido: ' . gettype($row)
                    );
                }

                fputcsv($handle, $row, self::CSV_DELIMITER);
            }

            // Obtener contenido completo
            rewind($handle);
            $content = stream_get_contents($handle);

            if ($content === false) {
                throw new \RuntimeException('Error al leer el contenido del CSV');
            }

            return $content;

        } finally {
            // Asegurar que el handle se cierra incluso si hay error
            fclose($handle);
        }
    }

    /**
     * Crear respuesta HTTP con headers apropiados para descarga de CSV
     *
     * @param string $content Contenido del CSV
     * @param string $filename Nombre del archivo
     * @return Response
     */
    private function createCsvResponse(string $content, string $filename): Response
    {
        return response($content, 200)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Length', strlen($content))
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    /**
     * Generar nombre de archivo con timestamp
     *
     * @param string $prefix
     * @return string
     */
    private function generateFilename(string $prefix): string
    {
        // Sanitizar el prefix (remover caracteres no permitidos en nombres de archivo)
        $safePrefix = preg_replace('/[^a-zA-Z0-9_-]/', '_', $prefix);

        // Formato: prefix_YYYY-MM-DD_HH-ii-ss.csv
        return $safePrefix . '_' . date('Y-m-d_H-i-s') . '.csv';
    }

    /**
     * Helper: Formatear número decimal para CSV (español)
     *
     * @param float|int|null $number
     * @param int $decimals
     * @return string
     */
    public static function formatNumber($number, int $decimals = 2): string
    {
        if ($number === null) {
            return '';
        }

        return number_format((float)$number, $decimals, ',', '.');
    }

    /**
     * Helper: Formatear fecha para CSV
     *
     * @param \Carbon\Carbon|\DateTime|null $date
     * @param string $format
     * @return string
     */
    public static function formatDate($date, string $format = 'd/m/Y H:i'): string
    {
        if ($date === null) {
            return '';
        }

        if ($date instanceof \Carbon\Carbon || $date instanceof \DateTime) {
            return $date->format($format);
        }

        return '';
    }

    /**
     * Helper: Formatear booleano para CSV
     *
     * @param bool|null $value
     * @param string $trueLabel
     * @param string $falseLabel
     * @return string
     */
    public static function formatBoolean(?bool $value, string $trueLabel = 'Sí', string $falseLabel = 'No'): string
    {
        if ($value === null) {
            return '';
        }

        return $value ? $trueLabel : $falseLabel;
    }
}
