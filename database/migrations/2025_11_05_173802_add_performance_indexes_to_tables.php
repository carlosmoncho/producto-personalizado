<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Añade índices para mejorar el rendimiento de consultas críticas
     */
    public function up(): void
    {
        // Helper para crear índice solo si no existe (compatible con SQLite y MySQL)
        $safeAddIndex = function($table, $columns, $indexName) {
            try {
                Schema::table($table, function (Blueprint $t) use ($columns, $indexName) {
                    $t->index($columns, $indexName);
                });
            } catch (\Exception $e) {
                // Silenciosamente ignorar errores de índices duplicados
                // Funciona tanto en MySQL ('Duplicate key name') como en SQLite ('index already exists')
                if (!str_contains($e->getMessage(), 'Duplicate key name') &&
                    !str_contains($e->getMessage(), 'already exists')) {
                    throw $e;
                }
            }
        };

        // Índices para tabla products
        $safeAddIndex('products', ['active', 'category_id'], 'idx_products_active_category');
        $safeAddIndex('products', ['active', 'subcategory_id'], 'idx_products_active_subcategory');
        $safeAddIndex('products', 'has_configurator', 'idx_products_has_configurator');
        $safeAddIndex('products', 'slug', 'idx_products_slug');

        // Índices para tabla product_configurations
        $safeAddIndex('product_configurations', ['session_id', 'expires_at'], 'idx_config_session_expires');
        $safeAddIndex('product_configurations', 'product_id', 'idx_config_product');
        $safeAddIndex('product_configurations', ['status', 'expires_at'], 'idx_config_status_expires');

        // Índices para tabla attribute_dependencies
        $safeAddIndex('attribute_dependencies', ['product_id', 'parent_attribute_id'], 'idx_dep_product_parent');
        $safeAddIndex('attribute_dependencies', 'priority', 'idx_dep_priority');
        $safeAddIndex('attribute_dependencies', 'condition_type', 'idx_dep_condition_type');

        // Índices para tabla product_attribute_values
        $safeAddIndex('product_attribute_values', 'attribute_group_id', 'idx_pav_group');
        $safeAddIndex('product_attribute_values', 'product_attribute_id', 'idx_pav_attribute');

        // Índices para tabla price_rules
        $safeAddIndex('price_rules', ['valid_from', 'valid_until'], 'idx_price_dates');

        // Índices para tabla product_variants
        $safeAddIndex('product_variants', 'stock_quantity', 'idx_variant_stock');
        $safeAddIndex('product_variants', 'track_inventory', 'idx_variant_track_inv');

        // Índices para tabla product_attributes
        $safeAddIndex('product_attributes', ['attribute_group_id', 'active'], 'idx_attr_group_active');
        $safeAddIndex('product_attributes', 'sort_order', 'idx_attr_sort');

        // Índices para tabla orders
        $safeAddIndex('orders', ['status', 'created_at'], 'idx_orders_status_date');

        // Solo si las columnas existen
        if (Schema::hasColumn('orders', 'customer_id')) {
            $safeAddIndex('orders', 'customer_id', 'idx_orders_customer');
        }
        if (Schema::hasColumn('orders', 'delivery_date')) {
            $safeAddIndex('orders', 'delivery_date', 'idx_orders_delivery');
        }

        // Índices para tabla categories
        $safeAddIndex('categories', 'slug', 'idx_categories_slug');

        // Índices para tabla subcategories
        $safeAddIndex('subcategories', 'slug', 'idx_subcat_slug');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Helper para eliminar índice solo si existe (compatible con SQLite y MySQL)
        $safeDropIndex = function($table, $indexName) {
            try {
                Schema::table($table, function (Blueprint $t) use ($indexName) {
                    $t->dropIndex($indexName);
                });
            } catch (\Exception $e) {
                // Silenciosamente ignorar errores si el índice no existe
                // Compatible con SQLite y MySQL
            }
        };

        // Eliminar índices en orden inverso
        $safeDropIndex('subcategories', 'idx_subcat_slug');
        $safeDropIndex('categories', 'idx_categories_slug');
        $safeDropIndex('orders', 'idx_orders_delivery');
        $safeDropIndex('orders', 'idx_orders_customer');
        $safeDropIndex('orders', 'idx_orders_status_date');
        $safeDropIndex('product_attributes', 'idx_attr_sort');
        $safeDropIndex('product_attributes', 'idx_attr_group_active');
        $safeDropIndex('product_variants', 'idx_variant_track_inv');
        $safeDropIndex('product_variants', 'idx_variant_stock');
        $safeDropIndex('price_rules', 'idx_price_dates');
        $safeDropIndex('product_attribute_values', 'idx_pav_attribute');
        $safeDropIndex('product_attribute_values', 'idx_pav_group');
        $safeDropIndex('attribute_dependencies', 'idx_dep_condition_type');
        $safeDropIndex('attribute_dependencies', 'idx_dep_priority');
        $safeDropIndex('attribute_dependencies', 'idx_dep_product_parent');
        $safeDropIndex('product_configurations', 'idx_config_status_expires');
        $safeDropIndex('product_configurations', 'idx_config_product');
        $safeDropIndex('product_configurations', 'idx_config_session_expires');
        $safeDropIndex('products', 'idx_products_slug');
        $safeDropIndex('products', 'idx_products_has_configurator');
        $safeDropIndex('products', 'idx_products_active_subcategory');
        $safeDropIndex('products', 'idx_products_active_category');
    }
};
