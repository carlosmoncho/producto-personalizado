<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;

class DataImportController extends Controller
{
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

    public function index()
    {
        // Get current table counts
        $tableCounts = [];
        foreach ($this->tablesToImport as $table) {
            try {
                if (Schema::hasTable($table)) {
                    $tableCounts[$table] = DB::table($table)->count();
                }
            } catch (\Exception $e) {
                $tableCounts[$table] = 'Error';
            }
        }

        return view('admin.data-import.index', compact('tableCounts'));
    }

    public function export()
    {
        $exportData = [];

        foreach ($this->tablesToImport as $table) {
            try {
                if (Schema::hasTable($table)) {
                    $count = DB::table($table)->count();
                    if ($count > 0) {
                        $data = DB::table($table)->get()->toArray();

                        // Convert to array and remove large image data
                        $exportData[$table] = array_map(function($item) {
                            $row = (array) $item;
                            unset($row['design_image']);
                            unset($row['preview_3d']);
                            return $row;
                        }, $data);
                    }
                }
            } catch (\Exception $e) {
                // Skip table on error
            }
        }

        $filename = 'database_export_' . date('Y-m-d_H-i-s') . '.json';

        return response()->json($exportData)
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Type', 'application/json');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:json,txt|max:51200', // Max 50MB
        ]);

        try {
            $content = file_get_contents($request->file('file')->getRealPath());
            $allData = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()->with('error', 'Error al parsear JSON: ' . json_last_error_msg());
            }

            $results = [];
            $driver = Schema::getConnection()->getDriverName();

            // For PostgreSQL: delete in reverse order to respect foreign keys
            // For MySQL: disable foreign key checks
            if ($driver === 'pgsql') {
                // Delete tables in reverse order (respecting FK constraints)
                $reverseTables = array_reverse($this->tablesToImport);
                foreach ($reverseTables as $table) {
                    try {
                        if (Schema::hasTable($table)) {
                            DB::table($table)->delete();
                        }
                    } catch (\Exception $e) {
                        // Some tables might not exist or have issues, continue
                    }
                }
            } else {
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                // Truncate tables
                foreach ($this->tablesToImport as $table) {
                    try {
                        if (Schema::hasTable($table)) {
                            DB::table($table)->truncate();
                        }
                    } catch (\Exception $e) {
                        // Continue on error
                    }
                }
            }

            // Process tables in order (respecting FK when inserting)
            foreach ($this->tablesToImport as $table) {
                if (!isset($allData[$table]) || empty($allData[$table])) {
                    continue;
                }

                try {
                    if (!Schema::hasTable($table)) {
                        $results[$table] = ['status' => 'error', 'message' => 'Tabla no existe'];
                        continue;
                    }

                    // Get existing columns
                    $columns = Schema::getColumnListing($table);

                    // Filter data to only include existing columns
                    $filteredData = array_map(function($row) use ($columns) {
                        return array_intersect_key($row, array_flip($columns));
                    }, $allData[$table]);

                    // Insert in chunks
                    $chunks = array_chunk($filteredData, 100);
                    $totalInserted = 0;

                    foreach ($chunks as $chunk) {
                        DB::table($table)->insert($chunk);
                        $totalInserted += count($chunk);
                    }

                    $results[$table] = ['status' => 'success', 'count' => $totalInserted];

                } catch (\Exception $e) {
                    $results[$table] = ['status' => 'error', 'message' => $e->getMessage()];
                }
            }

            // Re-enable foreign key checks (MySQL) and reset sequences (PostgreSQL)
            if ($driver === 'pgsql') {
                // Reset sequences for PostgreSQL
                foreach ($this->tablesToImport as $table) {
                    try {
                        if (Schema::hasTable($table) && Schema::hasColumn($table, 'id')) {
                            $maxId = DB::table($table)->max('id') ?? 0;
                            if ($maxId > 0) {
                                $sequence = "{$table}_id_seq";
                                DB::statement("SELECT setval('{$sequence}', ?, true)", [$maxId]);
                            }
                        }
                    } catch (\Exception $e) {
                        // Sequence might not exist
                    }
                }
            } else {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            }

            return back()->with('success', 'Importación completada')->with('results', $results);

        } catch (\Exception $e) {
            return back()->with('error', 'Error durante la importación: ' . $e->getMessage());
        }
    }
}
