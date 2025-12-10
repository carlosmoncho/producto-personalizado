<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ExportDataToJson extends Command
{
    protected $signature = 'db:export-json {--path=storage/app/db-export}';
    protected $description = 'Export all database tables to JSON files for migration';

    // Tables to export (in order to respect foreign keys)
    private array $tablesToExport = [
        'users',
        'categories',
        'subcategories',
        'attribute_groups',
        'product_attributes',
        'product_attribute_values',
        'printing_systems',
        'products',
        'product_printing_system',
        'attribute_dependencies',
        'price_rules',
        'available_colors',
        'available_materials',
        'available_print_colors',
        'available_sizes',
        'customers',
        'addresses',
        'orders',
        'order_items',
        'product_configurations',
        'product_pricing',
        'product_variants',
        'permissions',
        'roles',
        'role_has_permissions',
        'model_has_roles',
        'model_has_permissions',
    ];

    public function handle()
    {
        $path = $this->option('path');

        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }

        $this->info("Exporting database to JSON files in {$path}...\n");

        foreach ($this->tablesToExport as $table) {
            try {
                $count = DB::table($table)->count();

                if ($count > 0) {
                    $data = DB::table($table)->get()->toArray();

                    // Convert to array of arrays (not objects)
                    // Exclude large base64 image columns to keep export small
                    $data = array_map(function($item) {
                        $row = (array) $item;
                        // Remove large image data columns
                        unset($row['design_image']);
                        unset($row['preview_3d']);
                        return $row;
                    }, $data);

                    $filename = "{$path}/{$table}.json";
                    File::put($filename, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

                    $this->info("✓ {$table}: {$count} records exported");
                } else {
                    $this->line("  {$table}: empty (skipped)");
                }
            } catch (\Exception $e) {
                $this->warn("✗ {$table}: " . $e->getMessage());
            }
        }

        $this->info("\n✓ Export complete! Files saved to {$path}");

        return Command::SUCCESS;
    }
}
