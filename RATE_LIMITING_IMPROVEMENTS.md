# 🛡️ Mejoras en Rate Limiting - Implementadas

**Fecha**: 2025-11-06
**Fase**: Phase 1 - Task 1.2

---

## 📋 Resumen de Cambios

Se ha implementado un **sistema de rate limiting multi-nivel** (minuto/hora/día) con limitadores personalizados para prevenir abuso de API y mejorar la seguridad.

### ❌ Problemas Detectados (Antes)

1. **Pedidos sin protección adecuada**: 10 pedidos/min = 600/hora (vulnerable a spam)
2. **Cálculo de precios**: 30/min sin límite horario (vulnerable a scraping)
3. **Rutas sin rate limiting**: `/api/v1/categories/{category}/products` y `/api/v1/subcategories/{subcategory}/products` completamente desprotegidas
4. **Rutas API no cargadas**: `routes/api.php` no se estaba cargando en `bootstrap/app.php`

### ✅ Solución Implementada

#### 1. Rate Limiters Personalizados

**Archivo**: `app/Providers/AppServiceProvider.php`

Se crearon 5 rate limiters con protección multi-nivel:

| Rate Limiter | Límites | Uso |
|--------------|---------|-----|
| `public-read` | 100/min, 1000/hora | Lectura de categorías, productos, subcategorías |
| `price-calculation` | 20/min, 200/hora | Cálculo dinámico de precios |
| `orders` | **2/min, 10/hora, 50/día** | Creación de pedidos (MUY RESTRICTIVO) |
| `api-strict` | 30/min, 300/hora | Validación, atributos, configuración |
| `api` | 60/min | Endpoints generales |

#### 2. Protección Multi-Nivel para Pedidos

El rate limiter `orders` es el más restrictivo con **3 niveles de protección**:

```php
RateLimiter::for('orders', function (Request $request) {
    return [
        // Nivel 1: Por minuto
        Limit::perMinute(2)->by($request->ip())
            ->response(function (Request $request, array $headers) {
                return response()->json([
                    'error' => 'Demasiadas solicitudes. Límite: 2 pedidos por minuto.',
                    'retry_after' => $headers['Retry-After'] ?? 60,
                ], 429);
            }),

        // Nivel 2: Por hora
        Limit::perHour(10)->by($request->ip())
            ->response(function (Request $request, array $headers) {
                return response()->json([
                    'error' => 'Límite horario excedido. Límite: 10 pedidos por hora.',
                    'retry_after' => $headers['Retry-After'] ?? 3600,
                ], 429);
            }),

        // Nivel 3: Por día
        Limit::perDay(50)->by($request->ip())
            ->response(function (Request $request, array $headers) {
                return response()->json([
                    'error' => 'Límite diario excedido. Límite: 50 pedidos por día.',
                    'retry_after' => $headers['Retry-After'] ?? 86400,
                ], 429);
            }),
    ];
});
```

#### 3. Actualización de Rutas API

**Archivo**: `routes/api.php`

**Antes:**
```php
// Todo con throttle:api genérico
Route::prefix('v1')->middleware(['throttle:api'])->group(function () {
    Route::get('categories', ...);
    Route::post('orders', ...)->middleware('throttle:10,1'); // 10/min
    Route::post('products/{product}/price', ...)->middleware('throttle:30,1'); // 30/min
    // ...
});

// Rutas sin protección ❌
Route::prefix('v1')->group(function () {
    Route::get('categories/{category}/products', ...); // SIN RATE LIMITING
});
```

**Después:**
```php
// Separación por tipo de operación
Route::prefix('v1')->group(function () {

    // Lectura pública (100/min, 1000/hora)
    Route::middleware(['throttle:public-read'])->group(function () {
        Route::get('categories', ...);
        Route::get('products', ...);
        Route::get('orders/{order}', ...);
    });

    // Creación de pedidos (2/min, 10/hora, 50/día) ✅
    Route::post('orders', ...)->middleware('throttle:orders');

    // Configurador
    Route::prefix('configurator')->group(function () {
        // Configuración inicial (100/min, 1000/hora)
        Route::get('products/{product}/config', ...)->middleware('throttle:public-read');

        // Cálculo de precios (20/min, 200/hora) ✅
        Route::post('products/{product}/price', ...)->middleware('throttle:price-calculation');

        // Operaciones de validación (30/min, 300/hora)
        Route::post('products/{product}/attributes', ...)->middleware('throttle:api-strict');
        Route::post('products/{product}/validate', ...)->middleware('throttle:api-strict');
    });
});

// Rutas adicionales AHORA PROTEGIDAS ✅
Route::prefix('v1')->middleware(['throttle:public-read'])->group(function () {
    Route::get('categories/{category}/products', ...);
    Route::get('subcategories/{subcategory}/products', ...);
});
```

#### 4. Carga de Rutas API

**Archivo**: `bootstrap/app.php`

**Antes:**
```php
->withRouting(
    web: __DIR__.'/../routes/web.php',
    // ❌ routes/api.php no se cargaba
)
```

**Después:**
```php
->withRouting(
    web: __DIR__.'/../routes/web.php',
    api: __DIR__.'/../routes/api.php', // ✅ API routes ahora se cargan
    commands: __DIR__.'/../routes/console.php',
    health: '/up',
)
```

#### 5. Documentación en .env.example

Se documentaron todos los rate limiters en `.env.example`:

```env
# Rate Limiting - Multi-nivel (minuto/hora/día)
# Los rate limiters están configurados en AppServiceProvider:
#
# - public-read: 100/min, 1000/hora (lectura de categorías, productos)
# - price-calculation: 20/min, 200/hora (cálculo de precios)
# - orders: 2/min, 10/hora, 50/día (creación de pedidos - MUY RESTRICTIVO)
# - api-strict: 30/min, 300/hora (validación, configuración)
# - api: 60/min (general)
#
# No se requieren variables de entorno para rate limiting
```

---

## 📊 Comparación Antes/Después

| Endpoint | Antes | Después | Mejora |
|----------|-------|---------|--------|
| `POST /api/v1/orders` | 10/min (600/hora) | **2/min, 10/hora, 50/día** | ✅ 80% reducción + límites horario/diario |
| `POST /api/v1/configurator/.../price` | 30/min | **20/min, 200/hora** | ✅ 33% reducción + límite horario |
| `GET /api/v1/categories` | 60/min | **100/min, 1000/hora** | ✅ Aumento para usabilidad + límite horario |
| `GET /api/v1/categories/{id}/products` | ❌ SIN LÍMITE | **100/min, 1000/hora** | ✅ Protección agregada |
| `POST /api/v1/configurator/.../validate` | 60/min | **30/min, 300/hora** | ✅ Más restrictivo para operaciones críticas |

---

## 🔒 Beneficios de Seguridad

### 1. Prevención de Spam de Pedidos
- **Antes**: Un atacante podía crear 600 pedidos/hora desde una IP
- **Después**: Máximo 2/min, 10/hora, 50/día = **protección efectiva contra bots**

### 2. Prevención de Scraping de Precios
- **Antes**: 30/min = 1,800 cálculos/hora (suficiente para extraer toda la base de precios)
- **Después**: 20/min + 200/hora = **límite horario previene scraping masivo**

### 3. Protección de Endpoints Desprotegidos
- **Antes**: `/api/v1/categories/{id}/products` sin límite = vulnerable a DDoS
- **Después**: 100/min + 1000/hora = **protección contra abuso**

### 4. Respuestas de Error Informativas
Los rate limiters incluyen mensajes personalizados con `retry_after`:

```json
{
  "error": "Límite horario excedido. Límite: 10 pedidos por hora.",
  "retry_after": 3600
}
```

Esto permite a los clientes legítimos saber **cuándo pueden reintentar**.

---

## 🧪 Verificación

### Comprobar Rate Limiters Registrados

```bash
# Limpiar cache de configuración
php artisan config:clear

# Ver rutas con middleware
php artisan route:list | grep api/v1
```

### Ejemplo de Salida
```
POST    api/v1/orders .................... Api\OrderController@store
POST    api/v1/configurator/products/{product}/price configurator.price
GET     api/v1/categories ................ Api\CategoryController@index
```

### Probar Rate Limiting (Opcional)

Con `curl` o Postman, hacer múltiples requests a:
- `POST /api/v1/orders` - Debe rechazar después de 2 requests/min
- `POST /api/v1/configurator/products/1/price` - Debe rechazar después de 20/min

---

## 📁 Archivos Modificados

| Archivo | Cambios |
|---------|---------|
| `app/Providers/AppServiceProvider.php` | ✅ Agregados 5 rate limiters personalizados (65 líneas) |
| `routes/api.php` | ✅ Aplicados nuevos rate limiters a todos los endpoints |
| `bootstrap/app.php` | ✅ Agregada carga de `routes/api.php` |
| `.env.example` | ✅ Documentación de rate limiters |

---

## ✅ Estado: COMPLETADO

**Impacto en Seguridad**: 🔒🔒🔒🔒🔒 (5/5)
**Nivel de Protección**: **Alto** contra spam, scraping y DDoS

---

## 📚 Referencias

- [Laravel Rate Limiting Docs](https://laravel.com/docs/12.x/routing#rate-limiting)
- REFACTORING_PLAN.md - Fase 1, Tarea 1.2
- SECURITY_FIXES_2025_11_06.md

---

**Próxima Tarea**: Task 1.3 - Crear CsvExportService
