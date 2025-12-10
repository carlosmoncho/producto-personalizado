# 🔨 Plan de Refactorización - Mejoras de Arquitectura

**Proyecto**: Sistema de Configurador de Productos
**Fecha**: 6 Noviembre 2025
**Estado Actual**: 6/10 en arquitectura
**Objetivo**: 9/10 en arquitectura

---

## 📊 RESUMEN EJECUTIVO

### Situación Actual

**Fortalezas**:
- ✅ Código funcional (100%)
- ✅ Seguridad buena (8.5/10)
- ✅ Tests críticos cubiertos

**Problemas**:
- ❌ Controllers de 600-700 líneas
- ❌ Sin separación de capas (Services, Repositories)
- ❌ Código duplicado masivo
- ❌ N+1 queries y falta de caché
- ❌ Difícil de mantener y testear

### Impacto Esperado

**Después de refactorización**:
- ✅ Controllers de <200 líneas
- ✅ Arquitectura en capas clara
- ✅ 0% código duplicado
- ✅ Performance +40%
- ✅ Cobertura de tests de 18% → 70%
- ✅ Mantenibilidad: 5/10 → 9/10

---

## 🎯 FASES DEL PLAN

### FASE 1: SEGURIDAD Y QUICK WINS (1 semana)

**Objetivos**: Resolver vulnerabilidades y mejoras rápidas

#### 1.1 Mover Rutas de Testing (2 horas) 🔴 URGENTE

**Problema**: Rutas `/test/*` expuestas en producción

**Solución**:
```bash
# Crear archivo nuevo
touch routes/dev.php

# Mover todas las rutas de testing
mv web.php (líneas 150-201) → dev.php

# Modificar bootstrap/app.php
if (app()->environment('local')) {
    $app->withRouting(development: __DIR__.'/../routes/dev.php');
}
```

**Beneficio**: Eliminar superficie de ataque

---

#### 1.2 Mejorar Rate Limiting (4 horas) 🔴 CRÍTICO

**Problema**: 10 pedidos/minuto = 600/hora muy permisivo

**Solución**:
```php
// app/Providers/AppServiceProvider.php
RateLimiter::for('orders', function (Request $request) {
    return [
        Limit::perMinute(2)->by($request->ip()),    // 2/min
        Limit::perHour(10)->by($request->ip()),     // 10/hora
        Limit::perDay(50)->by($request->ip()),      // 50/día
    ];
});

// routes/api.php
Route::post('orders', [...])->middleware('throttle:orders');
```

**Beneficio**: Protección contra spam de pedidos

---

#### 1.3 Crear CsvExportService (1 día) ⭐ QUICK WIN

**Problema**: 189 líneas duplicadas entre CustomerController y OrderController

**Solución**:
```php
// app/Services/Export/CsvExportService.php
class CsvExportService
{
    public function export(
        string $filename,
        array $headers,
        Collection $data,
        callable $rowMapper
    ): Response {
        // ... lógica común (1 sola vez)
    }
}

// Uso en controllers (de 98 líneas → 15 líneas)
public function export(Request $request)
{
    $customers = $this->customerRepository->getFiltered($request->all());

    return $this->csvExportService->export(
        filename: "clientes_{$date}.csv",
        headers: ['ID', 'Nombre', 'Email', ...],
        data: $customers,
        rowMapper: fn($c) => [$c->id, $c->name, $c->email, ...]
    );
}
```

**Ahorro**: 189 líneas de código eliminadas
**Beneficio**: Mantenimiento en 1 solo lugar

---

#### 1.4 Implementar Caché Básica (1 día) ⚠️ PERFORMANCE

**Problema**: 7 queries repetidas en cada request de formularios

**Solución**:
```php
// app/Services/CatalogCacheService.php
class CatalogCacheService
{
    private const TTL = 3600; // 1 hora

    public function getActiveCategories(): Collection
    {
        return Cache::remember('categories.active', self::TTL,
            fn() => Category::where('active', true)->orderBy('sort_order')->get()
        );
    }

    public function clearCache(): void
    {
        Cache::forget('categories.active');
        Cache::forget('subcategories.active');
        Cache::forget('printing_systems.active');
    }
}

// ProductController
public function create()
{
    $categories = $this->catalogCache->getActiveCategories(); // CACHED
    // ...
}

// CategoryController (invalidar caché después de cambios)
public function update(...)
{
    $category->update(...);
    $this->catalogCache->clearCache(); // Invalidar
    // ...
}
```

**Beneficio**: De 7 queries → 0 queries (hasta que caduque caché)
**Mejora**: -40% en tiempo de carga de formularios

---

**RESUMEN FASE 1**:
- ⏱️ Tiempo: 3-4 días
- 💰 ROI: Muy alto (quick wins)
- ✅ Seguridad mejorada
- ✅ Performance +30%

---

### FASE 2: ESTRUCTURA DE SERVICES (2 semanas)

**Objetivo**: Extraer lógica de negocio de Controllers a Services

#### 2.1 Crear ProductService (3 días)

**Refactorizar**: ProductController (749 → ~150 líneas)

**Servicios a crear**:
```
app/Services/Product/
├── ProductService.php              # CRUD principal
├── ProductSlugService.php          # Generación de slugs únicos
├── ProductFileService.php          # Manejo de imágenes y 3D
├── ProductPricingService.php       # Cálculo de precios
└── ProductConfiguratorService.php  # Lógica del configurador
```

**ProductController ANTES** (749 líneas):
```php
public function store(Request $request)
{
    // 1. Validación inline (40 líneas)
    $request->validate([...]);

    // 2. Generación de slug (15 líneas)
    $baseSlug = Str::slug($request->name);
    while (Product::where('slug', $slug)->exists()) {
        $slug = $baseSlug . '-' . $counter++;
    }

    // 3. Procesamiento de archivos (80 líneas)
    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $image) {
            // ... validación, storage, etc.
        }
    }

    if ($request->hasFile('model_3d')) {
        // ... 36 líneas de procesamiento 3D
    }

    // 4. Creación de producto (30 líneas)
    $productData = [...];
    $product = Product::create($productData);

    // 5. Relaciones (20 líneas)
    if ($request->has('printing_systems')) {
        $product->printingSystems()->sync(...);
    }

    return redirect()->route('admin.products.index')
        ->with('success', 'Producto creado exitosamente.');
}
```

**ProductController DESPUÉS** (~150 líneas):
```php
public function __construct(
    private ProductService $productService,
    private ProductFileService $fileService
) {}

public function store(StoreProductRequest $request)
{
    try {
        $product = $this->productService->createFromRequest($request);

        return redirect()->route('admin.products.index')
            ->with('success', 'Producto creado exitosamente.');

    } catch (ProductCreationException $e) {
        return redirect()->back()
            ->withInput()
            ->with('error', $e->getMessage());
    }
}
```

**ProductService.php**:
```php
class ProductService
{
    public function __construct(
        private ProductRepository $repository,
        private ProductSlugService $slugService,
        private ProductFileService $fileService,
        private ProductRelationService $relationService
    ) {}

    public function createFromRequest(StoreProductRequest $request): Product
    {
        DB::beginTransaction();

        try {
            $product = $this->repository->create([
                'name' => $request->name,
                'slug' => $this->slugService->generateUniqueSlug($request->name),
                'description' => $request->description,
                // ...
            ]);

            if ($request->hasFile('images')) {
                $this->fileService->storeProductImages($product, $request->file('images'));
            }

            if ($request->hasFile('model_3d')) {
                $this->fileService->store3DModel($product, $request->file('model_3d'));
            }

            $this->relationService->syncPrintingSystems($product, $request->printing_systems);

            DB::commit();
            return $product;

        } catch (\Exception $e) {
            DB::rollBack();
            throw new ProductCreationException($e->getMessage(), $e);
        }
    }

    public function updateFromRequest(Product $product, UpdateProductRequest $request): Product
    {
        // ... lógica similar
    }
}
```

**Beneficios**:
- Controller: 749 → 150 líneas (-80%)
- Testeable: Services se pueden testear unitariamente
- Reutilizable: Services usables desde Jobs, Commands, etc.

---

#### 2.2 Crear OrderService (2 días)

**Refactorizar**: OrderController (364 → ~100 líneas)

```
app/Services/Order/
├── OrderService.php           # CRUD principal
├── OrderCalculationService.php  # Cálculos de totales
└── OrderStatusService.php       # Gestión de estados
```

---

#### 2.3 Crear AttributeDependencyService (3 días)

**Refactorizar**: AttributeDependencyController (633 → ~150 líneas)

```
app/Services/Configurator/
├── AttributeDependencyService.php      # CRUD
├── DependencyValidationService.php     # Validación circular
├── DependencyGraphService.php          # Algoritmos de grafo
└── DependencyConflictService.php       # Detección de conflictos
```

**Controller ANTES**:
```php
public function validateConfiguration()
{
    $errors = [];
    $warnings = [];

    // 55 líneas de lógica compleja
    foreach ($dependencies as $dependency) {
        $circular = $this->findCircularDependency(...);
        // ... algoritmo recursivo
    }

    return response()->json(...);
}

private function findCircularDependency($startId, $targetId, $dependencies, $visited = [])
{
    // 28 líneas de recursión
}
```

**Controller DESPUÉS**:
```php
public function validateConfiguration()
{
    $result = $this->validationService->validateConfiguration();

    return response()->json([
        'success' => true,
        'errors' => $result->errors,
        'warnings' => $result->warnings,
        'is_valid' => $result->isValid()
    ]);
}
```

---

**RESUMEN FASE 2**:
- ⏱️ Tiempo: 8-10 días
- 📉 Líneas de código en controllers: -60%
- ✅ Testabilidad: 4/10 → 8/10
- ✅ Mantenibilidad: 5/10 → 8/10

---

### FASE 3: REPOSITORIES (1 semana)

**Objetivo**: Abstraer acceso a datos

#### 3.1 Implementar Repository Pattern

**Crear estructura**:
```
app/Repositories/
├── Contracts/
│   ├── RepositoryInterface.php
│   ├── ProductRepositoryInterface.php
│   └── OrderRepositoryInterface.php
├── BaseRepository.php
├── ProductRepository.php
├── OrderRepository.php
└── CustomerRepository.php
```

**Ejemplo - ProductRepository**:
```php
interface ProductRepositoryInterface
{
    public function findWithRelations(int $id, array $relations = []): ?Product;
    public function getActive(): Collection;
    public function search(string $term): Collection;
    public function findBySlug(string $slug): ?Product;
}

class ProductRepository implements ProductRepositoryInterface
{
    public function findWithRelations(int $id, array $relations = []): ?Product
    {
        return Product::with($relations)->find($id);
    }

    public function getActive(): Collection
    {
        return Product::active()
            ->with(['category', 'subcategory'])
            ->orderBy('sort_order')
            ->get();
    }

    public function search(string $term): Collection
    {
        return Product::search($term)
            ->active()
            ->paginate(20);
    }

    public function findBySlug(string $slug): ?Product
    {
        return Cache::remember("product.slug.{$slug}", 3600,
            fn() => Product::where('slug', $slug)->first()
        );
    }
}
```

**Beneficios**:
- Queries centralizadas y reutilizables
- Fácil mockear para tests
- Caché implementado en Repository (transparente)
- Cambiar ORM sin afectar Services

---

**RESUMEN FASE 3**:
- ⏱️ Tiempo: 5-7 días
- ✅ Abstracción de datos
- ✅ Caché centralizada
- ✅ Testabilidad mejorada

---

### FASE 4: JOBS Y OPTIMIZACIÓN (1 semana)

**Objetivo**: Operaciones pesadas a background

#### 4.1 Jobs para Operaciones Bloqueantes

**Crear**:
```
app/Jobs/
├── Product/
│   ├── ProcessProductImages.php       # Procesar imágenes (async)
│   ├── Process3DModel.php              # Validar y optimizar 3D
│   └── GenerateProductVariants.php     # Generar variantes
├── Export/
│   ├── ExportCustomersToCSV.php        # Export async
│   └── ExportOrdersToCSV.php           # Export async
└── Notification/
    ├── OrderCreatedNotification.php
    └── OrderStatusChangedNotification.php
```

**Ejemplo - ProcessProductImages**:
```php
class ProcessProductImages implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(
        private int $productId,
        private array $imagePaths
    ) {}

    public function handle(ImageProcessingService $service): void
    {
        $product = Product::find($this->productId);

        $processedImages = collect($this->imagePaths)
            ->map(fn($path) => $service->optimize($path))
            ->toArray();

        $product->update(['images' => $processedImages]);

        // Notificar admin
        $admin = User::find($this->userId);
        $admin->notify(new ImagesProcessedNotification($product));
    }
}

// En ProductController
public function store(StoreProductRequest $request)
{
    $product = $this->productService->create($request->validated());

    if ($request->hasFile('images')) {
        ProcessProductImages::dispatch($product->id, $request->file('images'));
    }

    return redirect()->route('admin.products.index')
        ->with('success', 'Producto creado. Imágenes procesándose...');
}
```

**Beneficios**:
- Tiempo de respuesta: -80% (de 5s a 1s)
- Usuario no espera operaciones largas
- Escalable con múltiples workers

---

#### 4.2 Caché Avanzada

**Implementar**:
```php
// app/Services/Cache/ProductCacheService.php
class ProductCacheService
{
    public function getCachedProduct(int $id): ?Product
    {
        return Cache::remember("product.{$id}", 3600,
            fn() => Product::with(['category', 'pricing'])->find($id)
        );
    }

    public function invalidateProduct(int $id): void
    {
        Cache::forget("product.{$id}");
        Cache::forget("product.slug.*");
    }
}

// DashboardCacheService para stats
class DashboardCacheService
{
    public function getStats(): array
    {
        return Cache::remember('dashboard.stats', 300, function() {
            return [
                'total_products' => Product::count(),
                'total_orders' => Order::count(),
                'total_revenue' => Order::sum('total_amount'),
                'pending_orders' => Order::where('status', 'pending')->count(),
            ];
        });
    }
}
```

---

**RESUMEN FASE 4**:
- ⏱️ Tiempo: 5-7 días
- ⚡ Performance: +60%
- ✅ Escalabilidad mejorada

---

### FASE 5: DTOs Y VALUE OBJECTS (3 días)

**Objetivo**: Type-safety y validación

#### 5.1 DTOs para transferencia de datos

```
app/DTOs/
├── Product/
│   ├── CreateProductDTO.php
│   └── UpdateProductDTO.php
├── Order/
│   └── CreateOrderDTO.php
└── Customer/
    └── CreateCustomerDTO.php
```

**Ejemplo**:
```php
class CreateProductDTO
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $description,
        public readonly string $sku,
        public readonly ?array $images,
        public readonly bool $hasConfigurator,
        // ... todos los campos tipados
    ) {}

    public static function fromRequest(StoreProductRequest $request): self
    {
        return new self(
            name: $request->name,
            description: $request->description,
            sku: $request->sku,
            images: $request->file('images'),
            hasConfigurator: $request->boolean('has_configurator'),
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'sku' => $this->sku,
            // ...
        ];
    }
}
```

**Beneficios**:
- Type-safety (PHP 8.2+)
- IDE autocomplete
- Validación en construcción
- Inmutabilidad (readonly)

---

#### 5.2 Value Objects

```php
// app/ValueObjects/Money.php
final class Money
{
    private function __construct(
        public readonly float $amount,
        public readonly string $currency = 'EUR'
    ) {
        if ($amount < 0) {
            throw new InvalidArgumentException('Amount cannot be negative');
        }
    }

    public static function fromFloat(float $amount): self
    {
        return new self($amount);
    }

    public function formatted(): string
    {
        return '€' . number_format($this->amount, 2, ',', '.');
    }

    public function add(Money $other): self
    {
        return new self($this->amount + $other->amount, $this->currency);
    }
}

// Uso
$price = Money::fromFloat(125.50);
$total = $price->add(Money::fromFloat(25.00));
echo $total->formatted(); // "€150,50"
```

---

**RESUMEN FASE 5**:
- ⏱️ Tiempo: 3 días
- ✅ Type-safety
- ✅ Validación en compile-time

---

### FASE 6: ENUMS Y TRAITS (2 días)

**Objetivo**: Eliminar strings mágicos

#### 6.1 Enums para estados

```php
// app/Enums/OrderStatus.php
enum OrderStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case APPROVED = 'approved';
    case IN_PRODUCTION = 'in_production';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pendiente',
            self::PROCESSING => 'Procesando',
            self::APPROVED => 'Aprobado',
            self::IN_PRODUCTION => 'En Producción',
            self::SHIPPED => 'Enviado',
            self::DELIVERED => 'Entregado',
            self::CANCELLED => 'Cancelado',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDING => 'yellow',
            self::PROCESSING => 'blue',
            self::APPROVED => 'green',
            self::CANCELLED => 'red',
            default => 'gray',
        };
    }

    public function canTransitionTo(OrderStatus $newStatus): bool
    {
        return match($this) {
            self::PENDING => in_array($newStatus, [self::PROCESSING, self::CANCELLED]),
            self::PROCESSING => in_array($newStatus, [self::APPROVED, self::CANCELLED]),
            // ... lógica de transiciones
        };
    }
}

// En modelo
protected $casts = [
    'status' => OrderStatus::class,
];

// Uso
$order->status = OrderStatus::APPROVED;
echo $order->status->label(); // "Aprobado"

if (!$order->status->canTransitionTo(OrderStatus::SHIPPED)) {
    throw new InvalidStateTransitionException();
}
```

---

#### 6.2 Traits para código compartido

```php
// app/Models/Traits/HasActiveScope.php
trait HasActiveScope
{
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}

// app/Models/Traits/Searchable.php
trait Searchable
{
    public function scopeSearch($query, string $term)
    {
        if (empty($term)) return $query;

        $term = $this->sanitizeSearchTerm($term);

        return $query->where(function($q) use ($term) {
            foreach ($this->getSearchableFields() as $field) {
                $q->orWhere($field, 'LIKE', "%{$term}%");
            }
        });
    }

    abstract protected function getSearchableFields(): array;
}

// Uso en modelos
class Product extends Model
{
    use HasActiveScope, Searchable;

    protected function getSearchableFields(): array
    {
        return ['name', 'sku', 'description'];
    }
}
```

---

**RESUMEN FASE 6**:
- ⏱️ Tiempo: 2 días
- ✅ Type-safety mejorado
- ✅ Lógica centralizada

---

## 📊 ROADMAP COMPLETO

### Resumen por Fases

| Fase | Tiempo | Prioridad | Beneficios |
|------|--------|-----------|------------|
| **Fase 1: Seguridad y Quick Wins** | 3-4 días | 🔴 ALTA | Seguridad +20%, Performance +30% |
| **Fase 2: Services** | 8-10 días | 🔴 ALTA | Mantenibilidad +60%, Testabilidad +80% |
| **Fase 3: Repositories** | 5-7 días | 🟠 MEDIA | Abstracción, Caché |
| **Fase 4: Jobs y Optimización** | 5-7 días | 🟠 MEDIA | Performance +60% |
| **Fase 5: DTOs y Value Objects** | 3 días | 🟡 BAJA | Type-safety |
| **Fase 6: Enums y Traits** | 2 días | 🟡 BAJA | Código limpio |

**Total**: 26-33 días (~5-7 semanas)

---

## 🎯 RECOMENDACIONES

### Opción A: Mínimo Viable (1 semana)

**Solo Fase 1**:
- Seguridad crítica ✅
- Quick wins de performance ✅
- Código duplicado eliminado ✅

**Inversión**: 3-4 días
**ROI**: Muy alto

---

### Opción B: Refactorización Sólida (3-4 semanas) ⭐ RECOMENDADO

**Fases 1 + 2 + 3**:
- Todo de Fase 1 ✅
- Services completos ✅
- Repositories ✅
- Arquitectura limpia ✅

**Inversión**: 16-21 días
**ROI**: Excelente
**Proyecto queda**: 9/10 en arquitectura

---

### Opción C: Refactorización Completa (5-7 semanas)

**Todas las fases**:
- Arquitectura perfecta ✅
- Type-safety completo ✅
- Máxima escalabilidad ✅

**Inversión**: 26-33 días
**ROI**: Bueno para proyectos grandes

---

## 📈 MÉTRICAS DE ÉXITO

### Antes vs Después

| Métrica | Antes | Después | Mejora |
|---------|-------|---------|--------|
| **Líneas en Controllers** | 400+ | <200 | -50% |
| **Código duplicado** | 15% | 0% | -100% |
| **N+1 Queries** | 8 detectadas | 0 | -100% |
| **Cobertura de tests** | 18% | 70%+ | +400% |
| **Tiempo respuesta Dashboard** | 500ms | 100ms | -80% |
| **Queries por request** | 20+ | <10 | -50% |
| **Mantenibilidad** | 5/10 | 9/10 | +80% |

---

## 🚀 CÓMO EMPEZAR

### Paso 1: Elegir Opción

Revisar las 3 opciones y decidir según:
- Tiempo disponible
- Presupuesto
- Complejidad del proyecto futuro

### Paso 2: Iniciar Fase 1

**Día 1**:
- Mover rutas de testing (2h)
- Mejorar rate limiting (4h)

**Día 2-3**:
- Crear CsvExportService (1 día)
- Implementar caché básica (1 día)

### Paso 3: Tests

Después de cada refactorización:
```bash
php artisan test
```

Crear tests para nuevos Services.

---

## 📚 RECURSOS

- [Laravel Best Practices](https://github.com/alexeymezenin/laravel-best-practices)
- [Repository Pattern](https://laravel.com/docs/11.x/eloquent)
- [Service Layer Pattern](https://martinfowler.com/eaaCatalog/serviceLayer.html)
- [Clean Architecture](https://blog.cleancoder.com/uncle-bob/2012/08/13/the-clean-architecture.html)

---

## ⚠️ ADVERTENCIAS

1. **No refactorizar todo de golpe**: Hacer por fases
2. **Tests obligatorios**: Crear tests antes de refactorizar
3. **Git branches**: Una rama por fase
4. **Code review**: Revisión de cada PR
5. **Documentación**: Actualizar docs con cambios

---

**Creado**: 6 Noviembre 2025
**Versión**: 1.0
**Estado**: 📋 Plan completo - Listo para ejecutar

---

¿Quieres empezar con la Fase 1? 🚀
