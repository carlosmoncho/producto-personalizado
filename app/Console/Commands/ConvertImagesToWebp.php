<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ConvertImagesToWebp extends Command
{
    protected $signature = 'images:convert-webp {--dry-run : Solo mostrar qué se convertiría}';
    protected $description = 'Convierte imágenes PNG/JPG grandes a WebP para mejor rendimiento';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $manager = new ImageManager(new Driver());

        $this->info('Buscando imágenes grandes en storage/app/public/products...');

        $files = Storage::disk('public')->files('products');
        $converted = 0;
        $savedBytes = 0;

        foreach ($files as $file) {
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

            // Solo procesar PNG y JPG
            if (!in_array($extension, ['png', 'jpg', 'jpeg'])) {
                continue;
            }

            $fullPath = Storage::disk('public')->path($file);
            $fileSize = filesize($fullPath);

            // Solo convertir imágenes mayores a 100KB
            if ($fileSize < 100 * 1024) {
                continue;
            }

            $fileSizeKb = round($fileSize / 1024);
            $webpPath = preg_replace('/\.(png|jpg|jpeg)$/i', '.webp', $file);
            $webpFullPath = Storage::disk('public')->path($webpPath);

            // Si ya existe el WebP, saltar
            if (Storage::disk('public')->exists($webpPath)) {
                $this->line("  [SKIP] {$file} - WebP ya existe");
                continue;
            }

            if ($dryRun) {
                $this->line("  [DRY] {$file} ({$fileSizeKb}KB) -> {$webpPath}");
                $converted++;
                continue;
            }

            try {
                // Cargar y convertir a WebP
                $image = $manager->read($fullPath);
                $image->toWebp(85)->save($webpFullPath);

                $newSize = filesize($webpFullPath);
                $newSizeKb = round($newSize / 1024);
                $saved = $fileSize - $newSize;
                $savedKb = round($saved / 1024);
                $percentage = round(($saved / $fileSize) * 100);

                $savedBytes += $saved;
                $converted++;

                $this->info("  [OK] {$file} ({$fileSizeKb}KB) -> {$webpPath} ({$newSizeKb}KB) - Ahorrado: {$savedKb}KB ({$percentage}%)");

            } catch (\Exception $e) {
                $this->error("  [ERROR] {$file}: " . $e->getMessage());
            }
        }

        $totalSavedMb = round($savedBytes / 1024 / 1024, 2);

        $this->newLine();
        if ($dryRun) {
            $this->info("Modo dry-run: {$converted} imágenes se convertirían");
        } else {
            $this->info("Convertidas: {$converted} imágenes");
            $this->info("Espacio ahorrado: {$totalSavedMb}MB");
        }

        return Command::SUCCESS;
    }
}
