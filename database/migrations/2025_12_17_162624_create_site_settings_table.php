<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, file, json, boolean
            $table->string('group')->default('general'); // general, 3d, email, etc.
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insertar configuración inicial del HDRI
        DB::table('site_settings')->insert([
            'key' => 'hdri_environment',
            'value' => null,
            'type' => 'file',
            'group' => '3d',
            'description' => 'Archivo HDRI para iluminación de modelos 3D',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
