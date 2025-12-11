<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateStorageToS3 extends Command
{
    protected $signature = 'storage:migrate-to-s3
                            {--dry-run : Show what would be migrated without actually migrating}
                            {--directory= : Only migrate a specific directory (e.g., products, categories, 3d-models)}';

    protected $description = 'Migrate files from local storage to S3';

    private array $directories = [
        'products',
        'categories',
        'subcategories',
        '3d-models',
        'designs',
        'orders',
    ];

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $specificDir = $this->option('directory');

        if ($specificDir) {
            $this->directories = [$specificDir];
        }

        $this->info($dryRun ? '🔍 Modo simulación (dry-run)' : '🚀 Iniciando migración a S3');
        $this->newLine();

        $totalFiles = 0;
        $migratedFiles = 0;
        $failedFiles = 0;

        foreach ($this->directories as $directory) {
            $this->info("📁 Procesando directorio: {$directory}");

            $files = Storage::disk('public')->allFiles($directory);

            if (empty($files)) {
                $this->warn("   ⚠️  No hay archivos en {$directory}");
                continue;
            }

            $bar = $this->output->createProgressBar(count($files));
            $bar->start();

            foreach ($files as $file) {
                $totalFiles++;

                try {
                    if ($dryRun) {
                        // Solo mostrar qué se migraría
                        $bar->advance();
                        $migratedFiles++;
                        continue;
                    }

                    // Verificar si ya existe en S3
                    if (Storage::disk('s3')->exists($file)) {
                        $bar->advance();
                        $migratedFiles++;
                        continue;
                    }

                    // Leer archivo desde local
                    $contents = Storage::disk('public')->get($file);

                    if ($contents === null) {
                        $this->error("   ❌ No se pudo leer: {$file}");
                        $failedFiles++;
                        $bar->advance();
                        continue;
                    }

                    // Subir a S3 (sin ACL, usamos bucket policy para acceso público)
                    $uploaded = Storage::disk('s3')->put($file, $contents);

                    if ($uploaded) {
                        $migratedFiles++;
                    } else {
                        $this->error("   ❌ Error al subir: {$file}");
                        $failedFiles++;
                    }

                } catch (\Exception $e) {
                    $this->error("   ❌ Error con {$file}: {$e->getMessage()}");
                    $failedFiles++;
                }

                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
        }

        $this->newLine();
        $this->info('═══════════════════════════════════════');
        $this->info("📊 Resumen de migración:");
        $this->info("   Total archivos: {$totalFiles}");
        $this->info("   ✅ Migrados: {$migratedFiles}");

        if ($failedFiles > 0) {
            $this->error("   ❌ Fallidos: {$failedFiles}");
        }

        if ($dryRun) {
            $this->newLine();
            $this->warn('⚠️  Esto fue una simulación. Ejecuta sin --dry-run para migrar realmente.');
        }

        return $failedFiles > 0 ? 1 : 0;
    }
}
