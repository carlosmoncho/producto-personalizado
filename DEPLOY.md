# Guía de Deploy - Hostelking Personalizados Backend

## Requisitos del Servidor

- PHP 8.2+
- Redis Server
- MySQL 8.0+
- Composer
- Node.js (para assets si aplica)

## Variables de Entorno Producción

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-dominio.com

# Cache y Queue con Redis (OBLIGATORIO)
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=tu_password_seguro
REDIS_PORT=6379
```

## Pasos de Deploy

### 1. Subir código
```bash
git pull origin main
```

### 2. Instalar dependencias (sin dev)
```bash
composer install --no-dev --optimize-autoloader
```

### 3. Ejecutar script de optimización
```bash
chmod +x scripts/deploy-optimize.sh
./scripts/deploy-optimize.sh
```

El script hace:
- Limpia cachés antiguos
- Genera config cache (`php artisan config:cache`)
- Genera route cache (`php artisan route:cache`)
- Genera view cache (`php artisan view:cache`)
- Optimiza autoloader de Composer

### 4. Migraciones (si hay cambios)
```bash
php artisan migrate --force
```

### 5. Reiniciar servicios
```bash
# Reiniciar PHP-FPM para aplicar cambios de Opcache
sudo systemctl restart php8.2-fpm

# Reiniciar queue workers
php artisan queue:restart
```

## Configuración Opcache (Producción)

Copiar configuración optimizada a PHP:

```bash
sudo cp docker/php/opcache-prod.ini /etc/php/8.2/fpm/conf.d/10-opcache-prod.ini
sudo systemctl restart php8.2-fpm
```

Configuración incluida:
- `opcache.validate_timestamps=0` - No verifica cambios (más rápido)
- `opcache.memory_consumption=256` - 256MB para scripts
- `opcache.jit=1255` - JIT habilitado
- `opcache.jit_buffer_size=100M` - Buffer JIT

## Queue Workers

Los jobs de emails y notificaciones requieren workers activos.

### Supervisor (Recomendado)

Crear `/etc/supervisor/conf.d/hostelking-worker.conf`:

```ini
[program:hostelking-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/hostelking/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600 --queue=emails,notifications,default
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/hostelking/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start hostelking-worker:*
```

### Verificar Workers
```bash
php artisan queue:monitor emails,notifications,default
```

## Checklist Pre-Deploy

- [ ] Tests pasando: `php artisan test`
- [ ] Variables .env configuradas
- [ ] Redis accesible
- [ ] Base de datos migrada
- [ ] Storage linkado: `php artisan storage:link`
- [ ] Permisos correctos en storage/ y bootstrap/cache/

## Checklist Post-Deploy

- [ ] Health check OK: `curl https://tu-dominio.com/api/health`
- [ ] Queue workers corriendo: `php artisan queue:monitor`
- [ ] Logs sin errores: `tail -f storage/logs/laravel.log`
- [ ] Cache funcionando: `php artisan tinker` → `Cache::get('test')`

## Rollback

Si algo falla:

```bash
# Volver a versión anterior
git checkout HEAD~1

# Re-ejecutar optimizaciones
./scripts/deploy-optimize.sh

# Reiniciar servicios
sudo systemctl restart php8.2-fpm
php artisan queue:restart
```

## Monitoreo

### Endpoints de Health Check
- `/api/health` - Estado general
- `/api/health/detailed` - Estado detallado (DB, Redis, etc.)
- `/api/health/metrics` - Métricas de rendimiento
- `/api/health/ready` - Readiness probe (Kubernetes)
- `/api/health/alive` - Liveness probe (Kubernetes)

### Rate Limits Configurados
| Endpoint | Límite |
|----------|--------|
| Health checks | 1000/min |
| Lectura pública | 100/min, 1000/hora |
| Cálculo precios | 20/min, 200/hora |
| Crear pedidos | 2/min, 10/hora, 50/día |
| API general | 60/min |

## Contacto

Para problemas de deploy, contactar al equipo de desarrollo.
