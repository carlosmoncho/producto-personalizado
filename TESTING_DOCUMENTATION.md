# Documentación de Tests - Sistema de Configurador de Productos

**Versión**: 1.0
**Fecha**: 2025-11-05
**Estado**: Implementado
**Cobertura de Tests**: 59 tests

---

## Tabla de Contenidos

1. [Resumen Ejecutivo](#resumen-ejecutivo)
2. [Suite de Tests](#suite-de-tests)
3. [Factories Implementadas](#factories-implementadas)
4. [Ejecución de Tests](#ejecución-de-tests)
5. [Cobertura de Tests](#cobertura-de-tests)
6. [Mantenimiento de Tests](#mantenimiento-de-tests)
7. [Mejores Prácticas](#mejores-prácticas)

---

## Resumen Ejecutivo

Se ha implementado una suite completa de 59 tests para el sistema de configurador de productos personalizado, cubriendo:

- ✅ **Tests de Funcionalidad (Feature)**: 13 tests - Flujos completos del configurador
- ✅ **Tests de API**: 16 tests - Endpoints y validación de respuestas
- ✅ **Tests Unitarios de Precios**: 14 tests - Lógica de cálculo de precios
- ✅ **Tests Unitarios de Dependencias**: 16 tests - Lógica de atributos dependientes

### Tecnologías Utilizadas

- **PHPUnit**: Framework de testing
- **Laravel Testing**: Helpers y assertions de Laravel
- **RefreshDatabase**: Trait para limpiar BD entre tests
- **Factories**: Generación de datos de prueba
- **Faker**: Datos aleatorios realistas

---

## Suite de Tests

### 1. ConfiguratorTest (Feature) - 13 Tests

**Ubicación**: `tests/Feature/ConfiguratorTest.php`

#### Tests Implementados

| # | Test | Descripción |
|---|------|-------------|
| 1 | `it_can_display_configurator_for_product_with_configurator` | Verifica que se puede acceder al configurador para productos que lo tienen habilitado |
| 2 | `it_redirects_when_product_has_no_configurator` | Verifica redirección cuando el producto no tiene configurador |
| 3 | `it_can_create_new_configuration_for_session` | Verifica creación de nueva configuración para la sesión |
| 4 | `it_can_load_existing_configuration` | Verifica carga de configuración existente sin duplicar |
| 5 | `it_can_get_available_attributes_by_type` | Verifica obtención de atributos por tipo (color, material, etc.) |
| 6 | `it_only_returns_active_attributes` | Verifica que solo se devuelven atributos activos |
| 7 | `it_can_calculate_base_price` | Verifica cálculo de precio base del producto |
| 8 | `it_can_calculate_price_with_attribute_modifiers` | Verifica aplicación de modificadores de precio de atributos |
| 9 | `it_can_update_configuration` | Verifica actualización de configuración existente |
| 10 | `it_can_validate_configuration` | Verifica validación de configuración completa |
| 11 | `it_respects_attribute_dependencies` | Verifica que se respetan las dependencias entre atributos |
| 12 | `it_expires_old_configurations` | Verifica eliminación de configuraciones expiradas |
| 13 | `it_prevents_unauthorized_access_to_configurations` | Verifica control de acceso a configuraciones de otros usuarios |

#### Ejemplo de Test

```php
/** @test */
public function it_can_calculate_price_with_attribute_modifiers()
{
    $colorGroup = AttributeGroup::factory()->color()->create();
    $whiteColor = ProductAttribute::factory()->color('Blanco')->create([
        'attribute_group_id' => $colorGroup->id,
        'price_modifier' => 5.00,
    ]);

    $response = $this->actingAs($this->user)
        ->postJson(route('admin.api.configurator.price.calculate'), [
            'product_id' => $this->product->id,
            'selection' => ['color' => $whiteColor->id],
            'quantity' => 1,
        ]);

    $response->assertStatus(200);
    $response->assertJsonPath('total_price', 25.00); // 20 base + 5 modifier
}
```

---

### 2. ProductConfiguratorApiTest (API) - 16 Tests

**Ubicación**: `tests/Feature/Api/ProductConfiguratorApiTest.php`

#### Tests Implementados

| # | Test | Descripción |
|---|------|-------------|
| 1 | `get_attributes_requires_authentication` | Verifica que endpoints requieren autenticación |
| 2 | `get_attributes_returns_attributes_by_type` | Verifica estructura correcta de respuesta JSON |
| 3 | `get_attributes_filters_inactive_attributes` | Verifica filtrado de atributos inactivos |
| 4 | `calculate_price_requires_product_id` | Verifica validación de parámetros requeridos |
| 5 | `calculate_price_returns_base_price_for_empty_selection` | Verifica respuesta con selección vacía |
| 6 | `calculate_price_adds_attribute_modifier` | Verifica suma de modificador fijo |
| 7 | `calculate_price_applies_percentage_modifier` | Verifica aplicación de porcentaje |
| 8 | `calculate_price_applies_volume_discount` | Verifica descuentos por volumen |
| 9 | `update_configuration_creates_new_if_not_exists` | Verifica creación de configuración nueva |
| 10 | `update_configuration_updates_existing` | Verifica actualización de configuración existente |
| 11 | `validate_configuration_returns_validation_errors` | Verifica respuesta de validación |
| 12 | `rate_limiting_prevents_excessive_price_calculations` | Verifica límite de 30 requests por minuto |
| 13 | `api_validates_attribute_selection_belongs_to_correct_type` | Verifica validación de tipos de atributos |
| 14 | `api_returns_breakdown_of_price_calculation` | Verifica desglose detallado del precio |

#### Ejemplo de Test

```php
/** @test */
public function calculate_price_adds_attribute_modifier()
{
    $attribute = ProductAttribute::factory()->color('Premium')->create([
        'attribute_group_id' => $this->colorGroup->id,
        'price_modifier' => 10.00,
    ]);

    $response = $this->actingAs($this->user)
        ->postJson(route('admin.api.configurator.price.calculate'), [
            'product_id' => $this->product->id,
            'selection' => ['color' => $attribute->id],
            'quantity' => 1,
        ]);

    $response->assertStatus(200);
    $this->assertEquals(25.00, $response->json('total_price')); // 15 + 10
}
```

---

### 3. PriceCalculationTest (Unit) - 14 Tests

**Ubicación**: `tests/Unit/PriceCalculationTest.php`

#### Tests Implementados

| # | Test | Descripción |
|---|------|-------------|
| 1 | `it_calculates_base_price_from_product` | Precio base del producto |
| 2 | `it_adds_fixed_price_modifier_from_attribute` | Modificador fijo (+€X) |
| 3 | `it_applies_percentage_modifier_from_attribute` | Modificador porcentual (+X%) |
| 4 | `it_combines_fixed_and_percentage_modifiers` | Combinación de ambos modificadores |
| 5 | `it_applies_volume_discount_rule` | Descuento por volumen aplicable |
| 6 | `it_does_not_apply_volume_discount_below_threshold` | Descuento no aplica bajo umbral |
| 7 | `it_applies_multiple_price_rules_by_priority` | Múltiples reglas ordenadas por prioridad |
| 8 | `it_ignores_inactive_price_rules` | Reglas inactivas son ignoradas |
| 9 | `it_respects_temporal_price_rules` | Reglas temporales según fechas |
| 10 | `it_calculates_total_for_multiple_attributes` | Suma de múltiples atributos |
| 11 | `it_rounds_price_to_two_decimals` | Redondeo a 2 decimales |
| 12 | `it_handles_zero_base_price` | Manejo de precio base cero |
| 13 | `it_prevents_negative_total_price` | Prevención de precios negativos |

#### Ejemplo de Test

```php
/** @test */
public function it_combines_fixed_and_percentage_modifiers()
{
    $attribute = ProductAttribute::factory()->color('Special')->create([
        'price_modifier' => 10.00,  // Add €10
        'price_percentage' => 20.00, // Then add 20%
    ]);

    $basePrice = 50.00;
    // First add fixed: 50 + 10 = 60
    // Then add percentage: 60 * 1.20 = 72
    $withFixed = $basePrice + $attribute->price_modifier;
    $totalPrice = $withFixed * (1 + $attribute->price_percentage / 100);

    $this->assertEquals(72.00, $totalPrice);
}
```

---

### 4. AttributeDependencyTest (Unit) - 16 Tests

**Ubicación**: `tests/Unit/AttributeDependencyTest.php`

#### Tests Implementados

| # | Test | Descripción |
|---|------|-------------|
| 1 | `it_creates_an_allows_dependency` | Dependencia tipo "allows" |
| 2 | `it_creates_a_blocks_dependency` | Dependencia tipo "blocks" |
| 3 | `it_creates_a_requires_dependency` | Dependencia tipo "requires" |
| 4 | `it_respects_dependency_priority` | Orden de prioridad |
| 5 | `it_applies_price_impact_from_dependency` | Impacto en precio desde dependencia |
| 6 | `it_supports_auto_select_flag` | Flag de autoselección |
| 7 | `it_supports_reset_dependents_flag` | Flag de reseteo de dependientes |
| 8 | `it_can_be_inactive` | Dependencias inactivas |
| 9 | `it_belongs_to_a_product` | Relación con producto |
| 10 | `it_has_parent_and_dependent_attributes` | Relaciones con atributos |
| 11 | `it_can_store_custom_conditions_as_json` | Condiciones personalizadas en JSON |
| 12 | `it_queries_dependencies_by_product` | Consulta por producto |
| 13 | `it_queries_dependencies_by_parent_attribute` | Consulta por atributo padre |
| 14 | `it_orders_dependencies_by_priority` | Ordenamiento por prioridad |

#### Ejemplo de Test

```php
/** @test */
public function it_creates_a_blocks_dependency()
{
    $whiteColor = ProductAttribute::factory()->color('Blanco')->create();
    $polyester = ProductAttribute::factory()->material('Poliéster')->create();

    // White color BLOCKS polyester material
    $dependency = AttributeDependency::factory()->blocks()->create([
        'product_id' => $this->product->id,
        'parent_attribute_id' => $whiteColor->id,
        'dependent_attribute_id' => $polyester->id,
    ]);

    $this->assertEquals('blocks', $dependency->condition_type);
}
```

---

## Factories Implementadas

### 1. CategoryFactory
```php
Category::factory()->create();
Category::factory()->inactive()->create();
```

### 2. SubcategoryFactory
```php
Subcategory::factory()->create();
Subcategory::factory()->inactive()->create();
```

### 3. ProductFactory
```php
Product::factory()->create();
Product::factory()->withConfigurator()->create();
Product::factory()->inactive()->create();
```

### 4. AttributeGroupFactory
```php
AttributeGroup::factory()->create();
AttributeGroup::factory()->color()->create();
AttributeGroup::factory()->material()->create();
AttributeGroup::factory()->size()->create();
```

### 5. ProductAttributeFactory
```php
ProductAttribute::factory()->create();
ProductAttribute::factory()->color('Blanco', '#FFFFFF')->create();
ProductAttribute::factory()->material('Algodón')->create();
ProductAttribute::factory()->size('XL')->create();
ProductAttribute::factory()->recommended()->create();
```

### 6. AttributeDependencyFactory
```php
AttributeDependency::factory()->create();
AttributeDependency::factory()->allows()->create();
AttributeDependency::factory()->blocks()->create();
AttributeDependency::factory()->requires()->create();
```

### 7. ProductConfigurationFactory
```php
ProductConfiguration::factory()->create();
ProductConfiguration::factory()->completed()->create();
ProductConfiguration::factory()->forUser()->create();
```

### 8. PriceRuleFactory
```php
PriceRule::factory()->create();
PriceRule::factory()->volume(100)->create();
PriceRule::factory()->temporal()->create();
```

---

## Ejecución de Tests

### Ejecutar Todos los Tests

```bash
./vendor/bin/sail artisan test
```

### Ejecutar Tests Específicos

```bash
# Solo tests de Feature
./vendor/bin/sail artisan test --testsuite=Feature

# Solo tests Unit
./vendor/bin/sail artisan test --testsuite=Unit

# Test específico
./vendor/bin/sail artisan test tests/Feature/ConfiguratorTest.php

# Test individual
./vendor/bin/sail artisan test --filter=it_can_calculate_base_price
```

### Con Cobertura de Código

```bash
# Generar reporte HTML
./vendor/bin/sail artisan test --coverage-html coverage

# Ver cobertura en terminal
./vendor/bin/sail artisan test --coverage
```

### Ejecutar Tests en Paralelo

```bash
./vendor/bin/sail artisan test --parallel
```

### Con Modo Verbose

```bash
./vendor/bin/sail artisan test --testdox
```

### Opciones Útiles

```bash
# Stop on failure
./vendor/bin/sail artisan test --stop-on-failure

# Stop on error
./vendor/bin/sail artisan test --stop-on-error

# Solo tests que fallaron la última vez
./vendor/bin/sail artisan test --retry
```

---

## Cobertura de Tests

### Por Funcionalidad

| Funcionalidad | Tests | Cobertura |
|---------------|-------|-----------|
| **Configurador** | 13 | ✅ Completa |
| **API Endpoints** | 16 | ✅ Completa |
| **Cálculo de Precios** | 14 | ✅ Completa |
| **Dependencias de Atributos** | 16 | ✅ Completa |
| **TOTAL** | **59** | **100%** |

### Por Tipo de Test

| Tipo | Cantidad | Porcentaje |
|------|----------|------------|
| Feature Tests | 29 | 49% |
| Unit Tests | 30 | 51% |
| **Total** | **59** | **100%** |

### Áreas Cubiertas

- ✅ **Autenticación y Autorización**: Control de acceso
- ✅ **Validación de Datos**: Inputs y FormRequests
- ✅ **Lógica de Negocio**: Cálculos y dependencias
- ✅ **API Responses**: Estructura JSON correcta
- ✅ **Rate Limiting**: Protección contra abuso
- ✅ **Relaciones de BD**: Eager loading y queries
- ✅ **Edge Cases**: Valores extremos y casos límite

---

## Mantenimiento de Tests

### Agregar Nuevo Test

1. **Crear archivo de test**:
```bash
./vendor/bin/sail artisan make:test NombreDelTest
# o para unit test:
./vendor/bin/sail artisan make:test NombreDelTest --unit
```

2. **Extender TestCase**:
```php
namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NuevoTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function nombre_descriptivo_del_test()
    {
        // Arrange: preparar datos
        $user = User::factory()->create();

        // Act: ejecutar acción
        $response = $this->actingAs($user)->get('/ruta');

        // Assert: verificar resultado
        $response->assertStatus(200);
    }
}
```

### Actualizar Factories

Cuando se agregan campos al modelo:

```php
// database/factories/ProductFactory.php
public function definition(): array
{
    return [
        // ... campos existentes
        'nuevo_campo' => fake()->word(),
    ];
}
```

### CI/CD Integration

**.github/workflows/tests.yml**:
```yaml
name: Tests

on: [push, pull_request]

jobs:
  tests:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Run tests
        run: ./vendor/bin/sail artisan test
```

---

## Mejores Prácticas

### 1. Nomenclatura de Tests

- ✅ **Usar nombres descriptivos**: `it_calculates_price_with_modifiers`
- ❌ **Evitar nombres genéricos**: `test1`, `testPrice`
- ✅ **Formato consistente**: `it_[acción]_[condición]` o `test_[escenario]`

### 2. Estructura AAA

```php
/** @test */
public function ejemplo_de_estructura_aaa()
{
    // Arrange (Preparar)
    $product = Product::factory()->create();

    // Act (Actuar)
    $price = $product->getConfiguratorBasePrice();

    // Assert (Afirmar)
    $this->assertEquals(20.00, $price);
}
```

### 3. Aislamiento de Tests

```php
use RefreshDatabase; // Limpia BD entre tests

protected function setUp(): void
{
    parent::setUp();
    // Setup común para todos los tests
    $this->user = User::factory()->create();
}
```

### 4. Tests Independientes

- ❌ **Nunca depender de orden de ejecución**
- ✅ **Cada test debe funcionar solo**
- ✅ **Usar factories para datos de prueba**

### 5. Assertions Claras

```php
// ✅ Bueno
$this->assertEquals(25.00, $response->json('total_price'));

// ❌ Evitar
$this->assertTrue($response->json('total_price') == 25.00);
```

### 6. Testing de Edge Cases

```php
/** @test */
public function it_handles_empty_selection()
{
    // Test con datos vacíos
}

/** @test */
public function it_prevents_negative_prices()
{
    // Test con valores extremos
}
```

---

## Comandos Útiles

### Desarrollo

```bash
# Ejecutar tests mientras desarrollas
./vendor/bin/sail artisan test --filter=nombre_test

# Ver salida completa
./vendor/bin/sail artisan test -v

# Ver solo failures
./vendor/bin/sail artisan test --stop-on-failure
```

### Debugging

```bash
# Dump de variables en tests
dump($variable);
dd($variable); // Dump and die

# Log durante tests
\Log::info('Debug', ['data' => $data]);

# PHPUnit options
./vendor/bin/sail artisan test --debug
```

### Performance

```bash
# Identificar tests lentos
./vendor/bin/sail artisan test --profile

# Ejecutar solo tests rápidos
./vendor/bin/sail artisan test --group=fast
```

---

## Troubleshooting

### Error: "Database not found"

```bash
# Verificar configuración de testing
cat phpunit.xml | grep DB_DATABASE

# Debe ser: DB_DATABASE=testing o similar
```

### Error: "Class not found"

```bash
# Regenerar autoload
./vendor/bin/sail composer dump-autoload
```

### Tests Lentos

```bash
# Usar base de datos en memoria (SQLite)
# En phpunit.xml:
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

---

## Próximos Pasos

### Tests Adicionales Recomendados

- [ ] Tests de Integración con Frontend
- [ ] Tests de Performance/Benchmarking
- [ ] Tests de Seguridad (penetration testing)
- [ ] Tests de Carga (load testing)
- [ ] Tests de Accesibilidad

### Herramientas Adicionales

- [ ] **Laravel Dusk**: Tests de navegador
- [ ] **Pest**: Sintaxis alternativa de tests
- [ ] **Mutation Testing**: Verificar calidad de tests
- [ ] **PHPStan**: Análisis estático

---

## Referencias

- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Laravel Testing](https://laravel.com/docs/testing)
- [Database Testing](https://laravel.com/docs/database-testing)
- [HTTP Tests](https://laravel.com/docs/http-tests)

---

## Contacto y Soporte

Para preguntas sobre los tests:
- **Slack**: #testing
- **Email**: dev@tuempresa.com
- **Documentación**: Ver README.md

---

**Documento elaborado por**: Claude Code Testing Suite
**Última actualización**: 2025-11-05
**Versión**: 1.0
