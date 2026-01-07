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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('shopify_draft_order_id')->nullable()->after('notes');
            $table->string('shopify_invoice_url', 500)->nullable()->after('shopify_draft_order_id');
            $table->timestamp('shopify_invoice_sent_at')->nullable()->after('shopify_invoice_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['shopify_draft_order_id', 'shopify_invoice_url', 'shopify_invoice_sent_at']);
        });
    }
};
