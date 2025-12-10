<?php

namespace App\Services\Attribute;

use App\Models\AttributeGroup;
use App\Models\ProductAttribute;
use App\Models\AttributeDependency;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Servicio de lógica de negocio para atributos
 *
 * Centraliza operaciones comunes como:
 * - Preparación de datos de atributos y grupos
 * - Procesamiento de metadata complejo
 * - Validaciones de dependencias
 * - Detección de dependencias circulares y conflictos
 * - Generación de slugs
 *
 * @package App\Services\Attribute
 */
class AttributeService
{
    /**
     * Tipos de atributos disponibles con sus etiquetas
     */
    private const ATTRIBUTE_TYPES = [
        ProductAttribute::TYPE_COLOR => 'Colores',
        ProductAttribute::TYPE_MATERIAL => 'Materiales',
        ProductAttribute::TYPE_SIZE => 'Tamaños/Dimensiones',
        ProductAttribute::TYPE_INK => 'Tintas',
        ProductAttribute::TYPE_INK_COLOR => 'Colores Tintas',
        ProductAttribute::TYPE_CLICHE => 'Cliché',
        ProductAttribute::TYPE_QUANTITY => 'Cantidades',
        ProductAttribute::TYPE_SYSTEM => 'Sistemas de Impresión',
    ];

    /**
     * Tipos de condiciones para dependencias
     */
    private const CONDITION_TYPES = [
        'allows' => 'Permite',
        'blocks' => 'Bloquea',
        'requires' => 'Requiere',
        'sets_price' => 'Modifica Precio',
        'price_modifier' => 'Modificador de Precio Individual'
    ];

    /**
     * Preparar datos de un grupo de atributos desde el request
     *
     * @param Request $request
     * @param AttributeGroup|null $existingGroup Grupo existente (para updates)
     * @return array Datos preparados
     */
    public function prepareGroupData(Request $request, ?AttributeGroup $existingGroup = null): array
    {
        $data = [
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type,
            'sort_order' => $request->sort_order ?? ($existingGroup->sort_order ?? 0),
            'is_required' => $request->boolean('is_required'),
            'allow_multiple' => $request->boolean('allow_multiple'),
            'affects_price' => $request->boolean('affects_price'),
            'affects_stock' => $request->boolean('affects_stock'),
            'show_in_filter' => $request->boolean('show_in_filter', !$existingGroup || $existingGroup->show_in_filter),
            'active' => $request->boolean('active', !$existingGroup || $existingGroup->active),
        ];

        // Generar slug si no se proporciona
        if (empty($request->slug)) {
            $data['slug'] = $this->generateGroupSlug($request->name, $existingGroup?->id);
        } else {
            $data['slug'] = $request->slug;
        }

        return $data;
    }

    /**
     * Generar slug único para un grupo
     *
     * @param string $name Nombre del grupo
     * @param int|null $excludeId ID a excluir (para updates)
     * @return string Slug único
     */
    public function generateGroupSlug(string $name, ?int $excludeId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while ($this->groupSlugExists($slug, $excludeId)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Verificar si un slug de grupo existe
     *
     * @param string $slug
     * @param int|null $excludeId
     * @return bool
     */
    private function groupSlugExists(string $slug, ?int $excludeId = null): bool
    {
        $query = AttributeGroup::where('slug', $slug);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Validar si un grupo puede ser eliminado
     *
     * @param AttributeGroup $group
     * @return array ['can_delete' => bool, 'reason' => string|null]
     */
    public function canDeleteGroup(AttributeGroup $group): array
    {
        $attributesCount = $group->attributes()->count();

        if ($attributesCount > 0) {
            return [
                'can_delete' => false,
                'reason' => "El grupo tiene {$attributesCount} atributo(s) asociado(s)",
                'details' => [
                    'attributes_count' => $attributesCount
                ]
            ];
        }

        return [
            'can_delete' => true,
            'reason' => null,
            'details' => []
        ];
    }

    /**
     * Preparar metadata de un atributo según su tipo
     *
     * Este método centraliza toda la lógica compleja de metadata
     * que estaba duplicada en store() y update()
     *
     * @param Request $request
     * @param string $type Tipo de atributo
     * @param array $existingMetadata Metadata existente (para updates)
     * @return array Metadata preparado
     */
    public function prepareAttributeMetadata(Request $request, string $type, array $existingMetadata = []): array
    {
        $metadata = $existingMetadata;

        // Certificaciones (común para varios tipos)
        if ($request->filled('certifications')) {
            $certifications = array_map('trim', explode(',', $request->certifications));
            $metadata['certifications'] = array_filter($certifications);
        }

        // Metadata específico por tipo
        switch ($type) {
            case ProductAttribute::TYPE_COLOR:
                $metadata = $this->prepareColorMetadata($request, $metadata);
                break;

            case ProductAttribute::TYPE_MATERIAL:
                $metadata = $this->prepareMaterialMetadata($request, $metadata);
                break;

            case ProductAttribute::TYPE_INK:
                $metadata = $this->prepareInkMetadata($request, $metadata);
                break;

            case ProductAttribute::TYPE_INK_COLOR:
                $metadata = $this->prepareInkColorMetadata($request, $metadata);
                break;

            case ProductAttribute::TYPE_SIZE:
                $metadata = $this->prepareSizeMetadata($request, $metadata);
                break;

            case ProductAttribute::TYPE_QUANTITY:
                $metadata = $this->prepareQuantityMetadata($request, $metadata);
                break;

            case ProductAttribute::TYPE_SYSTEM:
                $metadata = $this->prepareSystemMetadata($request, $metadata);
                break;

            case ProductAttribute::TYPE_CLICHE:
                $metadata = $this->prepareClicheMetadata($request, $metadata);
                break;
        }

        return $metadata;
    }

    /**
     * Preparar metadata para atributos de color
     */
    private function prepareColorMetadata(Request $request, array $metadata): array
    {
        if ($request->hex_code) {
            $metadata['luminosity'] = $this->calculateLuminosity($request->hex_code);
        }

        if ($request->filled('color_family')) {
            $metadata['color_family'] = $request->color_family;
        }

        if ($request->filled('finish_type')) {
            $metadata['finish_type'] = $request->finish_type;
        }

        return $metadata;
    }

    /**
     * Preparar metadata para atributos de material
     */
    private function prepareMaterialMetadata(Request $request, array $metadata): array
    {
        if ($request->filled('material_type')) {
            $metadata['material_type'] = $request->material_type;
        }

        if ($request->filled('thickness')) {
            $metadata['thickness'] = (float)$request->thickness;
        }

        if ($request->filled('thickness_unit')) {
            $metadata['thickness_unit'] = $request->thickness_unit;
        }

        if ($request->filled('surface_finish')) {
            $metadata['surface_finish'] = $request->surface_finish;
        }

        return $metadata;
    }

    /**
     * Preparar metadata para atributos de tinta
     */
    private function prepareInkMetadata(Request $request, array $metadata): array
    {
        if ($request->hex_code) {
            $metadata['luminosity'] = $this->calculateLuminosity($request->hex_code);
        }

        if ($request->filled('ink_type')) {
            $metadata['ink_type'] = $request->ink_type;
        }

        if ($request->filled('opacity')) {
            $metadata['opacity'] = $request->opacity;
        }

        if ($request->filled('durability')) {
            $metadata['durability'] = $request->durability;
        }

        $metadata['is_metallic'] = $request->boolean('is_metallic');
        $metadata['is_fluorescent'] = $request->boolean('is_fluorescent');

        return $metadata;
    }

    /**
     * Preparar metadata para atributos de color de tinta
     */
    private function prepareInkColorMetadata(Request $request, array $metadata): array
    {
        if ($request->hex_code) {
            $metadata['luminosity'] = $this->calculateLuminosity($request->hex_code);
        }

        if ($request->filled('color_family')) {
            $metadata['color_family'] = $request->color_family;
        }

        if ($request->filled('finish_type')) {
            $metadata['finish_type'] = $request->finish_type;
        }

        $metadata['is_metallic'] = $request->boolean('is_metallic');
        $metadata['is_fluorescent'] = $request->boolean('is_fluorescent');

        return $metadata;
    }

    /**
     * Preparar metadata para atributos de tamaño
     */
    private function prepareSizeMetadata(Request $request, array $metadata): array
    {
        if ($request->filled('width')) {
            $metadata['width'] = (float)$request->width;
        }

        if ($request->filled('width_unit')) {
            $metadata['width_unit'] = $request->width_unit;
        }

        if ($request->filled('height')) {
            $metadata['height'] = (float)$request->height;
        }

        if ($request->filled('height_unit')) {
            $metadata['height_unit'] = $request->height_unit;
        }

        if ($request->filled('depth')) {
            $metadata['depth'] = (float)$request->depth;
        }

        if ($request->filled('depth_unit')) {
            $metadata['depth_unit'] = $request->depth_unit;
        }

        if ($request->filled('size_category')) {
            $metadata['size_category'] = $request->size_category;
        }

        if ($request->filled('print_area')) {
            $metadata['print_area'] = (int)$request->print_area;
        }

        return $metadata;
    }

    /**
     * Preparar metadata para atributos de cantidad
     */
    private function prepareQuantityMetadata(Request $request, array $metadata): array
    {
        if ($request->filled('quantity_value')) {
            $metadata['quantity_value'] = (int)$request->quantity_value;
        }

        if ($request->filled('packaging')) {
            $metadata['packaging'] = $request->packaging;
        }

        if ($request->filled('unit_price')) {
            $metadata['unit_price'] = (float)$request->unit_price;
        }

        if ($request->filled('min_quantity')) {
            $metadata['min_quantity'] = (int)$request->min_quantity;
        }

        if ($request->price_percentage) {
            $metadata['discount_percentage'] = abs((float)$request->price_percentage);
        }

        return $metadata;
    }

    /**
     * Preparar metadata para atributos de sistema
     */
    private function prepareSystemMetadata(Request $request, array $metadata): array
    {
        if ($request->filled('system_type')) {
            $metadata['system_type'] = $request->system_type;
        }

        if ($request->filled('max_colors')) {
            $metadata['max_colors'] = (int)$request->max_colors;
        }

        if ($request->filled('resolution')) {
            $metadata['resolution'] = $request->resolution;
        }

        if ($request->filled('production_speed')) {
            $metadata['production_speed'] = $request->production_speed;
        }

        return $metadata;
    }

    /**
     * Preparar metadata para atributos de cliché
     */
    private function prepareClicheMetadata(Request $request, array $metadata): array
    {
        if ($request->filled('cliche_type')) {
            $metadata['cliche_type'] = $request->cliche_type;
        }

        return $metadata;
    }

    /**
     * Calcular luminosidad de un color hexadecimal
     *
     * Nota: Misma implementación que en PricingService
     *
     * @param string $hex Color en formato #RRGGBB
     * @return float Luminosidad (0.0 = negro, 1.0 = blanco)
     */
    public function calculateLuminosity(string $hex): float
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) !== 6) {
            return 0.5; // Default: medio
        }

        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;

        // Fórmula estándar de luminosidad relativa
        return 0.299 * $r + 0.587 * $g + 0.114 * $b;
    }

    /**
     * Validar si un atributo puede ser eliminado
     *
     * @param ProductAttribute $attribute
     * @return array ['can_delete' => bool, 'reason' => string|null]
     */
    public function canDeleteAttribute(ProductAttribute $attribute): array
    {
        $parentDepsCount = $attribute->parentDependencies()->count();
        $childDepsCount = $attribute->childDependencies()->count();
        $totalDeps = $parentDepsCount + $childDepsCount;

        if ($totalDeps > 0) {
            return [
                'can_delete' => false,
                'reason' => "El atributo tiene {$totalDeps} dependencia(s) configurada(s)",
                'details' => [
                    'parent_dependencies' => $parentDepsCount,
                    'child_dependencies' => $childDepsCount,
                    'total_dependencies' => $totalDeps
                ]
            ];
        }

        return [
            'can_delete' => true,
            'reason' => null,
            'details' => []
        ];
    }

    /**
     * Construir query de atributos con filtros
     *
     * @param Request $request Request con filtros
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function buildAttributeQuery(Request $request)
    {
        $query = ProductAttribute::with('attributeGroup');

        // Filtro por tipo
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filtro de búsqueda
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('value', 'like', '%' . $request->search . '%');
            });
        }

        // Filtro por estado activo
        if ($request->filled('active')) {
            $query->where('active', $request->active === 'active');
        }

        return $query;
    }

    /**
     * Construir query de dependencias con filtros
     *
     * @param Request $request Request con filtros
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function buildDependencyQuery(Request $request)
    {
        $query = AttributeDependency::with(['parentAttribute', 'dependentAttribute']);

        // Filtro por tipo de atributo padre
        if ($request->filled('parent_type')) {
            $query->whereHas('parentAttribute', function($q) use ($request) {
                $q->where('type', $request->parent_type);
            });
        }

        // Filtro por tipo de atributo dependiente
        if ($request->filled('dependent_type')) {
            $query->whereHas('dependentAttribute', function($q) use ($request) {
                $q->where('type', $request->dependent_type);
            });
        }

        // Filtro por tipo de condición
        if ($request->filled('condition_type')) {
            $query->where('condition_type', $request->condition_type);
        }

        // Filtro de búsqueda por texto
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('parentAttribute', function($subQ) use ($search) {
                    $subQ->where('name', 'like', "%{$search}%")
                         ->orWhere('value', 'like', "%{$search}%");
                })->orWhereHas('dependentAttribute', function($subQ) use ($search) {
                    $subQ->where('name', 'like', "%{$search}%")
                         ->orWhere('value', 'like', "%{$search}%");
                });
            });
        }

        return $query;
    }

    /**
     * Validar datos de una dependencia
     *
     * Lógica centralizada de validación específica de negocio
     *
     * @param array $data Datos validados del request
     * @return array ['valid' => bool, 'errors' => array]
     */
    public function validateDependencyData(array $data): array
    {
        $errors = [];

        // Si no hay atributo dependiente, debe ser un modificador individual
        if (!isset($data['dependent_attribute_id']) || !$data['dependent_attribute_id']) {
            if (!isset($data['price_modifier']) || !$data['price_modifier']) {
                $errors['price_modifier'] = 'Para modificadores individuales es necesario especificar un modificador de precio.';
            }
        } else {
            // Si hay atributo dependiente, la condición es requerida
            if (!isset($data['condition_type']) || !$data['condition_type']) {
                $errors['condition_type'] = 'La condición es requerida para dependencias entre atributos.';
            }
        }

        // Validación legacy para sets_price
        if (isset($data['condition_type']) && $data['condition_type'] === 'sets_price') {
            if (!isset($data['price_impact']) || !$data['price_impact']) {
                $errors['price_impact'] = 'El impacto en el precio es requerido para este tipo de condición.';
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Verificar si una dependencia ya existe
     *
     * @param array $data Datos de la dependencia
     * @param int|null $excludeId ID a excluir (para updates)
     * @return bool
     */
    public function dependencyExists(array $data, ?int $excludeId = null): bool
    {
        $query = AttributeDependency::where('parent_attribute_id', $data['parent_attribute_id'])
            ->where('dependent_attribute_id', $data['dependent_attribute_id'] ?? null)
            ->where('condition_type', $data['condition_type'] ?? 'price_modifier')
            ->where('product_id', $data['product_id'] ?? null);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Buscar dependencias circulares
     *
     * @param int $startId ID del atributo inicial
     * @param int $targetId ID del atributo objetivo
     * @param \Illuminate\Support\Collection|null $dependencies Colección de dependencias (opcional)
     * @param array $visited Array de IDs visitados
     * @return bool True si se encontró dependencia circular
     */
    public function findCircularDependency(int $startId, int $targetId, $dependencies = null, array $visited = []): bool
    {
        // Evitar bucles infinitos
        if (in_array($startId, $visited)) {
            return false;
        }

        $visited[] = $startId;

        // Cargar dependencias si no se proporcionaron
        if ($dependencies === null) {
            $dependencies = AttributeDependency::with(['parentAttribute', 'dependentAttribute'])->get();
        }

        foreach ($dependencies as $dep) {
            if ($dep->parent_attribute_id == $startId) {
                if ($dep->dependent_attribute_id == $targetId) {
                    return true; // Encontrada dependencia circular
                }

                if ($dep->dependent_attribute_id && $this->findCircularDependency($dep->dependent_attribute_id, $targetId, $dependencies, $visited)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Encontrar dependencias conflictivas
     *
     * @param \Illuminate\Support\Collection|null $dependencies Colección de dependencias
     * @return array Array de conflictos encontrados
     */
    public function findConflictingDependencies($dependencies = null): array
    {
        $conflicts = [];

        // Cargar dependencias si no se proporcionaron
        if ($dependencies === null) {
            $dependencies = AttributeDependency::with(['parentAttribute', 'dependentAttribute'])->get();
        }

        foreach ($dependencies as $dep1) {
            foreach ($dependencies as $dep2) {
                if ($dep1->id >= $dep2->id) continue;

                // Verificar si hay conflicto directo
                if ($dep1->parent_attribute_id == $dep2->parent_attribute_id &&
                    $dep1->dependent_attribute_id == $dep2->dependent_attribute_id) {

                    if (($dep1->condition_type == 'allows' && $dep2->condition_type == 'blocks') ||
                        ($dep1->condition_type == 'blocks' && $dep2->condition_type == 'allows')) {

                        $parentName = $dep1->parentAttribute->name ?? 'Desconocido';
                        $dependentName = $dep1->dependentAttribute->name ?? 'Desconocido';

                        $conflicts[] = "Conflicto entre dependencias: {$parentName} -> {$dependentName} (permite vs bloquea)";
                    }
                }
            }
        }

        return $conflicts;
    }

    /**
     * Validar configuración completa de dependencias
     *
     * @return array ['valid' => bool, 'errors' => array, 'warnings' => array]
     */
    public function validateDependencyConfiguration(): array
    {
        $errors = [];
        $warnings = [];

        // Obtener todas las dependencias
        $dependencies = AttributeDependency::with(['parentAttribute', 'dependentAttribute'])->get();

        // Verificar dependencias circulares
        foreach ($dependencies as $dependency) {
            if (!$dependency->dependent_attribute_id) continue;

            $circular = $this->findCircularDependency(
                $dependency->parent_attribute_id,
                $dependency->dependent_attribute_id,
                $dependencies
            );

            if ($circular) {
                $parentName = $dependency->parentAttribute->name ?? 'Desconocido';
                $dependentName = $dependency->dependentAttribute->name ?? 'Desconocido';
                $errors[] = "Dependencia circular detectada: {$parentName} ↔ {$dependentName}";
            }
        }

        // Verificar conflictos
        $conflicts = $this->findConflictingDependencies($dependencies);
        $warnings = array_merge($warnings, $conflicts);

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings
        ];
    }

    /**
     * Reordenar grupos de atributos
     *
     * @param array $groupsData Array con ['id' => int, 'sort_order' => int]
     * @return int Cantidad de grupos reordenados
     */
    public function reorderGroups(array $groupsData): int
    {
        $updated = 0;

        foreach ($groupsData as $groupData) {
            AttributeGroup::where('id', $groupData['id'])
                ->update(['sort_order' => $groupData['sort_order']]);
            $updated++;
        }

        return $updated;
    }

    /**
     * Reordenar atributos
     *
     * @param array $ordersData Array con ['id' => int, 'sort_order' => int]
     * @return int Cantidad de atributos reordenados
     */
    public function reorderAttributes(array $ordersData): int
    {
        $updated = 0;

        foreach ($ordersData as $order) {
            ProductAttribute::where('id', $order['id'])
                ->update(['sort_order' => $order['sort_order']]);
            $updated++;
        }

        return $updated;
    }

    /**
     * Obtener tipos de atributos disponibles
     *
     * @return array
     */
    public function getAttributeTypes(): array
    {
        return self::ATTRIBUTE_TYPES;
    }

    /**
     * Obtener tipos de condiciones
     *
     * @return array
     */
    public function getConditionTypes(): array
    {
        return self::CONDITION_TYPES;
    }
}
