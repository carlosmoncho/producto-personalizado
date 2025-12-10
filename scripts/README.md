# Scripts de Mantenimiento

## 📦 Backup de Base de Datos

### Uso Manual

```bash
./scripts/backup-database.sh
```

Este script:
- ✅ Crea un backup comprimido de la base de datos
- ✅ Almacena backups en `storage/backups/`
- ✅ Elimina backups antiguos (más de 30 días)
- ✅ Muestra el tamaño del backup creado

### Configuración Automática con Cron

Para ejecutar backups automáticos diariamente a las 2 AM:

```bash
# Editar crontab
crontab -e

# Agregar esta línea (ajustar la ruta):
0 2 * * * /ruta/completa/al/proyecto/scripts/backup-database.sh >> /ruta/completa/al/proyecto/storage/logs/backup.log 2>&1
```

Ejemplo completo:
```bash
0 2 * * * /home/carlos/projects/BeeWall/Hostelking/Personalizados-Hostelking/producto-personalizado/scripts/backup-database.sh >> /home/carlos/projects/BeeWall/Hostelking/Personalizados-Hostelking/producto-personalizado/storage/logs/backup.log 2>&1
```

### Verificar Backups

```bash
# Listar backups existentes
ls -lh storage/backups/

# Ver logs de backup (si se configuró cron)
tail -f storage/logs/backup.log
```

### Restaurar un Backup

```bash
# Descomprimir
gunzip storage/backups/backup_DATABASE_20251106_120000.sql.gz

# Restaurar en MySQL
mysql -u usuario -p nombre_database < storage/backups/backup_DATABASE_20251106_120000.sql

# O con variables de .env
source .env
mysql -h "$DB_HOST" -u "$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" < storage/backups/backup_DATABASE_20251106_120000.sql
```

## ⚙️ Configuración Adicional

### Backup a AWS S3 (Opcional)

Instalar AWS CLI:
```bash
curl "https://awscli.amazonaws.com/awscli-exe-linux-x86_64.zip" -o "awscliv2.zip"
unzip awscliv2.zip
sudo ./aws/install
```

Configurar credenciales:
```bash
aws configure
```

Modificar el script para subir a S3 (agregar al final):
```bash
# En backup-database.sh, antes del final:
echo "☁️  Uploading to S3..."
aws s3 cp "$BACKUP_FILE" s3://mi-bucket/backups/database/
echo "✅ Uploaded to S3"
```

### Notificaciones (Opcional)

#### Slack
Descomentar las líneas al final de `backup-database.sh` y configurar el webhook:

```bash
curl -X POST https://hooks.slack.com/services/YOUR/WEBHOOK/URL \
  -H 'Content-Type: application/json' \
  -d "{\"text\":\"✅ Backup completado: $BACKUP_FILE\"}"
```

#### Email
Agregar al final del script:

```bash
echo "Backup completado: $BACKUP_FILE" | mail -s "Database Backup Success" admin@example.com
```

## 🔒 Seguridad

- ✅ Los backups se crean con permisos restrictivos (600)
- ✅ Los backups antiguos se eliminan automáticamente
- ✅ Las credenciales se leen desde `.env` (nunca las incluyas en el script)
- ⚠️  NO commitear backups al repositorio (ya incluido en `.gitignore`)
- ⚠️  Asegurar que `storage/backups/` tiene permisos apropiados

## 📊 Monitoreo

Para verificar que los backups se están ejecutando correctamente:

```bash
# Ver últimos backups
ls -lt storage/backups/ | head -10

# Ver tamaño total de backups
du -sh storage/backups/

# Verificar logs de cron
grep CRON /var/log/syslog | tail -20
```

## 🆘 Troubleshooting

### Error: "mysqldump: command not found"
```bash
# Ubuntu/Debian
sudo apt-get install mysql-client

# CentOS/RHEL
sudo yum install mysql
```

### Error: "Permission denied"
```bash
chmod +x scripts/backup-database.sh
chmod 755 storage/backups
```

### Backups muy grandes
Ajustar la retención de días editando `RETENTION_DAYS` en el script.
