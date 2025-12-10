<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class ImportDataFromJson extends Command
{
    protected $signature = 'db:import-json {--path=storage/app/db-export} {--fresh : Clear tables before import}';
    protected $description = 'Import database tables from JSON files';

    // Tables to import (in order to respect foreign keys)
    private array $tablesToImport = [
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
        $fresh = $this->option('fresh');

        if (!File::exists($path)) {
            $this->error("Export path {$path} does not exist!");
            return Command::FAILURE;
        }

        $this->info("Importing database from JSON files in {$path}...\n");

        // Disable foreign key checks
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'pgsql') {
            DB::statement('SET session_replication_role = replica;');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }

        if ($fresh) {
            $this->warn("Clearing tables first...\n");
            foreach (array_reverse($this->tablesToImport) as $table) {
                try {
                    if (Schema::hasTable($table)) {
                        DB::table($table)->truncate();
                        $this->line("  Truncated {$table}");
                    }
                } catch (\Exception $e) {
                    $this->warn("  Could not truncate {$table}: " . $e->getMessage());
                }
            }
            $this->line("");
        }

        foreach ($this->tablesToImport as $table) {
            $filename = "{$path}/{$table}.json";

            if (!File::exists($filename)) {
                $this->line("  {$table}: no export file (skipped)");
                continue;
            }

            try {
                $data = json_decode(File::get($filename), true);

                if (empty($data)) {
                    $this->line("  {$table}: empty file (skipped)");
                    continue;
                }

                // Check if table exists
                if (!Schema::hasTable($table)) {
                    $this->warn("✗ {$table}: table does not exist");
                    continue;
                }

                // Get existing columns
                $columns = Schema::getColumnListing($table);

                // Filter data to only include existing columns
                $filteredData = array_map(function($row) use ($columns) {
                    return array_intersect_key($row, array_flip($columns));
                }, $data);

                // Insert in chunks
                $chunks = array_chunk($filteredData, 100);
                $totalInserted = 0;

                foreach ($chunks as $chunk) {
                    DB::table($table)->insert($chunk);
                    $totalInserted += count($chunk);
                }

                $this->info("✓ {$table}: {$totalInserted} records imported");

            } catch (\Exception $e) {
                $this->error("✗ {$table}: " . $e->getMessage());
            }
        }

        // Re-enable foreign key checks
        if ($driver === 'pgsql') {
            DB::statement('SET session_replication_role = DEFAULT;');

            // Reset sequences for PostgreSQL
            $this->info("\nResetting PostgreSQL sequences...");
            foreach ($this->tablesToImport as $table) {
                try {
                    if (Schema::hasTable($table) && Schema::hasColumn($table, 'id')) {
                        $maxId = DB::table($table)->max('id') ?? 0;
                        $sequence = "{$table}_id_seq";
                        DB::statement("SELECT setval('{$sequence}', ?, true)", [$maxId]);
                        $this->line("  Reset {$sequence} to {$maxId}");
                    }
                } catch (\Exception $e) {
                    // Sequence might not exist, that's OK
                }
            }
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        $this->info("\n✓ Import complete!");

        return Command::SUCCESS;
    }
}
