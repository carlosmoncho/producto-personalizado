<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceRule extends Model
{
    use HasFactory;

    const RULE_TYPE_COMBINATION = 'combination';
    const RULE_TYPE_VOLUME = 'volume';
    const RULE_TYPE_ATTRIBUTE_SPECIFIC = 'attribute_specific';
    const RULE_TYPE_CONDITIONAL = 'conditional';

    const ACTION_ADD_FIXED = 'add_fixed';
    const ACTION_ADD_PERCENTAGE = 'add_percentage';
    const ACTION_MULTIPLY = 'multiply';
    const ACTION_SET_FIXED = 'set_fixed';
    const ACTION_SET_PERCENTAGE = 'set_percentage';

    protected $fillable = [
        'name',
        'description',
        'rule_type',
        'conditions',
        'action_type',
        'action_value',
        'priority',
        'product_id',
        'category_id',
        'quantity_min',
        'quantity_max',
        'valid_from',
        'valid_until',
        'active',
        'sort_order'
    ];

    protected $casts = [
        'conditions' => 'array',
        'action_value' => 'decimal:4',
        'active' => 'boolean',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
    ];

    // Relaciones
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('rule_type', $type);
    }

    public function scopeForProduct($query, $productId)
    {
        return $query->where(function($q) use ($productId) {
            $q->where('product_id', $productId)
              ->orWhereNull('product_id');
        });
    }

    public function scopeForCategory($query, $categoryId)
    {
        return $query->where(function($q) use ($categoryId) {
            $q->where('category_id', $categoryId)
              ->orWhereNull('category_id');
        });
    }

    public function scopeForQuantity($query, $quantity)
    {
        return $query->where(function($q) use ($quantity) {
            $q->where(function($subQ) use ($quantity) {
                $subQ->whereNull('quantity_min')->orWhere('quantity_min', '<=', $quantity);
            })->where(function($subQ) use ($quantity) {
                $subQ->whereNull('quantity_max')->orWhere('quantity_max', '>=', $quantity);
            });
        });
    }

    public function scopeValid($query)
    {
        $now = now();
        return $query->where(function($q) use ($now) {
            $q->where(function($subQ) use ($now) {
                $subQ->whereNull('valid_from')->orWhere('valid_from', '<=', $now);
            })->where(function($subQ) use ($now) {
                $subQ->whereNull('valid_until')->orWhere('valid_until', '>=', $now);
            });
        });
    }

    public function scopeOrderByPriority($query)
    {
        return $query->orderBy('priority', 'desc')->orderBy('sort_order');
    }

    // Métodos de utilidad
    public function isApplicable($attributes, $quantity, $productId, $categoryId = null)
    {
        // Verificar si la regla está activa y válida temporalmente
        if (!$this->active || !$this->isValidNow()) {
            return false;
        }

        // Verificar producto
        if ($this->product_id && $this->product_id != $productId) {
            return false;
        }

        // Verificar categoría
        if ($this->category_id && $this->category_id != $categoryId) {
            return false;
        }

        // Verificar cantidad
        if (!$this->isQuantityInRange($quantity)) {
            return false;
        }

        // Verificar condiciones específicas por tipo de regla
        return $this->checkRuleConditions($attributes, $quantity);
    }

    public function isValidNow()
    {
        $now = now();
        
        if ($this->valid_from && $this->valid_from > $now) {
            return false;
        }
        
        if ($this->valid_until && $this->valid_until < $now) {
            return false;
        }
        
        return true;
    }

    public function isQuantityInRange($quantity)
    {
        if ($this->quantity_min && $quantity < $this->quantity_min) {
            return false;
        }
        
        if ($this->quantity_max && $quantity > $this->quantity_max) {
            return false;
        }
        
        return true;
    }

    protected function checkRuleConditions($attributes, $quantity)
    {
        $conditions = $this->conditions;

        switch ($this->rule_type) {
            case self::RULE_TYPE_COMBINATION:
                return $this->checkCombinationConditions($conditions, $attributes);
            
            case self::RULE_TYPE_VOLUME:
                return $this->checkVolumeConditions($conditions, $quantity);
            
            case self::RULE_TYPE_ATTRIBUTE_SPECIFIC:
                return $this->checkAttributeSpecificConditions($conditions, $attributes);
            
            case self::RULE_TYPE_CONDITIONAL:
                return $this->checkConditionalConditions($conditions, $attributes, $quantity);
            
            default:
                return false;
        }
    }

    protected function checkCombinationConditions($conditions, $attributes)
    {
        if (!isset($conditions['attributes']) || !is_array($conditions['attributes'])) {
            return false;
        }

        $requiredAttributes = $conditions['attributes'];
        $selectedAttributes = array_values($attributes);

        // Verificar si todos los atributos requeridos están seleccionados
        foreach ($requiredAttributes as $requiredAttr) {
            if (!in_array($requiredAttr, $selectedAttributes)) {
                return false;
            }
        }

        return true;
    }

    protected function checkVolumeConditions($conditions, $quantity)
    {
        if (isset($conditions['quantity_min']) && $quantity < $conditions['quantity_min']) {
            return false;
        }
        
        if (isset($conditions['quantity_max']) && $quantity > $conditions['quantity_max']) {
            return false;
        }
        
        return true;
    }

    protected function checkAttributeSpecificConditions($conditions, $attributes)
    {
        if (!isset($conditions['attribute_type']) || !isset($conditions['attribute_values'])) {
            return false;
        }

        $type = $conditions['attribute_type'];
        $requiredValues = $conditions['attribute_values'];

        if (!isset($attributes[$type])) {
            return false;
        }

        return in_array($attributes[$type], $requiredValues);
    }

    protected function checkConditionalConditions($conditions, $attributes, $quantity)
    {
        // Implementar lógica condicional más compleja
        // Por ejemplo: "Si color = rojo Y material = algodón Y cantidad > 100"
        
        if (!isset($conditions['rules']) || !is_array($conditions['rules'])) {
            return false;
        }

        foreach ($conditions['rules'] as $rule) {
            if (!$this->evaluateConditionRule($rule, $attributes, $quantity)) {
                return false;
            }
        }

        return true;
    }

    protected function evaluateConditionRule($rule, $attributes, $quantity)
    {
        $field = $rule['field'] ?? '';
        $operator = $rule['operator'] ?? 'equals';
        $value = $rule['value'] ?? '';

        if ($field === 'quantity') {
            return $this->compareValues($quantity, $operator, $value);
        }

        if (isset($attributes[$field])) {
            return $this->compareValues($attributes[$field], $operator, $value);
        }

        return false;
    }

    protected function compareValues($actual, $operator, $expected)
    {
        switch ($operator) {
            case 'equals':
                return $actual == $expected;
            case 'not_equals':
                return $actual != $expected;
            case 'greater_than':
                return $actual > $expected;
            case 'less_than':
                return $actual < $expected;
            case 'greater_equal':
                return $actual >= $expected;
            case 'less_equal':
                return $actual <= $expected;
            case 'contains':
                return strpos($actual, $expected) !== false;
            case 'in':
                return in_array($actual, (array)$expected);
            default:
                return false;
        }
    }

    public function applyRule($basePrice)
    {
        switch ($this->action_type) {
            case self::ACTION_ADD_FIXED:
                return $basePrice + $this->action_value;
            
            case self::ACTION_ADD_PERCENTAGE:
                return $basePrice * (1 + ($this->action_value / 100));
            
            case self::ACTION_MULTIPLY:
                return $basePrice * $this->action_value;
            
            case self::ACTION_SET_FIXED:
                return $this->action_value;
            
            case self::ACTION_SET_PERCENTAGE:
                return $basePrice * ($this->action_value / 100);
            
            default:
                return $basePrice;
        }
    }

    /**
     * Aplicar reglas de precios a una configuración
     */
    public static function applyRules($basePrice, $attributes, $quantity, $productId, $categoryId = null)
    {
        $applicableRules = static::active()
            ->valid()
            ->forProduct($productId)
            ->forCategory($categoryId)
            ->forQuantity($quantity)
            ->orderByPriority()
            ->get();

        $finalPrice = $basePrice;

        foreach ($applicableRules as $rule) {
            if ($rule->isApplicable($attributes, $quantity, $productId, $categoryId)) {
                $finalPrice = $rule->applyRule($finalPrice);
            }
        }

        return $finalPrice;
    }
}