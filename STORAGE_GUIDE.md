# 📦 Guía de Almacenamiento - Imágenes y Modelos 3D

## 📊 Configuración Actual

### ✅ Sistema Implementado

**Disco por defecto:** `public` (storage/app/public)
**Acceso público:** Sí, a través de symlink
**Estado:** ✅ Configurado y funcionando

```
storage/app/public/         ← Archivos aquí
         ↓ (symlink)
public/storage/             ← Acceso web aquí
```

### Estructura de Directorios

```
storage/app/public/
├── products/               ← Imágenes de productos (JPG, PNG, GIF, WEBP)
│   ├── abc123.jpg
│   ├── def456.png
│   └── ...
├── 3d-models/             ← Modelos 3D (GLB, GLTF)
│   ├── xyz789.glb
│   ├── model001.gltf
│   └── ...
└── backups/               ← Backups de base de datos
    └── backup_20251106.sql.gz
```

---

## 🎯 Recomendaciones por Entorno

### 🏠 Desarrollo Local

**✅ USAR:** Disco `public` (actual)

**Ventajas:**
- ✅ Simple y rápido
- ✅ No requiere configuración adicional
- ✅ Perfecto para desarrollo

**Configuración:**
```bash
# Ya está configurado, solo verificar symlink
php artisan storage:link

# O con Sail
./vendor/bin/sail artisan storage:link
```

**URLs generadas:**
```
http://localhost/storage/products/imagen.jpg
http://localhost/storage/3d-models/modelo.glb
```

---

### 🚀 Producción Pequeña/Media (<1000 productos)

**✅ USAR:** Disco `public` + CDN opcional

**Configuración recomendada:**

1. **Servidor web debe servir archivos estáticos eficientemente**

```nginx
# nginx.conf
location /storage {
    alias /ruta/proyecto/storage/app/public;
    expires 30d;
    add_header Cache-Control "public, immutable";

    # Compresión
    gzip on;
    gzip_types image/jpeg image/png image/gif image/webp;

    # CORS para modelos 3D (si necesario)
    add_header Access-Control-Allow-Origin *;
}

# Límites de tamaño
client_max_body_size 100M;
```

2. **Opcional: CDN gratuito con Cloudflare**

```
1. Crear cuenta en Cloudflare
2. Agregar dominio
3. Activar "Caching Level: Standard"
4. Habilitar "Auto Minify" para imágenes
5. ¡Listo! URLs siguen igual pero se sirven desde CDN
```

**Ventajas:**
- ✅ Simple de mantener
- ✅ Backup fácil (solo copiar directorio)
- ✅ Sin costos adicionales

**Desventajas:**
- ⚠️  Usa espacio del servidor
- ⚠️  Escalabilidad limitada

---

### ☁️ Producción Grande (>1000 productos) o Alta Demanda

**✅ USAR:** Amazon S3 + CloudFront CDN

#### Opción A: Amazon S3 (Recomendado)

**Características:**
- ✅ Ilimitado escalable
- ✅ 99.99% disponibilidad
- ✅ Backup automático
- ✅ CDN integrado (CloudFront)
- ✅ Costo: ~$0.023/GB/mes + $0.09/GB transferencia

**Configuración:**

1. **Crear bucket S3**

```bash
# AWS CLI
aws s3 mb s3://mi-app-productos --region eu-west-1
aws s3 mb s3://mi-app-modelos-3d --region eu-west-1
```

2. **Configurar .env**

```bash
FILESYSTEM_DISK=s3

AWS_ACCESS_KEY_ID=AKIAXXXXXXXX
AWS_SECRET_ACCESS_KEY=xxxxxxxxxx
AWS_DEFAULT_REGION=eu-west-1
AWS_BUCKET=mi-app-productos
AWS_URL=https://mi-app-productos.s3.eu-west-1.amazonaws.com
```

3. **Instalar SDK**

```bash
composer require league/flysystem-aws-s3-v3 "^3.0" --with-all-dependencies
```

4. **Actualizar FileUploadService.php (Opcional)**

```php
// Cambiar disco para modelos 3D grandes
private const DEFAULT_DISK = 's3';  // Solo si quieres todo en S3
```

5. **URLs generadas automáticamente:**

```
https://mi-app-productos.s3.eu-west-1.amazonaws.com/products/imagen.jpg
```

**Costos estimados (ejemplo):**
- 10,000 imágenes × 100KB = 1GB → $0.023/mes
- 1,000 modelos 3D × 5MB = 5GB → $0.115/mes
- 100GB transferencia/mes → $9/mes
- **Total: ~$10/mes**

#### Opción B: DigitalOcean Spaces (Alternativa más barata)

**Características:**
- ✅ Compatible S3 API
- ✅ CDN incluido gratis
- ✅ Costo fijo: $5/mes (250GB storage + 1TB transferencia)

**Configuración:**

```bash
# .env
FILESYSTEM_DISK=s3

AWS_ACCESS_KEY_ID=tu_spaces_key
AWS_SECRET_ACCESS_KEY=tu_spaces_secret
AWS_DEFAULT_REGION=fra1
AWS_BUCKET=mi-app-productos
AWS_ENDPOINT=https://fra1.digitaloceanspaces.com
AWS_URL=https://mi-app-productos.fra1.cdn.digitaloceanspaces.com
AWS_USE_PATH_STYLE_ENDPOINT=false
```

**Ventajas sobre S3:**
- ✅ Precio predecible ($5/mes fijo)
- ✅ CDN incluido gratis
- ✅ Simple de configurar

---

## 🔧 Implementación Recomendada

### Para tu caso (Producto personalizado B2B):

**Recomiendo estrategia híbrida:**

#### Fase 1: Desarrollo y Lanzamiento Inicial
**USAR:** Disco `public` (actual) ✅

```bash
# Ya configurado, solo verificar
./vendor/bin/sail artisan storage:link
```

#### Fase 2: Al llegar a ~500 productos
**MIGRAR A:** DigitalOcean Spaces ($5/mes)

**Razones:**
- ✅ Modelos 3D son archivos grandes (5-20MB cada uno)
- ✅ Precio fijo predecible
- ✅ CDN gratis para carga rápida en toda Europa
- ✅ Fácil migración desde disco local

#### Script de Migración

```php
// routes/console.php o comando artisan
Artisan::command('storage:migrate-to-s3', function () {
    $localFiles = Storage::disk('public')->allFiles('products');

    foreach ($localFiles as $file) {
        $this->info("Migrando: $file");

        $contents = Storage::disk('public')->get($file);
        Storage::disk('s3')->put($file, $contents);

        $this->info("✓ Migrado: $file");
    }

    $this->info('¡Migración completada!');
});
```

**Ejecutar migración:**
```bash
php artisan storage:migrate-to-s3
```

---

## 🔒 Seguridad

### Archivos Públicos vs Privados

**Público (actual):** Imágenes de productos, modelos 3D
- ✅ Accesibles directamente por URL
- ✅ Cacheable por navegadores/CDN
- ✅ Mejor rendimiento

**Privado (si necesitas):** Documentos de clientes, facturas

```php
// Cambiar disco a 'local' para archivos privados
Storage::disk('local')->put('invoices/invoice-123.pdf', $content);

// Generar URL temporal (válida 1 hora)
$url = Storage::disk('s3')->temporaryUrl(
    'invoices/invoice-123.pdf',
    now()->addHour()
);
```

### Validaciones de Seguridad

**Ya implementado en FileUploadService:**

✅ Extensiones permitidas:
- Imágenes: JPG, JPEG, PNG, GIF, WEBP
- Modelos 3D: GLB, GLTF

✅ Límites de tamaño:
- Imágenes: 2MB (configurado en validación)
- Modelos 3D: 20MB

✅ Nombres aleatorios (evita sobrescritura)

---

## 📈 Monitoreo

### Verificar espacio usado

```bash
# Local
du -sh storage/app/public/products
du -sh storage/app/public/3d-models

# S3 (AWS CLI)
aws s3 ls s3://mi-app-productos --recursive --human-readable --summarize
```

### Alertas recomendadas

```bash
# Cron job para alertar si el espacio supera 80%
0 */6 * * * /ruta/proyecto/scripts/check-storage-usage.sh
```

---

## 🎯 Resumen de Decisiones

| Escenario | Solución Recomendada | Costo | Configuración |
|-----------|---------------------|-------|---------------|
| **Desarrollo** | Disco `public` (actual) | Gratis | ✅ Ya configurado |
| **MVP/Beta** | Disco `public` + Cloudflare | Gratis | Añadir Cloudflare |
| **Producción <500 productos** | Disco `public` + Cloudflare CDN | Gratis | Añadir Cloudflare |
| **Producción 500-5000** | DigitalOcean Spaces | $5/mes | Migración simple |
| **Producción >5000** | AWS S3 + CloudFront | $10-50/mes | Migración + CDN |

---

## ✅ Estado Actual: LISTO

Tu sistema **ya está configurado correctamente** para desarrollo y producción inicial.

**Próximos pasos:**
1. ✅ **Ahora:** Usar disco `public` (ya configurado)
2. 🔜 **Al llegar a 500 productos:** Migrar a DigitalOcean Spaces
3. 🔜 **Al escalar:** Evaluar AWS S3 + CloudFront

---

## 📝 URLs de Acceso

### Desarrollo Local
```
http://localhost/storage/products/imagen.jpg
http://localhost/storage/3d-models/modelo.glb
```

### Producción con dominio
```
https://tudominio.com/storage/products/imagen.jpg
https://tudominio.com/storage/3d-models/modelo.glb
```

### Producción con S3/Spaces
```
https://mi-bucket.s3.eu-west-1.amazonaws.com/products/imagen.jpg
https://mi-bucket.fra1.cdn.digitaloceanspaces.com/3d-models/modelo.glb
```

---

## 🆘 Troubleshooting

### Error: "File not found"
```bash
# Verificar symlink
ls -la public/storage

# Recrear symlink si es necesario
rm public/storage
php artisan storage:link
```

### Imágenes no se ven
```bash
# Verificar permisos
chmod -R 755 storage/app/public
```

### Modelos 3D muy pesados
```bash
# Optimizar modelos GLB con gltf-pipeline
npm install -g gltf-pipeline
gltf-pipeline -i modelo.glb -o modelo-optimized.glb -d
```

---

**Última actualización:** 2025-11-06
**Estado:** ✅ Configuración óptima implementada
