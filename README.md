# Hostelking - Sistema de Productos Personalizados

Sistema backend para configurador de productos personalizados con cálculo de precios en tiempo real, gestión de atributos dinámicos y panel de administración.

## Stack Tecnológico

- **Framework:** Laravel 12
- **PHP:** 8.2+
- **Base de datos:** MySQL 8.0
- **Storage:** Local / Amazon S3
- **Autenticación:** Laravel Breeze + Sanctum
- **Contenedores:** Docker (Laravel Sail)

---

## Instalación

### Requisitos

- PHP 8.2+
- Composer 2.x
- Node.js 18+
- MySQL 8.0+ / PostgreSQL 13+
- Docker (opcional, para Sail)

### Pasos

```bash
# Clonar repositorio
git clone https://github.com/carlosmoncho/producto-personalizado.git
cd producto-personalizado

# Instalar dependencias
composer install
npm install

# Configurar entorno
cp .env.example .env
php artisan key:generate

# Base de datos
php artisan migrate
php artisan db:seed

# Compilar assets
npm run build

# Iniciar servidor
php artisan serve
# o con Sail
./vendor/bin/sail up -d
```

---

## Estructura del Proyecto

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/              # Panel de administración
│   │   │   ├── ProductController.php
│   │   │   ├── CategoryController.php
│   │   │   ├── OrderController.php
│   │   │   ├── CustomerController.php
│   │   │   ├── ProductAttributeController.php
│   │   │   ├── AttributeGroupController.php
│   │   │   ├── AttributeDependencyController.php
│   │   │   ├── PriceRuleController.php
│   │   │   └── PrintingSystemController.php
│   │   └── Api/
│   │       ├── V1/
│   │       │   └── ConfiguratorController.php  # API del configurador
│   │       ├── ProductController.php
│   │       ├── CategoryController.php
│   │       └── OrderController.php
│   └── Resources/V1/          # API Resources
├── Models/
│   ├── Product.php
│   ├── Category.php
│   ├── Subcategory.php
│   ├── ProductAttribute.php
│   ├── AttributeGroup.php
│   ├── AttributeDependency.php
│   ├── ProductAttributeValue.php
│   ├── ProductPricing.php
│   ├── PriceRule.php
│   ├── PrintingSystem.php
│   ├── Order.php
│   ├── OrderItem.php
│   └── Customer.php
├── Services/
│   └── File/
│       └── FileUploadService.php
└── Helpers/
    └── StorageHelper.php
```

---

## Modelos de Datos

### Product
Producto configurable con atributos dinámicos.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | bigint | ID único |
| name | string | Nombre del producto |
| slug | string | URL amigable |
| sku | string | Código de producto |
| description | text | Descripción |
| images | json | Array de rutas de imágenes |
| model_3d_file | string | Ruta al modelo GLB/GLTF |
| category_id | bigint | FK a categoría |
| subcategory_id | bigint | FK a subcategoría |
| has_configurator | boolean | Tiene configurador |
| configurator_base_price | decimal | Precio base |
| pricing_unit | enum | 'unit' o 'thousand' |
| active | boolean | Estado activo |

### ProductAttribute
Atributos configurables (colores, materiales, tamaños, etc.)

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | bigint | ID único |
| name | string | Nombre visible |
| value | string | Valor interno |
| type | enum | color, material, size, ink, system, quantity, weight |
| hex_code | string | Código hexadecimal (colores) |
| pantone_code | string | Código Pantone |
| image_path | string | Imagen del atributo |
| price_modifier | decimal | Modificador de precio fijo |
| price_percentage | decimal | Modificador porcentual |
| sort_order | int | Orden de visualización |
| active | boolean | Estado activo |

### AttributeDependency
Reglas de dependencia entre atributos.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | bigint | ID único |
| product_id | bigint | FK a producto (null = global) |
| source_attribute_id | bigint | Atributo origen |
| target_attribute_id | bigint | Atributo destino |
| dependency_type | enum | allows, blocks, requires, auto_selects |
| priority | int | Prioridad de la regla |
| active | boolean | Estado activo |

### Order
Pedidos de clientes.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | bigint | ID único |
| order_number | string | Número de pedido único |
| customer_id | bigint | FK a cliente |
| status | enum | pending, processing, approved, in_production, shipped, delivered, cancelled |
| subtotal | decimal | Subtotal sin IVA |
| tax | decimal | IVA |
| total | decimal | Total con IVA |
| notes | text | Notas del pedido |
| shipping_address | json | Dirección de envío |

---

## API REST

### Base URL
- **Local:** `http://localhost:8080/api`
- **Producción:** `https://api.hostelking.com/api`

### Autenticación
La API usa Laravel Sanctum para autenticación con tokens.

```bash
# Obtener token
POST /api/auth/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password"
}

# Respuesta
{
  "token": "1|abc123...",
  "user": { ... }
}

# Usar token
Authorization: Bearer 1|abc123...
```

---

### Endpoints Públicos (API v1)

#### Productos

```bash
# Listar productos
GET /api/v1/products
Query params: limit, page, category, subcategory, active

# Detalle de producto
GET /api/v1/products/{slug}

# Respuesta
{
  "id": 1,
  "name": "Mantel Individual",
  "slug": "mantel-individual",
  "images": ["https://..."],
  "main_image": "https://...",
  "has_configurator": true,
  "configurator": {
    "base_price": 0.15,
    "description": "...",
    "max_print_colors": 4
  },
  "attributes": {
    "colors": [...],
    "materials": [...],
    "sizes": [...],
    "inks": [...]
  },
  "category": { "id": 1, "name": "Manteles" },
  "pricing_ranges": [
    { "quantity_from": 1000, "quantity_to": 2999, "unit_price": 0.15 },
    { "quantity_from": 3000, "quantity_to": 4999, "unit_price": 0.12 }
  ]
}
```

#### Categorías

```bash
# Listar categorías
GET /api/v1/categories

# Detalle con subcategorías
GET /api/v1/categories/{slug}
```

#### Configurador

```bash
# Obtener configuración inicial del producto
GET /api/v1/configurator/products/{id}/config

# Respuesta
{
  "product": { ... },
  "attributes": {
    "colors": [
      {
        "id": 1,
        "name": "Blanco",
        "hex_code": "#FFFFFF",
        "is_available": true,
        "price_modifier": 0,
        "images": ["https://..."]
      }
    ],
    "materials": [...],
    "sizes": [...],
    "inks": [...],
    "systems": [...],
    "quantities": [...]
  },
  "dependencies": [...],
  "pricing_ranges": [...]
}
```

```bash
# Calcular precio
POST /api/v1/configurator/products/{id}/price
Content-Type: application/json

{
  "color_id": 1,
  "material_id": 2,
  "size_id": 3,
  "ink_ids": [1, 2],
  "system_id": 1,
  "quantity": 5000,
  "faces": 1
}

# Respuesta
{
  "success": true,
  "pricing": {
    "base_price": 0.15,
    "unit_price": 0.12,
    "quantity": 5000,
    "subtotal": 600.00,
    "extras": {
      "ink_extra": 50.00,
      "material_extra": 0
    },
    "total_extras": 50.00,
    "total": 650.00,
    "discount_percentage": 5,
    "pricing_unit": "unit"
  }
}
```

```bash
# Validar configuración
POST /api/v1/configurator/products/{id}/validate
Content-Type: application/json

{
  "color_id": 1,
  "material_id": 2,
  "size_id": 3
}

# Respuesta
{
  "valid": true,
  "warnings": [],
  "blocked_attributes": [],
  "auto_selected": []
}
```

#### Pedidos

```bash
# Crear pedido
POST /api/v1/orders
Content-Type: application/json

{
  "customer": {
    "name": "Empresa S.L.",
    "email": "contacto@empresa.com",
    "phone": "+34612345678",
    "company": "Empresa S.L.",
    "tax_id": "B12345678"
  },
  "shipping_address": {
    "street": "Calle Principal 123",
    "city": "Madrid",
    "postal_code": "28001",
    "country": "España"
  },
  "items": [
    {
      "product_id": 1,
      "configuration": {
        "color_id": 1,
        "material_id": 2,
        "size_id": 3,
        "ink_ids": [1],
        "system_id": 1
      },
      "quantity": 5000,
      "design_file": "uploads/design123.pdf"
    }
  ],
  "notes": "Entrega urgente"
}

# Respuesta
{
  "success": true,
  "order": {
    "id": 123,
    "order_number": "ORD-2025-00123",
    "status": "pending",
    "total": 650.00
  }
}
```

---

### Endpoints Admin (requieren autenticación)

```bash
# Productos
GET    /api/admin/products
POST   /api/admin/products
GET    /api/admin/products/{id}
PUT    /api/admin/products/{id}
DELETE /api/admin/products/{id}

# Categorías
GET    /api/admin/categories
POST   /api/admin/categories
PUT    /api/admin/categories/{id}
DELETE /api/admin/categories/{id}

# Pedidos
GET    /api/admin/orders
GET    /api/admin/orders/{id}
PATCH  /api/admin/orders/{id}/status
DELETE /api/admin/orders/{id}
```

---

## Panel de Administración

Accesible en `/admin` con autenticación.

### Secciones

| Ruta | Descripción |
|------|-------------|
| /admin | Dashboard con estadísticas |
| /admin/products | Gestión de productos |
| /admin/categories | Categorías y subcategorías |
| /admin/product-attributes | Atributos (colores, materiales...) |
| /admin/attribute-groups | Agrupación de atributos |
| /admin/attribute-dependencies | Reglas de dependencia |
| /admin/price-rules | Reglas de precio |
| /admin/printing-systems | Sistemas de impresión |
| /admin/orders | Gestión de pedidos |
| /admin/customers | Base de datos de clientes |

### Gestión de Imágenes por Atributo

Los productos pueden tener imágenes específicas para cada combinación de atributos (ej: imagen diferente por color).

```
/admin/products/{slug}/attribute-images
```

---

## Configuración de Storage

### Local (desarrollo)

```env
FILESYSTEM_DISK=public
```

### Amazon S3 (producción)

```env
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
AWS_DEFAULT_REGION=eu-west-1
AWS_BUCKET=your-bucket
AWS_URL=https://your-bucket.s3.eu-west-1.amazonaws.com
```

### Symlink para storage público

```bash
php artisan storage:link
```

---

## Variables de Entorno

```env
# App
APP_NAME="Hostelking Personalizados"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8080

# Database
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=producto_personalizado
DB_USERNAME=sail
DB_PASSWORD=password

# Storage
FILESYSTEM_DISK=public

# AWS S3 (producción)
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=eu-west-1
AWS_BUCKET=

# CORS
ALLOWED_ORIGINS="http://localhost:3000,http://localhost:3001"

# Rate Limiting
API_RATE_LIMIT=60
API_PRICE_RATE_LIMIT=30
API_ORDER_RATE_LIMIT=10
```

---

## Comandos Útiles

```bash
# Desarrollo con Sail
./vendor/bin/sail up -d
./vendor/bin/sail down

# Migraciones
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan migrate:fresh --seed

# Cache
./vendor/bin/sail artisan config:clear
./vendor/bin/sail artisan cache:clear
./vendor/bin/sail artisan view:clear

# Tinker (consola interactiva)
./vendor/bin/sail artisan tinker

# Tests
./vendor/bin/sail artisan test

# Logs
./vendor/bin/sail logs -f
```

---

## Deployment

### Producción

```bash
# Optimizar
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm run build

# Migraciones
php artisan migrate --force

# Permisos
chmod -R 755 storage bootstrap/cache
```

### Checklist

- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] Configurar S3 para storage
- [ ] SSL/HTTPS habilitado
- [ ] CORS configurado para dominios de producción
- [ ] Base de datos MySQL configurada
- [ ] Colas configuradas (opcional)

---

## Licencia

Proyecto propietario - Hostelking 2025
