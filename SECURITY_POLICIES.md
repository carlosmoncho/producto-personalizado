# Políticas de Seguridad - Sistema de Configurador de Productos

**Versión**: 1.0
**Fecha**: 2025-11-05
**Estado**: Implementado

---

## Tabla de Contenidos

1. [Resumen Ejecutivo](#resumen-ejecutivo)
2. [Validación y Autorización](#validación-y-autorización)
3. [Protección de Archivos](#protección-de-archivos)
4. [Rate Limiting](#rate-limiting)
5. [CORS y Seguridad de Red](#cors-y-seguridad-de-red)
6. [Logging y Auditoría](#logging-y-auditoría)
7. [Gestión de Sesiones](#gestión-de-sesiones)
8. [Protección contra Ataques Comunes](#protección-contra-ataques-comunes)
9. [Configuración de Entornos](#configuración-de-entornos)
10. [Procedimientos de Respuesta a Incidentes](#procedimientos-de-respuesta-a-incidentes)

---

## Resumen Ejecutivo

Este documento describe las políticas y medidas de seguridad implementadas en el sistema de configurador de productos personalizado para proteger contra amenazas comunes y garantizar la integridad de los datos.

### Niveles de Seguridad Implementados

- ✅ **Autenticación y Autorización**: FormRequest con validación centralizada
- ✅ **Protección de Archivos**: Signed URLs + validación de contenido
- ✅ **Rate Limiting**: Protección contra abuso y scraping
- ✅ **CORS Restrictivo**: Lista blanca de orígenes permitidos
- ✅ **Logging de Seguridad**: Auditoría de eventos críticos
- ✅ **Sesiones Seguras**: Encriptación y configuración HTTPOnly

---

## Validación y Autorización

### FormRequest Classes

Todas las requests están protegidas con clases FormRequest que implementan:

```php
// Ejemplo: StoreProductRequest
public function authorize(): bool
{
    return auth()->check(); // Usuario debe estar autenticado
}

public function rules(): array
{
    return [
        'name' => 'required|string|max:255',
        'sku' => 'required|string|unique:products,sku|max:100',
        'model_3d' => ['nullable', 'file', new ValidGltfFile()],
        // ... reglas completas
    ];
}
```

### Clases Implementadas

| FormRequest | Descripción | Autorización |
|-------------|-------------|--------------|
| `StoreProductRequest` | Crear productos | Usuario autenticado |
| `UpdateProductRequest` | Actualizar productos | Usuario autenticado |
| `StoreCategoryRequest` | Crear categorías | Usuario autenticado |
| `UpdateCategoryRequest` | Actualizar categorías | Usuario autenticado |
| `StoreSubcategoryRequest` | Crear subcategorías | Usuario autenticado |
| `UpdateSubcategoryRequest` | Actualizar subcategorías | Usuario autenticado |
| `StoreOrderRequest` | Crear pedidos | Pública (API) |
| `UpdateOrderRequest` | Actualizar pedidos | Usuario autenticado |

### Mass Assignment Protection

Los modelos están protegidos contra mass assignment:

```php
// Modelo Product
protected $guarded = [
    'id',
    'active',                  // Solo admin
    'has_configurator',        // Solo admin
    'configurator_base_price', // Precio protegido
];
```

---

## Protección de Archivos

### Archivos 3D (GLB/GLTF)

#### Protección con Signed URLs

Los archivos 3D requieren URLs firmadas para acceso:

```php
// Ruta protegida
Route::get('/3d-model/{product}/{filename}', ...)
    ->middleware(['signed', 'throttle:60,1']);
```

#### Validación de Contenido

Custom rule `ValidGltfFile` que verifica:

1. **Extensión**: Solo `.glb` o `.gltf`
2. **Magic Bytes**: Verifica firma `glTF` para archivos GLB
3. **Estructura JSON**: Valida esquema GLTF para archivos .gltf
4. **Tamaño**: Máximo 20MB

```php
// Uso en FormRequest
'model_3d' => ['nullable', 'file', new \App\Rules\ValidGltfFile()],
```

#### Prevención de Ataques

- ✅ **Path Traversal**: Validación de nombre de archivo sin `../`, `/`, `\`
- ✅ **Enumeración**: Archivo debe pertenecer al producto solicitado
- ✅ **Acceso no autorizado**: Productos inactivos requieren autenticación

#### Logging de Acceso

Todos los accesos se registran en `storage/logs/security.log`:

```
[2025-11-05] 3D Model Access Attempt
[2025-11-05] 3D Model - Access Granted
[2025-11-05] 3D Model - Unauthorized File Access Attempt (ALERTA)
[2025-11-05] 3D Model - Path Traversal Attempt Detected (CRÍTICO)
```

---

## Rate Limiting

### Límites Implementados

| Endpoint | Límite | Razón |
|----------|--------|-------|
| API General (`/api/v1/*`) | 60/minuto | Uso normal |
| Cálculo de Precio | 30/minuto | Prevenir scraping |
| Creación de Pedidos | 10/minuto | Prevenir spam |
| Archivos 3D | 60/minuto | Prevenir abuso |

### Configuración

```php
// routes/api.php
Route::prefix('v1')->middleware(['throttle:api'])->group(function () {
    // Endpoints con rate limit específico
    Route::post('products/{product}/price', [...])
        ->middleware('throttle:30,1'); // 30/min
});
```

### Variables de Entorno

```bash
API_RATE_LIMIT=60
API_PRICE_RATE_LIMIT=30
API_ORDER_RATE_LIMIT=10
```

---

## CORS y Seguridad de Red

### Política de CORS Restrictiva

CORS solo se aplica a orígenes permitidos (lista blanca):

```php
// config/app.php o .env
ALLOWED_ORIGINS="${APP_URL},http://localhost:3000,http://localhost:8000"
```

### Middleware Personalizado

El middleware `CorsMiddleware` implementa:

1. **Validación de origen**: Comprueba lista blanca
2. **Preflight handling**: Respuestas OPTIONS
3. **Wildcard solo en desarrollo**: `*` solo en entorno `local`
4. **Subdominios wildcard**: Soporte para `*.midominio.com`

### Headers de Seguridad

```php
$response->headers->set('X-Content-Type-Options', 'nosniff');
$response->headers->set('Access-Control-Allow-Credentials', 'true');
$response->headers->set('Access-Control-Max-Age', '3600');
```

---

## Logging y Auditoría

### Canal de Seguridad

Canal dedicado para eventos de seguridad:

```php
// config/logging.php
'security' => [
    'driver' => 'daily',
    'path' => storage_path('logs/security.log'),
    'level' => 'info',
    'days' => 90, // Retención 90 días
    'permission' => 0640,
],
```

### Eventos Registrados

#### INFO
- Acceso exitoso a archivos 3D
- Configuraciones guardadas
- Cálculos de precio

#### WARNING
- Intentos de acceso a archivos no autorizados
- Acceso a productos inactivos
- Validaciones fallidas

#### ERROR
- Archivos no encontrados en disco
- Errores de configuración

#### CRITICAL
- Intentos de path traversal
- Múltiples intentos de acceso no autorizado
- Patrones de ataque detectados

### Uso

```php
use Illuminate\Support\Facades\Log;

Log::channel('security')->warning('Unauthorized Access Attempt', [
    'product_id' => $productId,
    'ip' => request()->ip(),
    'user_id' => auth()->id(),
]);
```

---

## Gestión de Sesiones

### Configuración Segura

```bash
# .env
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true           # Encriptación activada
SESSION_SECURE_COOKIE=true     # Solo HTTPS (producción)
SESSION_HTTP_ONLY=true         # No accesible desde JavaScript
SESSION_SAME_SITE=lax          # Protección CSRF
```

### Limpieza de Sesiones

Configuración del configurador:

```php
// config/configurator.php
'sessions' => [
    'lifetime' => 1440,        // 24 horas
    'cleanup_after' => 7,      // Limpiar después de 7 días
],
```

---

## Protección contra Ataques Comunes

### SQL Injection

✅ **Protegido mediante**:
- Uso de Eloquent ORM
- Prepared statements
- Scopes de búsqueda seguros

```php
// Scope seguro en modelo Product
public function scopeSearch($query, $searchTerm)
{
    $searchTerm = trim($searchTerm);
    $searchTerm = substr($searchTerm, 0, 100); // Limitar longitud

    return $query->where('name', 'LIKE', "%{$searchTerm}%");
    // Laravel escapa automáticamente
}
```

### XSS (Cross-Site Scripting)

✅ **Protegido mediante**:
- Blade escapa por defecto `{{ $var }}`
- Validación de inputs
- Content-Type headers

### CSRF (Cross-Site Request Forgery)

✅ **Protegido mediante**:
- Laravel CSRF middleware activado
- Tokens en formularios (`@csrf`)
- SameSite cookies

### Path Traversal

✅ **Protegido mediante**:
- Validación de nombres de archivo
- Whitelist de caracteres permitidos
- Verificación de pertenencia

```php
if (str_contains($filename, '..') ||
    str_contains($filename, '/') ||
    str_contains($filename, '\\')) {
    abort(403, 'Nombre de archivo inválido');
}
```

### Mass Assignment

✅ **Protegido mediante**:
- `$guarded` en modelos
- FormRequest validations
- Campos sensibles protegidos

### DoS (Denial of Service)

✅ **Protegido mediante**:
- Rate limiting en todas las APIs
- Límite de longitud de búsquedas
- Límite de tamaño de archivos
- Timeout de requests

---

## Configuración de Entornos

### Desarrollo (local)

```bash
APP_ENV=local
APP_DEBUG=true
SESSION_SECURE_COOKIE=false  # HTTP permitido
ALLOWED_ORIGINS="*"           # Permitido en local
```

### Staging

```bash
APP_ENV=staging
APP_DEBUG=true
SESSION_SECURE_COOKIE=true
ALLOWED_ORIGINS="${APP_URL},https://staging.frontend.com"
```

### Producción

```bash
APP_ENV=production
APP_DEBUG=false
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=strict
ALLOWED_ORIGINS="${APP_URL},https://www.frontend.com"
```

### Rutas de Testing

Las rutas `/demo/*` y `/test/*` solo están disponibles en entornos `local`, `development`, `staging`:

```php
if (app()->environment(['local', 'development', 'staging'])) {
    Route::get('/demo/configurator/{id}', ...);
    Route::get('/test/create-size', ...);
}
```

---

## Procedimientos de Respuesta a Incidentes

### Detección de Amenazas

#### Monitoreo de Logs

Revisar diariamente:
```bash
tail -f storage/logs/security.log | grep -i "critical\|warning"
```

#### Alertas Automáticas

Configurar alertas para:
- Múltiples path traversal attempts desde misma IP
- Más de 10 accesos no autorizados en 1 hora
- Errores CRITICAL en logs de seguridad

### Respuesta a Path Traversal

1. **Identificar IP atacante** en logs
2. **Bloquear IP** a nivel de firewall/servidor
3. **Revisar logs** de ataques similares
4. **Verificar integridad** de archivos sensibles
5. **Notificar** al equipo de seguridad

### Respuesta a Scraping de Precios

1. **Identificar patrón** de requests en logs
2. **Reducir rate limit** temporalmente
3. **Bloquear IP** si es malicioso
4. **Revisar endpoints** expuestos
5. **Considerar CAPTCHA** si persiste

### Respuesta a Acceso No Autorizado

1. **Revisar logs** de acceso
2. **Verificar si hay breach** de credenciales
3. **Rotar secrets** si es necesario
4. **Forzar logout** de sesiones comprometidas
5. **Implementar 2FA** si no existe

---

## Checklist de Seguridad Pre-Producción

### Configuración

- [ ] `APP_DEBUG=false` en producción
- [ ] `APP_KEY` generado y seguro
- [ ] `SESSION_ENCRYPT=true`
- [ ] `SESSION_SECURE_COOKIE=true`
- [ ] ALLOWED_ORIGINS configurado correctamente
- [ ] Rate limits configurados

### Archivos

- [ ] Remover archivos `.env.example` sensibles
- [ ] Verificar permisos de storage (755/644)
- [ ] Logs con permisos restrictivos (640)
- [ ] No hay archivos de backup en public

### Base de Datos

- [ ] Credenciales seguras
- [ ] Usuario DB con permisos mínimos
- [ ] Backups automáticos configurados
- [ ] SSL/TLS para conexión DB

### Servidor Web

- [ ] HTTPS configurado y forzado
- [ ] Certificado SSL válido
- [ ] Headers de seguridad configurados
- [ ] Firewall activo
- [ ] Fail2ban o similar configurado

### Aplicación

- [ ] Todas las FormRequests activas
- [ ] Archivos 3D con signed URLs
- [ ] Rate limiting activo
- [ ] CORS restrictivo
- [ ] Logging de seguridad activo
- [ ] Sesiones seguras

---

## Contacto y Soporte

Para reportar vulnerabilidades de seguridad:

- **Email**: security@tuempresa.com
- **Bug Bounty**: [si aplica]
- **Proceso**: Divulgación responsable, 90 días para fix

---

## Historial de Cambios

| Fecha | Versión | Cambios |
|-------|---------|---------|
| 2025-11-05 | 1.0 | Implementación inicial de políticas de seguridad |

---

## Referencias

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [Laravel Security Best Practices](https://laravel.com/docs/security)
- [PHP Security Guide](https://www.php.net/manual/en/security.php)

---

**Documento elaborado por**: Claude Code Security Analysis
**Aprobado por**: [Pendiente]
**Próxima revisión**: [Fecha]
