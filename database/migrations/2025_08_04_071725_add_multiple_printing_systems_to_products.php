<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Crear tabla pivote para la relación muchos a muchos
        Schema::create('product_printing_system', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('printing_system_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['product_id', 'printing_system_id']);
            $table->index('product_id');
            $table->index('printing_system_id');
        });

        // Migrar datos existentes si hay
        if (Schema::hasColumn('products', 'printing_system_id')) {
            $products = DB::table('products')
                ->whereNotNull('printing_system_id')
                ->orderBy('id')
                ->get();
                
            foreach ($products as $product) {
                DB::table('product_printing_system')->insert([
                    'product_id' => $product->id,
                    'printing_system_id' => $product->printing_system_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Eliminar la columna antigua después de migrar los datos
            Schema::table('products', function (Blueprint $table) {
                $table->dropForeign(['printing_system_id']);
                $table->dropColumn('printing_system_id');
            });
        }
    }

    public function down(): void
    {
        // Agregar de vuelta la columna singular
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('printing_system_id')->nullable()->after('sizes');
            $table->foreign('printing_system_id')->references('id')->on('printing_systems');
        });

        // Migrar el primer sistema de impresión de cada producto
        $records = DB::table('product_printing_system')
            ->select('product_id', DB::raw('MIN(printing_system_id) as printing_system_id'))
            ->groupBy('product_id')
            ->orderBy('product_id')
            ->get();
            
        foreach ($records as $record) {
            DB::table('products')
                ->where('id', $record->product_id)
                ->update(['printing_system_id' => $record->printing_system_id]);
        }

        // Eliminar la tabla pivote
        Schema::dropIfExists('product_printing_system');
    }
};