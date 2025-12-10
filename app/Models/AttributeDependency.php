<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttributeDependency extends Model
{
    use HasFactory;

    const CONDITION_ALLOWS = 'allows';
    const CONDITION_BLOCKS = 'blocks';
    const CONDITION_REQUIRES = 'requires';
    const CONDITION_SETS_PRICE = 'sets_price';
    const CONDITION_PRICE_MODIFIER = 'price_modifier';

    const PRICE_APPLIES_UNIT = 'unit';
    const PRICE_APPLIES_TOTAL = 'total';

    protected $fillable = [
        'product_id',
        'parent_attribute_id',
        'dependent_attribute_id',
        'third_attribute_id',
        'condition_type',
        'is_price_rule',
        'conditions',
        'price_impact',
        'price_modifier',
        'price_percentage',
        'price_applies_to',
        'priority',
        'auto_select',
        'reset_dependents',
        'active'
    ];

    protected $casts = [
        'conditions' => 'array',
        'price_impact' => 'decimal:4',
        'price_modifier' => 'decimal:4',
        'price_percentage' => 'decimal:2',
        'is_price_rule' => 'boolean',
        'auto_select' => 'boolean',
        'reset_dependents' => 'boolean',
        'active' => 'boolean',
    ];

    // Relaciones
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function parentAttribute()
    {
        return $this->belongsTo(ProductAttribute::class, 'parent_attribute_id');
    }

    public function dependentAttribute()
    {
        return $this->belongsTo(ProductAttribute::class, 'dependent_attribute_id');
    }

    public function thirdAttribute()
    {
        return $this->belongsTo(ProductAttribute::class, 'third_attribute_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeByConditionType($query, $type)
    {
        return $query->where('condition_type', $type);
    }

    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Evaluar si esta dependencia se aplica a la selección actual
     */
    public function appliesToSelection($selection)
    {
        // Verificar si el atributo padre está en la selección
        if (!in_array($this->parent_attribute_id, array_values($selection))) {
            return false;
        }

        // Si hay condiciones específicas, evaluarlas
        if (!empty($this->conditions)) {
            return $this->evaluateConditions($selection);
        }

        return true;
    }

    /**
     * Evaluar condiciones específicas
     */
    private function evaluateConditions($selection)
    {
        if (!$this->conditions) {
            return true;
        }

        foreach ($this->conditions as $condition) {
            $operator = $condition['operator'] ?? '=';
            $value = $condition['value'] ?? null;
            $field = $condition['field'] ?? 'value';

            $parentAttribute = $this->parentAttribute;
            
            if (!$parentAttribute) {
                continue;
            }

            $actualValue = $parentAttribute->{$field};

            switch ($operator) {
                case '=':
                case 'equals':
                    if ($actualValue !== $value) return false;
                    break;
                case '!=':
                case 'not_equals':
                    if ($actualValue === $value) return false;
                    break;
                case 'in':
                    if (!in_array($actualValue, (array)$value)) return false;
                    break;
                case 'not_in':
                    if (in_array($actualValue, (array)$value)) return false;
                    break;
                case 'contains':
                    if (is_array($actualValue) && !in_array($value, $actualValue)) return false;
                    if (is_string($actualValue) && strpos($actualValue, $value) === false) return false;
                    break;
            }
        }

        return true;
    }

    /**
     * Obtener todas las dependencias aplicables para una selección
     */
    public static function getApplicableDependencies($selection, $productId = null)
    {
        $query = static::active()->with(['parentAttribute', 'dependentAttribute']);

        if ($productId) {
            $query->forProduct($productId);
        }

        return $query->get()
            ->filter(function ($dependency) use ($selection) {
                return $dependency->appliesToSelection($selection);
            })
            ->sortBy('priority');
    }

    /**
     * Obtener atributos que deben auto-seleccionarse
     */
    public static function getAutoSelectAttributes($selection, $productId = null)
    {
        return static::getApplicableDependencies($selection, $productId)
            ->where('auto_select', true)
            ->pluck('dependent_attribute_id')
            ->unique();
    }

    /**
     * Obtener atributos que deben resetearse al cambiar la selección
     */
    public static function getAttributesToReset($changedAttributeId, $selection, $productId = null)
    {
        $toReset = [];

        // Encontrar dependencias donde el atributo cambiado es el padre
        $query = static::active()
            ->where('parent_attribute_id', $changedAttributeId)
            ->where('reset_dependents', true)
            ->with('dependentAttribute');

        if ($productId) {
            $query->forProduct($productId);
        }

        $dependencies = $query->get();

        foreach ($dependencies as $dependency) {
            $toReset[] = $dependency->dependent_attribute_id;

            // Recursivamente encontrar dependientes de dependientes
            $childResets = static::getAttributesToReset($dependency->dependent_attribute_id, $selection, $productId);
            $toReset = array_merge($toReset, $childResets);
        }

        return array_unique($toReset);
    }

    /**
     * Validar si una selección completa es válida
     */
    public static function validateSelection($selection, $productId = null)
    {
        $errors = [];
        $dependencies = static::getApplicableDependencies($selection, $productId);

        foreach ($dependencies as $dependency) {
            if ($dependency->condition_type === static::CONDITION_REQUIRES) {
                if (!in_array($dependency->dependent_attribute_id, array_values($selection))) {
                    $errors[] = "El atributo {$dependency->dependentAttribute->name} es requerido";
                }
            }

            if ($dependency->condition_type === static::CONDITION_BLOCKS) {
                if (in_array($dependency->dependent_attribute_id, array_values($selection))) {
                    $errors[] = "El atributo {$dependency->dependentAttribute->name} no es compatible con la selección actual";
                }
            }
        }

        return $errors;
    }

    /**
     * Calcular impacto de precio total de las dependencias
     */
    public static function calculatePriceImpact($selection, $basePrice, $productId = null)
    {
        $totalImpact = 0;
        $dependencies = static::getApplicableDependencies($selection, $productId)
            ->where('condition_type', static::CONDITION_SETS_PRICE);

        foreach ($dependencies as $dependency) {
            $totalImpact += $dependency->price_impact;
        }

        return $totalImpact;
    }
}