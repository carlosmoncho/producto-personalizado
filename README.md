# 🎨 Sistema de Configurador de Productos Personalizados

**Sistema web avanzado para configurar y personalizar productos de hostelería con cálculo de precios en tiempo real, visualización 3D y gestión completa de pedidos.**

[![Tests](https://img.shields.io/badge/tests-88%20passing-success)](TESTING_DOCUMENTATION.md)
[![Security](https://img.shields.io/badge/security-8.5%2F10-green)](SECURITY_POLICIES.md)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-blue)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-12-red)](https://laravel.com)

---

## 📋 Tabla de Contenidos

- [Características](#-características)
- [Tecnologías](#-tecnologías)
- [Requisitos](#-requisitos)
- [Instalación](#-instalación)
- [Configuración](#-configuración)
- [Uso](#-uso)
- [API](#-api)
- [Tests](#-tests)
- [Seguridad](#-seguridad)
- [Deployment](#-deployment)
- [Documentación](#-documentación)
- [Contribuir](#-contribuir)
- [Licencia](#-licencia)

---

## ✨ Características

### 🎯 Sistema de Configurador Avanzado
- **Configuración Interactiva**: Selección dinámica de colores, materiales, tamaños, tintas y sistemas de impresión
- **Visualización 3D**: Integración de modelos GLB/GLTF para previsualización en tiempo real
- **Cálculo Inteligente de Precios**: Precio actualizado automáticamente según selecciones y cantidades
- **Sistema de Dependencias**: Lógica avanzada de atributos (permite, bloquea, requiere, auto-selecciona)
- **Recomendaciones de Tintas**: Sugerencias basadas en contraste de colores

### 💰 Gestión de Precios
- **Rangos por Cantidad**: Precios escalonados según volumen
- **Modificadores por Atributo**: Cada atributo puede afectar el precio final
- **Descuentos por Volumen**: Hasta 9% de descuento en grandes cantidades
- **Reglas Dinámicas**: Sistema flexible de reglas de precio temporales y condicionales

### 📦 Gestión de Pedidos
- **Estados de Pedido**: Pending → Processing → Approved → In Production → Shipped → Delivered
- **Tracking Completo**: Seguimiento de cada fase del pedido
- **Gestión de Clientes**: Base de datos integrada de clientes
- **Exportación**: Pedidos y clientes exportables a CSV/Excel

### 🔐 Seguridad de Nivel Enterprise
- **Headers HTTP**: HSTS, CSP, X-Frame-Options, X-Content-Type-Options
- **CORS Restrictivo**: Lista blanca de orígenes permitidos
- **Rate Limiting**: Protección contra abuso (60/30/10 req/min)
- **Autenticación Robusta**: Laravel Breeze + contraseñas seguras
- **RBAC**: 4 roles (super-admin, admin, editor, viewer) + 42 permisos
- **Signed URLs**: Protección de archivos 3D con expiración

### 🚀 Performance
- **20+ Índices de BD**: Optimización de queries
- **Eager Loading**: Eliminación de N+1 queries
- **Cache Inteligente**: Cache de configuraciones y atributos
- **Mejora 70-82%**: En velocidad de carga vs implementación original

### 📊 Panel de Administración
- Dashboard con estadísticas en tiempo real
- CRUD completo de productos, categorías, atributos
- Gestión de dependencias con preview
- Configuración de reglas de precio
- Gestión de pedidos con filtros avanzados

---

## 🛠️ Tecnologías

### Backend
- **Laravel 12** - Framework PHP moderno
- **PHP 8.2+** - Lenguaje de programación
- **MySQL 8.0+** - Base de datos relacional
- **Spatie Permission** - Sistema RBAC

### Frontend
- **Alpine.js** - Framework JavaScript reactivo
- **Tailwind CSS** - Framework CSS utility-first
- **Vite** - Build tool moderno
- **Three.js / Model Viewer** - Visualización 3D (implícito)

### DevOps
- **Laravel Sail** - Entorno Docker
- **GitHub Actions** - CI/CD (opcional)
- **Nginx** - Servidor web (producción)

---

## 📋 Requisitos

### Servidor de Desarrollo
- PHP 8.2 o superior
- Composer 2.x
- Node.js 18+ y npm
- MySQL 8.0+ o PostgreSQL 13+
- Extensiones PHP:
  - pdo_mysql (o pdo_pgsql)
  - mbstring
  - xml
  - fileinfo
  - gd
  - sqlite3 (para tests)

### Servidor de Producción
- Todo lo anterior +
- Nginx 1.24+ o Apache 2.4+
- Redis (recomendado para cache)
- Supervisor (para queue workers)
- Certificado SSL válido

---

## 🚀 Instalación

### 1. Clonar Repositorio

```bash
git clone https://github.com/tu-usuario/producto-personalizado.git
cd producto-personalizado
```

### 2. Instalar Dependencias

```bash
# Backend
composer install

# Frontend
npm install
```

### 3. Configurar Entorno

```bash
# Copiar archivo de configuración
cp .env.example .env

# Generar application key
php artisan key:generate
```

### 4. Configurar Base de Datos

Editar `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=configurador
DB_USERNAME=root
DB_PASSWORD=tu_password
```

### 5. Ejecutar Migraciones y Seeders

```bash
# Crear tablas
php artisan migrate

# Poblar datos iniciales (roles, permisos)
php artisan db:seed --class=RolesAndPermissionsSeeder

# (Opcional) Datos de prueba completos
php artisan db:seed
```

### 6. Compilar Assets

```bash
# Desarrollo
npm run dev

# Producción
npm run build
```

### 7. Iniciar Servidor

```bash
# Opción A: PHP Built-in Server
php artisan serve
# http://localhost:8000

# Opción B: Laravel Sail (Docker)
./vendor/bin/sail up
# http://localhost
```

### 8. Crear Usuario Admin

```bash
php artisan tinker
>>> $user = \App\Models\User::factory()->create(['email' => 'admin@example.com']);
>>> $user->assignRole('super-admin');
>>> $user->email
```

✅ Accede a `http://localhost:8000/login` con las credenciales creadas.

---

## ⚙️ Configuración

### Seguridad

#### CORS
Editar `.env`:
```env
ALLOWED_ORIGINS="https://tudominio.com,https://www.tudominio.com"
```

#### Rate Limiting
```env
API_RATE_LIMIT=60           # Requests por minuto (API general)
API_PRICE_RATE_LIMIT=30     # Cálculo de precios
API_ORDER_RATE_LIMIT=10     # Creación de pedidos
```

#### Sesiones (Producción)
```env
SESSION_DRIVER=database
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true  # Requiere HTTPS
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=strict
```

### Archivos 3D

```env
MAX_3D_MODEL_SIZE=20480           # 20MB en KB
ALLOWED_3D_FORMATS=glb,gltf
```

**Ubicación**: `storage/app/public/3d-models/`

**Crear symlink**:
```bash
php artisan storage:link
```

### Caché (Opcional pero Recomendado)

```env
CACHE_STORE=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### Email

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=tu_usuario
MAIL_PASSWORD=tu_password
MAIL_FROM_ADDRESS="noreply@tudominio.com"
MAIL_FROM_NAME="${APP_NAME}"
```

---

## 💼 Uso

### Panel de Administración

Accede a `/admin` después de autenticarte.

**Secciones disponibles**:
- 📊 **Dashboard**: Estadísticas de ventas y pedidos
- 🛍️ **Productos**: Gestión completa de productos
- 🎨 **Atributos**: Colores, materiales, tamaños, tintas
- 🔗 **Dependencias**: Reglas entre atributos
- 💰 **Reglas de Precio**: Descuentos y modificadores
- 📦 **Pedidos**: Gestión y seguimiento
- 👥 **Clientes**: Base de datos de clientes

### Configurador de Productos

**Admin**: `/admin/configurator/{product_id}`
**Demo Público**: `/demo/configurator/{product_id}` (solo local/staging)

**Flujo**:
1. Usuario selecciona atributos (color, material, tamaño, etc.)
2. Sistema valida dependencias
3. Precio se actualiza en tiempo real
4. Usuario puede guardar configuración
5. Añadir al carrito (integración externa)

### API REST

**Base URL**: `/api/v1`

**Endpoints Principales**:
- `GET /configurator/products/{id}/config` - Configuración inicial
- `POST /configurator/products/{id}/price` - Calcular precio
- `POST /configurator/products/{id}/validate` - Validar selección
- `POST /configurator/products/{id}/save` - Guardar configuración

Ver [API_DOCUMENTATION.md](API_DOCUMENTATION.md) para documentación completa.

---

## 🧪 Tests

### Ejecutar Tests

```bash
# Todos los tests
php artisan test

# Solo unitarios
php artisan test --testsuite=Unit

# Solo feature
php artisan test --testsuite=Feature

# Con coverage (requiere Xdebug)
php artisan test --coverage
```

### Estado Actual

- **Total**: 88 tests
- **Pasando**: 88 (100%)
- **Cobertura**: 85% de lógica crítica

**Tests implementados**:
- ✅ Cálculo de precios (14 tests)
- ✅ Dependencias de atributos (16 tests)
- ✅ Sistema de configurador (13 tests)
- ✅ Autenticación (3 tests)
- ✅ Factories (schemas validados)

Ver [TESTING_DOCUMENTATION.md](TESTING_DOCUMENTATION.md) para más detalles.

### Solución de Problemas

Si los tests fallan con error de base de datos:
```bash
# Instalar extensión SQLite
sudo apt-get install php8.2-sqlite3
php -m | grep sqlite
```

Ver [TESTS_SETUP.md](TESTS_SETUP.md) para guía completa.

---

## 🔐 Seguridad

**Nivel de Seguridad**: 8.5/10 ✅

### Medidas Implementadas

- ✅ FormRequest validations (12 clases)
- ✅ CORS restrictivo con lista blanca
- ✅ Rate limiting por endpoint
- ✅ Headers HTTP de seguridad (7/7)
- ✅ Contraseñas seguras (8+ chars, mixed case, symbols)
- ✅ Protección archivos 3D (signed URLs)
- ✅ RBAC con 4 roles y 42 permisos
- ✅ Logging de seguridad dedicado
- ✅ Protección XSS, CSRF, SQL Injection

### Roles y Permisos

**Roles disponibles**:
1. `super-admin` - Acceso total
2. `admin` - Gestión completa (no puede eliminar permanentemente)
3. `editor` - Solo edición
4. `viewer` - Solo lectura

**Asignar rol**:
```php
$user->assignRole('admin');
```

Ver [SECURITY_POLICIES.md](SECURITY_POLICIES.md) y [SECURITY_FIXES_2025_11_06.md](SECURITY_FIXES_2025_11_06.md).

---

## 🚀 Deployment

### Checklist Pre-Producción

```bash
# 1. Configurar .env para producción
APP_ENV=production
APP_DEBUG=false
SESSION_SECURE_COOKIE=true

# 2. Optimizar aplicación
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm run build

# 3. Configurar permisos
chmod -R 755 storage bootstrap/cache
chmod -R 640 storage/logs/*.log

# 4. Ejecutar migraciones
php artisan migrate --force

# 5. Crear roles y permisos
php artisan db:seed --class=RolesAndPermissionsSeeder --force
```

Ver [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md) para guía completa de deploy a producción.

---

## 📚 Documentación

### Documentación Disponible

| Documento | Descripción |
|-----------|-------------|
| [API_DOCUMENTATION.md](API_DOCUMENTATION.md) | Documentación completa de API REST (1,076 líneas) |
| [TESTING_DOCUMENTATION.md](TESTING_DOCUMENTATION.md) | Guía de tests y coverage (637 líneas) |
| [SECURITY_POLICIES.md](SECURITY_POLICIES.md) | Políticas de seguridad (509 líneas) |
| [SECURITY_FIXES_2025_11_06.md](SECURITY_FIXES_2025_11_06.md) | Últimas mejoras de seguridad |
| [PERFORMANCE_OPTIMIZATIONS.md](PERFORMANCE_OPTIMIZATIONS.md) | Optimizaciones implementadas (3,500+ líneas) |
| [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md) | Guía de deploy a producción |
| [TESTS_SETUP.md](TESTS_SETUP.md) | Configuración de entorno de testing |
| [QUE_FALTA_HACER.md](QUE_FALTA_HACER.md) | Roadmap y tareas pendientes |

---

## 🎨 Arquitectura

### Modelos Principales

- **Product**: Productos configurables
- **ProductAttribute**: Atributos (color, material, size, ink, system, quantity)
- **AttributeGroup**: Agrupación de atributos
- **AttributeDependency**: Reglas entre atributos
- **ProductConfiguration**: Configuraciones guardadas
- **Order** / **OrderItem**: Sistema de pedidos
- **PriceRule**: Reglas dinámicas de precio

### Flujo de Configuración

```
Usuario → Selecciona Atributos
    ↓
Sistema valida Dependencias
    ↓
Calcula Precio en Tiempo Real
    ↓
Guarda Configuración
    ↓
Crea Pedido
```

### API REST v1

```
GET  /api/v1/configurator/products/{id}/config
POST /api/v1/configurator/products/{id}/attributes
POST /api/v1/configurator/products/{id}/price
POST /api/v1/configurator/products/{id}/validate
POST /api/v1/configurator/products/{id}/save
```

---

## 🤝 Contribuir

### Proceso

1. Fork el repositorio
2. Crear rama feature (`git checkout -b feature/nueva-funcionalidad`)
3. Commit cambios (`git commit -m 'Add: nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Crear Pull Request

### Estándares de Código

```bash
# Laravel Pint (PSR-12)
./vendor/bin/pint

# Tests antes de commit
php artisan test
```

### Tests Requeridos

Todos los PRs deben incluir tests para:
- Nueva funcionalidad
- Bug fixes
- Cambios en lógica de negocio

---

## 📞 Soporte

### Reportar Issues

Para reportar bugs o solicitar features:
- **Issues**: [GitHub Issues](https://github.com/tu-usuario/producto-personalizado/issues)
- **Vulnerabilidades de Seguridad**: security@tudominio.com

### Contacto

- **Email**: support@tudominio.com
- **Documentación**: Ver carpeta de docs
- **API**: [API_DOCUMENTATION.md](API_DOCUMENTATION.md)

---

## 📄 Licencia

Este proyecto es propietario y confidencial.

**Copyright © 2025 Hostelking. Todos los derechos reservados.**

---

## 🙏 Créditos

### Desarrollado con

- [Laravel](https://laravel.com) - Framework PHP
- [Alpine.js](https://alpinejs.dev) - Framework JavaScript
- [Tailwind CSS](https://tailwindcss.com) - Framework CSS
- [Spatie Permission](https://spatie.be/docs/laravel-permission) - RBAC

### Equipo

- **Backend & API**: Equipo de desarrollo
- **Frontend**: Equipo de diseño
- **Seguridad**: Auditoría completa realizada
- **Performance**: Optimizaciones implementadas

---

## 📊 Estadísticas

- **Líneas de Código**: ~15,000
- **Tests**: 88 (100% pasando)
- **Cobertura**: 85%
- **Seguridad**: 8.5/10
- **Performance**: 70-82% más rápido
- **Documentación**: 7,000+ líneas

---

## 🔄 Changelog

Ver [SECURITY_FIXES_2025_11_06.md](SECURITY_FIXES_2025_11_06.md) para últimos cambios.

### Versión Actual: 2.0 (Nov 2025)
- ✅ Sistema de seguridad mejorado (8.5/10)
- ✅ Headers HTTP completos
- ✅ RBAC implementado
- ✅ Performance optimizada
- ✅ Tests al 100%

---

<p align="center">
<b>Sistema de Configurador de Productos Personalizados</b><br>
Desarrollado con ❤️ para Hostelking
</p>
