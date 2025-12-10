# 🚀 API REST - Lista para Frontend

**Fecha**: 2025-11-06
**Estado**: ✅ **PRODUCCIÓN READY**
**Base URL**: `/api/v1`

---

## 📊 Resumen Ejecutivo

La API REST está **100% funcional** y optimizada para construir un frontend moderno (React, Vue, Angular, etc.).

### ✅ Lo que está implementado:

- **17 endpoints** públicos
- **API Resources** para respuestas consistentes
- **Eager loading** para evitar N+1 queries
- **Paginación** en todos los listados
- **Filtros y búsqueda** avanzados
- **Rate limiting** configurado
- **CORS** habilitado para desarrollo y producción
- **Validaciones** completas
- **Logging** de errores
- **Transacciones** en operaciones críticas

---

## 🎯 Endpoints Disponibles

### 📦 Productos

#### `GET /api/v1/products`
**Listar todos los productos activos**

Query params:
- `page` - Número de página
- `per_page` - Items por página (máx 50)
- `search` - Buscar por nombre, SKU o descripción
- `category_id` - Filtrar por categoría
- `subcategory_id` - Filtrar por subcategoría
- `has_configurator` - Filtrar productos con configurador
- `sort` - Ordenar por: name, created_at, configurator_base_price
- `order` - Dirección: asc, desc

**Ejemplo**:
```bash
GET /api/v1/products?search=servilleta&category_id=1&per_page=20&sort=name&order=asc
```

**Respuesta**:
```json
{
  "data": [
    {
      "id": 1,
      "name": "Servilletas Personalizadas",
      "slug": "servilletas-personalizadas",
      "sku": "SERV-001",
      "description": "...",
      "images": ["/storage/products/servilleta-1.jpg"],
      "main_image": "/storage/products/servilleta-1.jpg",
      "has_configurator": true,
      "configurator": {
        "base_price": 0.15,
        "max_print_colors": 4,
        "allow_file_upload": true
      },
      "category": {...},
      "pricing_ranges": [...],
      "urls": {
        "view": "/api/v1/products/1",
        "configure": "/api/v1/configurator/products/1/config"
      }
    }
  ],
  "links": {...},
  "meta": {
    "current_page": 1,
    "total": 50,
    "per_page": 15
  }
}
```

#### `GET /api/v1/products/{id}`
**Ver detalle de un producto**

---

### 📁 Categorías

#### `GET /api/v1/categories`
**Listar todas las categorías**

Query params:
- `active` - Filtrar por activas (default: true)
- `with_products` - Incluir conteo de productos

**Respuesta**:
```json
{
  "data": [
    {
      "id": 1,
      "name": "Papelería",
      "slug": "papeleria",
      "description": "...",
      "subcategories": [...],
      "products_count": 12,
      "urls": {
        "view": "/api/v1/categories/1",
        "products": "/api/v1/categories/1/products"
      }
    }
  ]
}
```

#### `GET /api/v1/categories/{id}`
**Ver detalle de categoría**

#### `GET /api/v1/categories/{id}/products`
**Listar productos de una categoría**

Query params: igual que `/products`

---

### 📂 Subcategorías

#### `GET /api/v1/subcategories`
**Listar todas las subcategorías**

Query params:
- `category_id` - Filtrar por categoría
- `active` - Filtrar por activas
- `with_products` - Incluir conteo

#### `GET /api/v1/subcategories/{id}`
**Ver detalle de subcategoría**

#### `GET /api/v1/subcategories/{id}/products`
**Listar productos de una subcategoría**

---

### 🛒 Órdenes/Pedidos

#### `POST /api/v1/orders`
**Crear una nueva orden**

**Body**:
```json
{
  "customer_name": "Juan Pérez",
  "customer_email": "juan@example.com",
  "customer_phone": "+34666777888",
  "customer_address": "Calle Mayor 1, Madrid",
  "notes": "Entrega por la mañana",
  "products": [
    {
      "id": 1,
      "quantity": 100,
      "price": 0.15,
      "configuration": {
        "color": "white",
        "material": "paper",
        "size": "40x40cm"
      }
    }
  ]
}
```

**Respuesta (201)**:
```json
{
  "data": {
    "id": 123,
    "order_number": "ORD-20251106-001",
    "customer": {
      "name": "Juan Pérez",
      "email": "juan@example.com",
      "phone": "+34666777888",
      "address": "Calle Mayor 1, Madrid"
    },
    "status": "pending",
    "status_label": "Pendiente",
    "total_amount": 15.00,
    "items": [...],
    "created_at": "2025-11-06T12:00:00.000000Z"
  }
}
```

#### `GET /api/v1/orders`
**Listar órdenes del usuario autenticado**

Query params:
- `status` - Filtrar por estado
- `page`, `per_page` - Paginación

⚠️ Requiere autenticación

#### `GET /api/v1/orders/{id}`
**Ver detalle de una orden**

⚠️ Solo el usuario dueño puede verla

---

### ⚙️ Configurador (ya existente)

#### `GET /api/v1/configurator/products/{product}/config`
**Obtener configuración inicial del producto**

#### `POST /api/v1/configurator/products/{product}/price`
**Calcular precio dinámico**

Body:
```json
{
  "selection": [1, 5, 12],  // IDs de atributos seleccionados
  "quantity": 100
}
```

#### `POST /api/v1/configurator/products/{product}/validate`
**Validar configuración**

#### `POST /api/v1/configurator/products/{product}/save`
**Guardar configuración en sesión**

#### `GET /api/v1/configurator/products/{product}/configuration`
**Recuperar configuración guardada**

#### `POST /api/v1/configurator/inks/recommended`
**Obtener tintas recomendadas según color**

---

## 🔒 Autenticación

- Endpoints **públicos**: Productos, categorías, configurador
- Endpoints **protegidos**: Órdenes del usuario (requiere login)
- Método: **Laravel Sanctum** (cookies + CSRF token)

### Para autenticar:

1. Obtener CSRF cookie:
```bash
GET /sanctum/csrf-cookie
```

2. Login:
```bash
POST /login
{
  "email": "user@example.com",
  "password": "password"
}
```

3. Usar cookies en requests subsecuentes

---

## 🚦 Rate Limiting

- **Configurador**: 60 requests/minuto
- **Otros endpoints**: 60 requests/minuto

Headers de respuesta:
- `X-RateLimit-Limit`: Límite total
- `X-RateLimit-Remaining`: Requests restantes

---

## 🌐 CORS

Configurado para:
- ✅ `http://localhost:3000` (React dev)
- ✅ `http://localhost:5173` (Vite dev)
- ✅ `http://localhost:8080` (Vue dev)
- ✅ Producción (actualizar dominio en `config/cors.php`)

---

## 📝 Formato de Respuestas

### Respuestas Exitosas

**Single resource**:
```json
{
  "data": {
    "id": 1,
    "name": "...",
    ...
  }
}
```

**Collection (paginada)**:
```json
{
  "data": [...],
  "links": {
    "first": "...",
    "last": "...",
    "prev": null,
    "next": "..."
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 5,
    "per_page": 15,
    "to": 15,
    "total": 73
  }
}
```

### Respuestas de Error

**Validación (422)**:
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["El campo email es obligatorio."],
    "quantity": ["La cantidad debe ser al menos 1."]
  }
}
```

**No encontrado (404)**:
```json
{
  "message": "Producto no encontrado"
}
```

**No autorizado (403)**:
```json
{
  "message": "No autorizado para ver esta orden"
}
```

**Error del servidor (500)**:
```json
{
  "message": "Error al crear la orden",
  "error": "Details..."
}
```

---

## ⚡ Optimizaciones Implementadas

### Performance
- ✅ Eager loading de relaciones (evita N+1 queries)
- ✅ Paginación en todos los listados
- ✅ Índices de base de datos
- ✅ Cache en configurador (5 minutos)

### Seguridad
- ✅ Validaciones completas en todos los endpoints
- ✅ Rate limiting
- ✅ CORS configurado
- ✅ SQL injection prevention (Eloquent ORM)
- ✅ XSS protection (Laravel sanitization)

### Developer Experience
- ✅ Respuestas consistentes (API Resources)
- ✅ Paginación automática con links
- ✅ Filtros y búsqueda en todos los listados
- ✅ Documentación inline en controladores
- ✅ Logging de errores

---

## 🧪 Testing

```bash
# Obtener productos
curl http://localhost/api/v1/products

# Buscar productos
curl "http://localhost/api/v1/products?search=servilleta&per_page=5"

# Productos por categoría
curl http://localhost/api/v1/categories/1/products

# Crear orden
curl -X POST http://localhost/api/v1/orders \
  -H "Content-Type: application/json" \
  -d '{
    "customer_name": "Test User",
    "customer_email": "test@example.com",
    "customer_phone": "666777888",
    "customer_address": "Test Street 1",
    "products": [
      {"id": 1, "quantity": 10, "price": 0.15}
    ]
  }'
```

---

## 📱 Ejemplo de Uso en Frontend

### React con Fetch

```javascript
// Listar productos
const getProducts = async (page = 1, search = '') => {
  const response = await fetch(
    `http://localhost/api/v1/products?page=${page}&search=${search}&per_page=20`
  );
  const data = await response.json();
  return data;
};

// Crear orden
const createOrder = async (orderData) => {
  const response = await fetch('http://localhost/api/v1/orders', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(orderData),
  });

  if (!response.ok) {
    const error = await response.json();
    throw new Error(error.message);
  }

  return response.json();
};
```

### Vue con Axios

```javascript
import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost/api/v1',
  withCredentials: true, // Para cookies de sesión
});

// Obtener configuración del producto
const getProductConfig = async (productId) => {
  const { data } = await api.get(`/configurator/products/${productId}/config`);
  return data.data;
};

// Calcular precio
const calculatePrice = async (productId, selection, quantity) => {
  const { data } = await api.post(`/configurator/products/${productId}/price`, {
    selection,
    quantity,
  });
  return data.data;
};
```

---

## 🎉 Conclusión

La API está **lista para producción** con:

✅ **17 endpoints** implementados
✅ **Optimización** completa (eager loading, caching)
✅ **Seguridad** robusta (validaciones, rate limiting)
✅ **CORS** configurado para frontend
✅ **Documentación** completa
✅ **Error handling** apropiado
✅ **Respuestas consistentes** (API Resources)

**Puedes empezar a construir tu frontend inmediatamente!** 🚀

---

**Próximos pasos opcionales**:
- Agregar autenticación OAuth2 si es necesario
- Implementar webhooks para eventos
- Agregar versionado de API (v2)
- Documentación Swagger/OpenAPI
