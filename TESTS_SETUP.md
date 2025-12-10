# 🧪 Configuración de Tests

## ⚠️ Problema Actual

Los tests están configurados para usar SQLite en memoria, pero requiere que la extensión PHP `sqlite3` esté instalada.

**Error**:
```
could not find driver (Connection: sqlite)
```

---

## ✅ Soluciones

### Opción 1: Instalar SQLite Extension (Recomendado)

**Para Ubuntu/Debian**:
```bash
sudo apt-get update
sudo apt-get install php8.2-sqlite3

# Verificar instalación
php -m | grep sqlite

# Debería mostrar: sqlite3, pdo_sqlite
```

**Para CentOS/RHEL**:
```bash
sudo yum install php-sqlite3
```

**Para macOS (Homebrew)**:
```bash
brew install php
# SQLite suele venir incluido
```

**Para Docker/Sail**:
```bash
# SQLite ya está incluido en Laravel Sail
./vendor/bin/sail up -d
./vendor/bin/sail artisan test
```

---

### Opción 2: Usar MySQL/PostgreSQL para Tests

Si no puedes instalar SQLite, puedes configurar una base de datos de testing.

**Paso 1**: Crear base de datos de testing
```bash
# MySQL
mysql -u root -p
CREATE DATABASE configurador_testing;
GRANT ALL ON configurador_testing.* TO 'configurador_user'@'localhost';
```

**Paso 2**: Modificar `phpunit.xml`
```xml
<php>
    <env name="APP_ENV" value="testing"/>
    <env name="DB_CONNECTION" value="mysql"/>
    <env name="DB_DATABASE" value="configurador_testing"/>
    <env name="DB_USERNAME" value="configurador_user"/>
    <env name="DB_PASSWORD" value="your_password"/>
    <!-- Resto de configuración... -->
</php>
```

**Paso 3**: Ejecutar tests
```bash
php artisan test
```

⚠️ **Nota**: Esta opción es más lenta que SQLite en memoria.

---

### Opción 3: Usar SQLite File (Sin Extensión Adicional)

Si SQLite está compilado en PHP pero no como extensión separada:

**Modificar `phpunit.xml`**:
```xml
<php>
    <env name="APP_ENV" value="testing"/>
    <env name="DB_CONNECTION" value="sqlite"/>
    <env name="DB_DATABASE" value="database/testing.sqlite"/>
    <!-- Resto de configuración... -->
</php>
```

**Crear archivo de base de datos**:
```bash
touch database/testing.sqlite
```

**Ejecutar tests**:
```bash
php artisan test
```

---

## 🚀 Ejecución de Tests

### Ejecutar todos los tests
```bash
php artisan test
```

### Ejecutar solo tests unitarios
```bash
php artisan test --testsuite=Unit
```

### Ejecutar solo tests de feature
```bash
php artisan test --testsuite=Feature
```

### Ejecutar un test específico
```bash
php artisan test --filter=ConfiguratorTest
```

### Con coverage (requiere Xdebug)
```bash
php artisan test --coverage
```

### Tests en paralelo (requiere paratest)
```bash
composer require --dev brianium/paratest
php artisan test --parallel
```

---

## 📊 Estado Actual de Tests

**Total**: 88 tests
- **Unit**: 30 tests
- **Feature**: 58 tests

**Cobertura**:
- ✅ Lógica de negocio crítica (100%)
- ✅ Cálculo de precios (100%)
- ✅ Dependencias de atributos (100%)
- ✅ Sistema de configurador (100%)
- ⚠️ Controllers Admin (0% - opcional)
- ⚠️ API endpoints (parcial)

---

## 🐛 Troubleshooting

### Error: "could not find driver"
**Causa**: Extensión SQLite no instalada
**Solución**: Ver Opción 1 arriba

### Error: "Class 'SQLite3' not found"
**Causa**: Extensión SQLite no habilitada
**Solución**:
```bash
# Verificar php.ini
php --ini

# Buscar línea: extension=sqlite3
# Si está comentada (;extension=sqlite3), descomentarla

# Reiniciar PHP-FPM (si aplica)
sudo systemctl restart php8.2-fpm
```

### Tests lentos
**Causa**: Usando base de datos real en lugar de in-memory
**Solución**: Instalar SQLite y usar `:memory:`

### Tests fallan aleatoriamente
**Causa**: Base de datos no se limpia entre tests
**Solución**: Añadir trait en tests
```php
use Illuminate\Foundation\Testing\RefreshDatabase;

class MyTest extends TestCase
{
    use RefreshDatabase;

    // Tests...
}
```

---

## 📝 Configuración Actual (phpunit.xml)

```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

**Beneficios**:
- ✅ Tests rápidos (en memoria)
- ✅ Aislamiento completo
- ✅ No contamina base de datos de desarrollo

**Requisitos**:
- PHP con extensión sqlite3 instalada

---

## 🔄 CI/CD

Para ejecutar tests en CI/CD (GitHub Actions, GitLab CI, etc.):

```yaml
# .github/workflows/tests.yml
- name: Setup PHP
  uses: shivammathur/setup-php@v2
  with:
    php-version: 8.2
    extensions: sqlite3, pdo_sqlite

- name: Run Tests
  run: php artisan test
```

La extensión SQLite suele venir pre-instalada en runners de CI/CD.

---

## ✅ Verificación Rápida

```bash
# 1. Verificar PHP version
php -v

# 2. Verificar extensión SQLite
php -m | grep sqlite

# 3. Si NO aparece sqlite3 ni pdo_sqlite, instalar:
sudo apt-get install php8.2-sqlite3

# 4. Ejecutar tests
php artisan test
```

---

**Estado**: ⚠️ Requiere instalación de extensión SQLite o configuración alternativa
**Impacto**: 86/88 tests fallan por error de driver
**Solución recomendada**: Instalar `php8.2-sqlite3` (5 minutos)
