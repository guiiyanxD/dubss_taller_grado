# 🐳 DUBSS - Configuración Docker

Sistema de Seguimiento de Trámites de Becas Socioeconómicas (DUBSS)

## 📋 Requisitos Previos

- Docker Desktop instalado y corriendo
- Windows 11 (o 10 con WSL2)
- Al menos 4GB de RAM disponible para Docker
- Puertos disponibles: 8000, 5173, 5432, 6379, 1025, 8025

## 🚀 Instalación Inicial

### 1. Copiar archivos de configuración

Copia todos los archivos Docker a la raíz de tu proyecto Laravel:

```
C:\laragon\www\dubss_backend_taller_de_grado\
├── docker-compose.yml
├── docker/
│   ├── php/
│   │   ├── Dockerfile
│   │   └── php.ini
│   └── nginx/
│       └── default.conf
├── .dockerignore
├── docker-up.ps1
├── docker-down.ps1
├── docker-migrate.ps1
└── docker-test.ps1
```

### 2. Configurar archivo .env

Reemplaza tu `.env` actual con el contenido de `.env.docker`:

```powershell
# Copia el contenido de .env.docker a .env
Copy-Item .env.docker .env
```

O manualmente actualiza estas líneas en tu `.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=dubss_db
DB_USERNAME=dubss_user
DB_PASSWORD=dubss_password

REDIS_HOST=redis
MAIL_HOST=mailpit
MAIL_PORT=1025
```

### 3. Iniciar el entorno

```powershell
# Opción A: Usar el script automatizado (recomendado)
.\docker-up.ps1

# Opción B: Comandos manuales
docker-compose build
docker-compose up -d
docker-compose exec app composer install
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate

# Dar permisos a storage y cache
docker-compose exec app chmod -R 775 storage
docker-compose exec app chmod -R 775 bootstrap/cache
# Cambiar el propietario a www-data
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
```

## 🌐 URLs Disponibles

- **Aplicación Laravel**: http://localhost:8000
- **Vite Dev Server**: http://localhost:5173
- **Mailpit (emails)**: http://localhost:8025
- **PostgreSQL**: http://localhost:5432

## 🛠️ Comandos Útiles

### Gestión de Contenedores

```powershell
# Ver logs en tiempo real
docker-compose logs -f

# Ver logs de un servicio específico
docker-compose logs -f app

# Reiniciar todos los contenedores
docker-compose restart

# Reiniciar un contenedor específico
docker-compose restart app

# Detener contenedores
.\docker-down.ps1
# o
docker-compose down

# Detener y eliminar volúmenes (⚠️ BORRA LA BD)
docker-compose down -v
```

### Artisan Commands

```powershell
# Ejecutar cualquier comando Artisan
docker-compose exec app php artisan [comando]

# Ejemplos:
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed
docker-compose exec app php artisan make:model Beca
docker-compose exec app php artisan route:list
docker-compose exec app php artisan cache:clear
```

### Base de Datos

```powershell
# Ejecutar migraciones
.\docker-migrate.ps1
# o
docker-compose exec app php artisan migrate

# Revertir última migración
docker-compose exec app php artisan migrate:rollback

# Refrescar BD (⚠️ BORRA TODO)
docker-compose exec app php artisan migrate:fresh

# Ejecutar seeders
docker-compose exec app php artisan db:seed

# Ver estado de migraciones
docker-compose exec app php artisan migrate:status

# Acceder a PostgreSQL directamente
docker-compose exec postgres psql -U dubss_user -d dubss_db
```

### Composer

```powershell
# Instalar paquete
docker-compose exec app composer require nombre/paquete

# Actualizar dependencias
docker-compose exec app composer update

# Autoload
docker-compose exec app composer dump-autoload
```

### NPM/Vite

```powershell
# Instalar dependencias
docker-compose exec node npm install

# Compilar assets (producción)
docker-compose exec node npm run build

# El dev server ya corre automáticamente en puerto 5173
```

### Testing

```powershell
# Ejecutar todos los tests
.\docker-test.ps1
# o
docker-compose exec app php artisan test

# Test específico
.\docker-test.ps1 -Filter "PostulacionTest"

# Con cobertura
docker-compose exec app php artisan test --coverage

# Solo tests de Feature
docker-compose exec app php artisan test --testsuite=Feature

# Solo tests de Unit
docker-compose exec app php artisan test --testsuite=Unit
```

### Debugging

```powershell
# Entrar al contenedor PHP
docker-compose exec app sh

# Entrar al contenedor de PostgreSQL
docker-compose exec postgres sh

# Ver información de PHP
docker-compose exec app php -i

# Verificar extensiones PHP
docker-compose exec app php -m
```

## 🔧 Troubleshooting

### Docker no inicia

```powershell
# Verificar que Docker Desktop esté corriendo
docker info

# Reiniciar Docker Desktop desde la app
```

### Puerto 8000 ocupado

Edita `docker-compose.yml` y cambia:
```yaml
ports:
  - "8001:80"  # Cambiar 8000 por otro puerto
```

### Problemas de permisos

```powershell
# Dar permisos a storage y cache
docker-compose exec app chmod -R 775 storage bootstrap/cache
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
```

### PostgreSQL no conecta

```powershell
# Verificar que el contenedor esté corriendo
docker-compose ps

# Ver logs de PostgreSQL
docker-compose logs postgres

# Recrear el contenedor
docker-compose down
docker-compose up -d postgres
```

### Limpiar todo y empezar de nuevo

```powershell
# ⚠️ ESTO BORRA TODO: contenedores, imágenes, volúmenes
docker-compose down -v
docker-compose build --no-cache
docker-compose up -d
docker-compose exec app php artisan migrate:fresh --seed
```

## 📦 Servicios Incluidos

- **PHP 8.3**: Con extensiones necesarias para Laravel 11
- **Nginx**: Servidor web optimizado
- **PostgreSQL 16**: Base de datos principal
- **Redis 7**: Cache y colas
- **Mailpit**: Testing de emails (captura todos los emails enviados)
- **Node 20**: Para Vite y compilación de assets

## 🔐 Credenciales por Defecto

### PostgreSQL
- **Host**: localhost (desde Windows) / postgres (desde Docker)
- **Puerto**: 5432
- **Base de datos**: dubss_db
- **Usuario**: dubss_user
- **Contraseña**: dubss_password

### Redis
- **Host**: localhost (desde Windows) / redis (desde Docker)
- **Puerto**: 6379
- **Sin contraseña**

## 📚 Próximos Pasos

1. ✅ Docker configurado
2. ⬜ Instalar Laravel Breeze con Inertia
3. ⬜ Instalar Spatie Laravel Permission
4. ⬜ Instalar Scribe (documentación API)
5. ⬜ Crear migraciones desde el script SQL
6. ⬜ Configurar Vue 3 + Vite

---

**Desarrollado para**: DUBSS - Dirección Universitaria de Bienestar Social y Salud
**Stack**: Laravel 11 + Vue 3 + Inertia.js + PostgreSQL + Docker




# Instalar Laravel Breeze
docker-compose exec app composer require laravel/breeze --dev

# Instalar el stack de Inertia + Vue
docker-compose exec app php artisan breeze:install vue

# Instalar dependencias de Node
docker-compose exec node npm install

# Compilar assets
docker-compose exec node npm run build

# Crear las tablas de usuarios y autenticación
docker-compose exec app php artisan migrate
