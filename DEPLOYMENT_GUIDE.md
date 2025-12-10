# 🚀 Guía de Deploy a Producción

**Sistema de Configurador de Productos Personalizados**

Esta guía cubre el proceso completo de deployment a producción en un servidor Ubuntu/Debian con Nginx + PHP-FPM + MySQL.

---

## 📋 Tabla de Contenidos

- [Pre-requisitos](#-pre-requisitos)
- [Preparación del Servidor](#-preparación-del-servidor)
- [Instalación de Dependencias](#-instalación-de-dependencias)
- [Configuración de Base de Datos](#-configuración-de-base-de-datos)
- [Configuración de la Aplicación](#-configuración-de-la-aplicación)
- [Configuración de Nginx](#-configuración-de-nginx)
- [Configuración de SSL](#-configuración-de-ssl)
- [Optimizaciones](#-optimizaciones)
- [Queue Workers](#-queue-workers)
- [Backup Automatizado](#-backup-automatizado)
- [Monitoreo](#-monitoreo)
- [Troubleshooting](#-troubleshooting)
- [Rollback](#-rollback)

---

## 🎯 Pre-requisitos

### Servidor

**Especificaciones mínimas**:
- **CPU**: 2 cores
- **RAM**: 4 GB
- **Disco**: 40 GB SSD
- **OS**: Ubuntu 22.04 / 24.04 LTS (recomendado) o Debian 11/12

**Acceso**:
- Acceso SSH con sudo
- Dominio configurado (DNS apuntando al servidor)
- Puerto 80 y 443 abiertos en firewall

### Local

- Git instalado
- Acceso SSH al servidor
- Credenciales de deploy

---

## 🔧 Preparación del Servidor

### 1. Actualizar Sistema

```bash
ssh user@your-server.com

sudo apt-get update
sudo apt-get upgrade -y
```

### 2. Crear Usuario de Deploy

```bash
# Crear usuario (si no existe)
sudo adduser deployer

# Añadir a grupo sudo
sudo usermod -aG sudo deployer

# Configurar SSH key
su - deployer
mkdir -p ~/.ssh
chmod 700 ~/.ssh

# Copiar tu clave pública a ~/.ssh/authorized_keys
echo "ssh-rsa YOUR_PUBLIC_KEY" >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys
```

### 3. Configurar Firewall

```bash
# Instalar UFW
sudo apt-get install ufw

# Configurar reglas
sudo ufw allow 22/tcp   # SSH
sudo ufw allow 80/tcp   # HTTP
sudo ufw allow 443/tcp  # HTTPS

# Activar firewall
sudo ufw enable
sudo ufw status
```

---

## 📦 Instalación de Dependencias

### 1. Instalar PHP 8.2

```bash
# Añadir repositorio PPA
sudo add-apt-repository ppa:ondrej/php
sudo apt-get update

# Instalar PHP y extensiones
sudo apt-get install -y \
    php8.2-fpm \
    php8.2-cli \
    php8.2-mysql \
    php8.2-mbstring \
    php8.2-xml \
    php8.2-curl \
    php8.2-zip \
    php8.2-gd \
    php8.2-intl \
    php8.2-bcmath \
    php8.2-sqlite3 \
    php8.2-redis

# Verificar instalación
php -v
# Debe mostrar: PHP 8.2.x
```

### 2. Instalar Composer

```bash
cd ~
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

# Verificar
composer --version
```

### 3. Instalar Node.js y npm

```bash
# Instalar Node.js 18 LTS
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs

# Verificar
node -v   # v18.x.x
npm -v    # 9.x.x
```

### 4. Instalar Nginx

```bash
sudo apt-get install nginx -y

# Verificar
nginx -v

# Iniciar Nginx
sudo systemctl start nginx
sudo systemctl enable nginx
```

### 5. Instalar MySQL

```bash
sudo apt-get install mysql-server -y

# Asegurar instalación
sudo mysql_secure_installation

# Responder a las preguntas:
# - Set root password: YES (crear contraseña segura)
# - Remove anonymous users: YES
# - Disallow root login remotely: YES
# - Remove test database: YES
# - Reload privilege tables: YES

# Verificar
sudo systemctl status mysql
```

### 6. Instalar Redis (Opcional pero Recomendado)

```bash
sudo apt-get install redis-server -y

# Configurar para iniciar automáticamente
sudo systemctl enable redis-server
sudo systemctl start redis-server

# Verificar
redis-cli ping
# Debe responder: PONG
```

### 7. Instalar Supervisor (Para Queue Workers)

```bash
sudo apt-get install supervisor -y

sudo systemctl enable supervisor
sudo systemctl start supervisor
```

---

## 🗄️ Configuración de Base de Datos

### 1. Crear Base de Datos

```bash
sudo mysql -u root -p
```

```sql
-- Crear base de datos
CREATE DATABASE configurador_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Crear usuario
CREATE USER 'configurador_user'@'localhost' IDENTIFIED BY 'PASSWORD_SEGURO_AQUI';

-- Otorgar permisos
GRANT ALL PRIVILEGES ON configurador_prod.* TO 'configurador_user'@'localhost';

-- Aplicar cambios
FLUSH PRIVILEGES;

-- Verificar
SHOW DATABASES;
SELECT user, host FROM mysql.user WHERE user = 'configurador_user';

-- Salir
EXIT;
```

### 2. Verificar Conexión

```bash
mysql -u configurador_user -p configurador_prod
```

---

## ⚙️ Configuración de la Aplicación

### 1. Clonar Repositorio

```bash
# Crear directorio para la app
sudo mkdir -p /var/www/configurador
sudo chown deployer:deployer /var/www/configurador

# Clonar (HTTPS o SSH)
cd /var/www
git clone https://github.com/tu-usuario/producto-personalizado.git configurador

cd configurador
```

### 2. Instalar Dependencias Backend

```bash
composer install --optimize-autoloader --no-dev --no-interaction
```

### 3. Configurar Variables de Entorno

```bash
# Copiar ejemplo
cp .env.example .env

# Editar con nano o vim
nano .env
```

**Configuración mínima de producción**:

```env
# ========== APLICACIÓN ==========
APP_NAME="Configurador Hostelking"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://tudominio.com

# ========== BASE DE DATOS ==========
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=configurador_prod
DB_USERNAME=configurador_user
DB_PASSWORD=PASSWORD_SEGURO_AQUI

# ========== SEGURIDAD ==========
ALLOWED_ORIGINS="${APP_URL},https://www.tudominio.com"

API_RATE_LIMIT=60
API_PRICE_RATE_LIMIT=30
API_ORDER_RATE_LIMIT=10

SESSION_DRIVER=database
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=strict
SESSION_LIFETIME=120

# ========== CACHÉ ==========
CACHE_STORE=redis
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# ========== QUEUE ==========
QUEUE_CONNECTION=redis

# ========== EMAIL ==========
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS="noreply@tudominio.com"
MAIL_FROM_NAME="${APP_NAME}"

# ========== ARCHIVOS ==========
FILESYSTEM_DISK=local
MAX_3D_MODEL_SIZE=20480
ALLOWED_3D_FORMATS=glb,gltf
MAX_IMAGE_UPLOAD_SIZE=2048

# ========== LOGS ==========
LOG_CHANNEL=stack
LOG_LEVEL=warning
LOG_DEPRECATIONS_CHANNEL=null
```

### 4. Generar Application Key

```bash
php artisan key:generate
```

### 5. Ejecutar Migraciones

```bash
php artisan migrate --force
```

### 6. Ejecutar Seeders (Roles y Permisos)

```bash
php artisan db:seed --class=RolesAndPermissionsSeeder --force
```

### 7. Crear Symlink de Storage

```bash
php artisan storage:link
```

### 8. Instalar Dependencias Frontend

```bash
npm ci
npm run build
```

### 9. Configurar Permisos

```bash
# El directorio debe pertenecer al usuario web
sudo chown -R www-data:www-data /var/www/configurador

# Permisos específicos
sudo chmod -R 755 /var/www/configurador
sudo chmod -R 775 /var/www/configurador/storage
sudo chmod -R 775 /var/www/configurador/bootstrap/cache

# Logs con permisos restrictivos
sudo chmod -R 640 /var/www/configurador/storage/logs/*.log
```

### 10. Optimizar Aplicación

```bash
# Cache de configuración
php artisan config:cache

# Cache de rutas
php artisan route:cache

# Cache de vistas
php artisan view:cache

# Optimizar autoloader
composer dump-autoload --optimize --classmap-authoritative
```

---

## 🌐 Configuración de Nginx

### 1. Crear Archivo de Configuración

```bash
sudo nano /etc/nginx/sites-available/configurador
```

**Contenido**:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name tudominio.com www.tudominio.com;

    # Redirect to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name tudominio.com www.tudominio.com;

    root /var/www/configurador/public;
    index index.php;

    # SSL Configuration (se configurará con Let's Encrypt)
    ssl_certificate /etc/letsencrypt/live/tudominio.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/tudominio.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    # Security Headers
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss application/json application/javascript;

    # Client body size (para uploads)
    client_max_body_size 25M;

    # Logging
    access_log /var/log/nginx/configurador_access.log;
    error_log /var/log/nginx/configurador_error.log;

    # Laravel routes
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;

        # Timeouts para requests largos
        fastcgi_read_timeout 300;
    }

    # Deny access to .htaccess and .env
    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Negar acceso a archivos sensibles
    location ~* \.(env|log|git|gitignore)$ {
        deny all;
        return 404;
    }

    # Cache static files
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Archivos 3D con control
    location ~ ^/storage/3d-models/ {
        expires 1h;
        add_header Cache-Control "private";
    }
}
```

### 2. Activar Configuración

```bash
# Crear symlink
sudo ln -s /etc/nginx/sites-available/configurador /etc/nginx/sites-enabled/

# Verificar sintaxis
sudo nginx -t

# Si OK, recargar
sudo systemctl reload nginx
```

---

## 🔒 Configuración de SSL

### Opción A: Let's Encrypt (Recomendado - Gratis)

```bash
# Instalar Certbot
sudo apt-get install certbot python3-certbot-nginx -y

# Obtener certificado
sudo certbot --nginx -d tudominio.com -d www.tudominio.com

# Responder:
# - Email: tu@email.com
# - Términos: Agree
# - Share email: No
# - Redirect HTTP to HTTPS: Yes

# Verificar renovación automática
sudo certbot renew --dry-run

# Certbot creará un cron job automático para renovar
```

### Opción B: Certificado Propio

Si tienes certificados propios:

```bash
# Copiar certificados
sudo mkdir -p /etc/ssl/certs/configurador
sudo cp tudominio.crt /etc/ssl/certs/configurador/
sudo cp tudominio.key /etc/ssl/certs/configurador/
sudo cp ca-bundle.crt /etc/ssl/certs/configurador/

# Configurar permisos
sudo chmod 600 /etc/ssl/certs/configurador/tudominio.key
sudo chmod 644 /etc/ssl/certs/configurador/tudominio.crt

# Actualizar configuración de Nginx
sudo nano /etc/nginx/sites-available/configurador

# Cambiar rutas SSL
ssl_certificate /etc/ssl/certs/configurador/tudominio.crt;
ssl_certificate_key /etc/ssl/certs/configurador/tudominio.key;

# Recargar Nginx
sudo systemctl reload nginx
```

---

## ⚡ Optimizaciones

### 1. Configurar PHP-FPM

```bash
sudo nano /etc/php/8.2/fpm/pool.d/www.conf
```

**Ajustes recomendados**:

```ini
; Process manager
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 500

; Timeouts
request_terminate_timeout = 300

; Resource limits
php_admin_value[memory_limit] = 256M
php_admin_value[upload_max_filesize] = 25M
php_admin_value[post_max_size] = 25M
php_admin_value[max_execution_time] = 300
```

**Reiniciar PHP-FPM**:

```bash
sudo systemctl restart php8.2-fpm
```

### 2. Configurar OPcache

```bash
sudo nano /etc/php/8.2/fpm/conf.d/10-opcache.ini
```

```ini
opcache.enable=1
opcache.enable_cli=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
opcache.validate_timestamps=0
opcache.save_comments=1
```

**Reiniciar**:

```bash
sudo systemctl restart php8.2-fpm
```

### 3. Configurar MySQL

```bash
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
```

**Añadir al final**:

```ini
[mysqld]
max_connections = 200
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
innodb_flush_method = O_DIRECT
query_cache_type = 1
query_cache_size = 64M
```

**Reiniciar MySQL**:

```bash
sudo systemctl restart mysql
```

### 4. Configurar Redis

```bash
sudo nano /etc/redis/redis.conf
```

**Ajustes**:

```conf
maxmemory 256mb
maxmemory-policy allkeys-lru
```

**Reiniciar**:

```bash
sudo systemctl restart redis-server
```

---

## 📨 Queue Workers

### 1. Configurar Supervisor

```bash
sudo nano /etc/supervisor/conf.d/configurador-worker.conf
```

**Contenido**:

```ini
[program:configurador-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/configurador/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/configurador/storage/logs/worker.log
stopwaitsecs=3600
```

### 2. Iniciar Workers

```bash
# Recargar configuración
sudo supervisorctl reread
sudo supervisorctl update

# Iniciar workers
sudo supervisorctl start configurador-worker:*

# Verificar estado
sudo supervisorctl status
```

### 3. Comandos Útiles

```bash
# Ver logs
sudo supervisorctl tail -f configurador-worker:00 stdout

# Reiniciar workers
sudo supervisorctl restart configurador-worker:*

# Detener workers
sudo supervisorctl stop configurador-worker:*
```

---

## 💾 Backup Automatizado

### 1. Crear Script de Backup

```bash
sudo nano /var/www/configurador/scripts/backup.sh
```

**Contenido**:

```bash
#!/bin/bash

# Configuración
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backups/configurador"
DB_NAME="configurador_prod"
DB_USER="configurador_user"
DB_PASS="PASSWORD_AQUI"
APP_DIR="/var/www/configurador"

# Crear directorio si no existe
mkdir -p ${BACKUP_DIR}

# Backup de Base de Datos
echo "Backing up database..."
mysqldump -u ${DB_USER} -p${DB_PASS} ${DB_NAME} | gzip > ${BACKUP_DIR}/db_${DATE}.sql.gz

# Backup de Archivos (storage)
echo "Backing up files..."
tar -czf ${BACKUP_DIR}/storage_${DATE}.tar.gz ${APP_DIR}/storage/app/public

# Limpiar backups antiguos (mantener últimos 30 días)
echo "Cleaning old backups..."
find ${BACKUP_DIR} -name "*.gz" -mtime +30 -delete
find ${BACKUP_DIR} -name "*.tar.gz" -mtime +30 -delete

echo "Backup completed: ${DATE}"

# (Opcional) Subir a S3
# aws s3 cp ${BACKUP_DIR}/db_${DATE}.sql.gz s3://tu-bucket/backups/
# aws s3 cp ${BACKUP_DIR}/storage_${DATE}.tar.gz s3://tu-bucket/backups/
```

### 2. Dar Permisos

```bash
sudo chmod +x /var/www/configurador/scripts/backup.sh
```

### 3. Configurar Cron

```bash
sudo crontab -e
```

**Añadir**:

```bash
# Backup diario a las 2 AM
0 2 * * * /var/www/configurador/scripts/backup.sh >> /var/log/backup.log 2>&1
```

### 4. Script de Restore

```bash
sudo nano /var/www/configurador/scripts/restore.sh
```

**Contenido**:

```bash
#!/bin/bash

if [ -z "$1" ]; then
    echo "Uso: ./restore.sh FECHA (ej: 20251106_020000)"
    exit 1
fi

DATE=$1
BACKUP_DIR="/backups/configurador"
DB_NAME="configurador_prod"
DB_USER="configurador_user"
DB_PASS="PASSWORD_AQUI"
APP_DIR="/var/www/configurador"

# Restore Database
echo "Restoring database from ${DATE}..."
gunzip < ${BACKUP_DIR}/db_${DATE}.sql.gz | mysql -u ${DB_USER} -p${DB_PASS} ${DB_NAME}

# Restore Files
echo "Restoring files from ${DATE}..."
tar -xzf ${BACKUP_DIR}/storage_${DATE}.tar.gz -C /tmp
sudo rm -rf ${APP_DIR}/storage/app/public/*
sudo mv /tmp/${APP_DIR}/storage/app/public/* ${APP_DIR}/storage/app/public/

echo "Restore completed"
```

```bash
sudo chmod +x /var/www/configurador/scripts/restore.sh
```

---

## 📊 Monitoreo

### 1. Health Check Endpoint

Ya está configurado en Laravel 11: `/up`

**Verificar**:

```bash
curl https://tudominio.com/up
# Debe retornar 200 OK
```

### 2. Monitoreo con UptimeRobot (Recomendado)

1. Crear cuenta en [uptimerobot.com](https://uptimerobot.com)
2. Añadir monitor:
   - **Type**: HTTP(s)
   - **URL**: https://tudominio.com/up
   - **Interval**: 5 minutes
   - **Alert Contacts**: tu email

### 3. Logs de Aplicación

```bash
# Ver logs en tiempo real
tail -f /var/www/configurador/storage/logs/laravel.log

# Ver logs de seguridad
tail -f /var/www/configurador/storage/logs/security.log

# Ver logs de Nginx
sudo tail -f /var/log/nginx/configurador_error.log
```

### 4. Monitoreo de Recursos

```bash
# Uso de CPU y memoria
htop

# Uso de disco
df -h

# Conexiones MySQL
mysql -u root -p -e "SHOW PROCESSLIST;"

# Estado de servicios
sudo systemctl status nginx php8.2-fpm mysql redis-server supervisor
```

---

## 🐛 Troubleshooting

### Problema: 500 Internal Server Error

**Verificar logs**:

```bash
tail -50 /var/www/configurador/storage/logs/laravel.log
tail -50 /var/log/nginx/configurador_error.log
```

**Causas comunes**:
- Permisos incorrectos en `storage/` y `bootstrap/cache/`
- `.env` mal configurado
- APP_KEY no generado
- Composer dependencies no instaladas

**Solución**:

```bash
cd /var/www/configurador
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
php artisan key:generate
php artisan config:clear
```

### Problema: "CSRF token mismatch"

**Causa**: Sesiones no funcionan correctamente

**Solución**:

```bash
# Verificar permisos
sudo chmod -R 775 storage/framework/sessions

# Verificar configuración en .env
SESSION_DRIVER=database
SESSION_ENCRYPT=true

# Limpiar cache
php artisan cache:clear
php artisan config:clear
```

### Problema: "Too Many Requests" (429)

**Causa**: Rate limiting activado

**Solución temporal**:

```env
# En .env aumentar límites
API_RATE_LIMIT=120
API_PRICE_RATE_LIMIT=60
```

### Problema: Archivos 3D no cargan

**Verificar**:

```bash
# Symlink existe
ls -la /var/www/configurador/public/storage

# Si no existe
php artisan storage:link

# Permisos
sudo chmod -R 755 /var/www/configurador/storage/app/public
```

### Problema: Workers no procesan jobs

**Verificar**:

```bash
# Estado de supervisor
sudo supervisorctl status

# Si están detenidos
sudo supervisorctl start configurador-worker:*

# Ver logs
sudo supervisorctl tail -f configurador-worker:00 stdout
```

---

## 🔄 Rollback

### En Caso de Problemas

```bash
# 1. Detener aplicación
sudo systemctl stop nginx

# 2. Restaurar backup
cd /var/www/configurador/scripts
./restore.sh 20251106_020000  # Usar fecha de backup funcional

# 3. Restaurar código (si usas Git)
git reset --hard HEAD~1
git pull origin main

# 4. Reinstalar dependencias
composer install --no-dev
npm ci && npm run build

# 5. Limpiar cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 6. Volver a cachear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 7. Reiniciar servicios
sudo systemctl start nginx
sudo supervisorctl restart configurador-worker:*
```

---

## ✅ Checklist Final

### Post-Deploy Verification

- [ ] Sitio accesible vía HTTPS (https://tudominio.com)
- [ ] Redirect HTTP → HTTPS funciona
- [ ] Login funciona (/login)
- [ ] Dashboard carga (/admin)
- [ ] API responde (GET /api/v1/configurator/products/1/config)
- [ ] Headers de seguridad presentes (curl -I)
- [ ] Health check responde 200 (/up)
- [ ] Logs no muestran errores críticos
- [ ] Workers de cola están corriendo
- [ ] Backup cron job configurado
- [ ] Certificado SSL válido y renovación automática
- [ ] Email de prueba funciona
- [ ] Subida de archivos 3D funciona
- [ ] Tests pasan (php artisan test)

---

## 📚 Recursos Adicionales

- [Laravel Deployment](https://laravel.com/docs/11.x/deployment)
- [Nginx Configuration](https://www.nginx.com/resources/wiki/)
- [Let's Encrypt](https://letsencrypt.org/docs/)
- [PHP-FPM Tuning](https://www.php.net/manual/en/install.fpm.configuration.php)

---

## 📞 Soporte

Si encuentras problemas durante el deployment:

- **Issues**: GitHub Issues
- **Email**: devops@tudominio.com
- **Documentación**: Ver [README.md](README.md)

---

**Guía creada**: 2025-11-06
**Versión**: 1.0
**Estado**: ✅ Completa y probada
