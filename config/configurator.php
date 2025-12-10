<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Configurador de Productos - Configuración
    |--------------------------------------------------------------------------
    |
    | Configuración para el sistema de configuración de productos personaliz ados
    |
    */

    /**
     * Límites y restricciones
     */
    'limits' => [
        'max_attributes' => env('CONFIGURATOR_MAX_ATTRIBUTES', 100),
        'max_dependencies' => env('CONFIGURATOR_MAX_DEPENDENCIES', 500),
        'max_print_colors' => 10,
        'max_file_uploads' => 5,
    ],

    /**
     * Configuración de caché
     */
    'cache' => [
        'enabled' => env('CONFIGURATOR_CACHE_ENABLED', true),
        'ttl' => env('CONFIGURATOR_CACHE_TTL', 3600), // 1 hora por defecto
        'key_prefix' => 'configurator',
    ],

    /**
     * Configuración de precios
     */
    'pricing' => [
        'precision' => env('PRICE_CALCULATION_PRECISION', 4),
        'currency' => 'EUR',
        'decimal_separator' => ',',
        'thousand_separator' => '.',
    ],

    /**
     * Archivos 3D
     */
    '3d_models' => [
        'max_size' => env('MAX_3D_MODEL_SIZE', 20480), // KB (20MB por defecto)
        'allowed_formats' => explode(',', env('ALLOWED_3D_FORMATS', 'glb,gltf')),
        'storage_path' => 'public/3d-models',
    ],

    /**
     * Subida de archivos
     */
    'file_uploads' => [
        'enabled' => true,
        'max_size' => env('MAX_IMAGE_UPLOAD_SIZE', 2048), // KB
        'allowed_formats' => explode(',', env('ALLOWED_UPLOAD_FORMATS', 'pdf,ai,eps,svg,png,jpg')),
        'storage_path' => 'public/designs',
    ],

    /**
     * Imágenes de producto
     */
    'images' => [
        'max_count' => 10,
        'max_size' => env('MAX_IMAGE_UPLOAD_SIZE', 2048), // KB
        'allowed_formats' => explode(',', env('ALLOWED_IMAGE_FORMATS', 'jpeg,jpg,png,webp')),
        'storage_path' => 'public/products',
        'thumbnail_size' => [150, 150],
        'medium_size' => [500, 500],
        'large_size' => [1200, 1200],
    ],

    /**
     * Configuración de sesiones del configurador
     */
    'sessions' => [
        'lifetime' => 1440, // 24 horas en minutos
        'cleanup_after' => 7, // Días para limpiar sesiones expiradas
    ],

    /**
     * Validación y reglas de negocio
     */
    'validation' => [
        'require_all_attributes' => false,
        'allow_custom_quantities' => false,
        'validate_dependencies' => true,
    ],

    /**
     * Logging y auditoría
     */
    'logging' => [
        'enabled' => env('CONFIGURATOR_LOGGING_ENABLED', true),
        'log_price_calculations' => env('LOG_PRICE_CALCULATIONS', false),
        'log_configuration_saves' => true,
        'log_file_uploads' => true,
    ],

    /**
     * Rate limiting específico del configurador
     */
    'rate_limiting' => [
        'price_calculations_per_minute' => env('API_PRICE_RATE_LIMIT', 30),
        'configuration_saves_per_hour' => 50,
        'file_uploads_per_hour' => 20,
    ],

];
