#!/bin/bash

# Database Backup Script
# Usage: ./scripts/backup-database.sh

set -e

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuración
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"
BACKUP_DIR="${PROJECT_DIR}/storage/backups"
DATE=$(date +%Y%m%d_%H%M%S)
RETENTION_DAYS=30

echo -e "${GREEN}📦 Database Backup Script${NC}"
echo "=================================="

# Crear directorio de backups si no existe
mkdir -p "$BACKUP_DIR"

# Cargar variables de entorno
if [ -f "${PROJECT_DIR}/.env" ]; then
    export $(cat "${PROJECT_DIR}/.env" | grep -v '^#' | xargs)
else
    echo -e "${RED}❌ Error: .env file not found${NC}"
    exit 1
fi

# Verificar variables necesarias
if [ -z "$DB_DATABASE" ] || [ -z "$DB_USERNAME" ]; then
    echo -e "${RED}❌ Error: DB_DATABASE or DB_USERNAME not set in .env${NC}"
    exit 1
fi

BACKUP_FILE="${BACKUP_DIR}/backup_${DB_DATABASE}_${DATE}.sql"

echo -e "${YELLOW}🔄 Creating backup...${NC}"
echo "Database: $DB_DATABASE"
echo "File: $BACKUP_FILE"

# Crear backup según el driver
if [ "$DB_CONNECTION" = "mysql" ]; then
    if [ -z "$DB_PASSWORD" ]; then
        mysqldump -h "${DB_HOST:-localhost}" -P "${DB_PORT:-3306}" -u "$DB_USERNAME" "$DB_DATABASE" > "$BACKUP_FILE"
    else
        mysqldump -h "${DB_HOST:-localhost}" -P "${DB_PORT:-3306}" -u "$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" > "$BACKUP_FILE"
    fi
elif [ "$DB_CONNECTION" = "pgsql" ]; then
    PGPASSWORD="$DB_PASSWORD" pg_dump -h "${DB_HOST:-localhost}" -p "${DB_PORT:-5432}" -U "$DB_USERNAME" "$DB_DATABASE" > "$BACKUP_FILE"
else
    echo -e "${RED}❌ Error: Unsupported database driver: $DB_CONNECTION${NC}"
    exit 1
fi

# Verificar que el backup se creó correctamente
if [ ! -f "$BACKUP_FILE" ]; then
    echo -e "${RED}❌ Error: Backup file was not created${NC}"
    exit 1
fi

# Comprimir backup
echo -e "${YELLOW}🗜️  Compressing backup...${NC}"
gzip "$BACKUP_FILE"
BACKUP_FILE="${BACKUP_FILE}.gz"

# Obtener tamaño del backup
BACKUP_SIZE=$(du -h "$BACKUP_FILE" | cut -f1)

echo -e "${GREEN}✅ Backup created successfully!${NC}"
echo "File: $BACKUP_FILE"
echo "Size: $BACKUP_SIZE"

# Limpiar backups antiguos
echo -e "${YELLOW}🧹 Cleaning old backups (older than $RETENTION_DAYS days)...${NC}"
DELETED_COUNT=$(find "$BACKUP_DIR" -name "backup_*.sql.gz" -mtime +$RETENTION_DAYS -delete -print | wc -l)

if [ "$DELETED_COUNT" -gt 0 ]; then
    echo -e "${GREEN}✅ Deleted $DELETED_COUNT old backup(s)${NC}"
else
    echo "No old backups to delete"
fi

# Mostrar backups existentes
echo ""
echo -e "${YELLOW}📁 Existing backups:${NC}"
ls -lh "$BACKUP_DIR" | grep "backup_" || echo "No backups found"

echo ""
echo -e "${GREEN}✅ Backup process completed!${NC}"

# Opcional: Enviar notificación (descomentar y configurar)
# curl -X POST https://hooks.slack.com/services/YOUR/WEBHOOK/URL \
#   -H 'Content-Type: application/json' \
#   -d "{\"text\":\"✅ Database backup completed: $BACKUP_FILE ($BACKUP_SIZE)\"}"
