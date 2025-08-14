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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('company')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->default('España');
            $table->string('tax_id')->nullable(); // NIF/CIF
            $table->text('notes')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamp('last_order_at')->nullable();
            $table->decimal('total_orders_amount', 10, 2)->default(0);
            $table->integer('total_orders_count')->default(0);
            $table->timestamps();
            
            $table->index(['email', 'active']);
            $table->index(['name', 'active']);
            $table->index('last_order_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
