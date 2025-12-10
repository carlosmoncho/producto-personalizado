# 🏥 Health Check & Monitoring Endpoints

## Overview

El sistema incluye endpoints de health check para monitoreo y verificación de estado. Estos endpoints son esenciales para:

- ✅ Monitoreo continuo con herramientas como Datadog, New Relic, Prometheus
- ✅ Kubernetes liveness/readiness probes
- ✅ Load balancer health checks
- ✅ Alertas automáticas
- ✅ Debugging de producción

**Características:**
- Sin rate limiting (permiten verificaciones frecuentes)
- Tiempo de respuesta <100ms para checks básicos
- Formato JSON consistente
- HTTP status codes apropiados (200 OK, 503 Service Unavailable)

---

## 📍 Endpoints Disponibles

### 1. Basic Health Check

```
GET /api/health
```

**Descripción:** Verificación básica de que la aplicación está respondiendo.

**Respuesta (200 OK):**
```json
{
  "status": "ok",
  "service": "Laravel",
  "timestamp": "2025-11-06T15:30:45+00:00"
}
```

**Uso:**
- Load balancers
- Monitoring pings
- Uptime monitors

**Ejemplo:**
```bash
curl http://localhost/api/health
```

---

### 2. Detailed Health Check

```
GET /api/health/detailed
```

**Descripción:** Verificación completa de todos los componentes del sistema.

**Verifica:**
- ✅ Conexión a base de datos
- ✅ Sistema de cache
- ✅ Storage (filesystem)
- ✅ Configuración crítica

**Respuesta (200 OK):**
```json
{
  "status": "ok",
  "service": "Laravel",
  "timestamp": "2025-11-06T15:30:45+00:00",
  "checks": {
    "database": {
      "status": "ok",
      "driver": "mysql",
      "latency_ms": 2.34
    },
    "cache": {
      "status": "ok",
      "driver": "redis",
      "working": true
    },
    "storage": {
      "status": "ok",
      "disk": "local",
      "writable": true
    },
    "config": {
      "status": "ok",
      "environment": "production",
      "debug": false,
      "issues": []
    }
  }
}
```

**Respuesta con problemas (503 Service Unavailable):**
```json
{
  "status": "degraded",
  "service": "Laravel",
  "timestamp": "2025-11-06T15:30:45+00:00",
  "checks": {
    "database": {
      "status": "error",
      "driver": "mysql",
      "error": "SQLSTATE[HY000] [2002] Connection refused"
    },
    "cache": {
      "status": "ok",
      "driver": "redis",
      "working": true
    }
  }
}
```

**Uso:**
- Dashboards de monitoreo
- Alertas automáticas
- Debugging

**Ejemplo:**
```bash
curl http://localhost/api/health/detailed
```

---

### 3. Metrics

```
GET /api/health/metrics
```

**Descripción:** Métricas del sistema para análisis de rendimiento.

**Respuesta (200 OK):**
```json
{
  "timestamp": "2025-11-06T15:30:45+00:00",
  "uptime": "2d 5h",
  "memory": {
    "used": 12582912,
    "used_formatted": "12.00 MB",
    "peak": 15728640,
    "peak_formatted": "15.00 MB"
  },
  "database": {
    "driver": "mysql",
    "database": "laravel",
    "connections": "5",
    "max_connections": "151"
  },
  "cache": {
    "driver": "redis",
    "working": true
  },
  "storage": {
    "disk": "local",
    "working": true
  }
}
```

**Uso:**
- Prometheus/Grafana
- Performance monitoring
- Capacity planning

**Ejemplo:**
```bash
curl http://localhost/api/health/metrics
```

---

### 4. Readiness Probe (Kubernetes)

```
GET /api/health/ready
```

**Descripción:** Verifica si la aplicación está lista para recibir tráfico.

**Respuesta (200 OK):**
```json
{
  "status": "ready",
  "timestamp": "2025-11-06T15:30:45+00:00"
}
```

**Respuesta no lista (503 Service Unavailable):**
```json
{
  "status": "not_ready",
  "reason": "Database not available"
}
```

**Uso:**
- Kubernetes readiness probe
- Load balancer initialization

**Ejemplo Kubernetes:**
```yaml
readinessProbe:
  httpGet:
    path: /api/health/ready
    port: 80
  initialDelaySeconds: 10
  periodSeconds: 5
```

---

### 5. Liveness Probe (Kubernetes)

```
GET /api/health/alive
```

**Descripción:** Verificación simple de que la aplicación no está "muerta".

**Respuesta (200 OK):**
```json
{
  "status": "alive",
  "timestamp": "2025-11-06T15:30:45+00:00"
}
```

**Uso:**
- Kubernetes liveness probe
- Detección de deadlocks

**Ejemplo Kubernetes:**
```yaml
livenessProbe:
  httpGet:
    path: /api/health/alive
    port: 80
  initialDelaySeconds: 30
  periodSeconds: 10
```

---

## 🚀 Configuración en Producción

### Nginx Health Check

```nginx
location /api/health {
    access_log off;
    error_log off;
    proxy_pass http://backend;
}
```

### Docker Compose Health Check

```yaml
services:
  app:
    image: myapp:latest
    healthcheck:
      test: ["CMD", "curl", "-f", "http://localhost/api/health"]
      interval: 30s
      timeout: 10s
      retries: 3
      start_period: 40s
```

### Kubernetes Deployment

```yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: laravel-app
spec:
  template:
    spec:
      containers:
      - name: app
        image: myapp:latest
        ports:
        - containerPort: 80
        livenessProbe:
          httpGet:
            path: /api/health/alive
            port: 80
          initialDelaySeconds: 30
          periodSeconds: 10
        readinessProbe:
          httpGet:
            path: /api/health/ready
            port: 80
          initialDelaySeconds: 10
          periodSeconds: 5
```

---

## 📊 Monitoreo con Herramientas

### Prometheus Scraping

```yaml
scrape_configs:
  - job_name: 'laravel'
    metrics_path: '/api/health/metrics'
    static_configs:
      - targets: ['localhost:80']
```

### Datadog Integration

```bash
# Instalar Datadog agent
DD_API_KEY=your_key bash -c "$(curl -L https://s3.amazonaws.com/dd-agent/scripts/install_script.sh)"

# Configurar health check
# /etc/datadog-agent/conf.d/http_check.d/conf.yaml
init_config:

instances:
  - name: Laravel App
    url: http://localhost/api/health/detailed
    timeout: 5
```

### UptimeRobot

1. Ir a https://uptimerobot.com/
2. Crear nuevo monitor tipo "HTTP(s)"
3. URL: `https://tudominio.com/api/health`
4. Intervalo: 5 minutos
5. Alertas: Email/Slack/SMS

---

## 🔔 Alertas Recomendadas

### Alertas Críticas (Pager)

1. **API Down** - `/api/health` retorna 500/503
2. **Database Down** - `/api/health/detailed` muestra database error
3. **High Memory Usage** - `/api/health/metrics` memory > 90%

### Alertas de Advertencia (Email)

1. **Cache Issues** - `/api/health/detailed` muestra cache error
2. **Storage Issues** - `/api/health/detailed` muestra storage error
3. **Config Issues** - `/api/health/detailed` muestra config warnings

---

## 🧪 Testing

### Manual Testing

```bash
# Basic health
curl http://localhost/api/health

# Detailed check
curl http://localhost/api/health/detailed | jq

# Metrics
curl http://localhost/api/health/metrics | jq

# Readiness
curl -i http://localhost/api/health/ready

# Liveness
curl -i http://localhost/api/health/alive
```

### Automated Testing

```bash
# Test script
#!/bin/bash
endpoints=(
  "/api/health"
  "/api/health/detailed"
  "/api/health/metrics"
  "/api/health/ready"
  "/api/health/alive"
)

for endpoint in "${endpoints[@]}"; do
  echo "Testing $endpoint"
  status=$(curl -s -o /dev/null -w "%{http_code}" "http://localhost$endpoint")
  if [ "$status" = "200" ]; then
    echo "✅ OK"
  else
    echo "❌ FAILED (HTTP $status)"
  fi
done
```

---

## 🔧 Troubleshooting

### Health check returns 404

```bash
# Limpiar cache de rutas
php artisan route:clear
php artisan route:cache

# Verificar que las rutas existen
php artisan route:list --path=health
```

### Database check fails

```bash
# Verificar conexión
php artisan db:show

# Verificar credenciales en .env
cat .env | grep DB_

# Test connection
php artisan tinker
>>> DB::connection()->getPdo();
```

### Cache check fails

```bash
# Verificar configuración de cache
php artisan cache:clear

# Test cache
php artisan tinker
>>> Cache::put('test', 'value', 60);
>>> Cache::get('test');
```

---

## 📝 Logs

Los health checks NO generan logs por defecto para evitar saturación.

Si necesitas logging:

```php
// En HealthCheckController.php
\Log::channel('monitoring')->info('Health check failed', [
    'checks' => $checks,
]);
```

---

## 🎯 Best Practices

1. ✅ **Usar `/api/health` para load balancers** - Es rápido y ligero
2. ✅ **Usar `/api/health/detailed` para dashboards** - Información completa
3. ✅ **Usar `/api/health/ready` en Kubernetes** - Gestión de tráfico
4. ✅ **Monitorear `/api/health/metrics`** - Análisis de tendencias
5. ✅ **No hacer rate limiting** en health checks - Ya configurado
6. ✅ **Cachear checks costosos** - Si agregas verificaciones lentas
7. ⚠️  **No exponer información sensible** - Ya protegido
8. ⚠️  **Timeout < 10s** - Para evitar falsos positivos

---

## 🔐 Seguridad

- ✅ No expone información sensible (passwords, keys)
- ✅ No require autenticación (necesario para monitoring)
- ✅ Sin rate limiting (pero monitoreable por IP si se detecta abuso)
- ⚠️  Considera agregar IP whitelist si es necesario:

```php
// En routes/api.php
Route::prefix('health')->middleware('ip.whitelist')->group(function () {
    // ...
});
```

---

**Estado:** ✅ PRODUCTION READY

**Última actualización:** 2025-11-06
