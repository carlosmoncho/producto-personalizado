<?php

namespace App\Services\Cache;

use App\Models\Category;
use App\Models\Subcategory;
use App\Models\PrintingSystem;
use App\Models\ProductAttribute;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Servicio de caché para datos del catálogo
 *
 * Cachea datos que no cambian frecuentemente (categorías, subcategorías, etc.)
 * para mejorar el performance y reducir queries a la base de datos.
 *
 * @package App\Services\Cache
 */
class CatalogCacheService
{
    /**
     * TTL por defecto: 1 hora (3600 segundos)
     */
    private const DEFAULT_TTL = 3600;

    /**
     * TTL para datos muy estables: 24 horas (86400 segundos)
     */
    private const LONG_TTL = 86400;

    /**
     * Prefijo para keys de caché
     */
    private const CACHE_PREFIX = 'catalog:';

    /**
     * Obtener todas las categorías activas (con caché)
     *
     * @param bool $withProducts Incluir productos relacionados
     * @return Collection
     */
    public function getActiveCategories(bool $withProducts = false): Collection
    {
        $cacheKey = self::CACHE_PREFIX . 'categories:active';

        if ($withProducts) {
            $cacheKey .= ':with_products';
        }

        return Cache::remember($cacheKey, self::LONG_TTL, function () use ($withProducts) {
            $query = Category::where('active', true)
                ->orderBy('sort_order')
                ->orderBy('name');

            if ($withProducts) {
                $query->with(['products' => function ($q) {
                    $q->where('active', true)->orderBy('sort_order');
                }]);
            }

            return $query->get();
        });
    }

    /**
     * Obtener todas las subcategorías activas (con caché)
     *
     * @param int|null $categoryId Filtrar por categoría específica
     * @return Collection
     */
    public function getActiveSubcategories(?int $categoryId = null): Collection
    {
        $cacheKey = self::CACHE_PREFIX . 'subcategories:active';

        if ($categoryId) {
            $cacheKey .= ':category_' . $categoryId;
        }

        return Cache::remember($cacheKey, self::LONG_TTL, function () use ($categoryId) {
            $query = Subcategory::where('active', true)
                ->orderBy('sort_order')
                ->orderBy('name');

            if ($categoryId) {
                $query->where('category_id', $categoryId);
            }

            return $query->get();
        });
    }

    /**
     * Obtener todos los sistemas de impresión activos (con caché)
     *
     * @return Collection
     */
    public function getActivePrintingSystems(): Collection
    {
        $cacheKey = self::CACHE_PREFIX . 'printing_systems:active';

        return Cache::remember($cacheKey, self::LONG_TTL, function () {
            return PrintingSystem::where('active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Obtener atributos de producto por tipo (con caché)
     *
     * @param string $type Tipo de atributo (color, material, size, ink, etc.)
     * @return Collection
     */
    public function getAttributesByType(string $type): Collection
    {
        $cacheKey = self::CACHE_PREFIX . 'attributes:type_' . $type;

        return Cache::remember($cacheKey, self::DEFAULT_TTL, function () use ($type) {
            return ProductAttribute::where('type', $type)
                ->where('active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Obtener una categoría por slug (con caché)
     *
     * @param string $slug
     * @return Category|null
     */
    public function getCategoryBySlug(string $slug): ?Category
    {
        $cacheKey = self::CACHE_PREFIX . 'category:slug_' . $slug;

        return Cache::remember($cacheKey, self::LONG_TTL, function () use ($slug) {
            return Category::where('slug', $slug)
                ->where('active', true)
                ->first();
        });
    }

    /**
     * Obtener una subcategoría por slug (con caché)
     *
     * @param string $slug
     * @return Subcategory|null
     */
    public function getSubcategoryBySlug(string $slug): ?Subcategory
    {
        $cacheKey = self::CACHE_PREFIX . 'subcategory:slug_' . $slug;

        return Cache::remember($cacheKey, self::LONG_TTL, function () use ($slug) {
            return Subcategory::where('slug', $slug)
                ->where('active', true)
                ->first();
        });
    }

    /**
     * Obtener conteo de productos por categoría (con caché)
     *
     * @return array Array con category_id => count
     */
    public function getProductCountsByCategory(): array
    {
        $cacheKey = self::CACHE_PREFIX . 'product_counts:by_category';

        return Cache::remember($cacheKey, self::DEFAULT_TTL, function () {
            return \DB::table('products')
                ->select('category_id', \DB::raw('COUNT(*) as count'))
                ->where('active', true)
                ->groupBy('category_id')
                ->pluck('count', 'category_id')
                ->toArray();
        });
    }

    // ==================== INVALIDACIÓN DE CACHÉ ====================

    /**
     * Invalidar caché de categorías
     *
     * Llamar cuando se crea, actualiza o elimina una categoría
     */
    public function invalidateCategoriesCache(): void
    {
        Cache::forget(self::CACHE_PREFIX . 'categories:active');
        Cache::forget(self::CACHE_PREFIX . 'categories:active:with_products');
        Cache::forget(self::CACHE_PREFIX . 'product_counts:by_category');

        // Invalidar también slugs individuales (pattern-based)
        $this->forgetByPattern(self::CACHE_PREFIX . 'category:slug_*');

        \Log::info('Categories cache invalidated');
    }

    /**
     * Invalidar caché de subcategorías
     *
     * Llamar cuando se crea, actualiza o elimina una subcategoría
     *
     * @param int|null $categoryId Si se proporciona, solo invalida subcategorías de esa categoría
     */
    public function invalidateSubcategoriesCache(?int $categoryId = null): void
    {
        if ($categoryId) {
            Cache::forget(self::CACHE_PREFIX . 'subcategories:active:category_' . $categoryId);
        } else {
            // Invalidar todas las subcategorías
            Cache::forget(self::CACHE_PREFIX . 'subcategories:active');
            $this->forgetByPattern(self::CACHE_PREFIX . 'subcategories:active:category_*');
        }

        // Invalidar también slugs individuales
        $this->forgetByPattern(self::CACHE_PREFIX . 'subcategory:slug_*');

        \Log::info('Subcategories cache invalidated', ['category_id' => $categoryId]);
    }

    /**
     * Invalidar caché de sistemas de impresión
     *
     * Llamar cuando se crea, actualiza o elimina un sistema de impresión
     */
    public function invalidatePrintingSystemsCache(): void
    {
        Cache::forget(self::CACHE_PREFIX . 'printing_systems:active');

        \Log::info('Printing systems cache invalidated');
    }

    /**
     * Invalidar caché de atributos
     *
     * @param string|null $type Si se proporciona, solo invalida atributos de ese tipo
     */
    public function invalidateAttributesCache(?string $type = null): void
    {
        if ($type) {
            Cache::forget(self::CACHE_PREFIX . 'attributes:type_' . $type);
        } else {
            // Invalidar todos los tipos
            $types = ['color', 'material', 'size', 'ink', 'quantity', 'system', 'finish'];
            foreach ($types as $attrType) {
                Cache::forget(self::CACHE_PREFIX . 'attributes:type_' . $attrType);
            }
        }

        \Log::info('Attributes cache invalidated', ['type' => $type]);
    }

    /**
     * Invalidar caché de productos
     *
     * Llamar cuando se crea, actualiza o elimina un producto
     */
    public function invalidateProductsCache(): void
    {
        Cache::forget(self::CACHE_PREFIX . 'categories:active:with_products');
        Cache::forget(self::CACHE_PREFIX . 'product_counts:by_category');

        \Log::info('Products cache invalidated');
    }

    /**
     * Invalidar TODA la caché del catálogo
     *
     * Usar con precaución - solo en casos de actualizaciones masivas
     */
    public function invalidateAllCatalogCache(): void
    {
        $this->invalidateCategoriesCache();
        $this->invalidateSubcategoriesCache();
        $this->invalidatePrintingSystemsCache();
        $this->invalidateAttributesCache();
        $this->invalidateProductsCache();

        \Log::warning('ALL catalog cache invalidated');
    }

    // ==================== HELPERS ====================

    /**
     * Limpiar caché por patrón (simulado - Cache::forget no soporta patterns nativamente)
     *
     * Nota: Esto es una aproximación. Para producción con Redis, usar:
     * Redis::keys($pattern) + Redis::del()
     *
     * @param string $pattern
     */
    private function forgetByPattern(string $pattern): void
    {
        // Esta implementación depende del driver de caché
        // Para Redis:
        if (config('cache.default') === 'redis') {
            $redis = Cache::getStore()->getRedis();
            $keys = $redis->keys($pattern);

            if (!empty($keys)) {
                $redis->del($keys);
            }
        }

        // Para file/database cache, no hay forma eficiente de hacerlo
        // Se debe invalidar key por key
    }

    /**
     * Obtener estadísticas de caché (para debugging)
     *
     * @return array
     */
    public function getCacheStats(): array
    {
        $keys = [
            'categories:active',
            'categories:active:with_products',
            'subcategories:active',
            'printing_systems:active',
            'product_counts:by_category',
        ];

        $stats = [];
        foreach ($keys as $key) {
            $fullKey = self::CACHE_PREFIX . $key;
            $stats[$key] = Cache::has($fullKey) ? 'HIT' : 'MISS';
        }

        return $stats;
    }

    /**
     * Warming: Pre-cargar caché con datos más usados
     *
     * Llamar después de deploy o invalidación masiva
     */
    public function warmCache(): void
    {
        $this->getActiveCategories();
        $this->getActiveCategories(true);
        $this->getActiveSubcategories();
        $this->getActivePrintingSystems();
        $this->getProductCountsByCategory();

        // Atributos más comunes
        $this->getAttributesByType('color');
        $this->getAttributesByType('material');
        $this->getAttributesByType('size');

        \Log::info('Catalog cache warmed up');
    }
}
